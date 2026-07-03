<?php
/**
 * Intercom page icon helpers (reuses CCTV icon assets until intercom-specific icons are added).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

/**
 * Render an intercom section icon.
 *
 * @param string $filename Icon filename under assets/images/cctv/icons/.
 * @param int    $size     Display width/height in pixels.
 */
function site_blocks_intercom_icon( string $filename, int $size = 72 ): void {
	site_blocks_cctv_icon( $filename, $size );
}
