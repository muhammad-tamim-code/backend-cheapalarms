<?php
/**
 * CCTV, why well-placed cameras (prose + proof stack).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

site_blocks_render_intro_proofs(
	array(
		'heading_id'    => 'sg-cctv-intro-heading',
		'section_class' => 'sg-cctv-intro',
		'title_before'  => __( 'Why well-placed cameras ', 'site-blocks' ),
		'title_accent'  => __( 'work', 'site-blocks' ),
		'body'          => __( 'Cameras deter, record and let you check in remotely, but only when they\'re placed, angled and wired correctly. That\'s what we design for.', 'site-blocks' ),
		'proofs'        => array(
			array(
				'title' => __( 'Deterrence', 'site-blocks' ),
				'desc'  => __( 'Visible cameras discourage opportunistic entry.', 'site-blocks' ),
				'icon'  => 'ip-camera.png',
			),
			array(
				'title' => __( 'Evidence', 'site-blocks' ),
				'desc'  => __( 'Clear footage for police and insurance.', 'site-blocks' ),
				'icon'  => 'smart-detection.png',
			),
			array(
				'title' => __( 'Oversight', 'site-blocks' ),
				'desc'  => __( 'Live view and alerts on your phone.', 'site-blocks' ),
				'icon'  => 'remote-app.png',
			),
		),
		'icon_renderer' => 'site_blocks_cctv_icon',
	)
);
