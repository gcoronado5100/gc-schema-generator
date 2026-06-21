<?php
/**
 * Scoped admin asset enqueue for the settings screen (wp.media + picker JS).
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the media library and the settings picker JS, scoped to this screen.
 *
 * Hooked on `admin_enqueue_scripts` (Plan 03). Loads wp_enqueue_media() and the
 * settings.js media-picker glue ONLY on the plugin's settings page, by comparing
 * the incoming hook suffix against $GLOBALS['gcsg_settings_page_hook'] (set by
 * the Plan-02 menu registration). Loading these assets globally would bloat
 * every admin page (Pitfall 5), so we return early everywhere else.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param string $hook_suffix The current admin page hook suffix.
 * @return void
 */
function gcsg_admin_enqueue( $hook_suffix ) {
	$page_hook = isset( $GLOBALS['gcsg_settings_page_hook'] ) ? $GLOBALS['gcsg_settings_page_hook'] : '';
	if ( ! $page_hook || $hook_suffix !== $page_hook ) {
		return; // Scope strictly to this settings screen (Pitfall 5).
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'gcsg-settings',
		GCSG_PLUGIN_URL . 'assets/admin/settings.js',
		array( 'jquery' ),
		GCSG_VERSION,
		true
	);
}
