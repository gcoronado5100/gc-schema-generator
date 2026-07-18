<?php
/**
 * Minimal WordPress function stubs for standalone helper tests.
 *
 * The gcp-schema-generator helpers are pure and depend only on a handful of
 * core-WP functions (get_option, wp_parse_args, home_url, post_type_exists,
 * trailingslashit). The project has no build step and no WP-PHPUnit harness, so
 * these lightweight stubs let the pure helpers be exercised under plain `php`.
 *
 * Tests poke the backing arrays ($GLOBALS['__wp_options'], etc.) to control
 * the simulated WordPress environment.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

// The helpers guard against direct web access with `if ( ! defined( 'ABSPATH' ) ) exit;`.
// Define a dummy ABSPATH so requiring them under plain `php` does not short-circuit
// the test runner (otherwise the guard exits 0 and no assertions run).
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/' );
}

if ( ! isset( $GLOBALS['__wp_options'] ) ) {
	$GLOBALS['__wp_options'] = array();
}
if ( ! isset( $GLOBALS['__wp_post_types'] ) ) {
	$GLOBALS['__wp_post_types'] = array();
}
// Toggles backing the conditional output-engine stubs (default falsy when unset).
if ( ! isset( $GLOBALS['__wp_is_admin'] ) ) {
	$GLOBALS['__wp_is_admin'] = false;
}
if ( ! isset( $GLOBALS['__wp_is_front_page'] ) ) {
	$GLOBALS['__wp_is_front_page'] = false;
}
if ( ! isset( $GLOBALS['__wp_is_singular'] ) ) {
	// String post-type when "on a single of that type", or false.
	$GLOBALS['__wp_is_singular'] = false;
}
if ( ! isset( $GLOBALS['__wp_yoast_active'] ) ) {
	$GLOBALS['__wp_yoast_active'] = false;
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return array_key_exists( $name, $GLOBALS['__wp_options'] )
			? $GLOBALS['__wp_options'][ $name ]
			: $default;
	}
}

if ( ! function_exists( 'post_type_exists' ) ) {
	function post_type_exists( $post_type ) {
		return in_array( $post_type, $GLOBALS['__wp_post_types'], true );
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		$base = 'https://example.com';
		if ( '' === (string) $path ) {
			return $base;
		}
		return $base . '/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( (string) $string, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		$args = (array) $args;
		return array_merge( $defaults, $args );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		$str = (string) $str;
		$str = strip_tags( $str );
		// Collapse all whitespace (incl. newlines) to single spaces, then trim.
		$str = preg_replace( '/[\r\n\t ]+/', ' ', $str );
		return trim( $str );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $str ) {
		$str = (string) $str;
		$str = strip_tags( $str );
		// Trim trailing whitespace on each line; keep line breaks.
		$lines = preg_split( '/\r\n|\r|\n/', $str );
		$lines = array_map( 'trim', $lines );
		return trim( implode( "\n", $lines ) );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		$url = (string) $url;
		$url = strip_tags( $url );
		return trim( $url );
	}
}

/*
 * --------------------------------------------------------------------------
 * Phase 3 (output-engine) stubs — appended for the dealer-node / @graph /
 * wp_head injection / Yoast-detection test suite. Every pre-existing stub
 * above is left untouched.
 * --------------------------------------------------------------------------
 */

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Transparent stand-in for WordPress' wp_json_encode().
	 *
	 * CRITICAL — D-11 neutrality requirement: this stub passes the caller's
	 * $options through to PHP json_encode() UNCHANGED. It MUST NOT OR-in
	 * JSON_UNESCAPED_SLASHES / JSON_UNESCAPED_UNICODE. The production
	 * gcp_schema_encode_jsonld() helper (Plan 03-02) is the SINGLE place that supplies
	 * those two flags; if this stub re-added them it would silently mask a
	 * helper that forgot them, turning test-encode-jsonld.php GREEN on a
	 * D-11-violating implementation. DO NOT "helpfully" re-add the flags here.
	 *
	 * @since feat/new_inventory_plugin_gc
	 *
	 * @param mixed $data    Data to encode.
	 * @param int   $options json_encode option bitmask (passed through verbatim).
	 * @param int   $depth   Maximum recursion depth.
	 *
	 * @return string|false JSON string, or false on failure.
	 */
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	/**
	 * Minimal stand-in for WordPress' wp_strip_all_tags().
	 *
	 * Strips HTML/PHP tags; when $remove_breaks is true, collapses all runs of
	 * whitespace (including newlines and tabs) to single spaces. Always trims.
	 *
	 * @since feat/new_inventory_plugin_gc
	 *
	 * @param string $string        Input string.
	 * @param bool   $remove_breaks Whether to collapse line breaks/whitespace.
	 *
	 * @return string Cleaned string.
	 */
	function wp_strip_all_tags( $string, $remove_breaks = false ) {
		$s = strip_tags( (string) $string );
		if ( $remove_breaks ) {
			$s = preg_replace( '/[\r\n\t ]+/', ' ', $s );
		}
		return trim( $s );
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Stand-in for WordPress' is_admin(). Backed by a togglable global so tests
	 * can simulate the wp-admin context. Defaults falsy when the global is unset.
	 *
	 * @since feat/new_inventory_plugin_gc
	 *
	 * @return bool True when simulating wp-admin.
	 */
	function is_admin() {
		return ! empty( $GLOBALS['__wp_is_admin'] );
	}
}

if ( ! function_exists( 'is_front_page' ) ) {
	/**
	 * Stand-in for WordPress' is_front_page(). Backed by a togglable global.
	 *
	 * @since feat/new_inventory_plugin_gc
	 *
	 * @return bool True when simulating the site front page.
	 */
	function is_front_page() {
		return ! empty( $GLOBALS['__wp_is_front_page'] );
	}
}

if ( ! function_exists( 'is_singular' ) ) {
	/**
	 * Stand-in for WordPress' is_singular(). Backed by $GLOBALS['__wp_is_singular'],
	 * which holds the current single post-type string (or false). With no $type
	 * it returns true whenever any singular is simulated; with a $type it returns
	 * true only when the simulated post-type matches.
	 *
	 * @since feat/new_inventory_plugin_gc
	 *
	 * @param string $type Optional post-type to match against.
	 *
	 * @return bool True when simulating a matching singular view.
	 */
	function is_singular( $type = '' ) {
		return ! empty( $GLOBALS['__wp_is_singular'] )
			&& ( '' === $type || $GLOBALS['__wp_is_singular'] === $type );
	}
}

/**
 * Test-only Yoast toggle helper.
 *
 * The real gcp_schema_is_yoast_active() reads the WPSEO_VERSION constant / WPSEO_Options
 * class, neither of which a single in-process test can flip on and off (a defined
 * constant cannot be un-defined). Tests that need to exercise the Yoast-ON branch
 * therefore define WPSEO_VERSION in a SEPARATE php sub-process (see
 * tests/front/_yoast-on-probe.php). This global-backed toggle is provided for any
 * test path that prefers to assert the gate via the documented global instead.
 * These two helpers are NOT function_exists-guarded — they are test-only names
 * that never collide with real WordPress.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @param bool $active Whether to simulate Yoast being active.
 *
 * @return void
 */
function gcp_schema_test_set_yoast( $active ) {
	$GLOBALS['__wp_yoast_active'] = (bool) $active;
}

/**
 * Test-only readback for the Yoast toggle global.
 *
 * @since feat/new_inventory_plugin_gc
 *
 * @return bool Current simulated Yoast-active state.
 */
function gcp_schema_test_yoast_class_exists() {
	return ! empty( $GLOBALS['__wp_yoast_active'] );
}
