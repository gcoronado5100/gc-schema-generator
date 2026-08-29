<?php
/**
 * Behavioral tests for gcp_schema_build_vehicle_product_node() + gcp_schema_build_offer().
 *
 * Locks the Product/Car contract: multi-type node, required name+image gate,
 * Offer only on a real price, SoldOut on sold units, UsedCondition always,
 * no priceValidUntil, and never an aggregateRating.
 *
 * Standalone (no PHPUnit): run with `php tests/schema/test-vehicle-product-node.php`.
 * Exit code 0 = all pass, 1 = a failure.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
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

require_once $root . '/includes/inventory/parse-list-meta.php';
require_once $root . '/includes/inventory/clean-number.php';
require_once $root . '/includes/inventory/price-is-real.php';
require_once $root . '/includes/inventory/map-availability.php';
require_once $root . '/includes/inventory/get-vehicle-term.php';
require_once $root . '/includes/inventory/get-vehicle-images.php';
require_once $root . '/includes/inventory/get-vehicle-data.php';
require_once $root . '/includes/schema/build-offer.php';
require_once $root . '/includes/schema/build-vehicle-product-node.php';

$settings  = array( 'currency' => 'CAD' );
$seller_id = 'https://example.test/#/schema/Organization';

// --- Full vehicle -------------------------------------------------------------
$vid = 201;
$GLOBALS['__wp_titles'][ $vid ]     = '2019 Honda Civic LX';
$GLOBALS['__wp_permalinks'][ $vid ] = 'https://example.test/listings/2019-honda-civic-lx/';
$GLOBALS['__wp_posts'][ $vid ]      = (object) array( 'post_content' => 'Great condition Civic.' );
$GLOBALS['__wp_thumbnails'][ $vid ] = 'https://example.test/civic.jpg';
$GLOBALS['__wp_post_meta'][ $vid ]  = array(
	'_e1ci_price'          => '15995',
	'_e1ci_year'           => '2019',
	'_e1ci_mileage'        => '13577',
	'_e1ci_vin'            => '2HGFC2F52KH012345',
	'_e1ci_stock'          => 'ame-000201',
	'_e1ci_status'         => 'available',
	'_e1ci_drivetrain'     => 'FWD',
	'_e1ci_engine'         => '1.6L I4',
	'_e1ci_doors'          => '4',
	'_e1ci_color_exterior' => 'White',
	'_e1ci_color_interior' => 'Black',
);
$GLOBALS['__wp_terms'][ $vid ] = array(
	'make'         => array( (object) array( 'name' => 'Honda' ) ),
	'model'        => array( (object) array( 'name' => 'Civic' ) ),
	'body'         => array( (object) array( 'name' => 'Sedan' ) ),
	'fuel'         => array( (object) array( 'name' => 'Gasoline' ) ),
	'transmission' => array( (object) array( 'name' => 'Automatic' ) ),
);

$node = gcp_schema_build_vehicle_product_node( $vid, $settings, $seller_id );

gcp_schema_assert( array( 'Product', 'Car' ) === $node['@type'], 'node: @type is [Product, Car]', $tests, $failures );
gcp_schema_assert( 'https://example.test/listings/2019-honda-civic-lx/#vehicle' === $node['@id'], 'node: @id = permalink + #vehicle', $tests, $failures );
gcp_schema_assert( '2019 Honda Civic LX' === $node['name'] && ! empty( $node['image'] ), 'node: name + image present', $tests, $failures );
gcp_schema_assert( '2HGFC2F52KH012345' === $node['vehicleIdentificationNumber'], 'node: 17-char VIN emitted', $tests, $failures );
gcp_schema_assert( 'Brand' === $node['brand']['@type'] && 'Honda' === $node['brand']['name'], 'node: brand from make term', $tests, $failures );
gcp_schema_assert(
	'QuantitativeValue' === $node['mileageFromOdometer']['@type']
		&& 13577 === $node['mileageFromOdometer']['value']
		&& 'KMT' === $node['mileageFromOdometer']['unitCode'],
	'node: mileage as QuantitativeValue in KMT',
	$tests, $failures
);
gcp_schema_assert( '2019' === $node['vehicleModelDate'], 'node: vehicleModelDate from year', $tests, $failures );
gcp_schema_assert( 'https://schema.org/UsedCondition' === $node['itemCondition'], 'node: itemCondition always UsedCondition', $tests, $failures );
gcp_schema_assert( 'EngineSpecification' === $node['vehicleEngine']['@type'], 'node: engine as EngineSpecification', $tests, $failures );
gcp_schema_assert( ! array_key_exists( 'aggregateRating', $node ) && ! array_key_exists( 'review', $node ), 'node: never emits rating/review (D-08)', $tests, $failures );

gcp_schema_assert( array( 'Product', 'Car' ) === $node['@type'], 'node: real price → multi-type [Product, Car]', $tests, $failures );

$offer = $node['offers'];
gcp_schema_assert( 'Offer' === $offer['@type'] && '15995.00' === $offer['price'], 'offer: price formatted to 2dp string', $tests, $failures );
gcp_schema_assert( 'CAD' === $offer['priceCurrency'], 'offer: priceCurrency from settings', $tests, $failures );
gcp_schema_assert( 'https://schema.org/InStock' === $offer['availability'], 'offer: available → InStock', $tests, $failures );
gcp_schema_assert( 'https://schema.org/UsedCondition' === $offer['itemCondition'], 'offer: itemCondition UsedCondition', $tests, $failures );
gcp_schema_assert( $seller_id === $offer['seller']['@id'], 'offer: seller @id reference', $tests, $failures );
gcp_schema_assert( ! array_key_exists( 'priceValidUntil', $offer ), 'offer: no priceValidUntil (nothing fabricated)', $tests, $failures );

// --- Sold vehicle: Offer stays, honestly SoldOut -------------------------------
$vid2 = 202;
$GLOBALS['__wp_titles'][ $vid2 ]     = '2017 Mazda 3';
$GLOBALS['__wp_permalinks'][ $vid2 ] = 'https://example.test/listings/2017-mazda-3/';
$GLOBALS['__wp_thumbnails'][ $vid2 ] = 'https://example.test/mazda.jpg';
$GLOBALS['__wp_post_meta'][ $vid2 ]  = array(
	'_e1ci_price'  => '11500',
	'_e1ci_status' => 'sold',
);
$node2 = gcp_schema_build_vehicle_product_node( $vid2, $settings, $seller_id );
gcp_schema_assert( 'https://schema.org/SoldOut' === $node2['offers']['availability'], 'sold: Offer emitted with SoldOut availability', $tests, $failures );

// --- Contact-for-pricing: Car-only node, no Product, no offers ------------------
// Google requires offers|review|aggregateRating on every Product; we never
// fabricate any of them, so the Product type is dropped instead (GSC bug ticket).
$vid3 = 203;
$GLOBALS['__wp_titles'][ $vid3 ]     = '2020 Kia Forte';
$GLOBALS['__wp_permalinks'][ $vid3 ] = 'https://example.test/listings/2020-kia-forte/';
$GLOBALS['__wp_thumbnails'][ $vid3 ] = 'https://example.test/kia.jpg';
$GLOBALS['__wp_post_meta'][ $vid3 ]  = array(
	'_e1ci_price'  => '1', // Placeholder.
	'_e1ci_status' => 'available',
	'_e1ci_vin'    => '1HGCM82633A004352',
);
$node3 = gcp_schema_build_vehicle_product_node( $vid3, $settings, $seller_id );
gcp_schema_assert( ! array_key_exists( 'offers', $node3 ), 'placeholder price: NO offers key (never price 0)', $tests, $failures );
gcp_schema_assert( 'Car' === $node3['@type'], 'placeholder price: @type is plain "Car" (not a Product snippet)', $tests, $failures );
gcp_schema_assert( ! in_array( 'Product', (array) $node3['@type'], true ), 'placeholder price: Product type never attached without an Offer', $tests, $failures );
gcp_schema_assert( '2020 Kia Forte' === $node3['name'] && '1HGCM82633A004352' === $node3['vehicleIdentificationNumber'], 'placeholder price: Car semantics (name, VIN) still emitted', $tests, $failures );

// --- Gate: no image → array() ---------------------------------------------------
$vid4 = 204;
$GLOBALS['__wp_titles'][ $vid4 ]    = '2015 Ford Focus';
$GLOBALS['__wp_post_meta'][ $vid4 ] = array( '_e1ci_price' => '8000' );
gcp_schema_assert( array() === gcp_schema_build_vehicle_product_node( $vid4, $settings, $seller_id ), 'gate: photo-less vehicle → array() (no broken Product)', $tests, $failures );

// --- Gate: no name → array() ----------------------------------------------------
$vid5 = 205;
$GLOBALS['__wp_thumbnails'][ $vid5 ] = 'https://example.test/x.jpg';
gcp_schema_assert( array() === gcp_schema_build_vehicle_product_node( $vid5, $settings, $seller_id ), 'gate: nameless vehicle → array()', $tests, $failures );

// --- Offer directly: empty currency falls back to CAD ---------------------------
$offer_fallback = gcp_schema_build_offer( array( 'price' => 9999.0, 'status' => 'available', 'url' => '' ), array( 'currency' => '' ), $seller_id );
gcp_schema_assert( 'CAD' === $offer_fallback['priceCurrency'], 'offer: empty currency setting → CAD (mirrors e1ci_og_currency)', $tests, $failures );
gcp_schema_assert( ! array_key_exists( 'url', $offer_fallback ), 'offer: empty url omitted', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
