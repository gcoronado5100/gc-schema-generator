<?php
/**
 * Vehicle post-type name resolver.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The post-type name of the vehicle inventory CPT this plugin reads from.
 *
 * The e1connect-inventory plugin registers its CPT as `vehicle` (rewrite slug
 * `listings`). Every inventory-aware code path in this plugin resolves the
 * post-type through this single helper so a sibling site with a differently
 * named CPT can re-point the whole pipeline via one filter.
 *
 * `apply_filters` is function_exists-guarded so the helper stays callable from
 * the standalone test harness, which loads no WordPress plugin API.
 *
 * @since feat/vehicle-schema-v1
 *
 * @return string Vehicle post-type name (default 'vehicle').
 */
function gcp_schema_vehicle_post_type() {
	$post_type = 'vehicle';
	if ( function_exists( 'apply_filters' ) ) {
		$post_type = (string) apply_filters( 'gcp_schema/vehicle_post_type', $post_type );
	}
	return $post_type;
}
