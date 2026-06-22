<?php
/**
 * Sub-process probe: Yoast OFF but settings incomplete (dealer node suppressed)
 * → gcsg_output_schema() emits NOTHING — no empty <script> (D-07 / BIZ-03).
 *
 * Run in isolation by test-output-schema.php via shell_exec (own static cache).
 * Echoes 'EMPTY' when the orchestrator output is the empty string, else
 * 'NONEMPTY'.
 *
 * RED tolerance: when gcsg_output_schema() is not built, the probe echoes
 * 'NONEMPTY' so the parent's D-07 assertion (which expects 'EMPTY') fails RED.
 *
 * The leading underscore keeps this file OUT of the suite's `test-*.php` glob.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

// Yoast OFF, front-end, non-admin, but settings empty → dealer node suppressed.
$GLOBALS['__wp_is_admin']                 = false;
$GLOBALS['__wp_is_front_page']            = true;
$GLOBALS['__wp_options']['gcsg_settings'] = array(
	'name'                  => '', // missing name → D-06 suppression → zero nodes.
	'legal_name'            => '',
	'logo'                  => '',
	'image'                 => '',
	'telephone'             => '',
	'price_range'           => '',
	'street_address'        => '',
	'address_locality'      => '',
	'address_region'        => '',
	'postal_code'           => '',
	'address_country'       => '',
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

if ( ! function_exists( 'gcsg_output_schema' ) ) {
	echo 'NONEMPTY'; // RED default — parent D-07 assertion (wants EMPTY) fails.
	return;
}

ob_start();
gcsg_output_schema();
$out = ob_get_clean();
echo '' === $out ? 'EMPTY' : 'NONEMPTY';
