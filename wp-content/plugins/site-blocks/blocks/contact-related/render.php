<?php
/**
 * Contact page, related services.
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
		'heading_id'    => 'sg-contact-related-heading',
		'section_class' => 'sg-contact-related',
		'title_before'  => __( 'Explore our ', 'site-blocks' ),
		'title_accent'  => __( 'services', 'site-blocks' ),
		'cards'         => array(
			array(
				'title' => __( 'Alarm Systems', 'site-blocks' ),
				'desc'  => __( 'Wireless and smart alarms for homes and businesses.', 'site-blocks' ),
				'url'   => home_url( '/alarm-systems/' ),
				'icon'  => 'alarm-systems.png',
			),
			array(
				'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
				'desc'  => __( 'Designed, installed and supported by one team.', 'site-blocks' ),
				'url'   => home_url( '/cctv-security-cameras/' ),
				'icon'  => 'ip-camera.png',
			),
			array(
				'title' => __( 'Access Control', 'site-blocks' ),
				'desc'  => __( 'Keys, cards and cloud-managed entry.', 'site-blocks' ),
				'url'   => home_url( '/access-control/' ),
				'icon'  => 'access-control.png',
			),
			array(
				'title' => __( 'Alarm Monitoring', 'site-blocks' ),
				'desc'  => __( 'Back-to-base and virtual patrol options.', 'site-blocks' ),
				'url'   => home_url( '/monitoring/' ),
				'icon'  => 'support.png',
			),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
