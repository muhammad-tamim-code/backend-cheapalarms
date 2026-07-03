<?php
/**
 * Staging-only: write cheapalarms-plugin config/secrets.php when missing.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap CheapAlarms secrets on Safeguard staging (no manual upload).
 */
function site_blocks_maybe_bootstrap_cheapalarms_secrets(): void {
	if ( get_option( 'sg_ca_secrets_bootstrapped', '' ) === '1' ) {
		return;
	}

	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	if ( $host !== 'staging.safeguardsecurity.com.au' ) {
		return;
	}

	$plugin_dir = WP_PLUGIN_DIR . '/cheapalarms-plugin';
	$config_dir = $plugin_dir . '/config';
	$secrets    = $config_dir . '/secrets.php';

	if ( file_exists( $secrets ) ) {
		update_option( 'sg_ca_secrets_bootstrapped', '1', true );
		return;
	}

	if ( ! is_dir( $config_dir ) && ! wp_mkdir_p( $config_dir ) ) {
		return;
	}

	if ( ! is_writable( $config_dir ) ) {
		return;
	}

	$data = array(
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
		'frontend_url'           => 'https://safeguard-portal.vercel.app',
		'xero_redirect_uri'      => 'https://safeguard-portal.vercel.app/xero/callback',
	);

	$export = var_export( $data, true );
	$php    = "<?php\n\n/** AUTO-BOOTSTRAPPED by site-blocks (staging) */\nreturn {$export};\n";

	if ( false === file_put_contents( $secrets, $php ) ) {
		return;
	}

	update_option( 'ca_ghl_api_key', $data['ghl_token'], false );
	update_option( 'ca_ghl_location_id', $data['ghl_location_id'], false );
	update_option( 'ca_upload_shared_secret', $data['upload_shared_secret'], false );
	update_option( 'ca_jwt_secret', $data['jwt_secret'], false );
	update_option( 'sg_ca_secrets_bootstrapped', '1', true );
}
add_action( 'plugins_loaded', 'site_blocks_maybe_bootstrap_cheapalarms_secrets', 1 );
