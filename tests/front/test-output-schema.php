<?php
/**
 * RED tests for gcp_schema_output_schema() and gcp_schema_detect_context().
 *
 * Wave-0 (TDD RED): the orchestrator + context classifier DO NOT EXIST yet, so
 * this test exits 1 (RED). It locks:
 *   - gcp_schema_detect_context() branches (front / listing / other) — Plan 03 Task 1.
 *   - OUT-02 / D-09: Yoast active → emit nothing (isolated sub-process probe).
 *   - OUT-03 happy path: Yoast off + full settings → ONE <script> w/ AutoDealer.
 *   - D-07: zero nodes → no <script> at all.
 *   - is_admin() guard: wp-admin → emit nothing even with full settings.
 *
 * The detect-context branch assertions run IN-PROCESS (they read only the stub
 * globals, no static-cached settings). The output-buffering scenarios run in
 * dedicated `_*-probe.php` sub-processes invoked via shell_exec — this sidesteps
 * both the un-undefinable WPSEO_VERSION constant and the gcp_schema_get_settings()
 * per-process static cache (each contradictory settings state needs a clean
 * process).
 *
 * Standalone (no PHPUnit): run with `php tests/front/test-output-schema.php`.
 * Exit code 0 = all pass, 1 = a failure.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 ); // tests/front/file.php → plugin root.
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcp_schema_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

// Units under test — DO NOT EXIST in Wave 0. RED-guarded requires so missing
// files do not fatal; the first function_exists assertion drives a clean exit(1).
foreach (
	array(
		$root . '/includes/helpers/get-settings.php',
		$root . '/includes/helpers/vehicle-post-type.php',
		$root . '/includes/helpers/schema-id.php',
		$root . '/includes/helpers/is-yoast-active.php',
		$root . '/includes/helpers/schema-enums.php',
		$root . '/includes/helpers/encode-jsonld.php',
		$root . '/includes/schema/build-dealer-parts.php',
		$root . '/includes/schema/build-dealer-node.php',
		$root . '/includes/schema/assemble-graph.php',
		$root . '/includes/front/detect-context.php',
		$root . '/includes/front/output-schema.php',
	) as $f
) {
	if ( file_exists( $f ) ) {
		require_once $f;
	}
}

// --- RED guard ---
gcp_schema_assert( function_exists( 'gcp_schema_output_schema' ), 'gcp_schema_output_schema is defined', $tests, $failures );
gcp_schema_assert( function_exists( 'gcp_schema_detect_context' ), 'gcp_schema_detect_context is defined', $tests, $failures );

// --- gcp_schema_detect_context branches (front / listing / other) — in-process ---
$GLOBALS['__wp_is_front_page'] = true;
$GLOBALS['__wp_is_singular']   = false;
gcp_schema_assert(
	function_exists( 'gcp_schema_detect_context' ) && gcp_schema_detect_context() === 'front',
	"detect-context: is_front_page → 'front'",
	$tests,
	$failures
);

$GLOBALS['__wp_is_front_page'] = false;
$GLOBALS['__wp_is_singular']   = 'vehicle';
gcp_schema_assert(
	function_exists( 'gcp_schema_detect_context' ) && gcp_schema_detect_context() === 'vehicle',
	"detect-context: is_singular('vehicle') → 'vehicle'",
	$tests,
	$failures
);

$GLOBALS['__wp_is_front_page'] = false;
$GLOBALS['__wp_is_singular']   = false;
gcp_schema_assert(
	function_exists( 'gcp_schema_detect_context' ) && gcp_schema_detect_context() === 'other',
	"detect-context: neither → 'other'",
	$tests,
	$failures
);

// Reset the seed globals to a clean (false) context for tidiness.
$GLOBALS['__wp_is_front_page'] = false;
$GLOBALS['__wp_is_singular']   = false;

// --- Output-buffering scenarios via isolated sub-process probes ---
$php = escapeshellarg( PHP_BINARY );

// OUT-02 / D-09: Yoast active → emit nothing.
$yoast_out = shell_exec( $php . ' ' . escapeshellarg( $root . '/tests/front/_yoast-on-probe.php' ) );
gcp_schema_assert(
	function_exists( 'gcp_schema_output_schema' ) && 'EMPTY' === trim( (string) $yoast_out ),
	'OUT-02/D-09: Yoast active → gcp_schema_output_schema() emits nothing',
	$tests,
	$failures
);

// OUT-03 happy path: Yoast off + full settings → ONE <script type="application/ld+json">
// containing an AutoDealer node in @graph. The probe (_happy-path-probe.php) verifies the
// exact `<script type="application/ld+json">` tag count and decodes the JSON payload.
$happy_out = shell_exec( $php . ' ' . escapeshellarg( $root . '/tests/front/_happy-path-probe.php' ) );
gcp_schema_assert(
	'OK' === trim( (string) $happy_out ),
	'OUT-03: Yoast off + full settings → one <script type="application/ld+json"> with AutoDealer in @graph',
	$tests,
	$failures
);

// D-07: zero nodes (suppressed dealer) → no <script> at all.
$d07_out = shell_exec( $php . ' ' . escapeshellarg( $root . '/tests/front/_d07-probe.php' ) );
gcp_schema_assert(
	'EMPTY' === trim( (string) $d07_out ),
	'D-07: incomplete settings → zero nodes → no <script>',
	$tests,
	$failures
);

// is_admin() guard: wp-admin → emit nothing even with full settings.
$admin_out = shell_exec( $php . ' ' . escapeshellarg( $root . '/tests/front/_is-admin-probe.php' ) );
gcp_schema_assert(
	'EMPTY' === trim( (string) $admin_out ),
	'is_admin guard: wp-admin → gcp_schema_output_schema() emits nothing',
	$tests,
	$failures
);

// Mode B context-aware: vehicle single → dealer + Product/Car, seller @id matches.
$vehicle_out = shell_exec( $php . ' ' . escapeshellarg( $root . '/tests/front/_vehicle-single-probe.php' ) );
gcp_schema_assert(
	'OK' === trim( (string) $vehicle_out ),
	'Mode B: vehicle single → dealer + Product/Car nodes, seller.@id === dealer @id',
	$tests,
	$failures
);

// Suppression: noindex vehicle taxonomy archive → nothing at all.
$tax_out = shell_exec( $php . ' ' . escapeshellarg( $root . '/tests/front/_suppress-tax-archive-probe.php' ) );
gcp_schema_assert(
	'EMPTY' === trim( (string) $tax_out ),
	'Suppression: vehicle taxonomy archive (noindex) → no schema output',
	$tests,
	$failures
);

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
