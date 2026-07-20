<?php
/**
 * CCTV closing quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_quote_cta(
	array(
		'heading_id'    => 'sg-cctv-cta-heading',
		'before'        => __( 'A clearer view starts ', 'site-blocks' ),
		'accent'        => __( 'here', 'site-blocks' ),
		'sub'           => __( 'Tell us what to protect. Get a tailored estimate, no waiting on a salesperson.', 'site-blocks' ),
		'section_class' => 'sg-cctv-cta',
	)
);
