<?php
/**
 * Motors (STM) inventory presence guard.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Motors inventory is present on this install.
 *
 * This is the single guarded gate that makes the plugin portable across the
 * inventory-less sibling sites (mrautomotives.com, automaceast.com,
 * canadaauto.ai): every Motors-specific read is fronted by this check, so the
 * plugin degrades gracefully where Motors/inventory is absent.
 *
 * Detection uses guarded core-WP calls ONLY — `post_type_exists( 'listings' )`
 * and the presence of Motors' `stm_vehicle_listing_options` option. It never
 * calls Motors/STM functions or classes directly, which would fatal on sites
 * where the Motors plugin is not installed.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return bool True when Motors inventory is present, false otherwise.
 */
function gcsg_has_motors() {
	return post_type_exists( 'listings' ) || (bool) get_option( 'stm_vehicle_listing_options', false );
}
