<?php
/**
 * CCTV hero — Electronic Security product stage hero.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-stage-hero.php';

site_blocks_render_stage_hero(
	array(
		'id'            => 'sg-cctv-hero-heading',
		'class'         => 'sg-cctv-hero',
		'breadcrumb'    => array(
			array(
				'label' => __( 'Home', 'site-blocks' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Electronic Security', 'site-blocks' ),
				'url'   => home_url( '/electronic-security/' ),
			),
			array(
				'label'   => __( 'CCTV & Security Cameras', 'site-blocks' ),
				'current' => true,
			),
		),
		'eyebrow'       => __( 'CCTV · Sydney', 'site-blocks' ),
		'title'         => __( 'A camera on the wall isn\'t security. A properly designed system is.', 'site-blocks' ),
		'lead'          => __( 'Planned, installed and supported across Sydney. Start your quote online — reviewed by our technicians.', 'site-blocks' ),
		'frame_image'   => 'images/cctv/hero.webp',
		'frame_alt'     => __( 'Modern Sydney home with integrated CCTV cameras at dusk', 'site-blocks' ),
		'phone_image'   => 'images/cctv/hero-phone.webp',
		'phone_alt'     => __( 'Live CCTV monitoring app on a smartphone', 'site-blocks' ),
	)
);
