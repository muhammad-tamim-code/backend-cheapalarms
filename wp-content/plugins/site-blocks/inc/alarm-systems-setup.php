<?php
/**
 * Alarm Systems page, page detection (assets via safeguard-silo-setup.php).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the Alarm Systems service page is being viewed.
 */
function site_blocks_is_alarm_systems_page(): bool {
	return is_page( 'alarm-systems' );
}
