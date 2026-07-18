<?php
/**
 * Plugin activation handler.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run on plugin activation.
 *
 * Phase 1 has nothing to provision (no CPT, no roles, no scheduled events), so
 * this is a deliberate no-op that creates no orphaned data. In particular it
 * does NOT write the `gcp_schema_settings` option — settings are created by the
 * Phase 2 settings page, which keeps uninstall cleanup trivial. Downstream
 * phases (e.g. scheduled events) add their provisioning here.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_activate() {
	// Intentionally empty: no setup required in Phase 1 (no orphaned data).
}
