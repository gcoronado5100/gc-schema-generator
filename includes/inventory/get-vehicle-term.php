<?php
/**
 * First-term-name reader for vehicle taxonomies.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read the first term name a vehicle carries in a given taxonomy.
 *
 * e1connect's vehicle taxonomies (make, model, body, fuel, transmission, …)
 * are flat single-value classifications in practice, so the first term is the
 * value. Returns '' on WP_Error, unregistered taxonomy, or no terms — callers
 * omit the corresponding schema property.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param int    $post_id  Vehicle post ID.
 * @param string $taxonomy Taxonomy name (e.g. 'make').
 *
 * @return string First term name, or '' when unavailable.
 */
function gcp_schema_get_vehicle_term( $post_id, $taxonomy ) {
	if ( ! function_exists( 'get_the_terms' ) ) {
		return '';
	}

	$terms = get_the_terms( (int) $post_id, $taxonomy );
	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return '';
	}

	$first = reset( $terms );
	if ( is_object( $first ) && isset( $first->name ) ) {
		return trim( (string) $first->name );
	}

	return '';
}
