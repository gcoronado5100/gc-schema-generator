<?php
/**
 * Identity field group renderer for the settings page.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Dealer Identity field group.
 *
 * Prints inputs for name, legal_name, logo (media), image (media), telephone,
 * and price_range. Values are pre-populated from gcp_schema_get_settings() and escaped
 * at output (sanitize-in lives in the Plan-02 sanitize callback; escape-out here).
 * Logo and image carry the wp.media picker hooks (gcp-schema-media-pick /
 * gcp-schema-media-remove) whose data-target ids the Plan-03 settings.js resolves.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_render_identity_fields() {
	$s = gcp_schema_get_settings();
	?>
	<p>
		<label for="gcp_schema_name"><strong><?php echo esc_html__( 'Business name', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_name" name="gcp_schema_settings[name]" value="<?php echo esc_attr( $s['name'] ); ?>">
		<span class="description"><?php echo esc_html__( 'The public-facing dealership name (required for schema output).', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_legal_name"><strong><?php echo esc_html__( 'Legal name', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_legal_name" name="gcp_schema_settings[legal_name]" value="<?php echo esc_attr( $s['legal_name'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Registered legal entity name, if different from the business name.', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_logo"><strong><?php echo esc_html__( 'Logo', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_logo" name="gcp_schema_settings[logo]" value="<?php echo esc_attr( $s['logo'] ); ?>">
		<button type="button" class="button gcp-schema-media-pick" data-target="#gcp_schema_logo"><?php echo esc_html__( 'Select logo', 'gcp-schema-generator' ); ?></button>
		<button type="button" class="button gcp-schema-media-remove" data-target="#gcp_schema_logo"><?php echo esc_html__( 'Remove', 'gcp-schema-generator' ); ?></button>
		<?php if ( '' !== $s['logo'] ) : ?>
			<br /><img src="<?php echo esc_url( $s['logo'] ); ?>" alt="" style="max-width:150px;height:auto;margin-top:8px;">
		<?php endif; ?>
		<span class="description"><?php echo esc_html__( 'URL of the dealership logo image.', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_image"><strong><?php echo esc_html__( 'Image', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_image" name="gcp_schema_settings[image]" value="<?php echo esc_attr( $s['image'] ); ?>">
		<button type="button" class="button gcp-schema-media-pick" data-target="#gcp_schema_image"><?php echo esc_html__( 'Select image', 'gcp-schema-generator' ); ?></button>
		<button type="button" class="button gcp-schema-media-remove" data-target="#gcp_schema_image"><?php echo esc_html__( 'Remove', 'gcp-schema-generator' ); ?></button>
		<?php if ( '' !== $s['image'] ) : ?>
			<br /><img src="<?php echo esc_url( $s['image'] ); ?>" alt="" style="max-width:150px;height:auto;margin-top:8px;">
		<?php endif; ?>
		<span class="description"><?php echo esc_html__( 'URL of a representative photo of the business.', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_telephone"><strong><?php echo esc_html__( 'Telephone', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_telephone" name="gcp_schema_settings[telephone]" value="<?php echo esc_attr( $s['telephone'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Primary contact phone number.', 'gcp-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcp_schema_price_range"><strong><?php echo esc_html__( 'Price range', 'gcp-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcp_schema_price_range" name="gcp_schema_settings[price_range]" value="<?php echo esc_attr( $s['price_range'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Symbolic price range, e.g. "$$" or "$15,000-$45,000".', 'gcp-schema-generator' ); ?></span>
	</p>
	<?php
}
