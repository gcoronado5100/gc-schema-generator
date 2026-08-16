<?php
/**
 * Real-price gate.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a stored vehicle price is a real, publishable amount.
 *
 * e1connect-inventory uses `_e1ci_price = 1` as its "Contact for pricing"
 * placeholder — that value must NEVER reach an Offer. When e1connect is
 * active this delegates to its `e1ci_price_is_real()` so both plugins always
 * agree; otherwise it replicates the exact same rule including the
 * `e1ci/price_floor` filter (default floor 1, strictly greater-than).
 *
 * @since feat/vehicle-schema-v1
 *
 * @param mixed $price Raw price value.
 *
 * @return bool True when the price is safe to emit.
 */
function gcp_schema_price_is_real( $price ) {
	if ( function_exists( 'e1ci_price_is_real' ) ) {
		return (bool) e1ci_price_is_real( $price );
	}

	if ( ! is_numeric( $price ) ) {
		return false;
	}

	$floor = 1;
	if ( function_exists( 'apply_filters' ) ) {
		$floor = apply_filters( 'e1ci/price_floor', $floor );
	}

	return (float) $price > (float) $floor;
}
