<?php
/**
 * Multi-format list meta parser.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a list-shaped meta value into a clean string array.
 *
 * e1connect-inventory stores `_e1ci_gallery` and `_e1ci_features` in three
 * historical shapes — a real PHP array, a JSON-encoded string, or a CSV
 * string — depending on which import path wrote the post. This parser accepts
 * all three (the same defensive pattern as e1connect's own gallery decode) so
 * schema builds never depend on which writer touched the vehicle last.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param mixed $raw Raw meta value (array, JSON string, CSV string, or empty).
 *
 * @return array Flat list of trimmed non-empty string items.
 */
function gcp_schema_parse_list_meta( $raw ) {
	$items = array();

	if ( is_array( $raw ) ) {
		$items = $raw;
	} elseif ( is_string( $raw ) && '' !== trim( $raw ) ) {
		$decoded = json_decode( $raw, true );
		$items   = is_array( $decoded ) ? $decoded : explode( ',', $raw );
	}

	$clean = array();
	foreach ( $items as $item ) {
		if ( is_scalar( $item ) ) {
			$item = trim( (string) $item );
			if ( '' !== $item ) {
				$clean[] = $item;
			}
		}
	}

	return array_values( $clean );
}
