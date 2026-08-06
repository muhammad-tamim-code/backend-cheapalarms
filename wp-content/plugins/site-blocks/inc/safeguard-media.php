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
		'monitoring'           => 'images/monitoring/',
		'physical-security'    => 'images/physical-security/',
		'enterprise'           => 'images/enterprise/',
		'electronic-security'  => 'images/electronic-security/',
		'manpower'             => 'images/manpower/',
	);
}

/**
 * Placeholder class suffix per silo.
 *
 * @return array<string, string>
 */
function site_blocks_silo_placeholder_classes(): array {
	return array(
		'monitoring'           => 'sg-monitoring-media-placeholder',
		'physical-security'    => 'sg-ps-media-placeholder',
		'enterprise'           => 'sg-enterprise-media-placeholder',
		'electronic-security'  => 'sg-es-media-placeholder',
		'manpower'             => 'sg-mp-media-placeholder',
	);
}

/**
 * Hero placeholder class suffix per silo.
 *
 * @return array<string, string>
 */
function site_blocks_silo_hero_placeholder_classes(): array {
	return array(
		'monitoring'           => 'sg-monitoring-hero-placeholder',
		'physical-security'    => 'sg-ps-hero-placeholder',
		'enterprise'           => 'sg-enterprise-media-placeholder',
		'electronic-security'  => 'sg-es-hero-placeholder',
		'manpower'             => 'sg-mp-hero-placeholder',
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
	$placeholder   = $placeholders[ $silo ] ?? 'sg-monitoring-media-placeholder';

	if ( function_exists( 'site_blocks_print_managed_image' ) && site_blocks_print_managed_image( $relative_path, $alt, $class, $loading ) ) {
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder %s" aria-hidden="true"></span>', esc_attr( $placeholder ) );
}

/**
 * Hero image for a silo page.
 *
 * @param string $silo     Silo key.
 * @param string $filename Hero filename.
 * @param string $alt      Alt text.
 */
function site_blocks_silo_hero_image( string $silo, string $filename, string $alt ): void {
	$dirs          = site_blocks_silo_image_directories();
	$placeholders  = site_blocks_silo_hero_placeholder_classes();
	$relative_base = $dirs[ $silo ] ?? 'images/monitoring/';
	$relative_path = $relative_base . ltrim( $filename, '/' );
	$placeholder   = $placeholders[ $silo ] ?? 'sg-monitoring-hero-placeholder';

	if ( function_exists( 'site_blocks_media_source_exists' ) && site_blocks_media_source_exists( $relative_path ) ) {
		site_blocks_silo_image( $silo, $filename, $alt, 'sg-hero__pillar-img', 'eager' );
		return;
	}

	if ( is_readable( SITE_BLOCKS_DIR . 'assets/' . $relative_path ) ) {
		site_blocks_silo_image( $silo, $filename, $alt, 'sg-hero__pillar-img', 'eager' );
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder %s" aria-hidden="true"></span>', esc_attr( $placeholder ) );
}
