<?php
/**
 * Vehicle Product/Car node builder.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the multi-type Product/Car JSON-LD node for one vehicle.
 *
 * Single node with `@type: ['Product','Car']` — Google's Product rich result
 * requires the Product type, and after the "vehicle listing" rich result was
 * retired (Sept 2025) multi-typing one node is the standard way to keep the
 * Car vocabulary (VIN, odometer, engine, …) on the same entity as the Offer.
 *
 * The Product type is attached ONLY when a real Offer can be attached with it.
 * Google's Product snippet validation requires one of `offers`, `review` or
 * `aggregateRating`; this plugin never fabricates ratings (D-08) and never
 * emits a price-less/zero-price Offer, so a contact-for-pricing vehicle would
 * otherwise surface in Search Console as "Either 'offers', 'review', or
 * 'aggregateRating' should be specified". Google does not treat `Car` as a
 * Product subtype, so a plain `Car` node keeps the vehicle semantics (VIN,
 * odometer, engine, brand, …) without being evaluated as a Product snippet.
 *
 * Honesty rules (same discipline as the dealer node):
 *  - Required gate: a name AND at least one image; otherwise array() —
 *    Google's Product markup is invalid without an image, so a photo-less
 *    vehicle emits nothing rather than something broken.
 *  - Every optional property is omitted when its source is empty/null.
 *  - Real price  → `@type: ['Product','Car']` + `offers`.
 *  - No real price → `@type: 'Car'` only, no `offers` key (never price 0).
 *  - NEVER an aggregateRating/review (D-08).
 *
 * @since feat/vehicle-schema-v1
 *
 * @param int    $post_id   Vehicle post ID.
 * @param array  $settings  Sanitized plugin settings (passed through to the Offer).
 * @param string $seller_id Schema @id of the selling dealer node.
 *
 * @return array Product/Car node, or array() when the required gate fails.
 */
function gcp_schema_build_vehicle_product_node( $post_id, array $settings, $seller_id ) {
	$v = gcp_schema_get_vehicle_data( $post_id );

	// Required gate: name + at least one image.
	if ( '' === $v['name'] || empty( $v['images'] ) ) {
		return array();
	}

	// Build the Offer first: it decides whether the node may carry the Product type.
	$offer = gcp_schema_build_offer( $v, $settings, $seller_id );

	$node = array(
		'@type'         => empty( $offer ) ? 'Car' : array( 'Product', 'Car' ),
		'name'          => $v['name'],
		'image'         => $v['images'],
		'itemCondition' => 'https://schema.org/UsedCondition',
	);

	if ( '' !== $v['url'] ) {
		$node['@id'] = $v['url'] . '#vehicle';
		$node['url'] = $v['url'];
	}

	if ( '' !== $v['description'] ) {
		$node['description'] = $v['description'];
	}
	if ( '' !== $v['sku'] ) {
		$node['sku'] = $v['sku'];
	}
	if ( 17 === strlen( $v['vin'] ) ) {
		$node['vehicleIdentificationNumber'] = $v['vin'];
	}
	if ( '' !== $v['brand'] ) {
		$node['brand'] = array(
			'@type' => 'Brand',
			'name'  => $v['brand'],
		);
	}
	if ( '' !== $v['model'] ) {
		$node['model'] = $v['model'];
	}
	if ( null !== $v['year'] ) {
		$node['vehicleModelDate'] = (string) $v['year'];
	}
	if ( null !== $v['mileage_km'] ) {
		$node['mileageFromOdometer'] = array(
			'@type'    => 'QuantitativeValue',
			'value'    => $v['mileage_km'],
			'unitCode' => 'KMT',
		);
	}
	if ( '' !== $v['body_type'] ) {
		$node['bodyType'] = $v['body_type'];
	}
	if ( '' !== $v['fuel'] ) {
		$node['fuelType'] = $v['fuel'];
	}
	if ( '' !== $v['transmission'] ) {
		$node['vehicleTransmission'] = $v['transmission'];
	}
	if ( '' !== $v['drivetrain'] ) {
		$node['driveWheelConfiguration'] = $v['drivetrain'];
	}
	if ( '' !== $v['color_exterior'] ) {
		$node['color'] = $v['color_exterior'];
	}
	if ( '' !== $v['color_interior'] ) {
		$node['vehicleInteriorColor'] = $v['color_interior'];
	}
	if ( null !== $v['doors'] ) {
		$node['numberOfDoors'] = $v['doors'];
	}
	if ( '' !== $v['engine'] ) {
		$node['vehicleEngine'] = array(
			'@type' => 'EngineSpecification',
			'name'  => $v['engine'],
		);
	}

	if ( ! empty( $offer ) ) {
		$node['offers'] = $offer;
	}

	// NEVER add a fabricated rating/review aggregate (D-08).
	return $node;
}
