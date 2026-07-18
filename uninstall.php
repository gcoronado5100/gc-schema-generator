<?php
/**
 * Uninstall cleanup for GC Schema Generator.
 *
 * Runs when the plugin is deleted from the WordPress admin. Removes the single
 * `gcp_schema_settings` option this plugin ever writes, leaving no plugin rows behind
 * in wp_options. No transients exist in Phase 1, so nothing else is deleted.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'gcp_schema_settings' );
