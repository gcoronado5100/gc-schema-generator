<?php
/**
 * Yoast Organization node enrichment (Mode A).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enrich Yoast's Organization node into an AutoDealer/LocalBusiness.
 *
 * STRICTLY ADDITIVE: Yoast owns its Organization node (name, url, logo,
 * sameAs come from Yoast's own Site Representation settings), so this never
 * overwrites an existing key — it only promotes the @type and fills in the
 * local-business facts Yoast has no fields for: address, geo, opening hours,
 * telephone, and priceRange. Empty settings add nothing (D-06 omit).
 *
 * Composes over the same sub-builders as the Mode B dealer node
 * (gcp_schema_build_postal_address / _geo / _hours_specs) so both modes emit
 * identical shapes for identical settings.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array $node     Yoast's Organization graph piece.
 * @param array $settings Sanitized plugin settings.
 *
 * @return array The enriched node.
 */
function gcp_schema_enrich_yoast_organization( array $node, array $settings ) {
	// Promote @type: our dealer types first, Yoast's existing types deduped after.
	$existing      = isset( $node['@type'] ) ? (array) $node['@type'] : array();
	$node['@type'] = array_values( array_unique( array_merge( array( 'AutoDealer', 'LocalBusiness' ), $existing ) ) );

	if ( ! isset( $node['address'] ) ) {
		$address = gcp_schema_build_postal_address( $settings );
		if ( ! empty( $address ) ) {
			$node['address'] = $address;
		}
	}

	if ( ! isset( $node['geo'] ) ) {
		$geo = gcp_schema_build_geo( $settings );
		if ( ! empty( $geo ) ) {
			$node['geo'] = $geo;
		}
	}

	if ( ! isset( $node['openingHoursSpecification'] ) ) {
		$specs = gcp_schema_build_hours_specs( $settings );
		if ( ! empty( $specs ) ) {
			$node['openingHoursSpecification'] = $specs;
		}
	}

	if ( ! isset( $node['telephone'] ) && isset( $settings['telephone'] ) && '' !== trim( (string) $settings['telephone'] ) ) {
		$node['telephone'] = $settings['telephone'];
	}

	if ( ! isset( $node['priceRange'] ) && isset( $settings['price_range'] ) && '' !== trim( (string) $settings['price_range'] ) ) {
		$node['priceRange'] = $settings['price_range'];
	}

	if ( ! isset( $node['makesOffer'] ) ) {
		$financing = gcp_schema_build_financing_offer( $settings );
		if ( ! empty( $financing ) ) {
			$node['makesOffer'] = $financing;
		}
	}

	// NEVER add a fabricated rating/review aggregate (D-08).
	return $node;
}
