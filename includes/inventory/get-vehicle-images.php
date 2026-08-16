<?php
/**
 * Vehicle image URL collector.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collect full-size image URLs for a vehicle: featured image first, then gallery.
 *
 * Gallery attachment IDs come from `_e1ci_gallery` in any of its three storage
 * shapes (see gcp_schema_parse_list_meta). Dead attachment IDs resolve to
 * false and are dropped; duplicates are removed; the list is capped so a
 * 40-photo listing does not bloat the page head.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param int $post_id Vehicle post ID.
 * @param int $limit   Maximum number of URLs to return. Default 8.
 *
 * @return array Flat list of absolute image URLs (possibly empty).
 */
function gcp_schema_get_vehicle_images( $post_id, $limit = 8 ) {
	$post_id = (int) $post_id;
	$urls    = array();

	if ( function_exists( 'get_the_post_thumbnail_url' ) ) {
		$featured = get_the_post_thumbnail_url( $post_id, 'full' );
		if ( is_string( $featured ) && '' !== $featured ) {
			$urls[] = $featured;
		}
	}

	if ( function_exists( 'get_post_meta' ) && function_exists( 'wp_get_attachment_image_url' ) ) {
		$ids = gcp_schema_parse_list_meta( get_post_meta( $post_id, '_e1ci_gallery', true ) );
		foreach ( $ids as $id ) {
			$id = (int) $id;
			if ( $id <= 0 ) {
				continue;
			}
			$url = wp_get_attachment_image_url( $id, 'full' );
			if ( is_string( $url ) && '' !== $url ) {
				$urls[] = $url;
			}
		}
	}

	return array_slice( array_values( array_unique( $urls ) ), 0, max( 1, (int) $limit ) );
}
