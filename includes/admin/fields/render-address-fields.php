<?php
/**
 * Address & geo field group renderer for the settings page.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Address & Location field group.
 *
 * Prints text inputs for the postal-address parts plus optional decimal-degree
 * geo coordinates. Values come from gcsg_get_settings() and are esc_attr-escaped
 * at output. A complete address is required for the AutoDealer schema to emit
 * (the actual gate is downstream in Phase 3); geo is optional and omitted when
 * left blank.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_render_address_fields() {
	$s = gcsg_get_settings();
	?>
	<p class="description"><?php echo esc_html__( 'A complete street address is required for the dealer schema to be emitted.', 'gc-schema-generator' ); ?></p>

	<p>
		<label for="gcsg_street_address"><strong><?php echo esc_html__( 'Street address', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_street_address" name="gcsg_settings[street_address]" value="<?php echo esc_attr( $s['street_address'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Street and number (required for schema output).', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_address_locality"><strong><?php echo esc_html__( 'City / locality', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_address_locality" name="gcsg_settings[address_locality]" value="<?php echo esc_attr( $s['address_locality'] ); ?>">
	</p>

	<p>
		<label for="gcsg_address_region"><strong><?php echo esc_html__( 'State / region', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_address_region" name="gcsg_settings[address_region]" value="<?php echo esc_attr( $s['address_region'] ); ?>">
	</p>

	<p>
		<label for="gcsg_postal_code"><strong><?php echo esc_html__( 'Postal code', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_postal_code" name="gcsg_settings[postal_code]" value="<?php echo esc_attr( $s['postal_code'] ); ?>">
	</p>

	<p>
		<label for="gcsg_address_country"><strong><?php echo esc_html__( 'Country', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_address_country" name="gcsg_settings[address_country]" value="<?php echo esc_attr( $s['address_country'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Two-letter country code (e.g. US, CA) or full country name.', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_latitude"><strong><?php echo esc_html__( 'Latitude', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="small-text" id="gcsg_latitude" name="gcsg_settings[latitude]" value="<?php echo esc_attr( $s['latitude'] ); ?>">
		<label for="gcsg_longitude"><strong><?php echo esc_html__( 'Longitude', 'gc-schema-generator' ); ?></strong></label>
		<input type="text" class="small-text" id="gcsg_longitude" name="gcsg_settings[longitude]" value="<?php echo esc_attr( $s['longitude'] ); ?>">
		<br /><span class="description"><?php echo esc_html__( 'Decimal degrees (e.g. 33.7490, -84.3880). Leave blank to omit geo from the schema.', 'gc-schema-generator' ); ?></span>
	</p>
	<?php
}
