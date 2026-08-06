<?php
/**
 * Access Control hero — Electronic Security product stage hero.
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
		'id'          => 'sg-access-control-hero-heading',
		'class'       => 'sg-access-control-hero',
		'breadcrumb'  => array(
			array(
				'label' => __( 'Home', 'site-blocks' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Electronic Security', 'site-blocks' ),
				'url'   => home_url( '/electronic-security/' ),
			),
			array(
				'label'   => __( 'Access Control', 'site-blocks' ),
				'current' => true,
			),
		),
		'eyebrow'     => __( 'Access Control · Sydney', 'site-blocks' ),
		'title'       => __( 'Replace keys with access you can control.', 'site-blocks' ),
		'lead'        => __( 'Cards, mobile credentials, PINs and biometrics, designed, installed and supported across Sydney. Start your quote online, reviewed by our technicians.', 'site-blocks' ),
		'trust'       => array(
			array(
				'icon'  => 'award',
				'label' => __( 'Master Licence #000103619', 'site-blocks' ),
			),
			array(
				'icon'  => 'shield',
				'label' => __( 'ASIAL member', 'site-blocks' ),
			),
			array(
				'icon'  => 'house',
				'label' => __( 'Commercial & residential', 'site-blocks' ),
			),
		),
		'frame_image' => 'images/access-control/hero.webp',
		'frame_alt'   => __( 'Access control card reader at a Sydney building entrance', 'site-blocks' ),
	)
);
