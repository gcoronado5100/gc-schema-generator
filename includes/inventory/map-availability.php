<?php
/**
 * Vehicle status → schema.org ItemAvailability mapper.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map an `_e1ci_status` value to a full schema.org ItemAvailability IRI.
 *
 * Mirrors e1connect's Open Graph availability mapping exactly (available,
 * reserved, or empty → in stock; anything else → out of stock) so the
 * JSON-LD and the `product:availability` OG tag on the same page can never
 * contradict each other. Sold singles keep resolving publicly, so SoldOut is
 * an honest, intentional signal — not a suppression case.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param string $status Raw `_e1ci_status` meta value.
 *
 * @return string Full IRI: https://schema.org/InStock or https://schema.org/SoldOut.
 */
function gcp_schema_map_availability( $status ) {
	$status = strtolower( trim( (string) $status ) );
	if ( '' === $status || 'available' === $status || 'reserved' === $status ) {
		return 'https://schema.org/InStock';
	}
	return 'https://schema.org/SoldOut';
}
