<?php
/**
 * Dealer financing makesOffer builder.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the makesOffer → Offer → LoanOrCredit node from settings.
 *
 * Entity-surface value only: describes that the dealer offers vehicle
 * financing (useful to knowledge graphs and AI surfaces), with NO rich-result
 * claim attached. Emitted in both modes — on the Mode B dealer node and in
 * the Mode A Yoast Organization enrichment — and omitted entirely when the
 * financing_description setting is empty (D-06: nothing fabricated).
 *
 * @since feat/vehicle-schema-v1
 *
 * @param array $settings Sanitized plugin settings.
 *
 * @return array Offer node wrapping a LoanOrCredit, or array() when unset.
 */
function gcp_schema_build_financing_offer( array $settings ) {
	if ( ! isset( $settings['financing_description'] ) || '' === trim( (string) $settings['financing_description'] ) ) {
		return array();
	}

	return array(
		'@type'       => 'Offer',
		'itemOffered' => array(
			'@type'       => 'LoanOrCredit',
			'name'        => 'Vehicle Financing',
			'description' => wp_strip_all_tags( $settings['financing_description'] ),
		),
	);
}
