<?php
/**
 * Shared Safeguard silo image helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Silo image directory map.
 *
 * @return array<string, string>
 */
function site_blocks_silo_image_directories(): array {
	return array(
		'monitoring'         => 'images/monitoring/',
		'physical-security'  => 'images/physical-security/',
		'enterprise'         => 'images/enterprise/',
	);
}

/**
 * Placeholder class suffix per silo.
 *
 * @return array<string, string>
 */
function site_blocks_silo_placeholder_classes(): array {
	return array(
		'monitoring'         => 'sg-monitoring-media-placeholder',
		'physical-security'  => 'sg-ps-media-placeholder',
		'enterprise'         => 'sg-enterprise-media-placeholder',
	);
}

/**
 * Hero placeholder class suffix per silo.
 *
 * @return array<string, string>
 */
function site_blocks_silo_hero_placeholder_classes(): array {
	return array(
		'monitoring'         => 'sg-monitoring-hero-placeholder',
		'physical-security'  => 'sg-ps-hero-placeholder',
		'enterprise'         => 'sg-enterprise-media-placeholder',
	);
}

/**
 * Render a silo image or placeholder.
 *
 * @param string $silo     Silo key.
 * @param string $filename Filename under the silo images directory.
 * @param string $alt      Alt text.
 * @param string $class    Image class.
 * @param string $loading  loading attribute.
 */
function site_blocks_silo_image( string $silo, string $filename, string $alt = '', string $class = 'sg-ac-split__img', string $loading = 'lazy' ): void {
	$dirs          = site_blocks_silo_image_directories();
	$placeholders  = site_blocks_silo_placeholder_classes();
	$relative_base = $dirs[ $silo ] ?? 'images/monitoring/';
	$relative_path = $relative_base . ltrim( $filename, '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;
	$placeholder   = $placeholders[ $silo ] ?? 'sg-monitoring-media-placeholder';

	if ( ! is_readable( $disk ) ) {
		printf( '<span class="sg-cctv-media-placeholder %s" aria-hidden="true"></span>', esc_attr( $placeholder ) );
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
 * Hero image for a silo page.
 *
 * @param string $silo     Silo key.
 * @param string $filename Hero filename.
 * @param string $alt      Alt text.
 */
function site_blocks_silo_hero_image( string $silo, string $filename, string $alt ): void {
	$dirs         = site_blocks_silo_image_directories();
	$placeholders = site_blocks_silo_hero_placeholder_classes();
	$relative_base = $dirs[ $silo ] ?? 'images/monitoring/';
	$relative_path = $relative_base . ltrim( $filename, '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;
	$placeholder   = $placeholders[ $silo ] ?? 'sg-monitoring-hero-placeholder';

	if ( is_readable( $disk ) ) {
		site_blocks_silo_image( $silo, $filename, $alt, 'sg-hero__pillar-img', 'eager' );
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder %s" aria-hidden="true"></span>', esc_attr( $placeholder ) );
}
