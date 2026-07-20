<?php
/**
 * Intercom, Safeguard difference + process steps.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow-configs.php';

site_blocks_render_process_flow( site_blocks_process_flow_config( 'intercom' ) );
