<?php
/**
 * Schema.org enum helpers.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map a bare English day name to its full schema.org Day IRI (D-05).
 *
 * Schema.org's DayOfWeek enumeration members are themselves IRIs
 * (e.g. https://schema.org/Monday), so OpeningHoursSpecification.dayOfWeek
 * must carry the full IRI rather than a bare day string. This is the single
 * source of truth for that mapping; the dealer-node builder calls it when
 * grouping opening-hours rows.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param string $day English day name (e.g. 'Monday').
 *
 * @return string Full schema.org Day IRI, or '' for any unknown input.
 */
function gcp_schema_schema_day_iri( $day ) {
	$map = array(
		'Monday'    => 'https://schema.org/Monday',
		'Tuesday'   => 'https://schema.org/Tuesday',
		'Wednesday' => 'https://schema.org/Wednesday',
		'Thursday'  => 'https://schema.org/Thursday',
		'Friday'    => 'https://schema.org/Friday',
		'Saturday'  => 'https://schema.org/Saturday',
		'Sunday'    => 'https://schema.org/Sunday',
	);

	return isset( $map[ $day ] ) ? $map[ $day ] : '';
}
