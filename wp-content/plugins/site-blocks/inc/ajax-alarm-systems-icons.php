<?php
/**
 * Ajax Alarm Systems icon helpers (Lucide).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * Render a hero icon.
 *
 * @param string $filename Legacy PNG filename, e.g. wifi.png.
 * @param int    $size     Display width/height in pixels.
 */
function site_blocks_ajax_hero_icon( string $filename, int $size = 72 ): void {
	site_blocks_lucide_icon_from_legacy( $filename, $size );
}

/**
 * Blue circle checkmark for Ajax difference lists.
 */
function site_blocks_ajax_difference_check(): void {
	site_blocks_lucide_icon( 'circle-check', 22, 'sg-lucide-icon--filled-check' );
}

/**
 * Render a card media image or placeholder when the asset is not yet provided.
 *
 * @param string $relative_path Path relative to assets/, e.g. images/ajax/products/hub.webp.
 * @param string $alt         Accessible alt text when an image is present.
 */
function site_blocks_ajax_card_image( string $relative_path, string $alt = '' ): void {
	$relative_path = trim( $relative_path );

	if ( '' === $relative_path ) {
		echo '<span class="sg-ajax-media-placeholder" aria-hidden="true"></span>';
		return;
	}

	$url = site_blocks_asset_url( ltrim( $relative_path, '/' ) );

	printf(
		'<img class="sg-ajax-media__img" src="%s" alt="%s" loading="lazy" decoding="async" />',
		esc_url( $url ),
		esc_attr( $alt )
	);
}

/**
 * Render a CTA section icon.
 *
 * @param string $filename Legacy PNG filename, e.g. call.png.
 * @param int    $size     Display width/height in pixels.
 */
function site_blocks_ajax_cta_icon( string $filename, int $size = 72 ): void {
	site_blocks_lucide_icon_from_legacy( $filename, $size );
}
