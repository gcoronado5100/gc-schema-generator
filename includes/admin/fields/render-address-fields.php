<?php
/**
 * Address & geo field group renderer for the settings page.
 *
 * @package GCP_Schema_Generator
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
 * geo coordinates. Values come from gcp_schema_get_settings() and are esc_attr-escaped
 * at output. A complete address is required for the AutoDealer schema to emit
 * (the actual gate is downstream in Phase 3); geo is optional and omitted when
 * left blank.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_render_address_fields() {
	$s = gcp_schema_get_settings();
	?>
	<p class="description"><?php echo esc_html__( 'A complete street address is required for the dealer schema to be emitted.', 'gcp-schema-generator' ); ?></p>

	<p>
		<label for="gcp_schema_street_address"><strong><?php echo esc_html__( 'Street address', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_street_address" name="gcp_schema_settings[street_address]" value="<?php echo esc_attr( $s['street_address'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Street and number (required for schema output).', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_address_locality"><strong><?php echo esc_html__( 'City / locality', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_address_locality" name="gcp_schema_settings[address_locality]" value="<?php echo esc_attr( $s['address_locality'] ); ?>">
	</p>

	<p>
		<label for="gcp_schema_address_region"><strong><?php echo esc_html__( 'State / region', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_address_region" name="gcp_schema_settings[address_region]" value="<?php echo esc_attr( $s['address_region'] ); ?>">
	</p>

	<p>
		<label for="gcp_schema_postal_code"><strong><?php echo esc_html__( 'Postal code', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_postal_code" name="gcp_schema_settings[postal_code]" value="<?php echo esc_attr( $s['postal_code'] ); ?>">
	</p>

	<p>
		<label for="gcp_schema_address_country"><strong><?php echo esc_html__( 'Country', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_address_country" name="gcp_schema_settings[address_country]" value="<?php echo esc_attr( $s['address_country'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Two-letter country code (e.g. US, CA) or full country name.', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_latitude"><strong><?php echo esc_html__( 'Latitude', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="small-text" id="gcp_schema_latitude" name="gcp_schema_settings[latitude]" value="<?php echo esc_attr( $s['latitude'] ); ?>">
		<label for="gcp_schema_longitude"><strong><?php echo esc_html__( 'Longitude', 'gcp-schema-generator' ); ?></strong></label>
		<input type="text" class="small-text" id="gcp_schema_longitude" name="gcp_schema_settings[longitude]" value="<?php echo esc_attr( $s['longitude'] ); ?>">
		<br /><span class="description"><?php echo esc_html__( 'Decimal degrees (e.g. 33.7490, -84.3880). Leave blank to omit geo from the schema.', 'gcp-schema-generator' ); ?></span>
	</p>
	<?php
}
