<?php
/**
 * Currency field renderer for the settings page.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Currency select field.
 *
 * Prints a <select> whose options mirror the sanitize whitelist (USD/CAD floor,
 * extensible via the gcp_schema_allowed_currencies filter — the same source the
 * sanitize callback uses, so the UI and the server agree by construction). The
 * stored value (from gcp_schema_get_settings()) is marked selected; an empty first
 * option allows "no currency set".
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_render_currency_field() {
	$s = gcp_schema_get_settings();

	// Mirror the sanitize whitelist from the SAME source (guard apply_filters so
	// the page degrades gracefully if called without core).
	$allowed = array( 'USD', 'CAD' );
	if ( function_exists( 'apply_filters' ) ) {
		$allowed = apply_filters( 'gcp_schema_allowed_currencies', $allowed );
	}
	?>
	<p>
		<label for="gcp_schema_currency"><strong><?php echo esc_html__( 'Currency', 'gcp-schema-generator' ); ?></strong></label><br />
		<select id="gcp_schema_currency" name="gcp_schema_settings[currency]">
			<option value=""><?php echo esc_html__( '- Select -', 'gcp-schema-generator' ); ?></option>
			<?php foreach ( (array) $allowed as $code ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $s['currency'], $code ); ?>><?php echo esc_html( $code ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="description"><?php echo esc_html__( 'Currency used for vehicle pricing in the schema output.', 'gcp-schema-generator' ); ?></span>
	</p>
	<?php
}
