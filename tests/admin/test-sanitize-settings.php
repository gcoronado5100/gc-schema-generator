<?php
/**
 * Behavioral tests for gcsg_sanitize_settings().
 *
 * Standalone (no PHPUnit): run with `php tests/admin/test-sanitize-settings.php`.
 * Exit code 0 = all pass, 1 = a failure. This is the Wave-0 RED test: it asserts
 * the four automatable phase success criteria (SC-1 contract, SC-3a geo, SC-3b
 * HTML stripping, SC-4 enums/hours) plus the same_as round-trip. Until Plan 02
 * implements gcsg_sanitize_settings() the file fails cleanly (exit 1, no fatal),
 * then turns GREEN once the callback lands.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 );
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

// Guard-require the callback file so the test RUNS and FAILS (not fatals) while
// the implementation is absent in this Wave-0 plan. Plan 02 creates this file.
$callback_file = $root . '/includes/admin/sanitize-settings.php';
if ( file_exists( $callback_file ) ) {
	require_once $callback_file;
}
if ( ! function_exists( 'gcsg_sanitize_settings' ) ) {
	echo "FAIL: gcsg_sanitize_settings() is not defined yet (RED — implemented in Plan 02)\n";
	echo "\n0 assertions, 1 failures\n";
	exit( 1 );
}

$expected_keys = array(
	'name',
	'legal_name',
	'logo',
	'image',
	'telephone',
	'price_range',
	'street_address',
	'address_locality',
	'address_region',
	'postal_code',
	'address_country',
	'latitude',
	'longitude',
	'opening_hours',
	'same_as',
	'currency',
	'financing_description',
);

// =============================================================================
// SC-1 — full 16-key contract (no missing/extra keys, always complete).
// =============================================================================
$out = gcsg_sanitize_settings( array( 'name' => 'Joe Auto' ) );
gcsg_assert( count( $out ) === 17, 'SC-1: sanitized output has exactly 17 keys (full gcsg_get_settings contract)', $tests, $failures );
gcsg_assert( array() === array_diff( $expected_keys, array_keys( $out ) ), 'SC-1: no expected key is missing', $tests, $failures );
gcsg_assert( array() === array_diff( array_keys( $out ), $expected_keys ), 'SC-1: no unexpected extra key', $tests, $failures );

// Partial input must STILL produce all 16 keys (Pitfall 2 — never partial).
$partial = gcsg_sanitize_settings( array( 'name' => 'Joe Auto' ) );
gcsg_assert( count( $partial ) === 17, 'SC-1: partial input still yields all 17 keys', $tests, $failures );
gcsg_assert( array() === array_diff( $expected_keys, array_keys( $partial ) ), 'SC-1: partial input has no missing key', $tests, $failures );

// =============================================================================
// SC-3a — geo lat/long round-trip as floats; empty stays empty (never 0.0).
// =============================================================================
$geo = gcsg_sanitize_settings( array( 'latitude' => '33.7490', 'longitude' => '-84.3880' ) );
gcsg_assert( (float) $geo['latitude'] === 33.7490, 'SC-3a: latitude round-trips as a float', $tests, $failures );
gcsg_assert( (float) $geo['longitude'] === -84.3880, 'SC-3a: longitude round-trips as a float', $tests, $failures );

$geo_empty = gcsg_sanitize_settings( array( 'latitude' => '', 'longitude' => '' ) );
gcsg_assert( '' === $geo_empty['latitude'], 'SC-3a: empty latitude stays empty (never 0.0)', $tests, $failures );
gcsg_assert( '' === $geo_empty['longitude'], 'SC-3a: empty longitude stays empty (never 0.0)', $tests, $failures );

$geo_ws = gcsg_sanitize_settings( array( 'latitude' => '   ' ) );
gcsg_assert( '' === $geo_ws['latitude'], 'SC-3a: whitespace-only latitude stays empty', $tests, $failures );

// =============================================================================
// SC-3b — injected <script>/HTML stripped on save.
// =============================================================================
$xss = gcsg_sanitize_settings( array( 'name' => '<script>alert(1)</script>Joe Auto' ) );
gcsg_assert( false === strpos( $xss['name'], '<' ), 'SC-3b: angle brackets stripped from name', $tests, $failures );
gcsg_assert( false === strpos( $xss['name'], '<script' ), 'SC-3b: <script tag stripped from name', $tests, $failures );

$fin = gcsg_sanitize_settings( array( 'financing_description' => "Low rates<script>x</script>\nApply today" ) );
gcsg_assert( false === strpos( $fin['financing_description'], '<script' ), 'SC-3b: <script stripped from financing_description', $tests, $failures );
gcsg_assert( false !== strpos( $fin['financing_description'], "\n" ), 'SC-3b: newline preserved in financing_description (textarea sanitizer)', $tests, $failures );

// =============================================================================
// SC-4 — enums + opening hours.
// =============================================================================
// Currency whitelist.
$cur_usd = gcsg_sanitize_settings( array( 'currency' => 'USD' ) );
gcsg_assert( 'USD' === $cur_usd['currency'], 'SC-4: USD accepted', $tests, $failures );

$cur_cad = gcsg_sanitize_settings( array( 'currency' => 'CAD' ) );
gcsg_assert( 'CAD' === $cur_cad['currency'], 'SC-4: CAD accepted', $tests, $failures );

$cur_bad = gcsg_sanitize_settings( array( 'currency' => '$' ) );
gcsg_assert( '' === $cur_bad['currency'], 'SC-4: non-whitelisted currency "$" rejected (not stored raw)', $tests, $failures );

// Opening hours — valid day produces a {day,opens,closes} list element.
$hrs = gcsg_sanitize_settings( array(
	'opening_hours' => array(
		'Monday' => array( 'opens' => '09:00', 'closes' => '18:00' ),
	),
) );
$has_monday = false;
foreach ( $hrs['opening_hours'] as $row ) {
	if ( isset( $row['day'] ) && 'Monday' === $row['day'] ) {
		$has_monday = ( array( 'day' => 'Monday', 'opens' => '09:00', 'closes' => '18:00' ) === $row );
	}
}
gcsg_assert( $has_monday, 'SC-4: valid Monday hours stored as {day,opens,closes}', $tests, $failures );

// Closed day omitted (D-04).
$closed = gcsg_sanitize_settings( array(
	'opening_hours' => array(
		'Sunday' => array( 'closed' => '1', 'opens' => '10:00', 'closes' => '14:00' ),
	),
) );
$has_sunday = false;
foreach ( $closed['opening_hours'] as $row ) {
	if ( isset( $row['day'] ) && 'Sunday' === $row['day'] ) {
		$has_sunday = true;
	}
}
gcsg_assert( ! $has_sunday, 'SC-4: closed day (Sunday) omitted from output', $tests, $failures );

// Tampered day key rejected (only the 7 whitelisted days survive).
$tampered = gcsg_sanitize_settings( array(
	'opening_hours' => array(
		'Funday' => array( 'opens' => '09:00', 'closes' => '18:00' ),
	),
) );
$has_funday = false;
foreach ( $tampered['opening_hours'] as $row ) {
	if ( isset( $row['day'] ) && 'Funday' === $row['day'] ) {
		$has_funday = true;
	}
}
gcsg_assert( ! $has_funday, 'SC-4: tampered day key (Funday) rejected', $tests, $failures );

// Invalid HH:MM rejected → day omitted.
$bad_time = gcsg_sanitize_settings( array(
	'opening_hours' => array(
		'Tuesday' => array( 'opens' => '25:99', 'closes' => '18:00' ),
	),
) );
$has_tuesday = false;
foreach ( $bad_time['opening_hours'] as $row ) {
	if ( isset( $row['day'] ) && 'Tuesday' === $row['day'] ) {
		$has_tuesday = true;
	}
}
gcsg_assert( ! $has_tuesday, 'SC-4: invalid time (25:99) omits the day', $tests, $failures );

// =============================================================================
// same_as — valid network kept, empty dropped (supports SET-01/SET-02).
// =============================================================================
$social = gcsg_sanitize_settings( array(
	'same_as' => array(
		'facebook' => 'https://fb.com/joe',
		'twitter'  => '',
	),
) );
gcsg_assert( isset( $social['same_as']['facebook'] ) && 'https://fb.com/joe' === $social['same_as']['facebook'], 'same_as: valid facebook url kept', $tests, $failures );
gcsg_assert( ! isset( $social['same_as']['twitter'] ), 'same_as: empty twitter dropped', $tests, $failures );

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
