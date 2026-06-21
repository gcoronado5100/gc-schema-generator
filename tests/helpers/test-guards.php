<?php
/**
 * Behavioral tests for gcsg_is_yoast_active() and gcsg_has_motors().
 *
 * Standalone (no PHPUnit): run with `php tests/helpers/test-guards.php`.
 * Exit code 0 = all pass, 1 = a failure. Asserts the graceful-degradation
 * behavior from 01-02-PLAN.md Task 2 — most importantly that gcsg_has_motors()
 * returns false with zero fatals on inventory-less sibling sites (Pitfall 4).
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcsg_guard_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

require_once $root . '/includes/helpers/is-yoast-active.php';
require_once $root . '/includes/helpers/has-motors.php';

// --- Sibling site: no Motors, no Yoast ---
$GLOBALS['__wp_options']    = array();
$GLOBALS['__wp_post_types'] = array( 'post', 'page' );

$yoast = gcsg_is_yoast_active();
gcsg_guard_assert( false === $yoast, 'gcsg_is_yoast_active() is false when Yoast absent', $tests, $failures );

$motors = gcsg_has_motors();
gcsg_guard_assert( false === $motors, 'gcsg_has_motors() is false on inventory-less site (no fatal)', $tests, $failures );

// --- Motors present via listings CPT ---
$GLOBALS['__wp_post_types'] = array( 'post', 'page', 'listings' );
gcsg_guard_assert( true === gcsg_has_motors(), 'gcsg_has_motors() is true when listings CPT registered', $tests, $failures );

// --- Motors present via STM option only (no CPT yet) ---
$GLOBALS['__wp_post_types']               = array( 'post', 'page' );
$GLOBALS['__wp_options']['stm_vehicle_listing_options'] = array( 'make' => 'make' );
gcsg_guard_assert( true === gcsg_has_motors(), 'gcsg_has_motors() is true when stm_vehicle_listing_options exists', $tests, $failures );

// --- Yoast present via constant ---
if ( ! defined( 'WPSEO_VERSION' ) ) {
	define( 'WPSEO_VERSION', '21.0' );
}
gcsg_guard_assert( true === gcsg_is_yoast_active(), 'gcsg_is_yoast_active() is true when WPSEO_VERSION defined', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
