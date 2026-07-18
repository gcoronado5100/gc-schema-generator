<?php
/**
 * Social-profiles field group renderer for the settings page.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Social Profiles field group.
 *
 * Renders 6 fixed labeled URL inputs over the SAME network keys the sanitize
 * callback accepts (facebook, twitter, instagram, linkedin, youtube, tiktok).
 * Per D-07 the code-host network is omitted — low value for sameAs. When the
 * gabecode-plus-e1d
 * `gcp_social_options` option is non-empty, a read-only D-08 info notice tells
 * the admin those links are managed elsewhere and these fields are a fallback.
 * This renderer NEVER writes `gcp_social_options` (read-only detection only).
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_render_social_fields() {
	$s = gcp_schema_get_settings();

	// D-08: read-only detection of the sibling plugin's social option — never written here.
	$gcp = get_option( 'gcp_social_options' );
	if ( ! empty( $gcp ) ) {
		echo '<div class="notice notice-info inline"><p>'
			. esc_html__( 'Social links are managed on the gabecode-plus-e1d "Social Links" page. The fields below are used only as a fallback when that plugin isn\'t active.', 'gcp-schema-generator' )
			. '</p></div>';
	}

	$networks = array(
		'facebook'  => 'Facebook',
		'twitter'   => 'Twitter / X',
		'instagram' => 'Instagram',
		'linkedin'  => 'LinkedIn',
		'youtube'   => 'YouTube',
		'tiktok'    => 'TikTok',
	);

	$same = (array) $s['same_as'];

	foreach ( $networks as $key => $label ) {
		$val = isset( $same[ $key ] ) ? $same[ $key ] : '';
		?>
		<p>
			<label for="gcp_schema_same_as_<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br />
			<input type="url" class="regular-text" id="gcp_schema_same_as_<?php echo esc_attr( $key ); ?>" name="gcp_schema_settings[same_as][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $val ); ?>">
		</p>
		<?php
	}
}
