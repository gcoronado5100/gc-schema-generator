<?php
/**
 * Yoast whole-graph stitching filter (Mode A).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stitch this plugin's schema into Yoast's assembled @graph (Mode A).
 *
 * Runs on the `wpseo_schema_graph` filter — the whole-graph hook chosen over
 * per-piece filters / Abstract_Schema_Piece extension because it needs no
 * Yoast class coupling and one code path handles every site-representation
 * case:
 *  1. Yoast emits an Organization node (Site Representation = Organization,
 *     @id ends in `#organization`) → enrich it into AutoDealer/LocalBusiness
 *     and use ITS @id for seller references.
 *  2. No Organization node (site represented as Person, or unset) → append
 *     this plugin's own complete dealer node and use its @id.
 * On vehicle singles the Product/Car node is appended with its Offer's
 * seller pointing at whichever @id won — the reference always resolves to a
 * node present in the same graph.
 *
 * Mode B's wp_head output keeps its Yoast early-return, so schema is emitted
 * exactly once per request in either mode. Suppressed requests (search / 404 /
 * noindex vehicle taxonomy archives) pass Yoast's graph through untouched —
 * this filter never removes anything Yoast built.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array $graph   Yoast's assembled graph pieces.
 * @param mixed $context Yoast Meta_Tags_Context (unused; reserved).
 *
 * @return array The (possibly) extended graph.
 */
function gcp_schema_filter_yoast_graph( $graph, $context = null ) {
	if ( ! is_array( $graph ) || gcp_schema_should_suppress() ) {
		return $graph;
	}

	$settings  = gcp_schema_get_settings();
	$seller_id = '';
	$org_found = false;

	foreach ( $graph as $i => $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		$types  = isset( $node['@type'] ) ? (array) $node['@type'] : array();
		$id     = isset( $node['@id'] ) ? (string) $node['@id'] : '';
		$is_org = in_array( 'Organization', $types, true )
			|| '#organization' === substr( $id, -strlen( '#organization' ) );

		if ( $is_org ) {
			$graph[ $i ] = gcp_schema_enrich_yoast_organization( $node, $settings );
			$seller_id   = $id;
			$org_found   = true;
			break;
		}
	}

	if ( ! $org_found ) {
		$dealer = gcp_schema_build_dealer_node( $settings );
		if ( ! empty( $dealer ) ) {
			$graph[]   = $dealer;
			$seller_id = $dealer['@id'];
		}
	}

	if (
		'vehicle' === gcp_schema_detect_context()
		&& gcp_schema_has_inventory()
		&& function_exists( 'get_queried_object_id' )
	) {
		$vehicle_node = gcp_schema_build_vehicle_product_node( get_queried_object_id(), $settings, $seller_id );
		if ( ! empty( $vehicle_node ) ) {
			$graph[] = $vehicle_node;
		}
	}

	return $graph;
}
