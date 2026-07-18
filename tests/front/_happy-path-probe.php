<?php
/**
 * Sub-process probe: Yoast OFF + full settings → ONE <script> with an
 * AutoDealer node in @graph (OUT-03 happy path).
 *
 * Run in isolation by test-output-schema.php via shell_exec to sidestep the
 * gcp_schema_get_settings() per-process static cache. Echoes a compact verdict line
 * the parent asserts on:
 *   'OK'      — exactly one <script type="application/ld+json"> whose JSON
 *               decodes to a graph containing an AutoDealer node.
 *   anything else (e.g. 'RED' / 'BAD:...') — the happy path is not satisfied.
 *
 * The leading underscore keeps this file OUT of the suite's `test-*.php` glob.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

// Yoast OFF (constant undefined), front-end, non-admin, full settings.
$GLOBALS['__wp_is_admin']                 = false;
$GLOBALS['__wp_is_front_page']            = true;
$GLOBALS['__wp_options']['gcp_schema_settings'] = array(
	'name'                  => 'Demo Motors',
	'legal_name'            => 'Demo Motors Inc.',
	'logo'                  => 'https://example.com/logo.png',
	'image'                 => 'https://example.com/store.jpg',
	'telephone'             => '+1-555-0100',
	'price_range'           => '$$',
	'street_address'        => '1 Main St',
	'address_locality'      => 'Townsville',
	'address_region'        => 'ON',
	'postal_code'           => 'A1A1A1',
	'address_country'       => 'CA',
	'latitude'              => '43.65107',
	'longitude'             => '-79.347015',
	'opening_hours'         => array(
		array( 'day' => 'Monday', 'opens' => '09:00', 'closes' => '18:00' ),
	),
	'same_as'               => array(),
	'currency'              => 'CAD',
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
	echo 'RED'; // function not built yet — parent's happy-path assertion fails (intended).
	return;
}

ob_start();
gcp_schema_output_schema();
$out = ob_get_clean();

// Exactly one opening script tag.
if ( 1 !== substr_count( $out, '<script type="application/ld+json">' ) ) {
	echo 'BAD:script-count';
	return;
}

// Extract the JSON payload and confirm an AutoDealer node lives in @graph.
if ( ! preg_match( '#<script type="application/ld\+json">(.*?)</script>#s', $out, $m ) ) {
	echo 'BAD:no-payload';
	return;
}
$data = json_decode( trim( $m[1] ), true );
if ( ! is_array( $data ) || ! isset( $data['@graph'] ) || ! is_array( $data['@graph'] ) ) {
	echo 'BAD:no-graph';
	return;
}
$found = false;
foreach ( $data['@graph'] as $node ) {
	$type = isset( $node['@type'] ) ? (array) $node['@type'] : array();
	if ( in_array( 'AutoDealer', $type, true ) ) {
		$found = true;
		break;
	}
}
echo $found ? 'OK' : 'BAD:no-autodealer';
