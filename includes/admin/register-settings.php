<?php
/**
 * Settings API registration: option, sections, and fields.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the single dealer-identity option, its sections, and its fields.
 *
 * Hooked on `admin_init` in Plan 03. Registers one array option `gcp_schema_settings`
 * in the `gcp_schema` option group, bound to the Task-1 sanitize callback (the single
 * sanitize choke point — D-10). It then declares the 6 settings sections and
 * registers one field group per section, each pointing at a renderer function
 * implemented in Plan 03 (Wave 2). Section callbacks use the core no-op
 * `__return_false` so no intro markup is required here.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_register_settings() {
	// Option group 'gcp_schema' must match settings_fields('gcp_schema'); 'gcp_schema_settings' is the single array option.
	register_setting( 'gcp_schema', 'gcp_schema_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'gcp_schema_sanitize_settings',
			'default'           => array(), // gcp_schema_get_settings() supplies read defaults.
			'show_in_rest'      => false,   // Admin-only.
		)
	);

	// 6 sections (geo lives inside address).
	add_settings_section( 'gcp_schema_identity', __( 'Dealer Identity', 'gcp-schema-generator' ), '__return_false', 'gcp_schema' );
	add_settings_section( 'gcp_schema_address', __( 'Address & Location', 'gcp-schema-generator' ), '__return_false', 'gcp_schema' );
	add_settings_section( 'gcp_schema_hours', __( 'Opening Hours', 'gcp-schema-generator' ), '__return_false', 'gcp_schema' );
	add_settings_section( 'gcp_schema_social', __( 'Social Profiles', 'gcp-schema-generator' ), '__return_false', 'gcp_schema' );
	add_settings_section( 'gcp_schema_currency', __( 'Currency', 'gcp-schema-generator' ), '__return_false', 'gcp_schema' );
	add_settings_section( 'gcp_schema_financing', __( 'Financing', 'gcp-schema-generator' ), '__return_false', 'gcp_schema' );

	// One field per section: each renderer prints that section's whole group.
	// The renderer FUNCTIONS are implemented in Plan 03 (Wave 2).
	add_settings_field( 'gcp_schema_identity_group', __( 'Identity', 'gcp-schema-generator' ), 'gcp_schema_render_identity_fields', 'gcp_schema', 'gcp_schema_identity' );
	add_settings_field( 'gcp_schema_address_group', __( 'Address', 'gcp-schema-generator' ), 'gcp_schema_render_address_fields', 'gcp_schema', 'gcp_schema_address' );
	add_settings_field( 'gcp_schema_hours_group', __( 'Hours', 'gcp-schema-generator' ), 'gcp_schema_render_hours_fields', 'gcp_schema', 'gcp_schema_hours' );
	add_settings_field( 'gcp_schema_social_group', __( 'Profiles', 'gcp-schema-generator' ), 'gcp_schema_render_social_fields', 'gcp_schema', 'gcp_schema_social' );
	add_settings_field( 'gcp_schema_currency_group', __( 'Currency', 'gcp-schema-generator' ), 'gcp_schema_render_currency_field', 'gcp_schema', 'gcp_schema_currency' );
	add_settings_field( 'gcp_schema_financing_group', __( 'Financing', 'gcp-schema-generator' ), 'gcp_schema_render_financing_field', 'gcp_schema', 'gcp_schema_financing' );
}
