<?php
/**
 * Ajax Alarm Systems FAQ block render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-faq.php';

site_blocks_render_faq_section(
	site_blocks_get_ajax_alarm_faq_items(),
	array(
		'heading_id'     => 'sg-ajax-faq-heading',
		'heading_before' => __( 'Ajax alarm system ', 'site-blocks' ),
		'heading_accent' => __( 'FAQs.', 'site-blocks' ),
		'id_prefix'      => 'sg-ajax-faq-',
	)
);
