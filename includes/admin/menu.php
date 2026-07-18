<?php
/**
 * Admin integration + boot — GabeCode Plus add-on (hard dependency).
 *
 * The Schema Generator only runs when GabeCode Plus (the hub) is active: it
 * registers a "Schema Generator" sub-page under the GabeCode menu (via the
 * `gcp/admin_subpages` filter) and emits JSON-LD on the front-end. When the hub
 * is absent it registers nothing except an admin notice.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the GabeCode Plus admin shell is available to host our sub-page.
 *
 * @since feat/new_inventory_plugin_gc
 * @return bool
 */
function gcp_schema_hub_active() {
	return function_exists( 'gcp_admin_slug' ) && function_exists( 'gcp_admin_open' ) && function_exists( 'gcp_render_settings_cards' );
}

/**
 * Boot the add-on. Hooked to `plugins_loaded`.
 *
 * Wires every hook only when GabeCode Plus is active; otherwise shows a
 * requirement notice and registers nothing.
 *
 * @since feat/new_inventory_plugin_gc
 * @return void
 */
function gcp_schema_boot() {
	if ( ! gcp_schema_hub_active() ) {
		add_action( 'admin_notices', 'gcp_schema_missing_hub_notice' );
		return;
	}

	// Admin: Schema Generator sub-page under the GabeCode hub.
	add_filter( 'gcp/admin_subpages',    'gcp_schema_register_hub_tab' );
	add_filter( 'gcp/addons',            'gcp_schema_register_addon' );
	add_action( 'admin_init',            'gcp_schema_register_settings' );
	add_action( 'admin_enqueue_scripts', 'gcp_schema_admin_enqueue' );

	// Front-end JSON-LD output.
	add_action( 'wp_head', 'gcp_schema_output_schema', 99 );
}

/**
 * Admin notice shown when GabeCode Plus is not active.
 *
 * @since feat/new_inventory_plugin_gc
 * @return void
 */
function gcp_schema_missing_hub_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>'
		. esc_html__( 'GabeCode Schema Generator', 'gcp-schema-generator' )
		. '</strong> — '
		. esc_html__( 'requires the GabeCode Plus plugin to be installed and activated. Schema output is disabled until then.', 'gcp-schema-generator' )
		. '</p></div>';
}

/**
 * Register the Schema Generator tab in the GabeCode sub-page registry.
 *
 * Hooked to `gcp/admin_subpages`. The hub adds the actual sub-menu from this
 * registry, so we only declare the entry here.
 *
 * @since feat/new_inventory_plugin_gc
 * @param array $pages Existing registry.
 * @return array
 */
function gcp_schema_register_hub_tab( $pages ) {
	$pages['schema'] = array(
		'slug'     => 'gcp-schema',
		'label'    => __( 'Schema Generator', 'gcp-schema-generator' ),
		'order'    => 50,
		'callback' => 'gcp_schema_render_settings_page',
	);
	return $pages;
}

/**
 * Self-register in the GabeCode add-on catalog.
 *
 * Hooked to `gcp/addons`. Marks the Schema Generator add-on active and links its
 * "Configure" button to the settings sub-page.
 *
 * @since feat/new_inventory_plugin_gc
 * @param array $addons Existing catalog.
 * @return array
 */
function gcp_schema_register_addon( $addons ) {
	$addons['schema-generator'] = array(
		'name'   => __( 'Schema Generator', 'gcp-schema-generator' ),
		'desc'   => __( 'Deterministic Schema.org JSON-LD (AutoDealer + Product/Offer); yields to Yoast when present.', 'gcp-schema-generator' ),
		'icon'   => 'dashicons-editor-code',
		'status' => 'active',
		'link'   => admin_url( 'admin.php?page=gcp-schema' ),
	);
	return $addons;
}
