<?php
/**
 * Staging-only: write cheapalarms-plugin config/secrets.php when missing.
 * Also backfill deepseek_api_key when secrets exist but the key was wiped
 * by a WP Admin plugin upload (zip excludes secrets.php).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is Safeguard staging.
 */
function site_blocks_is_safeguard_staging_host(): bool {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

	return $host === 'staging.safeguardsecurity.com.au';
}

/**
 * @return array<string, mixed>
 */
function site_blocks_staging_secrets_defaults(): array {
	return array(
		'brand_name'             => 'Safeguard',
		'brand_tagline'          => 'Security Services',
		'brand_primary_color'    => '#2B7FB3',
		'brand_accent_color'     => '#E88324',
		'support_name'           => 'Safeguard Support',
		'support_email'          => 'support@safeguardsecurity.com.au',
		'email_from_name'        => 'Safeguard',
		'email_from_address'     => 'quotes@safeguardsecurity.com.au',
		'ghl_token'              => getenv( 'CA_GHL_TOKEN' ) ?: 'pit-195d44e7-6b55-4e86-aa33-1c039d458e5c',
		'ghl_location_id'        => getenv( 'CA_LOCATION_ID' ) ?: 'aLTXtdwNknfmEFo3WBIX',
		'ghl_user_id'            => getenv( 'CA_GHL_USER_ID' ) ?: '',
		'upload_shared_secret'   => getenv( 'CA_UPLOAD_SHARED_SECRET' ) ?: 'RXquQdZurMTanrs1L2f6jwm-TkJjjS4o1Zs__LQa8qBUGaKF_DQpsurQT13JyZXk',
		'jwt_secret'             => getenv( 'CA_JWT_SECRET' ) ?: 'MZi3PniMBCF7r9SrXUbICLZF8cVz1ou12BOz_1bc8ZQC0-qgicEcL1Agn1dBlFNq',
		'stripe_publishable_key' => 'pk_test_51SgyW7PZnmpzepwm77Y1I1VeheOhybZgTrzmml7pneZ0N821hpGGqKtS3wtGbkAW7ugayllCOiUBmzc5UftAeCPC00nwmDV0Fg',
		'stripe_secret_key'      => getenv( 'CA_STRIPE_SECRET_KEY' ) ?: '',
		'stripe_webhook_secret'  => getenv( 'CA_STRIPE_WEBHOOK_SECRET' ) ?: '',
		'deepseek_api_key'       => getenv( 'CA_DEEPSEEK_API_KEY' ) ?: 'sk-326a4ea7fc3a45a98e5d8f378bd63bf1',
		'deepseek_model'         => getenv( 'CA_DEEPSEEK_MODEL' ) ?: 'deepseek-chat',
		'frontend_url'           => 'https://safeguard-portal.vercel.app',
		'xero_redirect_uri'      => 'https://safeguard-portal.vercel.app/xero/callback',
	);
}

/**
 * @param array<string, mixed> $data
 */
function site_blocks_write_cheapalarms_secrets_file( string $path, array $data ): bool {
	$export = var_export( $data, true );
	$php    = "<?php\n\n/** AUTO-BOOTSTRAPPED by site-blocks (staging) */\nreturn {$export};\n";

	return false !== file_put_contents( $path, $php );
}

/**
 * Bootstrap CheapAlarms secrets on Safeguard staging (no manual upload).
 */
function site_blocks_maybe_bootstrap_cheapalarms_secrets(): void {
	if ( ! site_blocks_is_safeguard_staging_host() ) {
		return;
	}

	$plugin_dir = WP_PLUGIN_DIR . '/cheapalarms-plugin';
	$config_dir = $plugin_dir . '/config';
	$secrets    = $config_dir . '/secrets.php';

	// If secrets already exist, still try to backfill a missing DeepSeek key.
	if ( file_exists( $secrets ) ) {
		site_blocks_maybe_backfill_deepseek_secret( $secrets );
		update_option( 'sg_ca_secrets_bootstrapped', '1', true );
		return;
	}

	// Important: WP Admin plugin upload deletes the plugin folder (and secrets.php)
	// but leaves sg_ca_secrets_bootstrapped=1. Always recreate when missing.
	if ( ! is_dir( $config_dir ) && ! wp_mkdir_p( $config_dir ) ) {
		return;
	}

	if ( ! is_writable( $config_dir ) ) {
		return;
	}

	$data = site_blocks_staging_secrets_defaults();
	if ( ! site_blocks_write_cheapalarms_secrets_file( $secrets, $data ) ) {
		return;
	}

	update_option( 'ca_ghl_api_key', $data['ghl_token'], false );
	update_option( 'ca_ghl_location_id', $data['ghl_location_id'], false );
	update_option( 'ca_upload_shared_secret', $data['upload_shared_secret'], false );
	update_option( 'ca_jwt_secret', $data['jwt_secret'], false );
	update_option( 'sg_ca_secrets_bootstrapped', '1', true );
}

/**
 * After WP Admin plugin uploads wipe secrets.php, bootstrap recreates GHL/JWT
 * but historically left DeepSeek empty. Merge the key from env when missing.
 */
function site_blocks_maybe_backfill_deepseek_secret( string $secrets_path ): void {
	if ( ! is_readable( $secrets_path ) || ! is_writable( $secrets_path ) ) {
		return;
	}

	$existing = include $secrets_path;
	if ( ! is_array( $existing ) ) {
		return;
	}

	$current = trim( (string) ( $existing['deepseek_api_key'] ?? '' ) );
	if ( $current !== '' ) {
		return;
	}

	$from_env = getenv( 'CA_DEEPSEEK_API_KEY' );
	$key      = is_string( $from_env ) ? trim( $from_env ) : '';
	if ( $key === '' ) {
		return;
	}

	$existing['deepseek_api_key'] = $key;
	$model_env                    = getenv( 'CA_DEEPSEEK_MODEL' );
	$existing['deepseek_model']   = ( is_string( $model_env ) && trim( $model_env ) !== '' )
		? trim( $model_env )
		: ( (string) ( $existing['deepseek_model'] ?? 'deepseek-chat' ) ?: 'deepseek-chat' );

	site_blocks_write_cheapalarms_secrets_file( $secrets_path, $existing );
}
add_action( 'plugins_loaded', 'site_blocks_maybe_bootstrap_cheapalarms_secrets', 1 );
