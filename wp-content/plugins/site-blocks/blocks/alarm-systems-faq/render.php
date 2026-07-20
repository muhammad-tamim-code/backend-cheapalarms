<?php
/**
 * Alarm Systems FAQ block render.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

site_blocks_render_faq_section(
	site_blocks_get_safeguard_faq_items(),
	array(
		'heading_id'     => 'sg-alarm-faq-heading',
		'heading_before' => __( 'Frequently asked ', 'site-blocks' ),
		'heading_accent' => __( 'questions', 'site-blocks' ),
		'id_prefix'      => 'sg-alarm-faq-',
		'columns_split'  => 3,
	)
);
