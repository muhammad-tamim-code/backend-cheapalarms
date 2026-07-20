<?php
/**
 * Intercom closing quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_quote_cta(
	array(
		'heading_id'    => 'sg-intercom-cta-heading',
		'before'        => __( 'See who\'s there. Decide from ', 'site-blocks' ),
		'accent'        => __( 'anywhere', 'site-blocks' ),
		'after'         => __( '.', 'site-blocks' ),
		'sub'           => __( 'Tell us your entries and how you\'d like to answer them. Get a tailored intercom estimate, reviewed by our technicians, without waiting days for a salesperson.', 'site-blocks' ),
		'section_class' => 'sg-cctv-cta sg-intercom-cta',
	)
);
