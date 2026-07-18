<?php
/**
 * Plugin deactivation handler.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run on plugin deactivation.
 *
 * Phase 1 has no scheduled events or transients to clear, so this is a
 * deliberate side-effect-free no-op. Deactivation preserves user settings:
 * it MUST NOT delete `gcp_schema_settings` (only uninstall removes them).
 * Downstream phases (e.g. caching, cron) add their teardown here.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return void
 */
function gcp_schema_deactivate() {
	// Intentionally empty: nothing to tear down in Phase 1 (settings preserved).
}
