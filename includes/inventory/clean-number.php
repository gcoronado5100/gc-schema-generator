<?php
/**
 * Numeric meta cleaner.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract a float from a possibly decorated numeric meta value.
 *
 * Motors-era imports left values like "13,577 Km" in `_e1ci_mileage`; newer
 * writers store plain integers. This cleaner strips thousands separators and
 * unit suffixes so both shapes normalize to the same float, and returns null
 * (never a fabricated 0) when no digits remain — callers omit the schema
 * property entirely in that case.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param mixed $raw Raw meta value.
 *
 * @return float|null Parsed number, or null when not numeric.
 */
function gcp_schema_clean_number( $raw ) {
	if ( is_int( $raw ) || is_float( $raw ) ) {
		return (float) $raw;
	}
	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return null;
	}

	$stripped = preg_replace( '/[^0-9.\-]/', '', $raw );
	if ( '' === $stripped || ! is_numeric( $stripped ) ) {
		return null;
	}

	return (float) $stripped;
}
