<?php
/**
 * CCTV, why Safeguard trust panel.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

site_blocks_render_trust_panel(
	array(
		'heading_id'    => 'sg-cctv-trust-heading',
		'section_class' => 'sg-cctv-trust',
		'panel_class'   => 'sg-alarm-why__panel sg-cctv-trust__panel',
		'title_before'  => __( 'Designed and supported by experienced ', 'site-blocks' ),
		'title_accent'  => __( 'technicians', 'site-blocks' ),
		'items'         => array(
			array(
				'title' => __( 'Licensed and accredited', 'site-blocks' ),
				'desc'  => __( 'Licensed installers, ASIAL member.', 'site-blocks' ),
				'icon'  => 'weatherproof.png',
			),
			array(
				'title' => __( 'Residential and commercial', 'site-blocks' ),
				'desc'  => __( 'Homes to multi-site business systems.', 'site-blocks' ),
				'icon'  => 'property-coverage.png',
			),
			array(
				'title' => __( 'Built for complex sites', 'site-blocks' ),
				'desc'  => __( 'Heritage, no-cavity and large properties.', 'site-blocks' ),
				'icon'  => 'home-camera.png',
			),
			array(
				'title' => __( 'Support after install', 'site-blocks' ),
				'desc'  => __( 'Documentation, maintenance and monitoring.', 'site-blocks' ),
				'icon'  => 'support.png',
			),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
