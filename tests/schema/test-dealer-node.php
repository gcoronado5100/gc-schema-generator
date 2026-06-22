<?php
/**
 * RED behavioral tests for gcsg_build_dealer_node() and gcsg_schema_day_iri().
 *
 * Wave-0 (TDD RED): the builder/enum functions DO NOT EXIST yet, so this test
 * exits 1 (RED). It locks the dealer-node contract — BIZ-01, D-01 (multi-type),
 * D-04/D-05 (opening-hours consolidation + full Day IRIs), D-06 (omit-on-missing),
 * D-08 (no aggregateRating), OUT-04 (stable @id) — as executable assertions that
 * turn GREEN when Plan 03-02 implements the builders.
 *
 * Standalone (no PHPUnit): run with `php tests/schema/test-dealer-node.php`.
 * Exit code 0 = all pass, 1 = a failure.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 ); // tests/<area>/file.php → plugin root.
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcsg_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

// Helpers that already exist on disk (needed for the @id assertion).
require_once $root . '/includes/helpers/schema-id.php';

// Units under test — DO NOT EXIST in Wave 0. Guard the require so a missing file
// does not emit an uncatchable fatal that masks the RED signal; the first
// assertion (function_exists) then drives a clean exit(1) until Plan 03-02 lands.
foreach (
	array(
		$root . '/includes/helpers/schema-enums.php',
		$root . '/includes/schema/build-dealer-node.php',
	) as $f
) {
	if ( file_exists( $f ) ) {
		require_once $f;
	}
}

// --- RED guard: the test stays RED (exit 1) until these functions are defined. ---
gcsg_assert( function_exists( 'gcsg_build_dealer_node' ), 'gcsg_build_dealer_node is defined', $tests, $failures );
gcsg_assert( function_exists( 'gcsg_schema_day_iri' ), 'gcsg_schema_day_iri is defined', $tests, $failures );

$have_builder = function_exists( 'gcsg_build_dealer_node' );
$have_iri     = function_exists( 'gcsg_schema_day_iri' );

// Full-settings fixture — literal 17-key array matching the gcsg_get_settings contract.
$full = array(
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
		array( 'day' => 'Monday',    'opens' => '09:00', 'closes' => '18:00' ),
		array( 'day' => 'Tuesday',   'opens' => '09:00', 'closes' => '18:00' ),
		array( 'day' => 'Wednesday', 'opens' => '09:00', 'closes' => '18:00' ),
		array( 'day' => 'Thursday',  'opens' => '09:00', 'closes' => '18:00' ),
		array( 'day' => 'Friday',    'opens' => '09:00', 'closes' => '18:00' ),
	),
	'same_as'               => array( 'facebook' => 'https://facebook.com/demomotors' ),
	'currency'              => 'CAD',
	'financing_description' => 'In-house financing available.',
);

// --- BIZ-01 / D-01: multi-type @type array ---
$node = $have_builder ? gcsg_build_dealer_node( $full ) : array();
gcsg_assert(
	$have_builder && isset( $node['@type'] ) && array( 'AutoDealer', 'LocalBusiness' ) === $node['@type'],
	'D-01: @type === ["AutoDealer","LocalBusiness"]',
	$tests,
	$failures
);

// --- BIZ-01: name present ---
gcsg_assert(
	$have_builder && isset( $node['name'] ) && 'Demo Motors' === $node['name'],
	'BIZ-01: node has name',
	$tests,
	$failures
);

// --- BIZ-01: PostalAddress with all 5 address keys ---
$addr = isset( $node['address'] ) && is_array( $node['address'] ) ? $node['address'] : array();
gcsg_assert(
	$have_builder
		&& isset( $addr['@type'] ) && 'PostalAddress' === $addr['@type']
		&& isset( $addr['streetAddress'], $addr['addressLocality'], $addr['addressRegion'], $addr['postalCode'], $addr['addressCountry'] ),
	'BIZ-01: address is PostalAddress with all 5 keys',
	$tests,
	$failures
);

// --- OUT-04: @id === gcsg_schema_id('Organization') ---
gcsg_assert(
	$have_builder && isset( $node['@id'] ) && gcsg_schema_id( 'Organization' ) === $node['@id'],
	'OUT-04: @id === gcsg_schema_id("Organization")',
	$tests,
	$failures
);

// --- D-06 suppression: empty name → array() ---
$no_name         = $full;
$no_name['name'] = '';
gcsg_assert(
	$have_builder && array() === gcsg_build_dealer_node( $no_name ),
	'D-06: empty name → builder returns array()',
	$tests,
	$failures
);

// --- D-06 suppression: missing postal_code (one of 6 required) → array() ---
$no_postal                = $full;
$no_postal['postal_code'] = '';
gcsg_assert(
	$have_builder && array() === gcsg_build_dealer_node( $no_postal ),
	'D-06: missing postal_code → builder returns array()',
	$tests,
	$failures
);

// --- D-06 optional-omit: empty optionals are ABSENT (not empty-keyed) ---
$minimal = array(
	'name'                  => 'Bare Dealer',
	'legal_name'            => '',
	'logo'                  => '',
	'image'                 => '',
	'telephone'             => '',
	'price_range'           => '',
	'street_address'        => '2 Side Rd',
	'address_locality'      => 'Villageton',
	'address_region'        => 'BC',
	'postal_code'           => 'B2B2B2',
	'address_country'       => 'CA',
	'latitude'              => '',
	'longitude'             => '',
	'opening_hours'         => array(),
	'same_as'               => array(),
	'currency'              => '',
	'financing_description' => '',
);
$min_node = $have_builder ? gcsg_build_dealer_node( $minimal ) : array();
gcsg_assert(
	$have_builder
		&& ! array_key_exists( 'telephone', $min_node )
		&& ! array_key_exists( 'logo', $min_node )
		&& ! array_key_exists( 'image', $min_node )
		&& ! array_key_exists( 'priceRange', $min_node )
		&& ! array_key_exists( 'legalName', $min_node )
		&& ! array_key_exists( 'geo', $min_node ),
	'D-06: empty optional fields are absent (not empty-keyed)',
	$tests,
	$failures
);

// --- D-04/D-05: Mon–Fri same hours → ONE spec with 5-element IRI dayOfWeek array ---
$specs = isset( $node['openingHoursSpecification'] ) && is_array( $node['openingHoursSpecification'] )
	? $node['openingHoursSpecification']
	: array();
gcsg_assert(
	$have_builder && 1 === count( $specs ),
	'D-04: Mon–Fri identical hours collapse to ONE spec',
	$tests,
	$failures
);
$expected_days = array(
	'https://schema.org/Monday',
	'https://schema.org/Tuesday',
	'https://schema.org/Wednesday',
	'https://schema.org/Thursday',
	'https://schema.org/Friday',
);
gcsg_assert(
	$have_builder && isset( $specs[0]['dayOfWeek'] ) && $expected_days === $specs[0]['dayOfWeek'],
	'D-05: dayOfWeek is the 5-element full-IRI array Monday..Friday',
	$tests,
	$failures
);

// --- D-04: adding a second group (Sat) yields a SECOND spec ---
$two_groups                  = $full;
$two_groups['opening_hours'] = array_merge(
	$full['opening_hours'],
	array( array( 'day' => 'Saturday', 'opens' => '10:00', 'closes' => '14:00' ) )
);
$two_node  = $have_builder ? gcsg_build_dealer_node( $two_groups ) : array();
$two_specs = isset( $two_node['openingHoursSpecification'] ) ? $two_node['openingHoursSpecification'] : array();
gcsg_assert(
	$have_builder && 2 === count( $two_specs ),
	'D-04: a second hours group produces a SECOND spec',
	$tests,
	$failures
);

// --- D-04: non-contiguous same-hours (Mon+Wed+Fri identical, Tue+Thu differ) collapse to a 3-day spec ---
$noncontig                  = $full;
$noncontig['opening_hours'] = array(
	array( 'day' => 'Monday',    'opens' => '09:00', 'closes' => '18:00' ),
	array( 'day' => 'Tuesday',   'opens' => '11:00', 'closes' => '15:00' ),
	array( 'day' => 'Wednesday', 'opens' => '09:00', 'closes' => '18:00' ),
	array( 'day' => 'Thursday',  'opens' => '11:00', 'closes' => '15:00' ),
	array( 'day' => 'Friday',    'opens' => '09:00', 'closes' => '18:00' ),
);
$nc_node  = $have_builder ? gcsg_build_dealer_node( $noncontig ) : array();
$nc_specs = isset( $nc_node['openingHoursSpecification'] ) ? $nc_node['openingHoursSpecification'] : array();
$nc_first_days = ( isset( $nc_specs[0]['dayOfWeek'] ) && is_array( $nc_specs[0]['dayOfWeek'] ) )
	? $nc_specs[0]['dayOfWeek']
	: array();
gcsg_assert(
	$have_builder && 2 === count( $nc_specs ) && 3 === count( $nc_first_days ),
	'D-04: non-contiguous same-hours days collapse (3-element dayOfWeek)',
	$tests,
	$failures
);

// --- D-05: gcsg_schema_day_iri mapping + unknown → '' ---
gcsg_assert(
	$have_iri && 'https://schema.org/Monday' === gcsg_schema_day_iri( 'Monday' ),
	'D-05: gcsg_schema_day_iri("Monday") === https://schema.org/Monday',
	$tests,
	$failures
);
gcsg_assert(
	$have_iri && '' === gcsg_schema_day_iri( 'Notaday' ),
	'D-05: gcsg_schema_day_iri(unknown) === ""',
	$tests,
	$failures
);

// --- D-08 honesty: encoded node never contains aggregateRating ---
gcsg_assert(
	$have_builder && false === strpos( (string) json_encode( $node ), 'aggregateRating' ),
	'D-08: node JSON contains no aggregateRating',
	$tests,
	$failures
);

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
