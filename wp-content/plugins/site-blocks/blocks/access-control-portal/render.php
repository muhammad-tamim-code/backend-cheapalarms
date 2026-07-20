<?php
/**
 * Access Control, customer portal.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_portal_band(
	array(
		'heading_id'    => 'sg-access-control-portal-heading',
		'section_class' => 'sg-access-control-portal',
		'title_before'  => __( 'Access control quotes in ', 'site-blocks' ),
		'title_accent'  => __( 'one portal', 'site-blocks' ),
		'intro'         => __( 'Share door schedules, user lists and site photos online so we can scope readers, credentials and integration before install.', 'site-blocks' ),
		'bullets'       => array(
			__( 'Track your quote progress in real time', 'site-blocks' ),
			__( 'Upload floor plans and entry photos', 'site-blocks' ),
			__( 'Approve scope and book install dates', 'site-blocks' ),
		),
	)
);
