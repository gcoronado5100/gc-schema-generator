<?php
/**
 * RED round-trip test for gcp_schema_encode_jsonld() (OUT-01 / SC-3).
 *
 * Wave-0 (TDD RED): gcp_schema_encode_jsonld() DOES NOT EXIST yet, so this test
 * exits 1 (RED). It proves the production helper supplies the two
 * JSON_UNESCAPED_* flags itself — the wp-stubs wp_json_encode is transparent
 * (does NOT force the flags), so the no-`\/` assertion only passes when the
 * helper passes JSON_UNESCAPED_SLASHES, and the unicode assertion only passes
 * when it passes JSON_UNESCAPED_UNICODE (D-11). Quotes/`&` must round-trip
 * through json_decode() (SC-3).
 *
 * Standalone (no PHPUnit): run with `php tests/schema/test-encode-jsonld.php`.
 * Exit code 0 = all pass, 1 = a failure.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

$root = dirname( __DIR__, 2 ); // tests/<area>/file.php → plugin root.
require_once $root . '/tests/wp-stubs.php';

$failures = array();
$tests    = 0;

function gcp_schema_assert( $cond, $message, &$tests, &$failures ) {
	$tests++;
	if ( ! $cond ) {
		$failures[] = $message;
		echo "FAIL: $message\n";
	} else {
		echo "ok:   $message\n";
	}
}

// Unit under test — DOES NOT EXIST in Wave 0. RED-guarded require.
$f = $root . '/includes/helpers/encode-jsonld.php';
if ( file_exists( $f ) ) {
	require_once $f;
}

// --- RED guard ---
gcp_schema_assert( function_exists( 'gcp_schema_encode_jsonld' ), 'gcp_schema_encode_jsonld is defined', $tests, $failures );
$have = function_exists( 'gcp_schema_encode_jsonld' );

// --- SC-3: quotes/ampersand round-trip intact ---
$qa_in   = array( 'name' => 'Bob & "Sons"' );
$qa_json = $have ? gcp_schema_encode_jsonld( $qa_in ) : '';
$qa_back = is_string( $qa_json ) && '' !== $qa_json ? json_decode( $qa_json, true ) : null;
gcp_schema_assert(
	$have && is_array( $qa_back ) && isset( $qa_back['name'] ) && 'Bob & "Sons"' === $qa_back['name'],
	'SC-3: quotes/ampersand round-trip through json_decode intact',
	$tests,
	$failures
);

// --- OUT-01: URL value is NOT escaped to `\/` (JSON_UNESCAPED_SLASHES from the PRODUCTION helper) ---
$url_json = $have ? gcp_schema_encode_jsonld( array( 'url' => 'https://example.com/path' ) ) : '';
gcp_schema_assert(
	$have && is_string( $url_json ) && false === strpos( $url_json, '\\/' ),
	'OUT-01: encoded URL contains no escaped \\/ (UNESCAPED_SLASHES applied by helper)',
	$tests,
	$failures
);

// --- OUT-01: unicode round-trips (JSON_UNESCAPED_UNICODE from the PRODUCTION helper) ---
$uni_json = $have ? gcp_schema_encode_jsonld( array( 'name' => 'Café Motors' ) ) : '';
gcp_schema_assert(
	$have && is_string( $uni_json ) && false !== strpos( $uni_json, 'Café Motors' ),
	'OUT-01: unicode value survives literally (UNESCAPED_UNICODE applied by helper)',
	$tests,
	$failures
);
$uni_back = is_string( $uni_json ) && '' !== $uni_json ? json_decode( $uni_json, true ) : null;
gcp_schema_assert(
	$have && is_array( $uni_back ) && isset( $uni_back['name'] ) && 'Café Motors' === $uni_back['name'],
	'OUT-01: unicode value round-trips through json_decode',
	$tests,
	$failures
);

// Hardening: a literal </script> in any value must not survive verbatim —
// `</` is re-escaped to `<\/` (identical parsed value, cannot close the tag).
$xss      = gcp_schema_encode_jsonld( array( 'description' => 'evil</script><script>alert(1)</script>' ) );
$xss_back = json_decode( $xss, true );
gcp_schema_assert(
	false === strpos( $xss, '</script>' )
		&& is_array( $xss_back )
		&& 'evil</script><script>alert(1)</script>' === $xss_back['description'],
	'hardening: </script> in a value is escaped in output yet round-trips intact',
	$tests,
	$failures
);

echo "\n$tests assertions, " . count( $failures ) . " failures\n";
exit( empty( $failures ) ? 0 : 1 );
