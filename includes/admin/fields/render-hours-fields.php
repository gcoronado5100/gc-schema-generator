<?php
/**
 * Opening-hours field group renderer for the settings page.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Opening Hours field group as 7 Mon-Sun rows.
 *
 * The stored shape (from the Plan-02 sanitize callback) is a list of
 * {day, opens, closes} rows; this renderer indexes it by day to re-populate the
 * 7 fixed rows. Each row has an open time input, a close time input, and a
 * "Closed" checkbox. Closed days are omitted from the saved structure (a day
 * with empty times simply produces no stored row), so the checkbox renders
 * unchecked by default.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_render_hours_fields() {
	$s = gcp_schema_get_settings();

	// Index the stored list by day for re-population.
	$by_day = array();
	foreach ( (array) $s['opening_hours'] as $row ) {
		if ( isset( $row['day'] ) ) {
			$by_day[ $row['day'] ] = $row;
		}
	}

	$days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
	?>
	<div class="gcp-schema-hours">
		<?php foreach ( $days as $day ) : ?>
			<?php
			$opens  = isset( $by_day[ $day ]['opens'] ) ? $by_day[ $day ]['opens'] : '';
			$closes = isset( $by_day[ $day ]['closes'] ) ? $by_day[ $day ]['closes'] : '';
			?>
			<p class="gcp-schema-hours-row">
				<label style="display:inline-block;min-width:96px;"><strong><?php echo esc_html( $day ); ?></strong></label>
				<input type="time" name="gcp_schema_settings[opening_hours][<?php echo esc_attr( $day ); ?>][opens]" value="<?php echo esc_attr( $opens ); ?>">
				<span>&ndash;</span>
				<input type="time" name="gcp_schema_settings[opening_hours][<?php echo esc_attr( $day ); ?>][closes]" value="<?php echo esc_attr( $closes ); ?>">
				<label>
					<input type="checkbox" name="gcp_schema_settings[opening_hours][<?php echo esc_attr( $day ); ?>][closed]" value="1">
					<?php echo esc_html__( 'Closed', 'gcp-schema-generator' ); ?>
				</label>
			</p>
		<?php endforeach; ?>
		<p class="description"><?php echo esc_html__( 'Leave a day blank or mark it Closed to omit it from the schema.', 'gcp-schema-generator' ); ?></p>
	</div>
	<?php
}
