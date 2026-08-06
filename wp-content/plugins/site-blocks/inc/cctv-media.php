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
		'images/cctv/spotlight.webp'       => array( 'width' => 680, 'height' => 920 ),
		'images/cctv/spotlight-camera.webp' => array( 'width' => 1024, 'height' => 819 ),
		'images/cctv/hero-phone.webp'      => array( 'width' => 444, 'height' => 886 ),
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

	if ( function_exists( 'site_blocks_print_managed_image' ) && site_blocks_print_managed_image( $relative_path, $alt, $class, $loading ) ) {
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder" aria-hidden="true"></span>' );
}

/**
 * Hero image, falls back to alarm hero until CCTV asset is provided.
 */
function site_blocks_cctv_hero_image(): void {
	$cctv_path = 'images/cctv/hero.webp';
	if ( function_exists( 'site_blocks_media_source_exists' ) && site_blocks_media_source_exists( $cctv_path ) ) {
		site_blocks_cctv_image(
			$cctv_path,
			__( 'Sydney home with CCTV cameras and live phone monitoring app', 'site-blocks' ),
			'sg-hero__pillar-img',
			'eager'
		);
		return;
	}

	if ( is_readable( SITE_BLOCKS_DIR . 'assets/' . $cctv_path ) ) {
		site_blocks_cctv_image(
			$cctv_path,
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
