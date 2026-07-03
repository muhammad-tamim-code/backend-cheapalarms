<?php
/**
 * CCTV page PNG icon helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a CCTV icon from bundled PNG assets.
 *
 * @param string $filename Icon filename under assets/images/cctv/icons/, e.g. ip-camera.png.
 * @param int    $size     Display width/height in pixels.
 * @param string $class    Optional extra class on the img element.
 */
function site_blocks_cctv_icon( string $filename, int $size = 72, string $class = 'sg-cctv-icon__img' ): void {
	$filename = ltrim( $filename, '/' );
	$url      = site_blocks_asset_url( 'images/cctv/icons/' . $filename );

	printf(
		'<img class="%s" src="%s" alt="" width="%d" height="%d" loading="lazy" decoding="async" />',
		esc_attr( $class ),
		esc_url( $url ),
		(int) $size,
		(int) $size
	);
}
