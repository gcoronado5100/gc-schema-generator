<?php
/**
 * Sub-process probe: Yoast OFF + vehicle single → ONE <script> whose @graph
 * holds the dealer node AND a Product/Car node whose seller.@id matches the
 * dealer @id (Mode B context-aware emission).
 *
 * Echoes 'OK' on success or a compact 'BAD:*' verdict. The leading underscore
 * keeps this file OUT of the suite's `test-*.php` glob.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

// Yoast OFF, front-end, on a singular vehicle with the CPT registered.
$GLOBALS['__wp_is_admin']           = false;
$GLOBALS['__wp_is_front_page']      = false;
$GLOBALS['__wp_is_singular']        = 'vehicle';
$GLOBALS['__wp_post_types']         = array( 'post', 'page', 'vehicle' );
$GLOBALS['__wp_queried_object_id']  = 301;

$GLOBALS['__wp_options']['gcp_schema_settings'] = array(
	'name'             => 'Demo Motors',
	'street_address'   => '1 Main St',
	'address_locality' => 'Townsville',
	'address_region'   => 'ON',
	'postal_code'      => 'A1A1A1',
	'address_country'  => 'CA',
	'currency'         => 'CAD',
);

$GLOBALS['__wp_titles'][301]     = '2019 Honda Civic LX';
$GLOBALS['__wp_permalinks'][301] = 'https://example.com/listings/2019-honda-civic-lx/';
$GLOBALS['__wp_thumbnails'][301] = 'https://example.com/civic.jpg';
$GLOBALS['__wp_post_meta'][301]  = array(
	'_e1ci_price'  => '15995',
	'_e1ci_status' => 'available',
);

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
		'/includes/schema/build-dealer-parts.php',
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
	echo 'RED';
	return;
}

ob_start();
gcp_schema_output_schema();
$out = ob_get_clean();

if ( 1 !== substr_count( $out, '<script type="application/ld+json">' ) ) {
	echo 'BAD:script-count';
	return;
}
if ( ! preg_match( '#<script type="application/ld\+json">(.*?)</script>#s', $out, $m ) ) {
	echo 'BAD:no-payload';
	return;
}
$data = json_decode( trim( $m[1] ), true );
if ( ! is_array( $data ) || ! isset( $data['@graph'] ) || 2 !== count( $data['@graph'] ) ) {
	echo 'BAD:node-count';
	return;
}

$dealer_id = '';
$product   = null;
foreach ( $data['@graph'] as $node ) {
	$type = isset( $node['@type'] ) ? (array) $node['@type'] : array();
	if ( in_array( 'AutoDealer', $type, true ) ) {
		$dealer_id = isset( $node['@id'] ) ? $node['@id'] : '';
	}
	if ( in_array( 'Product', $type, true ) && in_array( 'Car', $type, true ) ) {
		$product = $node;
	}
}

if ( '' === $dealer_id || null === $product ) {
	echo 'BAD:missing-node';
	return;
}
if ( ! isset( $product['offers']['seller']['@id'] ) || $product['offers']['seller']['@id'] !== $dealer_id ) {
	echo 'BAD:seller-mismatch';
	return;
}
echo 'OK';
