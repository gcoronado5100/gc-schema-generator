<?php
/**
 * Schema.org @id factory helper.
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a stable, absolute @id URI for a schema.org node.
 *
 * Every JSON-LD node in the graph needs a deterministic `@id` so other nodes can
 * reference it. This generalizes the reference plugin's `home_url('/') .
 * '#organization'` convention into a type/suffix factory: the @id is an absolute
 * URL anchored on `home_url()` (so it is graph-resolvable, never a bare
 * host-less fragment), with a stable fragment built from the node type and an
 * optional per-entity suffix.
 *
 * Deterministic: the same `$type`/`$suffix` always yields the same string within
 * a site — there are no random or time-based components.
 *
 * Examples:
 *   gcsg_schema_id( 'Organization' )  => https://example.com/#/schema/Organization
 *   gcsg_schema_id( 'Offer', '123' )  => https://example.com/#/schema/Offer/123
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param string $type   Schema.org node type (e.g. 'Organization', 'Offer').
 * @param string $suffix Optional per-entity suffix for unique node @ids (e.g. a post ID).
 *
 * @return string Absolute @id URI.
 */
function gcsg_schema_id( $type, $suffix = '' ) {
	$base = trailingslashit( home_url( '/' ) ) . '#/schema/' . $type;

	if ( '' !== (string) $suffix ) {
		$base .= '/' . $suffix;
	}

	return $base;
}
