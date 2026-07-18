<?php
/**
 * Front-end JSON-LD output orchestrator (Mode B).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emit the plugin's self-contained JSON-LD @graph on wp_head (Mode B).
 *
 * Hooked at wp_head:99 so it runs after most head output and after Yoast has
 * defined WPSEO_VERSION (Pitfall 6). The single emit-vs-defer branch (D-09):
 * when Yoast is active this plugin emits NOTHING and yields its graph to Yoast
 * (Mode A graph-stitching is Phase 8). The full pipeline runs exactly once per
 * request — single build → single assemble → single encode → single echo
 * (D-12). No object/page caching is added here (deferred to Phase 9).
 *
 * Flow:
 *   1. is_admin()              → return (front-end only, Pitfall 6).
 *   2. gcp_schema_is_yoast_active()  → return (D-09 single gate; Mode A is Phase 8).
 *   3. gcp_schema_detect_context()   → classify for Phase 4/6 parity; the dealer node
 *                                emits regardless of context (D-02).
 *   4. gcp_schema_get_settings()     → single read (D-12).
 *   5. Build dealer node once; push only when non-empty (D-06 suppression).
 *   6. Zero nodes              → return, NO empty <script> (D-07 / BIZ-03).
 *   7. assemble → encode → echo one <script type="application/ld+json">.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void Echoes the JSON-LD <script> tag, or nothing when suppressed.
 */
function gcp_schema_output_schema() {
	if ( is_admin() ) {
		return; // Front-end only (Pitfall 6).
	}
	if ( gcp_schema_is_yoast_active() ) {
		return; // D-09 single emit-vs-defer gate; Mode A deferred to Phase 8.
	}

	$context  = gcp_schema_detect_context(); // Classify for Phase 4/6; dealer emits regardless (D-02).
	$settings = gcp_schema_get_settings();   // Single read (D-12).

	$nodes  = array();
	$dealer = gcp_schema_build_dealer_node( $settings );
	if ( ! empty( $dealer ) ) {
		$nodes[] = $dealer;
	}

	if ( empty( $nodes ) ) {
		return; // D-07: no empty <script>.
	}

	$graph = gcp_schema_assemble_graph( $nodes );
	echo '<script type="application/ld+json">' . gcp_schema_encode_jsonld( $graph ) . '</script>' . "\n";
}
