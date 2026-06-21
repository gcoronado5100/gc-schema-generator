<?php
/**
 * Behavioral tests for gcsg_get_settings() and gcsg_schema_id().
 *
 * Standalone (no PHPUnit): run with `php tests/helpers/test-get-settings-and-schema-id.php`.
 * Exit code 0 = all pass, 1 = a failure. Asserts the documented behaviors from
 * 01-02-PLAN.md Task 1.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcsg_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

// Fresh DB: no gcsg_settings option set.
$GLOBALS['__wp_options'] = array();

require_once $root . '/includes/helpers/get-settings.php';
require_once $root . '/includes/helpers/schema-id.php';

// --- gcsg_get_settings: safe defaults when option absent ---
$settings = gcsg_get_settings();
gcsg_assert( is_array( $settings ), 'gcsg_get_settings() returns an array when option is absent', $tests, $failures );
gcsg_assert( array_key_exists( 'name', $settings ) && '' === $settings['name'], 'default name is empty string', $tests, $failures );
gcsg_assert( array_key_exists( 'opening_hours', $settings ) && array() === $settings['opening_hours'], 'default opening_hours is empty array', $tests, $failures );
gcsg_assert( array_key_exists( 'same_as', $settings ) && array() === $settings['same_as'], 'default same_as is empty array', $tests, $failures );
gcsg_assert( array_key_exists( 'currency', $settings ), 'currency key exists in defaults', $tests, $failures );

// --- static cache: option read at most once ---
// First call already cached above. Now change the underlying option; cached
// value must NOT change within the same request.
$GLOBALS['__wp_options']['gcsg_settings'] = array( 'name' => 'Mutated After Cache' );
$cached = gcsg_get_settings();
gcsg_assert( '' === $cached['name'], 'gcsg_get_settings() is static-cached (ignores post-first-call DB change)', $tests, $failures );

// --- gcsg_schema_id: deterministic, absolute, suffix-aware ---
$org1 = gcsg_schema_id( 'Organization' );
$org2 = gcsg_schema_id( 'Organization' );
gcsg_assert( $org1 === $org2, 'gcsg_schema_id() is deterministic for same input', $tests, $failures );
gcsg_assert( 0 === strpos( $org1, 'http' ), 'gcsg_schema_id() returns an absolute URL (has host)', $tests, $failures );
gcsg_assert( false !== strpos( $org1, '#' ), 'gcsg_schema_id() output contains a fragment', $tests, $failures );
gcsg_assert( false !== strpos( $org1, 'Organization' ), 'gcsg_schema_id() incorporates the type', $tests, $failures );

$offer = gcsg_schema_id( 'Offer', '123' );
gcsg_assert( false !== strpos( $offer, '123' ), 'gcsg_schema_id() incorporates the suffix for per-post uniqueness', $tests, $failures );
gcsg_assert( $offer !== gcsg_schema_id( 'Offer', '456' ), 'different suffixes yield different @ids', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
