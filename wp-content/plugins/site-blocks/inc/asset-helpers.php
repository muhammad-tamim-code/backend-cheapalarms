<?php
/**
 * Plugin asset URL helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prefer a sibling WebP file when the requested raster asset has one on disk.
 *
 * @param string $relative_path Path relative to assets/, e.g. images/hero/house.png.
 */
function site_blocks_resolve_asset_path( string $relative_path ): string {
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );

	if ( ! preg_match( '/\.(png|jpe?g)$/i', $relative_path ) ) {
		return $relative_path;
	}

	$webp_path = preg_replace( '/\.(png|jpe?g)$/i', '.webp', $relative_path );
	$disk_path = SITE_BLOCKS_DIR . 'assets/' . $webp_path;

	if ( is_readable( $disk_path ) ) {
		return $webp_path;
	}

	return $relative_path;
}

/**
 * Build a public URL for a file under site-blocks/assets/.
 *
 * Raster paths (.png, .jpg, .jpeg) automatically use a sibling .webp file when present.
 * Image paths under images/ prefer the Media Library URL after import.
 *
 * @param string $relative_path Path relative to assets/, e.g. images/hero/house.png.
 */
function site_blocks_asset_url( string $relative_path ): string {
	$relative_path = site_blocks_resolve_asset_path( $relative_path );

	if ( 0 === strpos( $relative_path, 'images/' ) && function_exists( 'site_blocks_media_library_url' ) ) {
		$ml = site_blocks_media_library_url( $relative_path );
		if ( '' !== $ml ) {
			return esc_url( $ml );
		}
	}

	$segments = explode( '/', $relative_path );
	$encoded  = implode( '/', array_map( 'rawurlencode', $segments ) );

	return esc_url( SITE_BLOCKS_URL . 'assets/' . $encoded );
}
