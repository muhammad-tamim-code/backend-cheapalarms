<?php
/**
 * Safeguard homepage — hide theme chrome, enqueue assets.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the Safeguard full-page homepage is active.
 */
function site_blocks_is_safeguard_homepage(): bool {
	return is_front_page();
}

/**
 * Enqueue Safeguard homepage CSS and JS.
 */
function site_blocks_enqueue_safeguard_home(): void {
	if ( ! site_blocks_is_safeguard_homepage() ) {
		return;
	}

	wp_enqueue_style(
		'safeguard-home-fonts',
		'https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/css/safeguard-home.css',
		array( 'safeguard-home-fonts' ),
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
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_safeguard_home', 30 );

/**
 * Body classes for Safeguard homepage.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_safeguard_body_class( array $classes ): array {
	if ( site_blocks_is_safeguard_homepage() ) {
		$classes[] = 'safeguard-homepage';
		$classes[] = 'is-safeguard-front';
	}
	return $classes;
}
add_filter( 'body_class', 'site_blocks_safeguard_body_class' );
