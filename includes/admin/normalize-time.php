<?php
/**
 * Opening-hours time normalizer.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a time string to HH:MM (24h) or return '' if invalid.
 *
 * Accepts only zero-padded 24-hour times in the range 00:00–23:59. Anything that
 * does not match the strict `HH:MM` pattern (out-of-range hours/minutes, missing
 * colon, extra characters, empty/whitespace input) is rejected as '' so the
 * sanitize callback can omit incomplete or tampered opening-hours rows.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param mixed $value Raw time value from the settings form.
 * @return string A normalized `HH:MM` string, or '' when invalid.
 */
function gcp_schema_normalize_time( $value ) {
	$value = trim( (string) $value );

	return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
}
