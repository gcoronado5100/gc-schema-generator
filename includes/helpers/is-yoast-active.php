<?php
/**
 * Yoast SEO detection helper.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Yoast SEO is active on this install.
 *
 * The plugin must yield to Yoast's existing schema graph to avoid emitting
 * duplicate/competing JSON-LD, so every Yoast-aware code path keys off this one
 * guard. Detection is purely in-memory (no DB reads, no warnings when Yoast is
 * absent): the `WPSEO_VERSION` constant is Yoast's primary load signal, with
 * `WPSEO_Options` as a secondary class-based check.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return bool True when Yoast SEO is active, false otherwise.
 */
function gcp_schema_is_yoast_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Options' );
}
