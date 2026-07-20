<?php
/**
 * Ajax Alarm Systems, related services.
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
		'heading_id'    => 'sg-ajax-related-heading',
		'section_class' => 'sg-ajax-related',
		'title_before'  => __( 'Often installed ', 'site-blocks' ),
		'title_accent'  => __( 'with', 'site-blocks' ),
		'cards'         => array(
			array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'See entries and verify alarm events.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
			array( 'title' => __( 'Intercom Systems', 'site-blocks' ), 'desc' => __( 'Verify visitors before you open up.', 'site-blocks' ), 'url' => home_url( '/intercom-systems/' ), 'icon' => 'access-control.png' ),
			array( 'title' => __( 'Back-to-Base Monitoring', 'site-blocks' ), 'desc' => __( 'Professional alarm response 24/7.', 'site-blocks' ), 'url' => home_url( '/monitoring/back-to-base/' ), 'icon' => 'support.png' ),
			array( 'title' => __( 'Alarm Systems', 'site-blocks' ), 'desc' => __( 'Broader alarm options beyond Ajax wireless.', 'site-blocks' ), 'url' => home_url( '/alarm-systems/' ), 'icon' => 'alarm-systems.png' ),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
