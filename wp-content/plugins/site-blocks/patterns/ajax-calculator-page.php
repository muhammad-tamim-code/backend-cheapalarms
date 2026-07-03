<?php
/**
 * Ajax calculator page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Ajax Calculator Page', 'site-blocks' ),
	'description' => __( 'Full-width embed of the Ajax system design calculator.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'ajax', 'calculator', 'quote' ),
	'content'     => '<!-- wp:shortcode -->[sg_ajax_calculator]<!-- /wp:shortcode -->',
);
