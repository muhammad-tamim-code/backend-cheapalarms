<?php

/**

 * CCTV hero block.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



require_once SITE_BLOCKS_DIR . 'inc/cctv-media.php';

require_once SITE_BLOCKS_DIR . 'inc/pillar-hero.php';



site_blocks_render_pillar_hero(

	array(

		'id'              => 'sg-cctv-hero-heading',

		'class'           => 'sg-cctv-hero',

		'breadcrumb'      => array(

			array(

				'label' => __( 'Home', 'site-blocks' ),

				'url'   => home_url( '/' ),

			),

			array(

				'label' => __( 'Services', 'site-blocks' ),

			),

			array(

				'label'   => __( 'CCTV & Security Cameras', 'site-blocks' ),

				'current' => true,

			),

		),

		'badge'           => __( 'CCTV · Sydney', 'site-blocks' ),

		'title_before'    => __( 'A camera on the wall isn\'t security. A properly designed ', 'site-blocks' ),

		'title_accent'    => __( 'system', 'site-blocks' ),

		'title_after'     => __( ' is.', 'site-blocks' ),

		'lead'            => __( 'Planned, installed and supported across Sydney. Start your quote online — reviewed by our technicians.', 'site-blocks' ),

		'primary_label'   => __( 'Start My Quote', 'site-blocks' ),

		'primary_url'     => home_url( '/get-an-instant-quote/' ),

		'secondary_label' => __( 'Help Me Choose', 'site-blocks' ),

		'secondary_url'   => home_url( '/design-my-solution/' ),

		'footnote'        => __( 'Licensed installers · ASIAL member · Residential & commercial', 'site-blocks' ),

		'visual'          => static function (): void {

			site_blocks_cctv_hero_image();

		},

	)

);

