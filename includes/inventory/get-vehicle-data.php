<?php
/**
 * Normalized vehicle data façade.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read every `_e1ci_*` meta and taxonomy a schema node needs, normalized.
 *
 * The single read point between e1connect-inventory's storage and the schema
 * builders: node builders never touch get_post_meta/get_the_terms directly.
 * Numeric fields come back as float/int or null (never fabricated zeros);
 * string fields come back trimmed or ''; `price` is already gated through
 * gcp_schema_price_is_real() so a null price means "no Offer". Results are
 * statically cached per post ID for the request.
 *
 * Description preference: the post content (AI-generated vehicle description,
 * tag-stripped, capped) with the stored Yoast meta description as fallback.
 *
 * @since feat/vehicle-schema-v1
 *
 * @param int $post_id Vehicle post ID.
 *
 * @return array Normalized vehicle data (all keys always present).
 */
function gcp_schema_get_vehicle_data( $post_id ) {
	static $cache = array();

	$post_id = (int) $post_id;
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$meta = static function ( $key ) use ( $post_id ) {
		if ( ! function_exists( 'get_post_meta' ) ) {
			return '';
		}
		$value = get_post_meta( $post_id, $key, true );
		return is_scalar( $value ) ? trim( (string) $value ) : $value;
	};

	// --- Identity ---------------------------------------------------------
	$name = function_exists( 'get_the_title' ) ? trim( (string) get_the_title( $post_id ) ) : '';
	$url  = function_exists( 'get_permalink' ) ? (string) get_permalink( $post_id ) : '';

	// --- Description: post content first, Yoast meta description fallback --
	$description = '';
	if ( function_exists( 'get_post' ) ) {
		$post = get_post( $post_id );
		if ( is_object( $post ) && ! empty( $post->post_content ) && function_exists( 'wp_strip_all_tags' ) ) {
			$description = trim( wp_strip_all_tags( (string) $post->post_content ) );
		}
	}
	if ( '' === $description ) {
		$yoast_desc  = $meta( '_yoast_wpseo_metadesc' );
		$description = is_string( $yoast_desc ) ? $yoast_desc : '';
	}
	if ( function_exists( 'mb_substr' ) ) {
		$description = mb_substr( $description, 0, 300 );
	} else {
		$description = substr( $description, 0, 300 );
	}

	// --- Numbers (null when absent/placeholder — never fabricated) --------
	$price_raw = $meta( '_e1ci_price' );
	$price     = gcp_schema_price_is_real( $price_raw ) ? (float) $price_raw : null;

	$msrp_raw = gcp_schema_clean_number( $meta( '_e1ci_msrp' ) );
	$msrp     = ( null !== $msrp_raw && gcp_schema_price_is_real( $msrp_raw ) ) ? $msrp_raw : null;

	$year_raw = gcp_schema_clean_number( $meta( '_e1ci_year' ) );
	$year     = ( null !== $year_raw && $year_raw > 1900 ) ? (int) $year_raw : null;

	$mileage_raw = gcp_schema_clean_number( $meta( '_e1ci_mileage' ) );
	$mileage_km  = ( null !== $mileage_raw && $mileage_raw >= 0 ) ? (int) $mileage_raw : null;

	$doors_raw = gcp_schema_clean_number( $meta( '_e1ci_doors' ) );
	$doors     = ( null !== $doors_raw && $doors_raw > 0 ) ? (int) $doors_raw : null;

	// --- Strings ----------------------------------------------------------
	$vin = strtoupper( (string) $meta( '_e1ci_vin' ) );

	$data = array(
		'name'           => $name,
		'description'    => $description,
		'url'            => $url,
		'price'          => $price,
		'msrp'           => $msrp,
		'year'           => $year,
		'mileage_km'     => $mileage_km,
		'vin'            => $vin,
		'sku'            => (string) $meta( '_e1ci_stock' ),
		'status'         => (string) $meta( '_e1ci_status' ),
		'images'         => gcp_schema_get_vehicle_images( $post_id ),
		'brand'          => gcp_schema_get_vehicle_term( $post_id, 'make' ),
		'model'          => gcp_schema_get_vehicle_term( $post_id, 'model' ),
		'body_type'      => gcp_schema_get_vehicle_term( $post_id, 'body' ),
		'vehicle_type'   => gcp_schema_get_vehicle_term( $post_id, 'vtype' ),
		'fuel'           => gcp_schema_get_vehicle_term( $post_id, 'fuel' ),
		'transmission'   => gcp_schema_get_vehicle_term( $post_id, 'transmission' ),
		'drivetrain'     => (string) $meta( '_e1ci_drivetrain' ),
		'engine'         => (string) $meta( '_e1ci_engine' ),
		'doors'          => $doors,
		'color_exterior' => (string) $meta( '_e1ci_color_exterior' ),
		'color_interior' => (string) $meta( '_e1ci_color_interior' ),
	);

	// Legacy fallback: some vehicles predate the model taxonomy.
	if ( '' === $data['model'] ) {
		$legacy_model  = $meta( '_e1ci_model' );
		$data['model'] = is_string( $legacy_model ) ? $legacy_model : '';
	}

	$cache[ $post_id ] = $data;
	return $data;
}
