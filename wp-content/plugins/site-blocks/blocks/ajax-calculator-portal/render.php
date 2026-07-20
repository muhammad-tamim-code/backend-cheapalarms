<?php
/**
 * Ajax calculator page portal band.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_portal_band(
	array(
		'heading_id'    => 'sg-ajax-calculator-portal-heading',
		'section_class' => 'sg-ajax-calculator-portal',
		'title_before'  => __( 'Save your design and finish in the ', 'site-blocks' ),
		'title_accent'  => __( 'portal', 'site-blocks' ),
		'intro'         => __( 'Your calculator session feeds into our quote portal so a technician can review device choices, photos and site notes before you approve.', 'site-blocks' ),
		'bullets'       => array(
			__( 'Keep your Ajax device list in one place', 'site-blocks' ),
			__( 'Upload photos without email back-and-forth', 'site-blocks' ),
			__( 'Approve estimates and book install online', 'site-blocks' ),
		),
		'image_path'    => 'images/portal/portal-2.webp',
	)
);
