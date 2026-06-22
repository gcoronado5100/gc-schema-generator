<?php
/**
 * JSON-LD @graph assembler.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrap a flat list of schema nodes in the {@context, @graph} envelope (OUT-03).
 *
 * This is the single graph-assembly point for the whole plugin. Phase 4
 * (BreadcrumbList), Phase 6 (Vehicle/Offer) and Phase 7 (financing) will pass
 * their additional nodes through this SAME assembler rather than each emitting
 * its own envelope, so there is exactly one {@context, @graph} per page.
 *
 * array_values() guarantees @graph serializes as a JSON array even when the
 * caller passes an associative/keyed array of nodes.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param array $nodes Ordered list of schema node arrays (may be empty).
 *
 * @return array {@context, @graph} structure ready for gcsg_encode_jsonld().
 */
function gcsg_assemble_graph( array $nodes ) {
	return array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $nodes ),
	);
}
