<?php
/**
 * JSON-LD encode helper — the single encode point (D-11).
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Encode a schema graph array to a JSON-LD string (OUT-01).
 *
 * This is the SINGLE place in the plugin where the graph is JSON-encoded
 * (D-11). It always supplies the two UNESCAPED flags so URLs stay readable
 * (JSON_UNESCAPED_SLASHES — no `\/`) and non-ASCII text survives literally
 * (JSON_UNESCAPED_UNICODE — e.g. "Café"), which is what Google's structured-data
 * tooling expects.
 *
 * Note on safety: free-text values (name, addresses, etc.) are HTML-stripped
 * by the node builders BEFORE they reach here, so this function is
 * responsible only for JSON escaping — never for sanitizing markup. One
 * defense stays here because only the encoder can provide it: with
 * JSON_UNESCAPED_SLASHES a literal `</script>` inside any string value would
 * survive encoding verbatim and terminate the inline <script> tag early, so
 * every `</` is re-escaped to `<\/` (valid JSON, identical parsed value)
 * regardless of which builder produced the string.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param array $graph The assembled {@context, @graph} structure.
 *
 * @return string JSON-LD string with slashes and unicode left unescaped.
 */
function gcp_schema_encode_jsonld( array $graph ) {
	$json = wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	return str_replace( '</', '<\/', (string) $json );
}
