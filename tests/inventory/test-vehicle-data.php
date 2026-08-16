<?php
/**
 * Behavioral tests for the inventory resolver layer (Phase 2).
 *
 * Covers gcp_schema_get_vehicle_data() and its sub-helpers: the contact-for-
 * pricing placeholder gate, decorated-number cleanup, the three gallery meta
 * storage shapes, availability mapping, and graceful degradation when meta /
 * terms / e1connect are absent.
 *
 * Standalone (no PHPUnit): run with `php tests/inventory/test-vehicle-data.php`.
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

// --- gcp_schema_parse_list_meta: three storage shapes -----------------------
gcp_schema_assert(
	array( '1', '2', '3' ) === gcp_schema_parse_list_meta( array( '1', '2', '3' ) ),
	'parse-list-meta: PHP array passes through',
	$tests, $failures
);
gcp_schema_assert(
	array( '4', '5' ) === gcp_schema_parse_list_meta( '[4,5]' ),
	'parse-list-meta: JSON string decodes',
	$tests, $failures
);
gcp_schema_assert(
	array( '6', '7', '8' ) === gcp_schema_parse_list_meta( '6, 7 ,8' ),
	'parse-list-meta: CSV string splits and trims',
	$tests, $failures
);
gcp_schema_assert(
	array() === gcp_schema_parse_list_meta( '' ) && array() === gcp_schema_parse_list_meta( null ),
	'parse-list-meta: empty/null → empty array',
	$tests, $failures
);

// --- gcp_schema_clean_number ------------------------------------------------
gcp_schema_assert(
	13577.0 === gcp_schema_clean_number( '13,577 Km' ),
	"clean-number: '13,577 Km' → 13577.0",
	$tests, $failures
);
gcp_schema_assert(
	2019.0 === gcp_schema_clean_number( 2019 ) && 15000.5 === gcp_schema_clean_number( '15000.5' ),
	'clean-number: plain int and decimal string parse',
	$tests, $failures
);
gcp_schema_assert(
	null === gcp_schema_clean_number( '' ) && null === gcp_schema_clean_number( 'N/A' ) && null === gcp_schema_clean_number( null ),
	'clean-number: empty / non-numeric → null (no fabricated 0)',
	$tests, $failures
);

// --- gcp_schema_price_is_real (fallback path — e1ci_price_is_real absent) ---
gcp_schema_assert(
	false === gcp_schema_price_is_real( 1 ) && false === gcp_schema_price_is_real( '1' ),
	'price-is-real: placeholder price 1 is NOT real',
	$tests, $failures
);
gcp_schema_assert(
	true === gcp_schema_price_is_real( 15995 ) && false === gcp_schema_price_is_real( 0 ) && false === gcp_schema_price_is_real( 'call' ),
	'price-is-real: real price passes; 0 / non-numeric fail',
	$tests, $failures
);

// --- gcp_schema_map_availability --------------------------------------------
gcp_schema_assert(
	'https://schema.org/InStock' === gcp_schema_map_availability( 'available' )
		&& 'https://schema.org/InStock' === gcp_schema_map_availability( 'reserved' )
		&& 'https://schema.org/InStock' === gcp_schema_map_availability( '' ),
	'map-availability: available/reserved/empty → InStock (mirrors OG)',
	$tests, $failures
);
gcp_schema_assert(
	'https://schema.org/SoldOut' === gcp_schema_map_availability( 'sold' ),
	'map-availability: sold → SoldOut',
	$tests, $failures
);

// --- Full façade: rich vehicle ----------------------------------------------
$vid = 101;
$GLOBALS['__wp_titles'][ $vid ]     = '2019 Honda Civic LX';
$GLOBALS['__wp_permalinks'][ $vid ] = 'https://example.test/listings/2019-honda-civic-lx/';
$GLOBALS['__wp_posts'][ $vid ]      = (object) array( 'post_content' => '<p>Clean <strong>Civic</strong> with low kms.</p>' );
$GLOBALS['__wp_thumbnails'][ $vid ] = 'https://example.test/f.jpg';
$GLOBALS['__wp_post_meta'][ $vid ]  = array(
	'_e1ci_price'          => '15995',
	'_e1ci_msrp'           => '18500',
	'_e1ci_year'           => '2019',
	'_e1ci_mileage'        => '13,577 Km',
	'_e1ci_vin'            => '2hgfc2f52kh012345',
	'_e1ci_stock'          => 'ame-000101',
	'_e1ci_status'         => 'available',
	'_e1ci_gallery'        => '201,202,999', // 999 is a dead attachment ID.
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
$GLOBALS['__wp_attachment_urls'] = array(
	201 => 'https://example.test/g1.jpg',
	202 => 'https://example.test/g2.jpg',
);

$v = gcp_schema_get_vehicle_data( $vid );
gcp_schema_assert( '2019 Honda Civic LX' === $v['name'], 'facade: name from post title', $tests, $failures );
gcp_schema_assert( 15995.0 === $v['price'], 'facade: real price → float', $tests, $failures );
gcp_schema_assert( 13577 === $v['mileage_km'], "facade: '13,577 Km' → 13577 int", $tests, $failures );
gcp_schema_assert( 2019 === $v['year'] && 4 === $v['doors'], 'facade: year and doors cast to int', $tests, $failures );
gcp_schema_assert( '2HGFC2F52KH012345' === $v['vin'], 'facade: VIN uppercased', $tests, $failures );
gcp_schema_assert(
	array( 'https://example.test/f.jpg', 'https://example.test/g1.jpg', 'https://example.test/g2.jpg' ) === $v['images'],
	'facade: featured first, gallery CSV decoded, dead ID 999 dropped',
	$tests, $failures
);
gcp_schema_assert( 'Honda' === $v['brand'] && 'Civic' === $v['model'] && 'Sedan' === $v['body_type'], 'facade: taxonomy terms resolved', $tests, $failures );
gcp_schema_assert(
	false !== strpos( $v['description'], 'Clean Civic with low kms.' ) && false === strpos( $v['description'], '<' ),
	'facade: description tag-stripped from post content',
	$tests, $failures
);

// --- Placeholder price → null ------------------------------------------------
$vid2 = 102;
$GLOBALS['__wp_titles'][ $vid2 ]    = '2018 Toyota Corolla';
$GLOBALS['__wp_post_meta'][ $vid2 ] = array(
	'_e1ci_price'  => '1', // Contact-for-pricing placeholder.
	'_e1ci_status' => 'sold',
	'_e1ci_model'  => 'Corolla', // Legacy string fallback, no model term.
);
$v2 = gcp_schema_get_vehicle_data( $vid2 );
gcp_schema_assert( null === $v2['price'], 'facade: placeholder price 1 → null (never emitted)', $tests, $failures );
gcp_schema_assert( 'sold' === $v2['status'], 'facade: sold status surfaces', $tests, $failures );
gcp_schema_assert( 'Corolla' === $v2['model'], 'facade: legacy _e1ci_model fallback when model term absent', $tests, $failures );

// --- Yoast meta description fallback -----------------------------------------
$vid3 = 103;
$GLOBALS['__wp_post_meta'][ $vid3 ] = array( '_yoast_wpseo_metadesc' => 'Fallback description.' );
$v3 = gcp_schema_get_vehicle_data( $vid3 );
gcp_schema_assert( 'Fallback description.' === $v3['description'], 'facade: Yoast metadesc fallback when no content', $tests, $failures );

// --- Bare post: everything absent → no fatals, safe defaults ------------------
$v4 = gcp_schema_get_vehicle_data( 999999 );
gcp_schema_assert(
	null === $v4['price'] && null === $v4['year'] && null === $v4['mileage_km']
		&& '' === $v4['vin'] && '' === $v4['brand'] && array() === $v4['images'],
	'facade: missing everything → nulls/empties, zero fatals',
	$tests, $failures
);

// --- Static cache -------------------------------------------------------------
$GLOBALS['__wp_post_meta'][ $vid ]['_e1ci_price'] = '99999';
$v_again = gcp_schema_get_vehicle_data( $vid );
gcp_schema_assert( 15995.0 === $v_again['price'], 'facade: static per-request cache (second read ignores mutated meta)', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
