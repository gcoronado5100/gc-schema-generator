<?php
/**
 * Settings API registration: option, sections, and fields.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the single dealer-identity option, its sections, and its fields.
 *
 * Hooked on `admin_init` in Plan 03. Registers one array option `gcsg_settings`
 * in the `gcsg` option group, bound to the Task-1 sanitize callback (the single
 * sanitize choke point — D-10). It then declares the 6 settings sections and
 * registers one field group per section, each pointing at a renderer function
 * implemented in Plan 03 (Wave 2). Section callbacks use the core no-op
 * `__return_false` so no intro markup is required here.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_register_settings() {
	// Option group 'gcsg' must match settings_fields('gcsg'); 'gcsg_settings' is the single array option.
	register_setting( 'gcsg', 'gcsg_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'gcsg_sanitize_settings',
			'default'           => array(), // gcsg_get_settings() supplies read defaults.
			'show_in_rest'      => false,   // Admin-only.
		)
	);

	// 6 sections (geo lives inside address).
	add_settings_section( 'gcsg_identity', __( 'Dealer Identity', 'gc-schema-generator' ), '__return_false', 'gcsg' );
	add_settings_section( 'gcsg_address', __( 'Address & Location', 'gc-schema-generator' ), '__return_false', 'gcsg' );
	add_settings_section( 'gcsg_hours', __( 'Opening Hours', 'gc-schema-generator' ), '__return_false', 'gcsg' );
	add_settings_section( 'gcsg_social', __( 'Social Profiles', 'gc-schema-generator' ), '__return_false', 'gcsg' );
	add_settings_section( 'gcsg_currency', __( 'Currency', 'gc-schema-generator' ), '__return_false', 'gcsg' );
	add_settings_section( 'gcsg_financing', __( 'Financing', 'gc-schema-generator' ), '__return_false', 'gcsg' );

	// One field per section: each renderer prints that section's whole group.
	// The renderer FUNCTIONS are implemented in Plan 03 (Wave 2).
	add_settings_field( 'gcsg_identity_group', __( 'Identity', 'gc-schema-generator' ), 'gcsg_render_identity_fields', 'gcsg', 'gcsg_identity' );
	add_settings_field( 'gcsg_address_group', __( 'Address', 'gc-schema-generator' ), 'gcsg_render_address_fields', 'gcsg', 'gcsg_address' );
	add_settings_field( 'gcsg_hours_group', __( 'Hours', 'gc-schema-generator' ), 'gcsg_render_hours_fields', 'gcsg', 'gcsg_hours' );
	add_settings_field( 'gcsg_social_group', __( 'Profiles', 'gc-schema-generator' ), 'gcsg_render_social_fields', 'gcsg', 'gcsg_social' );
	add_settings_field( 'gcsg_currency_group', __( 'Currency', 'gc-schema-generator' ), 'gcsg_render_currency_field', 'gcsg', 'gcsg_currency' );
	add_settings_field( 'gcsg_financing_group', __( 'Financing', 'gc-schema-generator' ), 'gcsg_render_financing_field', 'gcsg', 'gcsg_financing' );
}
