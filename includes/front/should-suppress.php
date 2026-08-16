<?php
/**
 * Schema suppression gate for noindex-style requests.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request should carry NO plugin schema at all.
 *
 * Suppressed requests:
 *  - Search results and 404s — transient pages, no entity to describe.
 *  - Vehicle taxonomy archives — e1connect force-noindexes all of them and
 *    excludes them from sitemaps, so emitting schema there would decorate
 *    pages Google is told to ignore.
 *
 * The taxonomy list mirrors e1connect's registration and is filterable
 * (`gcp_schema/suppressed_taxonomies`) so a sibling site can adjust it. Sold
 * vehicle singles are deliberately NOT suppressed — they emit an honest
 * SoldOut Offer instead.
 *
 * Every WP conditional is function_exists-guarded so the helper degrades to
 * false (no suppression) outside a full WP request.
 *
 * @since feat/vehicle-schema-v1
 *
 * @return bool True when schema output must be suppressed for this request.
 */
function gcp_schema_should_suppress() {
	if ( function_exists( 'is_search' ) && is_search() ) {
		return true;
	}
	if ( function_exists( 'is_404' ) && is_404() ) {
		return true;
	}

	if ( function_exists( 'is_tax' ) ) {
		$taxonomies = array(
			'make',
			'model',
			'vtype',
			'body',
			'fuel',
			'transmission',
			'engine',
			'color',
			'interior-color',
			'location',
			'vendors',
		);
		if ( function_exists( 'apply_filters' ) ) {
			$taxonomies = (array) apply_filters( 'gcp_schema/suppressed_taxonomies', $taxonomies );
		}
		if ( is_tax( $taxonomies ) ) {
			return true;
		}
	}

	return false;
}
