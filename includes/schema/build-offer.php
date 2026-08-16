<?php
/**
 * Per-vehicle Offer builder.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the Offer sub-node for a vehicle Product/Car node.
 *
 * Pure array builder over the normalized vehicle array from
 * gcp_schema_get_vehicle_data(). Honesty rules:
 *  - A null price (absent or the contact-for-pricing placeholder) → array();
 *    the Product then carries NO offers key at all — never a fabricated price.
 *  - Sold vehicles still get an Offer, honestly marked SoldOut (their single
 *    URLs keep resolving publicly; the availability IRI mirrors e1connect's
 *    Open Graph mapping so the two never disagree on one page).
 *  - itemCondition is always UsedCondition: the marketplace sells used
 *    vehicles only and the inventory has no condition meta to read.
 *  - No priceValidUntil: there is no expiry meta, and synthesizing one would
 *    be fabricated data (accepted Rich Results Test warning).
 *
 * The currency falls back to CAD when the setting is empty — the exact
 * default e1connect's e1ci_og_currency() uses for the OG price tags.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array  $vehicle   Normalized vehicle data (gcp_schema_get_vehicle_data shape).
 * @param array  $settings  Sanitized plugin settings (for `currency`).
 * @param string $seller_id Schema @id of the selling dealer node.
 *
 * @return array Offer node, or array() when no real price exists.
 */
function gcp_schema_build_offer( array $vehicle, array $settings, $seller_id ) {
	if ( ! isset( $vehicle['price'] ) || null === $vehicle['price'] ) {
		return array();
	}

	$currency = isset( $settings['currency'] ) && '' !== trim( (string) $settings['currency'] )
		? strtoupper( trim( (string) $settings['currency'] ) )
		: 'CAD';

	$offer = array(
		'@type'         => 'Offer',
		'price'         => number_format( (float) $vehicle['price'], 2, '.', '' ),
		'priceCurrency' => $currency,
		'availability'  => gcp_schema_map_availability( isset( $vehicle['status'] ) ? $vehicle['status'] : '' ),
		'itemCondition' => 'https://schema.org/UsedCondition',
	);

	if ( ! empty( $vehicle['url'] ) ) {
		$offer['url'] = $vehicle['url'];
	}

	$seller_id = trim( (string) $seller_id );
	if ( '' !== $seller_id ) {
		$offer['seller'] = array( '@id' => $seller_id );
	}

	return $offer;
}
