<?php
/**
 * Contact page, customer portal band.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_portal_band(
	array(
		'heading_id'    => 'sg-contact-portal-heading',
		'section_class' => 'sg-contact-portal',
		'title_before'  => __( 'Prefer to start online? Your quote lives in ', 'site-blocks' ),
		'title_accent'  => __( 'one portal', 'site-blocks' ),
		'intro'         => __( 'Many customers begin with our instant quote flow, then track photos, messages and approvals without chasing callbacks.', 'site-blocks' ),
		'bullets'       => array(
			__( 'Start a quote in minutes from any device', 'site-blocks' ),
			__( 'Upload site photos when it suits you', 'site-blocks' ),
			__( 'Message our team and approve estimates online', 'site-blocks' ),
		),
	)
);
