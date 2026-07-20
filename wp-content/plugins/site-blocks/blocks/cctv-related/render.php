<?php
/**
 * CCTV, related services.
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
		'heading_id'    => 'sg-cctv-related-heading',
		'section_class' => 'sg-cctv-related',
		'title_before'  => __( 'Related ', 'site-blocks' ),
		'title_accent'  => __( 'services', 'site-blocks' ),
		'cards'         => array(
			array( 'title' => __( 'Alarm Systems', 'site-blocks' ), 'desc' => __( 'Detection that triggers recording and response.', 'site-blocks' ), 'url' => home_url( '/alarm-systems/' ), 'icon' => 'alarm-systems.png' ),
			array( 'title' => __( 'Access Control', 'site-blocks' ), 'desc' => __( 'See who entered and when.', 'site-blocks' ), 'url' => home_url( '/access-control/' ), 'icon' => 'access-control.png' ),
			array( 'title' => __( 'Virtual Patrol', 'site-blocks' ), 'desc' => __( 'Live operator tours over your cameras.', 'site-blocks' ), 'url' => home_url( '/monitoring/virtual-patrol/' ), 'icon' => 'support.png' ),
			array( 'title' => __( 'Intercom Systems', 'site-blocks' ), 'desc' => __( 'Verify visitors at the door.', 'site-blocks' ), 'url' => home_url( '/intercom-systems/' ), 'icon' => 'access-control.png' ),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
