<?php
/**
 * AutoDealer/LocalBusiness node builder.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the multi-type AutoDealer/LocalBusiness JSON-LD node (BIZ-01).
 *
 * Pure array builder: receives the sanitized 17-key settings array from the
 * caller (no get_option for settings, no echo), composes over the already-tested
 * primitives gcp_schema_schema_id() and gcp_schema_schema_day_iri(), and returns a node
 * array ready for gcp_schema_assemble_graph().
 *
 * Honesty rules:
 *  - D-06 suppression: missing name OR any required address field → array().
 *  - D-06 omit: empty optional fields produce NO key (no fabrication, no empty
 *    strings/objects).
 *  - D-08: never emits a rating/review aggregate (no fabricated reviews).
 *
 * Structure:
 *  - D-01 multi-type @type ['AutoDealer','LocalBusiness'].
 *  - OUT-04 stable @id === gcp_schema_schema_id('Organization').
 *  - D-04/D-05 opening_hours rows grouped by identical (opens,closes) into one
 *    OpeningHoursSpecification per group, each with a dayOfWeek array of full IRIs.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param array $s Sanitized settings (gcp_schema_get_settings() contract shape).
 *
 * @return array Dealer node, or array() when the required fields are not all set.
 */
function gcp_schema_build_dealer_node( array $s ) {
	// 1. D-06 required gate — strict 6 (Open Question #2 resolved to this subset).
	$required = array( 'name', 'street_address', 'address_locality', 'address_region', 'postal_code', 'address_country' );
	foreach ( $required as $req ) {
		if ( ! isset( $s[ $req ] ) || '' === trim( (string) $s[ $req ] ) ) {
			return array();
		}
	}

	// 2. Base node. The address gate above guarantees the sub-builder is non-empty.
	$node = array(
		'@type'   => array( 'AutoDealer', 'LocalBusiness' ),
		'@id'     => gcp_schema_schema_id( 'Organization' ),
		'name'    => wp_strip_all_tags( $s['name'] ),
		'url'     => home_url( '/' ),
		'address' => gcp_schema_build_postal_address( $s ),
	);

	// 3. Optional scalars — key ONLY when non-empty (D-06 omit).
	if ( isset( $s['legal_name'] ) && '' !== trim( (string) $s['legal_name'] ) ) {
		$node['legalName'] = wp_strip_all_tags( $s['legal_name'] );
	}
	if ( isset( $s['telephone'] ) && '' !== trim( (string) $s['telephone'] ) ) {
		$node['telephone'] = $s['telephone'];
	}
	if ( isset( $s['price_range'] ) && '' !== trim( (string) $s['price_range'] ) ) {
		$node['priceRange'] = $s['price_range'];
	}
	if ( isset( $s['image'] ) && '' !== trim( (string) $s['image'] ) ) {
		$node['image'] = $s['image'];
	}
	if ( isset( $s['logo'] ) && '' !== trim( (string) $s['logo'] ) ) {
		$node['logo'] = $s['logo'];
	}

	// geo: only when BOTH latitude and longitude are non-empty.
	$geo = gcp_schema_build_geo( $s );
	if ( ! empty( $geo ) ) {
		$node['geo'] = $geo;
	}

	// 4. Opening hours (D-04 grouping by identical opens|closes).
	$specs = gcp_schema_build_hours_specs( $s );
	if ( ! empty( $specs ) ) {
		$node['openingHoursSpecification'] = $specs;
	}

	// 5. sameAs resolver (Phase 2 D-06 precedence; Open Question #1 — defensive flatten).
	$own = isset( $s['same_as'] ) && is_array( $s['same_as'] ) ? $s['same_as'] : array();
	$gcp = get_option( 'gcp_social_options' ); // every1drives only; read-only, never written.
	// gcp_social_options VALUE shape unconfirmed (RESEARCH Open Q1) — flatten defensively; verify on live every1drives in Phase 8.
	$source = ( is_array( $gcp ) && ! empty( $gcp ) ) ? $gcp : $own;
	$urls   = array();
	foreach ( (array) $source as $maybe ) {
		if ( is_array( $maybe ) ) {
			foreach ( $maybe as $u ) {
				$u = trim( (string) $u );
				if ( '' !== $u ) {
					$urls[] = $u;
				}
			}
		} else {
			$u = trim( (string) $maybe );
			if ( '' !== $u ) {
				$urls[] = $u;
			}
		}
	}
	$urls = array_values( array_unique( $urls ) );
	if ( ! empty( $urls ) ) {
		$node['sameAs'] = $urls;
	}

	// 6. Financing (BIZ-02): makesOffer → LoanOrCredit, only when the setting is filled.
	$financing = gcp_schema_build_financing_offer( $s );
	if ( ! empty( $financing ) ) {
		$node['makesOffer'] = $financing;
	}

	// 7. Return — NEVER add a fabricated rating/review aggregate (D-08).
	return $node;
}
