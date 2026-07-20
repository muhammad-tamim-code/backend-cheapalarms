<?php
/**
 * Alarm Systems closing quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_quote_cta(
	array(
		'heading_id'    => 'sg-alarm-cta-heading',
		'before'        => __( 'Protection that fits your property starts ', 'site-blocks' ),
		'accent'        => __( 'here', 'site-blocks' ),
		'sub'           => __( 'Tell us what to protect. Get a tailored alarm estimate, no waiting on a salesperson.', 'site-blocks' ),
		'section_class' => 'sg-alarm-cta',
	)
);
