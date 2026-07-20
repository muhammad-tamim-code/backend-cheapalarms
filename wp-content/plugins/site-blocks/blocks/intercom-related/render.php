<?php
/**
 * Intercom, related services.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-related-services.php';

site_blocks_render_related_services_page_grid(
	array(
		'heading_id'    => 'sg-intercom-related-heading',
		'section_class' => 'sg-intercom-related',
		'title_before'  => __( 'Related ', 'site-blocks' ),
		'title_accent'  => __( 'services', 'site-blocks' ),
		'cards'         => array(
			array( 'title' => __( 'Access Control', 'site-blocks' ), 'desc' => __( 'Combine entry management with intercom verification.', 'site-blocks' ), 'url' => home_url( '/access-control/' ), 'icon' => 'access-control.png' ),
			array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'Record who arrived and when.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
			array( 'title' => __( 'Ajax Alarm Systems', 'site-blocks' ), 'desc' => __( 'Wireless alarms with app control.', 'site-blocks' ), 'url' => home_url( '/ajax-alarm-systems/' ), 'icon' => 'alarm-systems.png' ),
			array( 'title' => __( 'Alarm Monitoring', 'site-blocks' ), 'desc' => __( 'Professional response when alarms trip.', 'site-blocks' ), 'url' => home_url( '/monitoring/' ), 'icon' => 'support.png' ),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
