<?php
/**
 * Intercom page media helpers.
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
function site_blocks_intercom_image_dimensions( string $relative_path ): array {
	$relative_path = ltrim( $relative_path, '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	$defaults = array(
		'images/intercom/hero.webp'                  => array( 'width' => 1600, 'height' => 900 ),
		'images/ajax/property/home.webp'             => array( 'width' => 1448, 'height' => 1086 ),
		'images/ajax/property/apartments.webp'       => array( 'width' => 1448, 'height' => 1086 ),
		'images/ajax/property/small-business.webp'   => array( 'width' => 1448, 'height' => 1086 ),
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
 * Render an intercom image or placeholder when asset is not uploaded yet.
 *
 * @param string $relative_path Path under assets/, e.g. images/intercom/hero.webp.
 * @param string $alt           Alt text.
 * @param string $class         Optional img class.
 * @param string $loading       loading attribute.
 */
function site_blocks_intercom_image( string $relative_path, string $alt = '', string $class = 'sg-cctv-media__img', string $loading = 'lazy' ): void {
	$relative_path = ltrim( $relative_path, '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	if ( ! is_readable( $disk ) ) {
		printf( '<span class="sg-cctv-media-placeholder" aria-hidden="true"></span>' );
		return;
	}

	$dims = site_blocks_intercom_image_dimensions( $relative_path );

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
 * Hero image — placeholder until intercom hero asset is provided.
 */
function site_blocks_intercom_hero_image(): void {
	$hero = SITE_BLOCKS_DIR . 'assets/images/intercom/hero.webp';
	if ( is_readable( $hero ) ) {
		site_blocks_intercom_image(
			'images/intercom/hero.webp',
			__( 'Video intercom door station, indoor monitor and phone app installed in Sydney', 'site-blocks' ),
			'sg-hero__pillar-img',
			'eager'
		);
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder sg-intercom-hero-placeholder" aria-hidden="true"></span>' );
}
