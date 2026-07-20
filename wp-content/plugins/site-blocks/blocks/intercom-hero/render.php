<?php

/**

 * Intercom Systems hero block.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



require_once SITE_BLOCKS_DIR . 'inc/intercom-media.php';

require_once SITE_BLOCKS_DIR . 'inc/pillar-hero.php';



site_blocks_render_pillar_hero(

	array(

		'id'              => 'sg-intercom-hero-heading',

		'class'           => 'sg-intercom-hero',

		'breadcrumb'      => array(

			array(

				'label' => __( 'Home', 'site-blocks' ),

				'url'   => home_url( '/' ),

			),

			array(

				'label' => __( 'Services', 'site-blocks' ),

			),

			array(

				'label'   => __( 'Intercom Systems', 'site-blocks' ),

				'current' => true,

			),

		),

		'badge'           => __( 'Intercom · Sydney', 'site-blocks' ),

		'title_before'    => __( 'Know who\'s at the door before you ', 'site-blocks' ),

		'title_accent'    => __( 'open', 'site-blocks' ),

		'title_after'     => __( ' it.', 'site-blocks' ),

		'lead'            => __( 'Video and audio intercoms for Sydney homes, apartments and businesses, see visitors, speak to them, and release the door from your monitor or phone.', 'site-blocks' ),

		'primary_label'   => __( 'Start My Quote', 'site-blocks' ),

		'primary_url'     => home_url( '/get-an-instant-quote/' ),

		'secondary_label' => __( 'Help Me Choose', 'site-blocks' ),

		'secondary_url'   => home_url( '/design-my-solution/' ),

		'footnote'        => __( 'Licensed installers · Master Licence · ASIAL member · Homes, strata & commercial', 'site-blocks' ),

		'visual'          => static function (): void {

			site_blocks_intercom_hero_image();

		},

	)

);

