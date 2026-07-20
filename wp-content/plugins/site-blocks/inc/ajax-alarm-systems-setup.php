<?php
/**
 * Ajax Alarm Systems page, page detection (assets via safeguard-silo-setup.php).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the Ajax Alarm Systems landing page is being viewed.
 */
function site_blocks_is_ajax_alarm_systems_page(): bool {
	return is_page( 'ajax-alarm-systems' );
}
