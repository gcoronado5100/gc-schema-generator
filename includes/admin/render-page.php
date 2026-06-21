<?php
/**
 * Settings page renderer (the top-level menu callback).
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the dealer-identity settings page via the WordPress Settings API.
 *
 * The render callback for the top-level menu. It re-checks the capability, shows
 * a persistent D-09 warning notice when the business name or any required address
 * part is empty (save still succeeds — no hard server-side block), then prints the
 * Settings API form posting to options.php. Nonce and option-group hidden fields
 * come from settings_fields(), and the 6 sections/field groups render through
 * do_settings_sections() — all security is delegated to the Settings API (D-10).
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s = gcsg_get_settings();

	$addr_ok = '' !== $s['street_address']
		&& '' !== $s['address_locality']
		&& '' !== $s['address_region']
		&& '' !== $s['postal_code']
		&& '' !== $s['address_country'];

	if ( '' === $s['name'] || ! $addr_ok ) {
		echo '<div class="notice notice-warning"><p>'
			. esc_html__( "Dealer schema won't be emitted until both Business name and a complete address are filled in.", 'gc-schema-generator' )
			. '</p></div>';
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Schema Generator', 'gc-schema-generator' ) . '</h1>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'gcsg' );      // Nonce + option-group hidden fields.
	do_settings_sections( 'gcsg' ); // Renders the 6 sections + their field groups.
	submit_button();                // "Save Changes".
	echo '</form>';
	echo '</div>';
}
