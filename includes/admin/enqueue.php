<?php
/**
 * Scoped admin asset enqueue for the settings screen (wp.media + picker JS).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the media library and the settings picker JS, scoped to this screen.
 *
 * Hooked on `admin_enqueue_scripts`. Loads wp_enqueue_media() and the
 * settings.js media-picker glue ONLY on the Schema Generator page, gated by the
 * `?page=gcp-schema` slug (works both as a GabeCode sub-page and the standalone
 * fallback menu). The shell stylesheet itself is enqueued by the hub, since
 * `gcp-schema` is in its sub-page registry. Loading these globally would bloat
 * every admin page, so we return early everywhere else.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param string $hook_suffix The current admin page hook suffix (unused; we gate by ?page=).
 * @return void
 */
function gcp_schema_admin_enqueue( $hook_suffix ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page routing.
	if ( 'gcp-schema' !== $page ) {
		return; // Scope strictly to the Schema Generator screen.
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'gcp-schema-settings',
		GCP_SCHEMA_PLUGIN_URL . 'assets/admin/settings.js',
		array( 'jquery' ),
		GCP_SCHEMA_VERSION,
		true
	);
}
