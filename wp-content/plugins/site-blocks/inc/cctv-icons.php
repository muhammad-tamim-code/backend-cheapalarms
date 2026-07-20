<?php
/**
 * CCTV page icon helpers (Lucide).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * Render a CCTV section icon.
 *
 * @param string $filename Legacy PNG filename under assets/images/cctv/icons/.
 * @param int    $size     Display width/height in pixels.
 * @param string $class    Unused legacy param (kept for call-site compatibility).
 */
function site_blocks_cctv_icon( string $filename, int $size = 72, string $class = 'sg-cctv-icon__img' ): void {
	unset( $class );
	site_blocks_lucide_icon_from_legacy( $filename, $size );
}
