<?php
/**
 * Contact page FAQ.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-faq.php';

site_blocks_render_faq_section(
	site_blocks_get_safeguard_faq_items(),
	array(
		'heading_id'     => 'sg-contact-faq-heading',
		'heading_before' => __( 'Common ', 'site-blocks' ),
		'heading_accent' => __( 'questions', 'site-blocks' ),
		'id_prefix'      => 'sg-contact-faq-',
		'columns_split'  => 3,
		'section_class'  => 'sg-contact-faq',
	)
);
