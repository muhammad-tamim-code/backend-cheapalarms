<?php
/**
 * Safeguard Security Services — full homepage render.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-section-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-homepage-markup.php';

site_blocks_render_safeguard_homepage();
