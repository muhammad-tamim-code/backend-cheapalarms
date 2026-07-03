<?php
/**
 * CCTV category page — assets and body class.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';

/**
 * Whether the CCTV category page is being viewed.
 */
function site_blocks_is_cctv_page(): bool {
	return is_page( 'cctv-security-cameras' );
}

/**
 * Enqueue CCTV page styles (reuses alarm + home design system).
 */
function site_blocks_enqueue_cctv_assets(): void {
	if ( ! site_blocks_is_cctv_page() ) {
		return;
	}

	wp_enqueue_style(
		'safeguard-cctv-fonts',
		'https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/css/safeguard-home.css',
		array( 'safeguard-cctv-fonts' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_style(
		'safeguard-alarm-systems',
		SITE_BLOCKS_URL . 'assets/css/alarm-systems.css',
		array( 'safeguard-home' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_style(
		'safeguard-cctv',
		SITE_BLOCKS_URL . 'assets/css/cctv.css',
		array( 'safeguard-alarm-systems' ),
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
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_cctv_assets', 30 );

/**
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_cctv_body_class( array $classes ): array {
	if ( site_blocks_is_cctv_page() ) {
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}
	return $classes;
}
add_filter( 'body_class', 'site_blocks_cctv_body_class' );
