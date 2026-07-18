<?php
/**
 * Settings sanitize callback — the single choke point for the dealer-identity option.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure the HH:MM normalizer is available. Under the plugin glob-autoload both
// files load independently; this require_once keeps the callback self-contained
// for the standalone unit test (which requires only this file) without
// double-loading in production.
if ( ! function_exists( 'gcp_schema_normalize_time' ) ) {
	require_once __DIR__ . '/normalize-time.php';
}

/**
 * Sanitize the full dealer-identity settings array.
 *
 * Bound as the `sanitize_callback` of the single `gcp_schema_settings` option, this is
 * the one place where every saved value is cleaned. It ALWAYS returns the full
 * 16-key contract (the same shape as gcp_schema_get_settings() defaults) regardless of
 * how partial or empty the submitted input is, so reads always round-trip.
 *
 * Per-type handling:
 * - Plain text → sanitize_text_field (strips HTML/scripts).
 * - financing_description → sanitize_textarea_field (newlines preserved).
 * - logo, image → esc_url_raw.
 * - latitude/longitude → '' when empty/whitespace, else (string) floatval (never 0.0 fabricated).
 * - currency → only a value in the filterable whitelist (USD/CAD floor) survives.
 * - opening_hours → list of {day,opens,closes}; closed days and invalid days/times omitted.
 * - same_as → assoc network=>url map over the 6 known networks; empties dropped.
 *
 * No escaping for output is done here (that is render-time, Plan 03) and the
 * option is never written directly — the Settings API stores the return value.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param mixed $input Raw submitted settings (expected array; coerced defensively).
 * @return array The sanitized 16-key settings array.
 */
function gcp_schema_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();

	// Seed the FULL default shape so all 16 keys are always returned.
	$out = array(
		'name'                  => '',
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

	// Plain-text fields.
	foreach ( array(
		'name',
		'legal_name',
		'telephone',
		'price_range',
		'street_address',
		'address_locality',
		'address_region',
		'postal_code',
		'address_country',
	) as $k ) {
		if ( isset( $input[ $k ] ) ) {
			$out[ $k ] = sanitize_text_field( $input[ $k ] );
		}
	}

	// Multiline field (newlines preserved).
	if ( isset( $input['financing_description'] ) ) {
		$out['financing_description'] = sanitize_textarea_field( $input['financing_description'] );
	}

	// URL fields.
	foreach ( array( 'logo', 'image' ) as $k ) {
		if ( ! empty( $input[ $k ] ) ) {
			$out[ $k ] = esc_url_raw( $input[ $k ] );
		}
	}

	// Geo — empty stays empty; never fabricate 0.0.
	foreach ( array( 'latitude', 'longitude' ) as $k ) {
		if ( isset( $input[ $k ] ) && '' !== trim( (string) $input[ $k ] ) ) {
			$out[ $k ] = (string) floatval( $input[ $k ] );
		}
	}

	// Currency — filterable whitelist (USD/CAD floor). Guard apply_filters so the
	// pure unit test (no core stubs) still runs.
	$allowed_currency = array( 'USD', 'CAD' );
	if ( function_exists( 'apply_filters' ) ) {
		$allowed_currency = apply_filters( 'gcp_schema_allowed_currencies', $allowed_currency );
	}
	if ( isset( $input['currency'] ) && in_array( $input['currency'], $allowed_currency, true ) ) {
		$out['currency'] = $input['currency'];
	}

	// Opening hours — iterate the 7 whitelisted days; omit closed/invalid rows.
	$days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
	if ( isset( $input['opening_hours'] ) && is_array( $input['opening_hours'] ) ) {
		foreach ( $days as $day ) {
			$row = isset( $input['opening_hours'][ $day ] ) ? $input['opening_hours'][ $day ] : null;
			if ( ! is_array( $row ) || ! empty( $row['closed'] ) ) {
				continue;
			}
			$opens  = isset( $row['opens'] ) ? gcp_schema_normalize_time( $row['opens'] ) : '';
			$closes = isset( $row['closes'] ) ? gcp_schema_normalize_time( $row['closes'] ) : '';
			if ( '' === $opens || '' === $closes ) {
				continue;
			}
			$out['opening_hours'][] = array(
				'day'    => $day,
				'opens'  => $opens,
				'closes' => $closes,
			);
		}
	}

	// same_as — assoc network=>url map; empties dropped.
	$networks = array( 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok' );
	if ( isset( $input['same_as'] ) && is_array( $input['same_as'] ) ) {
		foreach ( $networks as $net ) {
			if ( ! empty( $input['same_as'][ $net ] ) ) {
				$out['same_as'][ $net ] = esc_url_raw( $input['same_as'][ $net ] );
			}
		}
	}

	return $out;
}
