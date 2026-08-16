<?php
/**
 * Sub-process probe: Yoast OFF + a vehicle taxonomy archive (noindex in
 * e1connect) → NO schema output at all, even with full dealer settings.
 *
 * Echoes 'EMPTY' when nothing was emitted (expected). The leading underscore
 * keeps this file OUT of the suite's `test-*.php` glob.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/vehicle-schema-v1
 */

$root = dirname( __DIR__, 2 );
require_once $root . '/tests/wp-stubs.php';

// Yoast OFF, front-end, on the `make` taxonomy archive (noindex).
$GLOBALS['__wp_is_admin'] = false;
$GLOBALS['__wp_is_tax']   = 'make';

$GLOBALS['__wp_options']['gcp_schema_settings'] = array(
	'name'             => 'Demo Motors',
	'street_address'   => '1 Main St',
	'address_locality' => 'Townsville',
	'address_region'   => 'ON',
	'postal_code'      => 'A1A1A1',
	'address_country'  => 'CA',
);

foreach (
	array(
		'/includes/helpers/get-settings.php',
		'/includes/helpers/vehicle-post-type.php',
		'/includes/helpers/has-inventory.php',
		'/includes/inventory/parse-list-meta.php',
		'/includes/inventory/clean-number.php',
		'/includes/inventory/price-is-real.php',
		'/includes/inventory/map-availability.php',
		'/includes/inventory/get-vehicle-term.php',
		'/includes/inventory/get-vehicle-images.php',
		'/includes/inventory/get-vehicle-data.php',
		'/includes/schema/build-offer.php',
		'/includes/schema/build-vehicle-product-node.php',
		'/includes/front/should-suppress.php',
		'/includes/helpers/schema-id.php',
		'/includes/helpers/is-yoast-active.php',
		'/includes/helpers/schema-enums.php',
		'/includes/helpers/encode-jsonld.php',
		'/includes/schema/build-financing-offer.php',
		'/includes/schema/build-dealer-parts.php',
		'/includes/schema/build-dealer-node.php',
		'/includes/schema/assemble-graph.php',
		'/includes/front/detect-context.php',
		'/includes/front/output-schema.php',
	) as $rel
) {
	$abs = $root . $rel;
	if ( file_exists( $abs ) ) {
		require_once $abs;
	}
}

if ( ! function_exists( 'gcp_schema_output_schema' ) ) {
	echo 'RED';
	return;
}

ob_start();
gcp_schema_output_schema();
$out = ob_get_clean();

echo '' === trim( $out ) ? 'EMPTY' : 'BAD:emitted';
