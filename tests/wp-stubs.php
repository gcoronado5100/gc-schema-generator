<?php
/**
 * Minimal WordPress function stubs for standalone helper tests.
 *
 * The gc-schema-generator helpers are pure and depend only on a handful of
 * core-WP functions (get_option, wp_parse_args, home_url, post_type_exists,
 * trailingslashit). The project has no build step and no WP-PHPUnit harness, so
 * these lightweight stubs let the pure helpers be exercised under plain `php`.
 *
 * Tests poke the backing arrays ($GLOBALS['__wp_options'], etc.) to control
 * the simulated WordPress environment.
 *
 * @package GC_Schema_Generator
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
