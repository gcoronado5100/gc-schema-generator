<?php
/**
 * Behavioral tests for gcp_schema_get_settings() and gcp_schema_schema_id().
 *
 * Standalone (no PHPUnit): run with `php tests/helpers/test-get-settings-and-schema-id.php`.
 * Exit code 0 = all pass, 1 = a failure. Asserts the documented behaviors from
 * 01-02-PLAN.md Task 1.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
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

// Fresh DB: no gcp_schema_settings option set.
$GLOBALS['__wp_options'] = array();

require_once $root . '/includes/helpers/get-settings.php';
require_once $root . '/includes/helpers/schema-id.php';

// --- gcp_schema_get_settings: safe defaults when option absent ---
$settings = gcp_schema_get_settings();
gcp_schema_assert( is_array( $settings ), 'gcp_schema_get_settings() returns an array when option is absent', $tests, $failures );
gcp_schema_assert( array_key_exists( 'name', $settings ) && '' === $settings['name'], 'default name is empty string', $tests, $failures );
gcp_schema_assert( array_key_exists( 'opening_hours', $settings ) && array() === $settings['opening_hours'], 'default opening_hours is empty array', $tests, $failures );
gcp_schema_assert( array_key_exists( 'same_as', $settings ) && array() === $settings['same_as'], 'default same_as is empty array', $tests, $failures );
gcp_schema_assert( array_key_exists( 'currency', $settings ), 'currency key exists in defaults', $tests, $failures );

// --- static cache: option read at most once ---
// First call already cached above. Now change the underlying option; cached
// value must NOT change within the same request.
$GLOBALS['__wp_options']['gcp_schema_settings'] = array( 'name' => 'Mutated After Cache' );
$cached = gcp_schema_get_settings();
gcp_schema_assert( '' === $cached['name'], 'gcp_schema_get_settings() is static-cached (ignores post-first-call DB change)', $tests, $failures );

// --- gcp_schema_schema_id: deterministic, absolute, suffix-aware ---
$org1 = gcp_schema_schema_id( 'Organization' );
$org2 = gcp_schema_schema_id( 'Organization' );
gcp_schema_assert( $org1 === $org2, 'gcp_schema_schema_id() is deterministic for same input', $tests, $failures );
gcp_schema_assert( 0 === strpos( $org1, 'http' ), 'gcp_schema_schema_id() returns an absolute URL (has host)', $tests, $failures );
gcp_schema_assert( false !== strpos( $org1, '#' ), 'gcp_schema_schema_id() output contains a fragment', $tests, $failures );
gcp_schema_assert( false !== strpos( $org1, 'Organization' ), 'gcp_schema_schema_id() incorporates the type', $tests, $failures );

$offer = gcp_schema_schema_id( 'Offer', '123' );
gcp_schema_assert( false !== strpos( $offer, '123' ), 'gcp_schema_schema_id() incorporates the suffix for per-post uniqueness', $tests, $failures );
gcp_schema_assert( $offer !== gcp_schema_schema_id( 'Offer', '456' ), 'different suffixes yield different @ids', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
