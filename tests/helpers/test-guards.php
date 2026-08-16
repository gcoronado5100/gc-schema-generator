<?php
/**
 * Behavioral tests for gcp_schema_is_yoast_active(), gcp_schema_has_inventory()
 * and gcp_schema_vehicle_post_type().
 *
 * Standalone (no PHPUnit): run with `php tests/helpers/test-guards.php`.
 * Exit code 0 = all pass, 1 = a failure. Asserts the graceful-degradation
 * behavior — most importantly that gcp_schema_has_inventory() returns false
 * with zero fatals on inventory-less sibling sites.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcp_schema_guard_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

require_once $root . '/includes/helpers/is-yoast-active.php';
require_once $root . '/includes/helpers/vehicle-post-type.php';
require_once $root . '/includes/helpers/has-inventory.php';

// --- Sibling site: no inventory, no Yoast ---
$GLOBALS['__wp_options']    = array();
$GLOBALS['__wp_post_types'] = array( 'post', 'page' );

$yoast = gcp_schema_is_yoast_active();
gcp_schema_guard_assert( false === $yoast, 'gcp_schema_is_yoast_active() is false when Yoast absent', $tests, $failures );

$inventory = gcp_schema_has_inventory();
gcp_schema_guard_assert( false === $inventory, 'gcp_schema_has_inventory() is false on inventory-less site (no fatal)', $tests, $failures );

// --- Default vehicle post-type name ---
gcp_schema_guard_assert( 'vehicle' === gcp_schema_vehicle_post_type(), "gcp_schema_vehicle_post_type() defaults to 'vehicle'", $tests, $failures );

// --- Inventory present via vehicle CPT ---
$GLOBALS['__wp_post_types'] = array( 'post', 'page', 'vehicle' );
gcp_schema_guard_assert( true === gcp_schema_has_inventory(), 'gcp_schema_has_inventory() is true when vehicle CPT registered', $tests, $failures );

// --- The legacy Motors guard must be gone ---
gcp_schema_guard_assert( ! function_exists( 'gcp_schema_has_motors' ), 'gcp_schema_has_motors() no longer exists (dead Motors path removed)', $tests, $failures );

// --- Yoast present via constant ---
if ( ! defined( 'WPSEO_VERSION' ) ) {
	define( 'WPSEO_VERSION', '21.0' );
}
gcp_schema_guard_assert( true === gcp_schema_is_yoast_active(), 'gcp_schema_is_yoast_active() is true when WPSEO_VERSION defined', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
