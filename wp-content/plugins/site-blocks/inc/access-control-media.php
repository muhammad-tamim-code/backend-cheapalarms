<?php
/**
 * Access Control page media helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render an access control image or placeholder when asset is not uploaded yet.
 *
 * @param string $relative_path Path under assets/, e.g. images/access-control/what.webp.
 * @param string $alt           Alt text.
 * @param string $class         Optional img class.
 * @param string $loading       loading attribute.
 */
function site_blocks_access_control_image( string $relative_path, string $alt = '', string $class = 'sg-ac-split__img', string $loading = 'lazy' ): void {
	$relative_path = ltrim( $relative_path, '/' );

	if ( function_exists( 'site_blocks_print_managed_image' ) && site_blocks_print_managed_image( $relative_path, $alt, $class, $loading ) ) {
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder sg-ac-media-placeholder" aria-hidden="true"></span>' );
}

/**
 * Hero image, placeholder until asset is provided.
 */
function site_blocks_access_control_hero_image(): void {
	$relative = 'images/access-control/hero.webp';
	if ( function_exists( 'site_blocks_media_source_exists' ) && site_blocks_media_source_exists( $relative ) ) {
		site_blocks_access_control_image(
			$relative,
			__( 'Access control card reader and door hardware installed in Sydney', 'site-blocks' ),
			'sg-hero__pillar-img',
			'eager'
		);
		return;
	}

	$hero = SITE_BLOCKS_DIR . 'assets/' . $relative;
	if ( is_readable( $hero ) ) {
		site_blocks_access_control_image(
			$relative,
			__( 'Access control card reader and door hardware installed in Sydney', 'site-blocks' ),
			'sg-hero__pillar-img',
			'eager'
		);
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder sg-ac-hero-placeholder" aria-hidden="true"></span>' );
}

/**
 * Square process-step thumbnail (1:1 crop). Placeholder until process-NN.webp is uploaded.
 *
 * @param int    $step 1–6.
 * @param string $alt  Alt text.
 */
function site_blocks_access_control_process_thumb( int $step, string $alt, string $class = 'sg-ac-process__ico' ): void {
	$step = max( 1, min( 6, $step ) );
	$path = sprintf( 'images/access-control/process-%02d.webp', $step );

	if ( function_exists( 'site_blocks_media_source_exists' ) && ! site_blocks_media_source_exists( $path ) ) {
		printf( '<span class="sg-cctv-media-placeholder sg-ac-process__placeholder" aria-hidden="true"></span>' );
		return;
	}

	if ( ! function_exists( 'site_blocks_media_source_exists' ) && ! is_readable( SITE_BLOCKS_DIR . 'assets/' . $path ) ) {
		printf( '<span class="sg-cctv-media-placeholder sg-ac-process__placeholder" aria-hidden="true"></span>' );
		return;
	}

	site_blocks_access_control_image( $path, $alt, $class, 'lazy' );
}
