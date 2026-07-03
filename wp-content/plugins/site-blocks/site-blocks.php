<?php
/**
 * Plugin Name: Site Blocks
 * Description: Server-rendered Gutenberg blocks, contact page, and security packages.
 * Version: 1.26.2
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Author: Site Developer
 * Text Domain: site-blocks
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SITE_BLOCKS_VERSION', '1.26.2' );
define( 'SITE_BLOCKS_FILE', __FILE__ );
define( 'SITE_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SITE_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once SITE_BLOCKS_DIR . 'inc/asset-helpers.php';
require_once SITE_BLOCKS_DIR . 'inc/register-blocks.php';
require_once SITE_BLOCKS_DIR . 'inc/setup-pages.php';
require_once SITE_BLOCKS_DIR . 'inc/form-handler.php';
require_once SITE_BLOCKS_DIR . 'inc/cpt-security-package.php';
require_once SITE_BLOCKS_DIR . 'inc/package-meta.php';
require_once SITE_BLOCKS_DIR . 'inc/package-helpers.php';
require_once SITE_BLOCKS_DIR . 'inc/package-templates.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-homepage-setup.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-footer.php';
require_once SITE_BLOCKS_DIR . 'inc/alarm-systems-setup.php';
require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-setup.php';
require_once SITE_BLOCKS_DIR . 'inc/cctv-setup.php';
require_once SITE_BLOCKS_DIR . 'inc/intercom-setup.php';
require_once SITE_BLOCKS_DIR . 'inc/access-control-setup.php';
require_once SITE_BLOCKS_DIR . 'inc/pillar-hero.php';
require_once SITE_BLOCKS_DIR . 'inc/ajax-calculator.php';
$secrets_bootstrap = SITE_BLOCKS_DIR . 'inc/cheapalarms-secrets-bootstrap.php';
if ( is_readable( $secrets_bootstrap ) ) {
	require_once $secrets_bootstrap;
}

/**
 * Enqueue compiled block styles.
 */
function site_blocks_enqueue_assets(): void {
	$load_styles = is_front_page()
		|| is_page( 'contact' )
		|| is_post_type_archive( 'security_package' )
		|| is_singular( 'security_package' )
		|| is_tax( 'package_type' )
		|| has_block( 'site/package-grid' )
		|| has_block( 'site/home-hero' );

	if ( ! $load_styles ) {
		return;
	}

	wp_enqueue_style(
		'site-blocks-main',
		SITE_BLOCKS_URL . 'assets/css/main.css',
		array( 'kadence-child-main' ),
		SITE_BLOCKS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_assets', 25 );

/**
 * Plugin activation.
 */
function site_blocks_activate(): void {
	site_blocks_register_security_package_cpt();
	site_blocks_seed_package_types();
	site_blocks_create_contact_page();
	site_blocks_create_home_page();
	site_blocks_create_alarm_systems_page();
	site_blocks_create_ajax_alarm_systems_page();
	site_blocks_create_cctv_page();
	site_blocks_create_intercom_systems_page();
	site_blocks_create_intercoms_redirect_page();
	site_blocks_create_access_control_page();
	site_blocks_create_ajax_calculator_page();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'site_blocks_activate' );

/**
 * Refresh Kadence disable flags on existing Safeguard pages.
 */
function site_blocks_refresh_safeguard_page_meta(): void {
	$slugs = array( 'home', 'contact', 'alarm-systems', 'ajax-alarm-systems', 'ajax-calculator', 'cctv-security-cameras', 'intercom-systems', 'access-control' );

	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page instanceof WP_Post ) {
			site_blocks_apply_safeguard_page_meta( (int) $page->ID );
		}
	}
}

/**
 * Run upgrades when plugin version changes (e.g. homepage added).
 */
function site_blocks_maybe_upgrade(): void {
	$stored = get_option( 'site_blocks_db_version', '' );

	if ( $stored === SITE_BLOCKS_VERSION ) {
		return;
	}

	if ( function_exists( 'site_blocks_create_home_page' ) ) {
		site_blocks_create_home_page();
	}

	if ( function_exists( 'site_blocks_create_alarm_systems_page' ) ) {
		site_blocks_create_alarm_systems_page();
	}

	if ( function_exists( 'site_blocks_create_ajax_alarm_systems_page' ) ) {
		site_blocks_create_ajax_alarm_systems_page();
	}

	if ( function_exists( 'site_blocks_create_cctv_page' ) ) {
		site_blocks_create_cctv_page();
	}

	if ( function_exists( 'site_blocks_create_intercom_systems_page' ) ) {
		site_blocks_create_intercom_systems_page();
	}

	if ( function_exists( 'site_blocks_create_intercoms_redirect_page' ) ) {
		site_blocks_create_intercoms_redirect_page();
	}

	if ( function_exists( 'site_blocks_create_access_control_page' ) ) {
		site_blocks_create_access_control_page();
	}

	if ( function_exists( 'site_blocks_create_ajax_calculator_page' ) ) {
		site_blocks_create_ajax_calculator_page();
	}

	if ( function_exists( 'site_blocks_refresh_safeguard_page_meta' ) ) {
		site_blocks_refresh_safeguard_page_meta();
	}

	update_option( 'site_blocks_db_version', SITE_BLOCKS_VERSION );
	flush_rewrite_rules();
}
add_action( 'init', 'site_blocks_maybe_upgrade', 5 );
