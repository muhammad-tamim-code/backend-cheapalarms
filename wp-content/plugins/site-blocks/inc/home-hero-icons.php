<?php
/**
 * Lucide icons for home hero orbit.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/** Shield center icon. */
function site_blocks_home_icon_shield(): void {
	site_blocks_lucide_icon( 'shield-check', 64, 'sg-home-hero__shield-icon' );
}

/** Camera orbit icon. */
function site_blocks_home_icon_camera(): void {
	site_blocks_lucide_icon( 'cctv', 40 );
}

/** Alarm orbit icon. */
function site_blocks_home_icon_alarm(): void {
	site_blocks_lucide_icon( 'bell', 40 );
}

/** Intercom orbit icon. */
function site_blocks_home_icon_intercom(): void {
	site_blocks_lucide_icon( 'video', 40 );
}

/** Monitor orbit icon. */
function site_blocks_home_icon_monitor(): void {
	site_blocks_lucide_icon( 'monitor', 40 );
}

/** Access control orbit icon. */
function site_blocks_home_icon_access(): void {
	site_blocks_lucide_icon( 'door-closed', 40 );
}
