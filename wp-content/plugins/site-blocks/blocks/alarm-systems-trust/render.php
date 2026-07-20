<?php
/**
 * Alarm Systems, trust panel.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * @param string $icon Lucide icon key.
 */
function site_blocks_alarm_trust_lucide_icon( string $icon ): void {
	site_blocks_lucide_icon( $icon, 24 );
}

site_blocks_render_trust_panel(
	array(
		'heading_id'    => 'sg-alarm-trust-heading',
		'section_class' => 'sg-alarm-trust',
		'title_before'  => __( 'Designed and supported by experienced ', 'site-blocks' ),
		'title_accent'  => __( 'technicians', 'site-blocks' ),
		'items'         => array(
			array(
				'title' => __( 'Licensed and accredited', 'site-blocks' ),
				'desc'  => __( 'Licensed installers, ASIAL member.', 'site-blocks' ),
				'icon'  => 'badge-check',
			),
			array(
				'title' => __( 'Residential and commercial', 'site-blocks' ),
				'desc'  => __( 'Homes to multi-site business systems.', 'site-blocks' ),
				'icon'  => 'building-2',
			),
			array(
				'title' => __( 'Wireless and hybrid options', 'site-blocks' ),
				'desc'  => __( 'Ajax, traditional and upgrade pathways.', 'site-blocks' ),
				'icon'  => 'wifi',
			),
			array(
				'title' => __( 'Support after install', 'site-blocks' ),
				'desc'  => __( 'Servicing, monitoring and portal access.', 'site-blocks' ),
				'icon'  => 'headset',
			),
		),
		'icon_renderer' => 'site_blocks_alarm_trust_lucide_icon',
	)
);
