<?php
/**
 * Ajax Alarm Systems hero icon helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a hero icon from bundled SVG assets.
 *
 * @param string $filename Icon filename under assets/images/ajax/icons/, e.g. wifi.png.
 * @param int    $size     Display width/height in pixels.
 */
function site_blocks_ajax_hero_icon( string $filename, int $size = 72 ): void {
	$url = site_blocks_asset_url( 'images/ajax/icons/' . ltrim( $filename, '/' ) );

	printf(
		'<img class="sg-ajax-hero__icon-img" src="%s" alt="" width="%d" height="%d" loading="lazy" decoding="async" />',
		esc_url( $url ),
		(int) $size,
		(int) $size
	);
}

/**
 * Blue circle checkmark for Ajax difference lists.
 */
function site_blocks_ajax_difference_check(): void {
	echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="11" cy="11" r="11" fill="#1769A1"/><path d="M7 11.2L9.6 13.8L15 8.4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
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
 * Render a CTA section icon from bundled SVG assets.
 *
 * @param string $filename Icon filename under assets/images/ajax/cta/, e.g. call.png.
 * @param int    $size     Display width/height in pixels.
 */
function site_blocks_ajax_cta_icon( string $filename, int $size = 72 ): void {
	$url = site_blocks_asset_url( 'images/ajax/cta/' . ltrim( $filename, '/' ) );

	printf(
		'<img class="sg-ajax-quote-cta__icon-img" src="%s" alt="" width="%d" height="%d" loading="lazy" decoding="async" />',
		esc_url( $url ),
		(int) $size,
		(int) $size
	);
}
