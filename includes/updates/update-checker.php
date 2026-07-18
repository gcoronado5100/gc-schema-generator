<?php
/**
 * GitHub update checker — serves plugin updates from the private repo.
 *
 * Wraps the bundled plugin-update-checker library (v5) so WordPress shows and
 * installs new versions published on gcoronado5100/gc-schema-generator
 * (latest release → tag → `main` branch). The repo is private, so every check
 * authenticates with a read-only fine-grained token.
 *
 * Token resolution mirrors GabeCode Plus and E1Connect Inventory: a server
 * constant `GABECODE_GH_TOKEN` (preferred — invisible in the admin) with the
 * shared encrypted option `gabecode_gh_token` as fallback. Both are
 * deliberately GENERIC (not plugin-specific): one token is shared by every
 * GabeCode plugin on the site, so each site is configured once.
 *
 * @package GCP_Schema_Generator
 * @author  Gabriel Coronado
 * @since   feat/new_inventory_plugin_gc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Decrypt a secret stored by a GabeCode plugin ('enc:' + base64(nonce +
 * ciphertext), sodium secretbox keyed off this site's WP salts). Returns ''
 * when the value cannot be decrypted (e.g. the salts were rotated) so callers
 * just see "not configured".
 *
 * @author Gabriel Coronado
 * @since  feat/new_inventory_plugin_gc
 * @param  string $stored
 * @return string
 */
function gcp_schema_secret_decrypt( $stored ) {
	$stored = (string) $stored;
	if ( 0 !== strpos( $stored, 'enc:' ) ) {
		return $stored; // Plain (sodium-less fallback or legacy value).
	}
	if ( ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
		return '';
	}
	$raw = base64_decode( substr( $stored, 4 ), true );
	if ( false === $raw || strlen( $raw ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return '';
	}
	$key   = sodium_crypto_generichash( wp_salt( 'auth' ), '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
	$nonce = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
	$plain = sodium_crypto_secretbox_open( substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ), $nonce, $key );
	return ( false === $plain ) ? '' : $plain;
}

/**
 * The GitHub read token, or '' when unset. Never rendered.
 *
 * Order: `GABECODE_GH_TOKEN` wp-config constant → shared encrypted option
 * `gabecode_gh_token` (written by other GabeCode plugins; the encryption key
 * derives from this site's salts, so any plugin on the same site can decrypt
 * it).
 *
 * @author Gabriel Coronado
 * @since  feat/new_inventory_plugin_gc
 * @return string
 */
function gcp_schema_gh_token() {
	if ( defined( 'GABECODE_GH_TOKEN' ) && '' !== trim( (string) GABECODE_GH_TOKEN ) ) {
		return trim( (string) GABECODE_GH_TOKEN );
	}
	return gcp_schema_secret_decrypt( (string) get_option( 'gabecode_gh_token', '' ) );
}

/**
 * Build the update checker against the GitHub repo (latest release → tag →
 * `main` branch). Skipped when no token is available — the repo is private,
 * so unauthenticated checks would just fail on every cron.
 *
 * Hooked to `plugins_loaded`.
 *
 * @author Gabriel Coronado
 * @since  feat/new_inventory_plugin_gc
 * @return void
 */
function gcp_schema_init_update_checker() {
	$token = gcp_schema_gh_token();
	if ( '' === $token ) {
		return;
	}

	require_once GCP_SCHEMA_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';

	$updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/gcoronado5100/gc-schema-generator/',
		GCP_SCHEMA_PLUGIN_FILE,
		'gcp-schema-generator'
	);
	$updater->setBranch( 'main' );
	$updater->setAuthentication( $token );
}
