<?php
/**
 * Intercom, why an intercom matters.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/intercom-icons.php';

site_blocks_render_intro_proofs(
	array(
		'heading_id'    => 'sg-intercom-intro-heading',
		'section_class' => 'sg-intercom-intro',
		'title_before'  => __( 'Every entry is a ', 'site-blocks' ),
		'title_accent'  => __( 'decision', 'site-blocks' ),
		'body'          => __( 'The front door is where security is decided. An intercom lets you see and speak to visitors before anything opens, from the monitor or your phone, wherever you are.', 'site-blocks' ),
		'proofs'        => array(
			array(
				'title' => __( 'Verify', 'site-blocks' ),
				'desc'  => __( 'See and speak to visitors before the door ever opens.', 'site-blocks' ),
				'icon'  => 'ip-camera.png',
			),
			array(
				'title' => __( 'Answer anywhere', 'site-blocks' ),
				'desc'  => __( 'Take door calls and release entry from your phone.', 'site-blocks' ),
				'icon'  => 'remote-app.png',
			),
			array(
				'title' => __( 'Control access', 'site-blocks' ),
				'desc'  => __( 'Buzz in deliveries and tradespeople without a key handover.', 'site-blocks' ),
				'icon'  => 'access-control.png',
			),
		),
		'icon_renderer' => 'site_blocks_intercom_icon',
	)
);
