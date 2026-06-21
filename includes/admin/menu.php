<?php
/**
 * Top-level admin menu registration for the settings page.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the top-level "Schema" admin menu and its settings page.
 *
 * Hooked on `admin_menu` in Plan 03; this function only defines the registration.
 * It adds a single top-level menu (D-01) restricted to `manage_options` (D-01/D-10)
 * whose page renders via gcsg_render_settings_page. The returned hook suffix is
 * stored in a global so the Plan-03 scoped asset enqueue can target only this page.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_register_settings_menu() {
	$hook = add_menu_page(
		__( 'Schema Generator', 'gc-schema-generator' ), // Page title.
		__( 'Schema', 'gc-schema-generator' ),           // Menu label (D-01).
		'manage_options',                                 // Capability (D-01/D-10).
		'gcsg',                                           // Menu slug.
		'gcsg_render_settings_page',                      // Render callback.
		'dashicons-analytics'                             // Dashicon.
	);

	$GLOBALS['gcsg_settings_page_hook'] = $hook; // Read by the scoped enqueue (Plan 03).
}
