<?php
/**
 * Lucide icons for Alarm Systems page sections.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/** Wireless & smart alarms. */
function site_blocks_alarm_icon_wireless(): void {
	site_blocks_lucide_icon( 'wifi', 42 );
}

/** Home alarm systems. */
function site_blocks_alarm_icon_home(): void {
	site_blocks_lucide_icon( 'house', 42 );
}

/** Business alarm systems. */
function site_blocks_alarm_icon_business(): void {
	site_blocks_lucide_icon( 'building-2', 42 );
}

/** Alarm upgrades. */
function site_blocks_alarm_icon_upgrade(): void {
	site_blocks_lucide_icon( 'arrow-up-circle', 42 );
}

/** Servicing & repairs. */
function site_blocks_alarm_icon_service(): void {
	site_blocks_lucide_icon( 'wrench', 42 );
}

/** Back-to-base monitoring. */
function site_blocks_alarm_icon_monitoring(): void {
	site_blocks_lucide_icon( 'headset', 42 );
}

/** Why: designed around your home. */
function site_blocks_alarm_icon_why_design(): void {
	site_blocks_lucide_icon( 'pen-tool', 56 );
}

/** Why: installed properly. */
function site_blocks_alarm_icon_why_install(): void {
	site_blocks_lucide_icon( 'clipboard-check', 56 );
}

/** Why: supported for years. */
function site_blocks_alarm_icon_why_support(): void {
	site_blocks_lucide_icon( 'headset', 56 );
}

/** Step 1: Tell us what you need. */
function site_blocks_alarm_icon_step_tell(): void {
	site_blocks_lucide_icon( 'message-square', 72 );
}

/** Step 2: Share a few photos. */
function site_blocks_alarm_icon_step_photos(): void {
	site_blocks_lucide_icon( 'camera', 72 );
}

/** Step 3: Get a tailored price. */
function site_blocks_alarm_icon_step_price(): void {
	site_blocks_lucide_icon( 'tag', 72 );
}

/** Step 4: Reviewed by a technician. */
function site_blocks_alarm_icon_step_review(): void {
	site_blocks_lucide_icon( 'user-check', 72 );
}
