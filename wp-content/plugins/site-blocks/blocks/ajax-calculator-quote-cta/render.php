<?php
/**
 * Ajax calculator page quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_ajax_quote_cta(
	array(
		'title'         => __( 'Ready for a technician-reviewed estimate?', 'site-blocks' ),
		'lead'          => __( 'Continue from the calculator into our portal and we will confirm device choices, coverage and install scope for your property.', 'site-blocks' ),
		'primary_label' => __( 'Start My Quote', 'site-blocks' ),
		'primary_url'   => home_url( '/get-an-instant-quote/' ),
		'section_class' => 'sg-ajax-calculator-quote-cta',
	)
);
