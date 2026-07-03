<?php
/**
 * Safeguard footer block render.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-footer.php';

site_blocks_render_safeguard_footer();
site_blocks_render_safeguard_mobile_bar();
