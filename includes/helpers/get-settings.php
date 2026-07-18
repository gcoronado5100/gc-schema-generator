<?php
/**
 * Settings accessor helper.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read the plugin settings, merged over safe defaults.
 *
 * The entire plugin reads dealer-identity configuration through this single
 * accessor. It returns an associative array that always contains every expected
 * key with a safe default, even when the `gcp_schema_settings` option does not yet
 * exist (it is created by the Phase 2 settings page). Saved values win over
 * defaults; missing keys fall back to defaults.
 *
 * The option is read from the database at most once per request: the merged
 * result is held in a static cache and returned on every subsequent call.
 *
 * This accessor performs NO sanitization and NO output — it is a pure read.
 * Sanitization belongs to the Phase 2 settings sanitize callback.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return array Dealer-identity settings merged over defaults.
 */
function gcp_schema_get_settings() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$defaults = array(
		'name'                  => '',
		'legal_name'            => '',
		'logo'                  => '',
		'image'                 => '',
		'telephone'             => '',
		'price_range'           => '',
		'street_address'        => '',
		'address_locality'      => '',
		'address_region'        => '',
		'postal_code'           => '',
		'address_country'       => '',
		'latitude'              => '',
		'longitude'             => '',
		'opening_hours'         => array(),
		'same_as'               => array(),
		'currency'              => '',
		'financing_description' => '',
	);

	$saved = get_option( 'gcp_schema_settings', array() );
	$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );

	return $cache;
}
