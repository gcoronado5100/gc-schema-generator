<?php
/**
 * Plugin Name:       GC Schema Generator
 * Plugin URI:        https://every1drives.com/
 * Description:       Emits deterministic Schema.org JSON-LD (AutoDealer + Product/Offer) for the dealership sites; yields to Yoast when present and degrades gracefully without Motors.
 * Version:           0.1.0
 * Author:            Gabriel Coronado
 * Author URI:        https://gabecode.com/about-me/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gc-schema-generator
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package GC_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GCSG_VERSION', '0.1.0' );
define( 'GCSG_PLUGIN_FILE', __FILE__ );
define( 'GCSG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCSG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/*
|--------------------------------------------------------------------------
| Glob-autoload of includes/**
|--------------------------------------------------------------------------
| Recursively require every .php file under includes/ (skipping index.php
| silence stubs). Callbacks live here; this main file stays hooks-only.
| Base definitions (interface-/trait-/abstract-) load first, then the rest
| alphabetically, so child classes never load before their parent.
*/
$gcsg_includes_dir = GCSG_PLUGIN_DIR . 'includes';
if ( is_dir( $gcsg_includes_dir ) ) {
	$gcsg_iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $gcsg_includes_dir, RecursiveDirectoryIterator::SKIP_DOTS )
	);

	$gcsg_files = array();
	foreach ( $gcsg_iterator as $gcsg_file ) {
		if ( ! $gcsg_file->isFile() ) {
			continue;
		}
		if ( strtolower( $gcsg_file->getExtension() ) !== 'php' ) {
			continue;
		}
		if ( 'index.php' === $gcsg_file->getFilename() ) {
			continue;
		}
		$gcsg_files[] = $gcsg_file->getPathname();
	}

	usort(
		$gcsg_files,
		static function ( $a, $b ) {
			$gcsg_base_priority = static function ( $path ) {
				$name = strtolower( basename( $path ) );
				foreach ( array( 'interface-', 'trait-', 'abstract-' ) as $prefix ) {
					if ( 0 === strpos( $name, $prefix ) ) {
						return 0;
					}
				}
				return 1;
			};

			$pa = $gcsg_base_priority( $a );
			$pb = $gcsg_base_priority( $b );
			if ( $pa !== $pb ) {
				return $pa <=> $pb;
			}
			return strcmp( $a, $b );
		}
	);

	foreach ( $gcsg_files as $gcsg_path ) {
		require_once $gcsg_path;
	}
}

/*
|--------------------------------------------------------------------------
| Hooks (callbacks live under includes/; no function bodies in this file)
|--------------------------------------------------------------------------
| Later phases append their own front-end/admin hook lines below.
*/
register_activation_hook( __FILE__, 'gcsg_activate' );
register_deactivation_hook( __FILE__, 'gcsg_deactivate' );

// Phase 2 — settings page (callbacks live under includes/admin/).
add_action( 'admin_menu',            'gcsg_register_settings_menu' );
add_action( 'admin_init',            'gcsg_register_settings' );
add_action( 'admin_enqueue_scripts', 'gcsg_admin_enqueue' );

// Phase 3 — front-end JSON-LD output (callback under includes/front/).
add_action( 'wp_head', 'gcsg_output_schema', 99 );
