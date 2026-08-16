<?php
/**
 * Behavioral tests for the Mode A Yoast graph stitching filter.
 *
 * Locks gcp_schema_filter_yoast_graph() + gcp_schema_enrich_yoast_organization():
 * additive-only enrichment of Yoast's Organization, dealer fallback when no
 * Organization piece exists, seller @id always resolving to a node in the same
 * graph, suppression passthrough, and never touching what Yoast built.
 *
 * Standalone (no PHPUnit): run with `php tests/yoast/test-filter-yoast-graph.php`.
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

foreach (
	array(
		'/includes/helpers/get-settings.php',
		'/includes/helpers/schema-id.php',
		'/includes/helpers/schema-enums.php',
		'/includes/helpers/vehicle-post-type.php',
		'/includes/helpers/has-inventory.php',
		'/includes/inventory/parse-list-meta.php',
		'/includes/inventory/clean-number.php',
		'/includes/inventory/price-is-real.php',
		'/includes/inventory/map-availability.php',
		'/includes/inventory/get-vehicle-term.php',
		'/includes/inventory/get-vehicle-images.php',
		'/includes/inventory/get-vehicle-data.php',
		'/includes/schema/build-financing-offer.php',
		'/includes/schema/build-dealer-parts.php',
		'/includes/schema/build-dealer-node.php',
		'/includes/schema/build-offer.php',
		'/includes/schema/build-vehicle-product-node.php',
		'/includes/front/detect-context.php',
		'/includes/front/should-suppress.php',
		'/includes/yoast/enrich-yoast-organization.php',
		'/includes/yoast/filter-yoast-graph.php',
	) as $rel
) {
	require_once $root . $rel;
}

// Full settings so the enrichment has material to add.
$GLOBALS['__wp_options']['gcp_schema_settings'] = array(
	'name'             => 'Demo Motors',
	'telephone'        => '+1-555-0100',
	'price_range'      => '$$',
	'street_address'   => '1 Main St',
	'address_locality' => 'Townsville',
	'address_region'   => 'ON',
	'postal_code'      => 'A1A1A1',
	'address_country'  => 'CA',
	'latitude'         => '43.65107',
	'longitude'        => '-79.347015',
	'opening_hours'    => array(
		array( 'day' => 'Monday', 'opens' => '09:00', 'closes' => '18:00' ),
	),
	'currency'              => 'CAD',
	'financing_description' => 'In-house financing available.',
);

$yoast_org_id = 'https://example.com/#organization';
$yoast_graph  = array(
	array(
		'@type' => 'WebSite',
		'@id'   => 'https://example.com/#website',
	),
	array(
		'@type'  => 'Organization',
		'@id'    => $yoast_org_id,
		'name'   => 'Yoast Set Name',
		'url'    => 'https://example.com/',
		'sameAs' => array( 'https://facebook.com/demo' ),
	),
);

// --- Case 1: Organization present, non-vehicle context ------------------------
$GLOBALS['__wp_is_front_page'] = true;
$GLOBALS['__wp_is_singular']   = false;

$out = gcp_schema_filter_yoast_graph( $yoast_graph, null );
$org = $out[1];

gcp_schema_assert( in_array( 'AutoDealer', $org['@type'], true ) && in_array( 'LocalBusiness', $org['@type'], true ), 'enrich: @type promoted to AutoDealer/LocalBusiness', $tests, $failures );
gcp_schema_assert( in_array( 'Organization', $org['@type'], true ), "enrich: Yoast's original Organization type retained", $tests, $failures );
gcp_schema_assert( 'Yoast Set Name' === $org['name'] && array( 'https://facebook.com/demo' ) === $org['sameAs'], 'enrich: Yoast-owned keys (name, sameAs) untouched', $tests, $failures );
gcp_schema_assert( 'PostalAddress' === $org['address']['@type'] && '1 Main St' === $org['address']['streetAddress'], 'enrich: address added from settings', $tests, $failures );
gcp_schema_assert( 'GeoCoordinates' === $org['geo']['@type'], 'enrich: geo added from settings', $tests, $failures );
gcp_schema_assert( 1 === count( $org['openingHoursSpecification'] ), 'enrich: opening hours added', $tests, $failures );
gcp_schema_assert( '+1-555-0100' === $org['telephone'] && '$$' === $org['priceRange'], 'enrich: telephone + priceRange added', $tests, $failures );
gcp_schema_assert( isset( $org['makesOffer']['itemOffered']['@type'] ) && 'LoanOrCredit' === $org['makesOffer']['itemOffered']['@type'], 'enrich: financing makesOffer → LoanOrCredit added (BIZ-02)', $tests, $failures );
gcp_schema_assert( 2 === count( $out ), 'non-vehicle context: no Product node appended', $tests, $failures );
gcp_schema_assert( 'WebSite' === $out[0]['@type'], 'other Yoast pieces pass through untouched', $tests, $failures );

// --- Case 2: never overwrite an existing address -------------------------------
$graph_with_address      = $yoast_graph;
$graph_with_address[1]['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => 'Yoast St' );
$out2 = gcp_schema_filter_yoast_graph( $graph_with_address, null );
gcp_schema_assert( 'Yoast St' === $out2[1]['address']['streetAddress'], 'enrich: existing address never overwritten', $tests, $failures );

// --- Case 3: vehicle single → Product appended, seller = Yoast org @id ---------
$vid = 401;
$GLOBALS['__wp_is_front_page']     = false;
$GLOBALS['__wp_is_singular']       = 'vehicle';
$GLOBALS['__wp_post_types']        = array( 'post', 'page', 'vehicle' );
$GLOBALS['__wp_queried_object_id'] = $vid;
$GLOBALS['__wp_titles'][ $vid ]     = '2019 Honda Civic LX';
$GLOBALS['__wp_permalinks'][ $vid ] = 'https://example.com/listings/2019-honda-civic-lx/';
$GLOBALS['__wp_thumbnails'][ $vid ] = 'https://example.com/civic.jpg';
$GLOBALS['__wp_post_meta'][ $vid ]  = array(
	'_e1ci_price'  => '15995',
	'_e1ci_status' => 'available',
);

$out3 = gcp_schema_filter_yoast_graph( $yoast_graph, null );
gcp_schema_assert( 3 === count( $out3 ), 'vehicle single: Product/Car node appended to Yoast graph', $tests, $failures );
$product = $out3[2];
gcp_schema_assert( in_array( 'Product', (array) $product['@type'], true ) && in_array( 'Car', (array) $product['@type'], true ), 'vehicle single: appended node is [Product, Car]', $tests, $failures );
gcp_schema_assert( $yoast_org_id === $product['offers']['seller']['@id'], "vehicle single: seller.@id === Yoast's #organization", $tests, $failures );

// --- Case 4: no Organization piece → own dealer node appended ------------------
$person_graph = array(
	array(
		'@type' => 'WebSite',
		'@id'   => 'https://example.com/#website',
	),
);
$out4 = gcp_schema_filter_yoast_graph( $person_graph, null );
$appended_dealer = null;
foreach ( $out4 as $node ) {
	if ( in_array( 'AutoDealer', (array) $node['@type'], true ) ) {
		$appended_dealer = $node;
	}
}
gcp_schema_assert( null !== $appended_dealer, 'no Organization: full dealer node appended instead', $tests, $failures );
$product4 = null;
foreach ( $out4 as $node ) {
	if ( in_array( 'Product', (array) $node['@type'], true ) ) {
		$product4 = $node;
	}
}
gcp_schema_assert( null !== $product4 && $product4['offers']['seller']['@id'] === $appended_dealer['@id'], 'no Organization: Product seller points at the appended dealer @id', $tests, $failures );

// --- Case 5: suppression + malformed input pass through ------------------------
$GLOBALS['__wp_is_search'] = true;
gcp_schema_assert( $yoast_graph === gcp_schema_filter_yoast_graph( $yoast_graph, null ), 'suppressed request (search): graph passes through untouched', $tests, $failures );
$GLOBALS['__wp_is_search'] = false;
gcp_schema_assert( 'not-a-graph' === gcp_schema_filter_yoast_graph( 'not-a-graph', null ), 'non-array graph: returned untouched', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
