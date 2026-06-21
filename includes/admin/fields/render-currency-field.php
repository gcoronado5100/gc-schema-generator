<?php
/**
 * Currency field renderer for the settings page.
 *
 * @package GC_Schema_Generator
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
 * extensible via the gcsg_allowed_currencies filter — the same source the
 * sanitize callback uses, so the UI and the server agree by construction). The
 * stored value (from gcsg_get_settings()) is marked selected; an empty first
 * option allows "no currency set".
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_render_currency_field() {
	$s = gcsg_get_settings();

	// Mirror the sanitize whitelist from the SAME source (guard apply_filters so
	// the page degrades gracefully if called without core).
	$allowed = array( 'USD', 'CAD' );
	if ( function_exists( 'apply_filters' ) ) {
		$allowed = apply_filters( 'gcsg_allowed_currencies', $allowed );
	}
	?>
	<p>
		<label for="gcsg_currency"><strong><?php echo esc_html__( 'Currency', 'gc-schema-generator' ); ?></strong></label><br />
		<select id="gcsg_currency" name="gcsg_settings[currency]">
			<option value=""><?php echo esc_html__( '- Select -', 'gc-schema-generator' ); ?></option>
			<?php foreach ( (array) $allowed as $code ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $s['currency'], $code ); ?>><?php echo esc_html( $code ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="description"><?php echo esc_html__( 'Currency used for vehicle pricing in the schema output.', 'gc-schema-generator' ); ?></span>
	</p>
	<?php
}
