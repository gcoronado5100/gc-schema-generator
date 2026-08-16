<?php
/**
 * Vehicle inventory presence guard.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a vehicle inventory CPT is present on this install.
 *
 * This is the single guarded gate that keeps the plugin portable across
 * inventory-less sibling sites: every vehicle-specific read is fronted by this
 * check, so the plugin degrades gracefully (dealer node only) where the
 * e1connect-inventory plugin is absent. Detection uses a guarded core-WP call
 * ONLY — it never touches e1connect functions or classes directly, which would
 * fatal on sites where that plugin is not installed.
 *
 * @since feat/vehicle-schema-v1
 *
 * @return bool True when the vehicle CPT is registered, false otherwise.
 */
function gcp_schema_has_inventory() {
	return post_type_exists( gcp_schema_vehicle_post_type() );
}
