<?php
/**
 * Contact page quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_quote_cta(
	array(
		'heading_id'    => 'sg-contact-cta-heading',
		'before'        => __( 'Ready for a quote? Start ', 'site-blocks' ),
		'accent'        => __( 'online', 'site-blocks' ),
		'sub'           => __( 'Use our instant quote flow for alarms, CCTV, access control and monitoring, or send us a message above.', 'site-blocks' ),
		'section_class' => 'sg-contact-cta',
	)
);
