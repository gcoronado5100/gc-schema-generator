<?php
/**
 * Front-end page-context classifier.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classify the current front-end request into a coarse page context.
 *
 * Returns one of three buckets used by LATER phases to decide which extra
 * schema nodes to attach:
 *   - 'front'   → the site front page (Phase 4 breadcrumb root).
 *   - 'listing' → a singular Motors `listings` post (Phase 6 Vehicle/Offer node).
 *   - 'other'   → everything else (search / 404 / archive / generic page).
 *
 * IMPORTANT: this classifier does NOT decide whether the dealer node emits.
 * Per D-02 the AutoDealer/LocalBusiness node emits sitewide across all three
 * contexts; the orchestrator (gcsg_output_schema) calls this only to classify
 * the page for the breadcrumb (Phase 4) and vehicle (Phase 6) work that will
 * key off the return value. Edge pages (search results, 404, archives) fall
 * through to 'other' for now — refined in Phase 4/6 (Open Question #3).
 *
 * Each WP conditional is function_exists-guarded so the file degrades to
 * 'other' rather than fataling if called outside a fully loaded WP request.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return string One of 'front', 'listing', or 'other'.
 */
function gcsg_detect_context() {
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		return 'front';
	}
	if ( function_exists( 'is_singular' ) && is_singular( 'listings' ) ) {
		return 'listing';
	}
	return 'other';
}
