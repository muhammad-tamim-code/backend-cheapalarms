<?php
/**
 * Access Control FAQ block.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/access-control-faq.php';

site_blocks_render_faq_section(
	site_blocks_get_access_control_faq_items(),
	array(
		'heading_id'     => 'sg-access-control-faq-heading',
		'heading_before' => __( 'Questions, ', 'site-blocks' ),
		'heading_accent' => __( 'answered', 'site-blocks' ),
		'id_prefix'      => 'sg-access-control-faq-',
		'section_class'  => 'sg-cctv-faq sg-access-control-faq',
		'columns_split'  => 3,
	)
);
