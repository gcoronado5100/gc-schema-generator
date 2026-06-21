<?php
/**
 * Financing field renderer for the settings page.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Financing description textarea.
 *
 * Prints a multiline textarea pre-populated from gcsg_get_settings() and escaped
 * with esc_textarea(). This free-text description feeds the financing
 * representation in the AutoDealer schema (Phase 7).
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_render_financing_field() {
	$s = gcsg_get_settings();
	?>
	<p>
		<label for="gcsg_financing_description"><strong><?php echo esc_html__( 'Financing description', 'gc-schema-generator' ); ?></strong></label><br />
		<textarea class="large-text" rows="4" id="gcsg_financing_description" name="gcsg_settings[financing_description]"><?php echo esc_textarea( $s['financing_description'] ); ?></textarea>
		<span class="description"><?php echo esc_html__( 'Describe the financing options offered (used in the dealer schema).', 'gc-schema-generator' ); ?></span>
	</p>
	<?php
}
