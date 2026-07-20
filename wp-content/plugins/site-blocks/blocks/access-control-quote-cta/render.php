<?php
/**
 * Access Control closing quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_quote_cta(
	array(
		'heading_id'    => 'sg-access-control-cta-heading',
		'before'        => __( 'Control who enters. From ', 'site-blocks' ),
		'accent'        => __( 'anywhere', 'site-blocks' ),
		'after'         => __( '.', 'site-blocks' ),
		'sub'           => __( 'Tell us your doors, users and scheduling needs. Get a tailored access control estimate, reviewed by our technicians, without waiting days for a salesperson.', 'site-blocks' ),
		'section_class' => 'sg-cctv-cta sg-access-control-cta',
	)
);
