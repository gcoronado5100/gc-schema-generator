<?php
/**
 * Sub-process probe: is_admin() guard — full settings but in wp-admin →
 * gcp_schema_output_schema() emits NOTHING (front-end-only guard, Pattern 2).
 *
 * Run in isolation by test-output-schema.php via shell_exec. Echoes 'EMPTY'
 * when the orchestrator output is the empty string, else 'NONEMPTY'.
 *
 * RED tolerance: when gcp_schema_output_schema() is not built, the probe echoes
 * 'NONEMPTY' so the parent's is_admin assertion (which expects 'EMPTY') fails RED.
 *
 * The leading underscore keeps this file OUT of the suite's `test-*.php` glob.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

// In wp-admin, with full settings — the is_admin() guard must still emit nothing.
$GLOBALS['__wp_is_admin']                 = true;
$GLOBALS['__wp_is_front_page']            = false;
$GLOBALS['__wp_options']['gcp_schema_settings'] = array(
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

foreach (
	array(
		'/includes/helpers/get-settings.php',
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

if ( ! function_exists( 'gcp_schema_output_schema' ) ) {
	echo 'NONEMPTY'; // RED default — parent is_admin assertion (wants EMPTY) fails.
	return;
}

ob_start();
gcp_schema_output_schema();
$out = ob_get_clean();
echo '' === $out ? 'EMPTY' : 'NONEMPTY';
