<?php
/**
 * Shared dealer sub-node builders (one tight group).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the PostalAddress sub-node from the settings array.
 *
 * Shared between the Mode B dealer node and the Mode A Yoast Organization
 * enrichment so both emit the identical address shape. Returns array() unless
 * ALL five address parts are non-empty (D-06: an incomplete address is not
 * emitted at all).
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array $s Sanitized settings (gcp_schema_get_settings() contract shape).
 *
 * @return array PostalAddress node, or array() when incomplete.
 */
function gcp_schema_build_postal_address( array $s ) {
	$parts = array( 'street_address', 'address_locality', 'address_region', 'postal_code', 'address_country' );
	foreach ( $parts as $part ) {
		if ( ! isset( $s[ $part ] ) || '' === trim( (string) $s[ $part ] ) ) {
			return array();
		}
	}

	return array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => wp_strip_all_tags( $s['street_address'] ),
		'addressLocality' => wp_strip_all_tags( $s['address_locality'] ),
		'addressRegion'   => wp_strip_all_tags( $s['address_region'] ),
		'postalCode'      => wp_strip_all_tags( $s['postal_code'] ),
		'addressCountry'  => wp_strip_all_tags( $s['address_country'] ),
	);
}

/**
 * Build the GeoCoordinates sub-node from the settings array.
 *
 * Emitted only when BOTH latitude and longitude are non-empty — never a
 * fabricated 0.0/0.0 point.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array $s Sanitized settings.
 *
 * @return array GeoCoordinates node, or array() when either coordinate is missing.
 */
function gcp_schema_build_geo( array $s ) {
	if (
		! isset( $s['latitude'], $s['longitude'] )
		|| '' === trim( (string) $s['latitude'] )
		|| '' === trim( (string) $s['longitude'] )
	) {
		return array();
	}

	return array(
		'@type'     => 'GeoCoordinates',
		'latitude'  => (float) $s['latitude'],
		'longitude' => (float) $s['longitude'],
	);
}

/**
 * Build the OpeningHoursSpecification list from the settings hours rows.
 *
 * D-04 grouping: rows sharing identical (opens, closes) collapse into one
 * OpeningHoursSpecification whose dayOfWeek is an array of full schema.org
 * day IRIs. Malformed rows and unknown day names are skipped.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array $s Sanitized settings.
 *
 * @return array List of OpeningHoursSpecification nodes (possibly empty).
 */
function gcp_schema_build_hours_specs( array $s ) {
	$rows   = isset( $s['opening_hours'] ) && is_array( $s['opening_hours'] ) ? $s['opening_hours'] : array();
	$groups = array();
	foreach ( $rows as $row ) {
		if ( empty( $row['day'] ) || empty( $row['opens'] ) || empty( $row['closes'] ) ) {
			continue;
		}
		$iri = gcp_schema_schema_day_iri( $row['day'] );
		if ( '' === $iri ) {
			continue;
		}
		$key = $row['opens'] . '|' . $row['closes'];
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array(
				'opens'  => $row['opens'],
				'closes' => $row['closes'],
				'days'   => array(),
			);
		}
		$groups[ $key ]['days'][] = $iri;
	}

	$specs = array();
	foreach ( $groups as $g ) {
		$specs[] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $g['days'], // Always an array; single-day groups are 1-element (uniform, valid).
			'opens'     => $g['opens'],
			'closes'    => $g['closes'],
		);
	}

	return $specs;
}
