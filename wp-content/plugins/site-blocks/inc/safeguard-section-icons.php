<?php
/**
 * Lucide icons for Safeguard homepage sections.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/** Funnel: I know what I need. */
function site_blocks_sg_icon_funnel_quote(): void {
	site_blocks_lucide_icon( 'file-text', 64 );
}

/** Funnel: Help me choose. */
function site_blocks_sg_icon_funnel_help(): void {
	site_blocks_lucide_icon( 'headset', 64 );
}

/** Service: Alarm Systems. */
function site_blocks_sg_icon_service_alarm(): void {
	site_blocks_lucide_icon( 'shield-check', 64 );
}

/** Service: CCTV & Cameras. */
function site_blocks_sg_icon_service_cctv(): void {
	site_blocks_lucide_icon( 'cctv', 64 );
}

/** Service: Video Intercoms. */
function site_blocks_sg_icon_service_intercom(): void {
	site_blocks_lucide_icon( 'video', 64 );
}

/** Service: Access Control. */
function site_blocks_sg_icon_service_access(): void {
	site_blocks_lucide_icon( 'door-closed', 64 );
}

/** Service: Monitoring & Response. */
function site_blocks_sg_icon_service_monitoring(): void {
	site_blocks_lucide_icon( 'monitor', 64 );
}

/** Service: Preventative Maintenance. */
function site_blocks_sg_icon_service_maintenance(): void {
	site_blocks_lucide_icon( 'wrench', 64 );
}

/** Service: Commercial. */
function site_blocks_sg_icon_service_commercial(): void {
	site_blocks_lucide_icon( 'building-2', 64 );
}

/** Service: Residential. */
function site_blocks_sg_icon_service_residential(): void {
	site_blocks_lucide_icon( 'house', 64 );
}

/** Ajax trust: Grade 2 security. */
function site_blocks_sg_icon_ajax_grade2(): void {
	site_blocks_lucide_icon( 'award', 44 );
}

/** Ajax trust: Encrypted end-to-end. */
function site_blocks_sg_icon_ajax_encrypted(): void {
	site_blocks_lucide_icon( 'lock', 44 );
}

/** Ajax trust: Scalable and future-ready. */
function site_blocks_sg_icon_ajax_scalable(): void {
	site_blocks_lucide_icon( 'network', 44 );
}

/** Ajax trust: Designed in Europe. */
function site_blocks_sg_icon_ajax_europe(): void {
	site_blocks_lucide_icon( 'globe', 44 );
}

/** Benefit: remote review (cloud). */
function site_blocks_sg_icon_benefit_remote(): void {
	site_blocks_lucide_icon( 'cloud-download', 64 );
}

/** Benefit: site visits (calendar). */
function site_blocks_sg_icon_benefit_visit(): void {
	site_blocks_lucide_icon( 'calendar-clock', 64 );
}

/** Benefit: better estimates (chart). */
function site_blocks_sg_icon_benefit_estimate(): void {
	site_blocks_lucide_icon( 'bar-chart-3', 64 );
}

/** Benefit: estimate includes checkmark. */
function site_blocks_sg_icon_benefit_check(): void {
	site_blocks_lucide_icon( 'circle-check', 18, 'sg-lucide-icon--success' );
}

/** Benefit: transparent trust shield. */
function site_blocks_sg_icon_benefit_shield(): void {
	site_blocks_lucide_icon( 'shield', 56 );
}

/** Team: experienced technicians. */
function site_blocks_sg_icon_team_technician(): void {
	site_blocks_lucide_icon( 'user', 40 );
}

/** Team: residential and commercial. */
function site_blocks_sg_icon_team_buildings(): void {
	site_blocks_lucide_icon( 'building-2', 40 );
}

/** Team: support and documentation. */
function site_blocks_sg_icon_team_docs(): void {
	site_blocks_lucide_icon( 'file-text', 40 );
}

/** Team: maintenance and monitoring. */
function site_blocks_sg_icon_team_monitor(): void {
	site_blocks_lucide_icon( 'monitor', 40 );
}
