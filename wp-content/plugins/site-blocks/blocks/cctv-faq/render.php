<?php
/**
 * CCTV FAQ block.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-faq.php';

site_blocks_render_faq_section(
	site_blocks_get_cctv_faq_items(),
	array(
		'heading_id'     => 'sg-cctv-faq-heading',
		'heading_before' => __( 'Questions, ', 'site-blocks' ),
		'heading_accent' => __( 'answered', 'site-blocks' ),
		'id_prefix'      => 'sg-cctv-faq-',
		'section_class'  => 'sg-cctv-faq',
		'columns_split'  => 3,
	)
);
