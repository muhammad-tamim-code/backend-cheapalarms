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
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	if ( ! is_readable( $disk ) ) {
		printf( '<span class="sg-cctv-media-placeholder sg-ac-media-placeholder" aria-hidden="true"></span>' );
		return;
	}

	printf(
		'<img class="%s" src="%s" alt="%s" loading="%s" decoding="async" />',
		esc_attr( $class ),
		esc_url( site_blocks_asset_url( $relative_path ) ),
		esc_attr( $alt ),
		esc_attr( $loading )
	);
}

/**
 * Hero image, placeholder until asset is provided.
 */
function site_blocks_access_control_hero_image(): void {
	$hero = SITE_BLOCKS_DIR . 'assets/images/access-control/hero.webp';
	if ( is_readable( $hero ) ) {
		site_blocks_access_control_image(
			'images/access-control/hero.webp',
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
	$disk = SITE_BLOCKS_DIR . 'assets/' . $path;

	if ( ! is_readable( $disk ) ) {
		printf( '<span class="sg-cctv-media-placeholder sg-ac-process__placeholder" aria-hidden="true"></span>' );
		return;
	}

	site_blocks_access_control_image( $path, $alt, $class, 'lazy' );
}
