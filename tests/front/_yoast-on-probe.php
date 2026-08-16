<?php
/**
 * Sub-process probe: Yoast ACTIVE → gcp_schema_output_schema() must emit nothing (D-09).
 *
 * Run in isolation by test-output-schema.php via shell_exec so that defining
 * WPSEO_VERSION here does NOT poison the parent test process (a defined constant
 * cannot be un-defined). Echoes exactly 'EMPTY' when the orchestrator output is
 * the empty string, else 'NONEMPTY'.
 *
 * The leading underscore keeps this file OUT of the suite's `test-*.php` glob.
 *
 * RED tolerance: when the Phase-3 production functions do not yet exist, the
 * guarded gcp_schema_output_schema() call is skipped and the probe echoes 'EMPTY' —
 * the documented RED default. The parent's happy-path/D-07 probes are what fail
 * RED; this gate-probe stays neutral so it never masks them.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 ); // tests/front/_probe.php → plugin root.
require_once $root . '/tests/wp-stubs.php';

// Activate the real Yoast gate for THIS process only.
if ( ! defined( 'WPSEO_VERSION' ) ) {
	define( 'WPSEO_VERSION', '99.9' );
}

// Front-end, non-admin; full settings so any missing gate would emit a node.
$GLOBALS['__wp_is_admin']                   = false;
$GLOBALS['__wp_is_front_page']              = true;
$GLOBALS['__wp_options']['gcp_schema_settings']   = array(
	'name'                  => 'Demo Motors',
	'legal_name'            => '',
	'logo'                  => '',
	'image'                 => '',
	'telephone'             => '',
	'price_range'           => '',
	'street_address'        => '1 Main St',
	'address_locality'      => 'Townsville',
	'address_region'        => 'ON',
	'postal_code'           => 'A1A1A1',
	'address_country'       => 'CA',
	'latitude'              => '',
	'longitude'             => '',
	'opening_hours'         => array(),
	'same_as'               => array(),
	'currency'              => '',
	'financing_description' => '',
);

// Load the full Phase-3 include set IF present (define-only files).
foreach (
	array(
		'/includes/helpers/get-settings.php',
		'/includes/helpers/vehicle-post-type.php',
		'/includes/helpers/has-inventory.php',
		'/includes/inventory/parse-list-meta.php',
		'/includes/inventory/clean-number.php',
		'/includes/inventory/price-is-real.php',
		'/includes/inventory/map-availability.php',
		'/includes/inventory/get-vehicle-term.php',
		'/includes/inventory/get-vehicle-images.php',
		'/includes/inventory/get-vehicle-data.php',
		'/includes/schema/build-offer.php',
		'/includes/schema/build-vehicle-product-node.php',
		'/includes/front/should-suppress.php',
		'/includes/helpers/schema-id.php',
		'/includes/helpers/is-yoast-active.php',
		'/includes/helpers/schema-enums.php',
		'/includes/helpers/encode-jsonld.php',
		'/includes/schema/build-dealer-node.php',
		'/includes/schema/assemble-graph.php',
		'/includes/front/detect-context.php',
		'/includes/front/output-schema.php',
	) as $rel
) {
	$abs = $root . $rel;
	if ( file_exists( $abs ) ) {
		require_once $abs;
	}
}

if ( function_exists( 'gcp_schema_output_schema' ) ) {
	ob_start();
	gcp_schema_output_schema();
	$out = ob_get_clean();
	echo '' === $out ? 'EMPTY' : 'NONEMPTY';
} else {
	// RED default: function not built yet.
	echo 'EMPTY';
}
