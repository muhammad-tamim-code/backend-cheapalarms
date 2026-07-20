<?php
/**
 * Ajax Alarm Systems, customer portal.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_portal_band(
	array(
		'heading_id'    => 'sg-ajax-portal-heading',
		'section_class' => 'sg-ajax-portal',
		'title_before'  => __( 'Your Ajax quote, photos and approvals in ', 'site-blocks' ),
		'title_accent'  => __( 'one place', 'site-blocks' ),
		'intro'         => __( 'Start with the calculator or instant quote, then manage device choices, site photos and technician review in our secure portal.', 'site-blocks' ),
		'bullets'       => array(
			__( 'Track quote progress in real time', 'site-blocks' ),
			__( 'Upload photos and approve your estimate', 'site-blocks' ),
			__( 'Message our team without waiting on callbacks', 'site-blocks' ),
		),
		'image_path'    => 'images/portal/portal-2.webp',
	)
);
