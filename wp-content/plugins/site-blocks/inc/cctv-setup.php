<?php
/**
 * CCTV category page, page detection (assets via safeguard-silo-setup.php).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the CCTV category page is being viewed.
 */
function site_blocks_is_cctv_page(): bool {
	return is_page( 'cctv-security-cameras' );
}
