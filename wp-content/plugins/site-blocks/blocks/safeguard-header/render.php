<?php
/**
 * Safeguard header block render.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';

site_blocks_render_safeguard_header();
