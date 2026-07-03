<?php
/**
 * CCTV page media helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array{width: int, height: int}
 */
function site_blocks_cctv_image_dimensions( string $relative_path ): array {
	$relative_path = ltrim( $relative_path, '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	$defaults = array(
		'images/cctv/hero.webp'         => array( 'width' => 1600, 'height' => 900 ),
		'images/cctv/install-band.webp' => array( 'width' => 1600, 'height' => 439 ),
		'images/cctv/residential.webp'  => array( 'width' => 1448, 'height' => 1086 ),
		'images/cctv/commercial.webp'   => array( 'width' => 1448, 'height' => 1086 ),
	);

	if ( isset( $defaults[ $relative_path ] ) ) {
		return $defaults[ $relative_path ];
	}

	if ( is_readable( $disk ) && function_exists( 'getimagesize' ) ) {
		$size = getimagesize( $disk );
		if ( is_array( $size ) && isset( $size[0], $size[1] ) ) {
			return array( 'width' => (int) $size[0], 'height' => (int) $size[1] );
		}
	}

	return array( 'width' => 0, 'height' => 0 );
}

/**
 * Render a CCTV image or a styled placeholder when the asset is not uploaded yet.
 *
 * @param string $relative_path Path under assets/, e.g. images/cctv/hero.webp.
 * @param string $alt           Alt text (empty when decorative).
 * @param string $class         Optional img class.
 * @param string $loading       loading attribute value.
 */
function site_blocks_cctv_image( string $relative_path, string $alt = '', string $class = 'sg-cctv-media__img', string $loading = 'lazy' ): void {
	$relative_path = ltrim( $relative_path, '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	if ( ! is_readable( $disk ) ) {
		printf( '<span class="sg-cctv-media-placeholder" aria-hidden="true"></span>' );
		return;
	}

	$dims = site_blocks_cctv_image_dimensions( $relative_path );

	$attrs = sprintf(
		'class="%s" src="%s" alt="%s"',
		esc_attr( $class ),
		esc_url( site_blocks_asset_url( $relative_path ) ),
		esc_attr( $alt )
	);

	if ( $dims['width'] > 0 && $dims['height'] > 0 ) {
		$attrs .= sprintf( ' width="%d" height="%d"', $dims['width'], $dims['height'] );
	}

	printf(
		'<img %s loading="%s" decoding="async" />',
		$attrs,
		esc_attr( $loading )
	);
}

/**
 * Hero image — falls back to alarm hero until CCTV asset is provided.
 */
function site_blocks_cctv_hero_image(): void {
	$cctv = SITE_BLOCKS_DIR . 'assets/images/cctv/hero.webp';
	if ( is_readable( $cctv ) ) {
		site_blocks_cctv_image(
			'images/cctv/hero.webp',
			__( 'Sydney home with CCTV cameras and live phone monitoring app', 'site-blocks' ),
			'sg-hero__pillar-img',
			'eager'
		);
		return;
	}

	$url = site_blocks_asset_url( 'images/alarm/hero.webp' );
	printf(
		'<img class="sg-hero__pillar-img" src="%s" alt="%s" width="1600" height="900" loading="eager" decoding="async" />',
		esc_url( $url ),
		esc_attr__( 'Sydney home with CCTV cameras and live phone monitoring app', 'site-blocks' )
	);
}
