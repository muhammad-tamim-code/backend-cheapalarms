<?php
/**
 * Ajax Alarm Systems page — assets, layout, and chrome.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';

/**
 * Whether the Ajax Alarm Systems landing page is being viewed.
 */
function site_blocks_is_ajax_alarm_systems_page(): bool {
	return is_page( 'ajax-alarm-systems' );
}

/**
 * Enqueue Ajax Alarm Systems page styles and fonts.
 */
function site_blocks_enqueue_ajax_alarm_systems_assets(): void {
	if ( ! site_blocks_is_ajax_alarm_systems_page() ) {
		return;
	}

	wp_enqueue_style(
		'safeguard-ajax-fonts',
		'https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/css/safeguard-home.css',
		array( 'safeguard-ajax-fonts' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_style(
		'safeguard-ajax-alarm-systems',
		SITE_BLOCKS_URL . 'assets/css/ajax-alarm-systems.css',
		array( 'safeguard-home' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_script(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/js/safeguard-home.js',
		array(),
		SITE_BLOCKS_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_ajax_alarm_systems_assets', 30 );

/**
 * Body class for Ajax Alarm Systems page.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_ajax_alarm_systems_body_class( array $classes ): array {
	if ( site_blocks_is_ajax_alarm_systems_page() ) {
		$classes[] = 'safeguard-ajax-page';
		$classes[] = 'safeguard-homepage';
	}
	return $classes;
}
add_filter( 'body_class', 'site_blocks_ajax_alarm_systems_body_class' );
