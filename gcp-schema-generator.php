<?php
/**
 * Plugin Name:       GabeCode Schema Generator
 * Plugin URI:        https://every1drives.com/
 * Description:       GabeCode Plus add-on. Emits deterministic Schema.org JSON-LD (AutoDealer + per-vehicle Product/Car + Offer); stitches into Yoast's graph when present and degrades gracefully without inventory.
 * Version:           1.0.1
 * Author:            Gabriel Coronado
 * Author URI:        https://gabecode.com/about-me/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gcp-schema-generator
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GCP_SCHEMA_VERSION', '1.0.1' );
define( 'GCP_SCHEMA_PLUGIN_FILE', __FILE__ );
define( 'GCP_SCHEMA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCP_SCHEMA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/*
|--------------------------------------------------------------------------
| Glob-autoload of includes/**
|--------------------------------------------------------------------------
| Recursively require every .php file under includes/ (skipping index.php
| silence stubs). Callbacks live here; this main file stays hooks-only.
| Base definitions (interface-/trait-/abstract-) load first, then the rest
| alphabetically, so child classes never load before their parent.
*/
$gcp_schema_includes_dir = GCP_SCHEMA_PLUGIN_DIR . 'includes';
if ( is_dir( $gcp_schema_includes_dir ) ) {
	$gcp_schema_iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $gcp_schema_includes_dir, RecursiveDirectoryIterator::SKIP_DOTS )
	);

	$gcp_schema_files = array();
	foreach ( $gcp_schema_iterator as $gcp_schema_file ) {
		if ( ! $gcp_schema_file->isFile() ) {
			continue;
		}
		if ( strtolower( $gcp_schema_file->getExtension() ) !== 'php' ) {
			continue;
		}
		if ( 'index.php' === $gcp_schema_file->getFilename() ) {
			continue;
		}
		$gcp_schema_files[] = $gcp_schema_file->getPathname();
	}

	usort(
		$gcp_schema_files,
		static function ( $a, $b ) {
			$gcp_schema_base_priority = static function ( $path ) {
				$name = strtolower( basename( $path ) );
				foreach ( array( 'interface-', 'trait-', 'abstract-' ) as $prefix ) {
					if ( 0 === strpos( $name, $prefix ) ) {
						return 0;
					}
				}
				return 1;
			};

			$pa = $gcp_schema_base_priority( $a );
			$pb = $gcp_schema_base_priority( $b );
			if ( $pa !== $pb ) {
				return $pa <=> $pb;
			}
			return strcmp( $a, $b );
		}
	);

	foreach ( $gcp_schema_files as $gcp_schema_path ) {
		require_once $gcp_schema_path;
	}
}

/*
|--------------------------------------------------------------------------
| Hooks (callbacks live under includes/; no function bodies in this file)
|--------------------------------------------------------------------------
| Later phases append their own front-end/admin hook lines below.
*/
register_activation_hook( __FILE__, 'gcp_schema_activate' );
register_deactivation_hook( __FILE__, 'gcp_schema_deactivate' );

// Hard dependency on GabeCode Plus — as an add-on, this plugin registers nothing
// (except a requirement notice) unless the hub is active. Deferred to
// `plugins_loaded` so plugin load order never matters. See includes/admin/menu.php.
add_action( 'plugins_loaded', 'gcp_schema_boot' );

// ----------- GitHub self-updates ------------
// Serves plugin updates from the private repo
// (gcoronado5100/gc-schema-generator) authenticated with the shared
// GABECODE_GH_TOKEN. Runs even when the hub is inactive so an installed copy
// can always update itself. See includes/updates/update-checker.php.
add_action( 'plugins_loaded', 'gcp_schema_init_update_checker' );
