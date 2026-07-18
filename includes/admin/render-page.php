<?php
/**
 * Settings page renderer (the GabeCode sub-page callback).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the dealer-identity settings page.
 *
 * When the GabeCode hub is active the page renders inside the shared shell
 * (branded banner + tab nav + shadcn cards) using the hub's card renderer;
 * otherwise it falls back to the plain WordPress Settings API layout. Either
 * way it re-checks the capability, shows a warning notice when the business
 * name or a required address part is empty (save still succeeds), and posts the
 * Settings API form to options.php (all security delegated to the Settings API).
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s = gcp_schema_get_settings();

	$addr_ok = '' !== $s['street_address']
		&& '' !== $s['address_locality']
		&& '' !== $s['address_region']
		&& '' !== $s['postal_code']
		&& '' !== $s['address_country'];

	$incomplete = ( '' === $s['name'] || ! $addr_ok );

	// Hard dependency: this callback only runs when the hub is active (the boot
	// gate registers it only then), so we always render inside the shell.
	gcp_admin_open( 'gcp-schema' );

	if ( $incomplete ) {
		echo '<div class="notice notice-warning"><p>'
			. esc_html__( "Dealer schema won't be emitted until both Business name and a complete address are filled in.", 'gcp-schema-generator' )
			. '</p></div>';
	}

	echo '<form method="post" action="options.php" class="gcp-form">';
	settings_fields( 'gcp_schema' ); // Nonce + option-group hidden fields.
	echo '<div class="gcp-grid">';
	gcp_render_settings_cards( 'gcp_schema' ); // 6 sections → cards.
	echo '</div>';
	echo '<div class="gcp-actions">';
	submit_button( __( 'Save changes', 'gcp-schema-generator' ), 'primary', 'submit', false );
	echo '</div>';
	echo '</form>';

	gcp_admin_close();
}
