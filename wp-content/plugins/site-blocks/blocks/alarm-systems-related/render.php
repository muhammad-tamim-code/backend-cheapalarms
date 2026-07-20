<?php
/**
 * Alarm Systems, related services.
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
		'heading_id'    => 'sg-alarm-related-heading',
		'section_class' => 'sg-alarm-related',
		'title_before'  => __( 'Related ', 'site-blocks' ),
		'title_accent'  => __( 'services', 'site-blocks' ),
		'cards'         => array(
			array(
				'title' => __( 'Ajax Alarm Systems', 'site-blocks' ),
				'desc'  => __( 'Wireless Grade 2 systems designed for your property.', 'site-blocks' ),
				'url'   => home_url( '/ajax-alarm-systems/' ),
				'icon'  => 'alarm-systems.png',
			),
			array(
				'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
				'desc'  => __( 'See and record every entry.', 'site-blocks' ),
				'url'   => home_url( '/cctv-security-cameras/' ),
				'icon'  => 'ip-camera.png',
			),
			array(
				'title' => __( 'Intercom Systems', 'site-blocks' ),
				'desc'  => __( 'Verify visitors before you open up.', 'site-blocks' ),
				'url'   => home_url( '/intercom-systems/' ),
				'icon'  => 'access-control.png',
			),
			array(
				'title' => __( 'Alarm Monitoring', 'site-blocks' ),
				'desc'  => __( 'Professional response around the clock.', 'site-blocks' ),
				'url'   => home_url( '/monitoring/' ),
				'icon'  => 'support.png',
			),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
