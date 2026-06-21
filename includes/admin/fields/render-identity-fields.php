<?php
/**
 * Identity field group renderer for the settings page.
 *
 * @package GC_Schema_Generator
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
 * and price_range. Values are pre-populated from gcsg_get_settings() and escaped
 * at output (sanitize-in lives in the Plan-02 sanitize callback; escape-out here).
 * Logo and image carry the wp.media picker hooks (gcsg-media-pick /
 * gcsg-media-remove) whose data-target ids the Plan-03 settings.js resolves.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcsg_render_identity_fields() {
	$s = gcsg_get_settings();
	?>
	<p>
		<label for="gcsg_name"><strong><?php echo esc_html__( 'Business name', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_name" name="gcsg_settings[name]" value="<?php echo esc_attr( $s['name'] ); ?>">
		<span class="description"><?php echo esc_html__( 'The public-facing dealership name (required for schema output).', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_legal_name"><strong><?php echo esc_html__( 'Legal name', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_legal_name" name="gcsg_settings[legal_name]" value="<?php echo esc_attr( $s['legal_name'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Registered legal entity name, if different from the business name.', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_logo"><strong><?php echo esc_html__( 'Logo', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_logo" name="gcsg_settings[logo]" value="<?php echo esc_attr( $s['logo'] ); ?>">
		<button type="button" class="button gcsg-media-pick" data-target="#gcsg_logo"><?php echo esc_html__( 'Select logo', 'gc-schema-generator' ); ?></button>
		<button type="button" class="button gcsg-media-remove" data-target="#gcsg_logo"><?php echo esc_html__( 'Remove', 'gc-schema-generator' ); ?></button>
		<?php if ( '' !== $s['logo'] ) : ?>
			<br /><img src="<?php echo esc_url( $s['logo'] ); ?>" alt="" style="max-width:150px;height:auto;margin-top:8px;">
		<?php endif; ?>
		<span class="description"><?php echo esc_html__( 'URL of the dealership logo image.', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_image"><strong><?php echo esc_html__( 'Image', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_image" name="gcsg_settings[image]" value="<?php echo esc_attr( $s['image'] ); ?>">
		<button type="button" class="button gcsg-media-pick" data-target="#gcsg_image"><?php echo esc_html__( 'Select image', 'gc-schema-generator' ); ?></button>
		<button type="button" class="button gcsg-media-remove" data-target="#gcsg_image"><?php echo esc_html__( 'Remove', 'gc-schema-generator' ); ?></button>
		<?php if ( '' !== $s['image'] ) : ?>
			<br /><img src="<?php echo esc_url( $s['image'] ); ?>" alt="" style="max-width:150px;height:auto;margin-top:8px;">
		<?php endif; ?>
		<span class="description"><?php echo esc_html__( 'URL of a representative photo of the business.', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_telephone"><strong><?php echo esc_html__( 'Telephone', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_telephone" name="gcsg_settings[telephone]" value="<?php echo esc_attr( $s['telephone'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Primary contact phone number.', 'gc-schema-generator' ); ?></span>
	</p>

	<p>
		<label for="gcsg_price_range"><strong><?php echo esc_html__( 'Price range', 'gc-schema-generator' ); ?></strong></label><br />
		<input type="text" class="regular-text" id="gcsg_price_range" name="gcsg_settings[price_range]" value="<?php echo esc_attr( $s['price_range'] ); ?>">
		<span class="description"><?php echo esc_html__( 'Symbolic price range, e.g. "$$" or "$15,000-$45,000".', 'gc-schema-generator' ); ?></span>
	</p>
	<?php
}
