<?php
/**
 * Front-end page-context classifier.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classify the current front-end request into a coarse page context.
 *
 * Returns one of three buckets used by the emission pipeline to decide which
 * extra schema nodes to attach:
 *   - 'front'   → the site front page.
 *   - 'vehicle' → a singular vehicle post (Product/Car + Offer node).
 *   - 'other'   → everything else (search / 404 / archive / generic page).
 *
 * IMPORTANT: this classifier does NOT decide whether the dealer node emits.
 * Per D-02 the AutoDealer/LocalBusiness node emits sitewide across all three
 * contexts; the orchestrator (gcp_schema_output_schema) and the Yoast graph
 * filter key off the return value only for the per-vehicle Product/Car node.
 * The vehicle post-type resolves through gcp_schema_vehicle_post_type() so a
 * sibling site can re-point it with one filter.
 *
 * Each WP conditional is function_exists-guarded so the file degrades to
 * 'other' rather than fataling if called outside a fully loaded WP request.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return string One of 'front', 'vehicle', or 'other'.
 */
function gcp_schema_detect_context() {
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		return 'front';
	}
	if ( function_exists( 'is_singular' ) && is_singular( gcp_schema_vehicle_post_type() ) ) {
		return 'vehicle';
	}
	return 'other';
}
