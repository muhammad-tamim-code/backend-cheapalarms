<?php
/**
 * Register blocks and patterns.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom blocks.
 */
function site_blocks_register_blocks(): void {
	$blocks = array(
		'contact-hero',
		'contact-info',
		'contact-form',
		'package-grid',
		'home-hero',
		'safeguard-homepage',
		'safeguard-header',
		'safeguard-footer',
		'alarm-systems-hero',
		'alarm-systems-services',
		'alarm-systems-why',
		'alarm-systems-ajax',
		'alarm-systems-steps',
		'alarm-systems-faq',
		'ajax-alarm-systems-hero',
		'ajax-alarm-systems-process',
		'ajax-alarm-systems-difference',
		'ajax-alarm-systems-products',
		'ajax-alarm-systems-monitoring',
		'ajax-alarm-systems-property-fit',
		'ajax-alarm-systems-compare',
		'ajax-alarm-systems-faq',
		'ajax-alarm-systems-quote-cta',
		'cctv-hero',
		'cctv-intro',
		'cctv-difference',
		'cctv-install',
		'cctv-photo-band',
		'cctv-segments',
		'cctv-layered',
		'cctv-ajax-promo',
		'cctv-portal',
		'cctv-trust',
		'cctv-faq',
		'cctv-quote-cta',
		'intercom-hero',
		'intercom-intro',
		'intercom-difference',
		'intercom-install',
		'intercom-segments',
		'intercom-layered',
		'intercom-portal',
		'intercom-trust',
		'intercom-faq',
		'intercom-quote-cta',
		'access-control-hero',
		'access-control-what',
		'access-control-www',
		'access-control-remote',
		'access-control-options',
		'access-control-keys',
		'access-control-integration',
		'access-control-install',
		'access-control-process',
		'access-control-gallery',
		'access-control-social-proof',
		'access-control-faq',
		'access-control-related',
		'access-control-quote-cta',
	);

	foreach ( $blocks as $block ) {
		register_block_type( SITE_BLOCKS_DIR . 'blocks/' . $block );
	}
}
add_action( 'init', 'site_blocks_register_blocks' );

/**
 * Register block pattern category.
 */
function site_blocks_register_pattern_category(): void {
	register_block_pattern_category(
		'site-pages',
		array(
			'label' => __( 'Site Pages', 'site-blocks' ),
		)
	);
}
add_action( 'init', 'site_blocks_register_pattern_category' );

/**
 * Register contact page pattern.
 */
function site_blocks_register_patterns(): void {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/contact-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return;
	}

	$pattern = include $pattern_file;

	if ( is_array( $pattern ) ) {
		register_block_pattern( 'site/contact-page', $pattern );
	}

	$home_pattern_file = SITE_BLOCKS_DIR . 'patterns/home-page.php';

	if ( file_exists( $home_pattern_file ) ) {
		$home_pattern = include $home_pattern_file;

		if ( is_array( $home_pattern ) ) {
			register_block_pattern( 'site/home-page', $home_pattern );
		}
	}

	$alarm_pattern_file = SITE_BLOCKS_DIR . 'patterns/alarm-systems-page.php';

	if ( file_exists( $alarm_pattern_file ) ) {
		$alarm_pattern = include $alarm_pattern_file;

		if ( is_array( $alarm_pattern ) ) {
			register_block_pattern( 'site/alarm-systems-page', $alarm_pattern );
		}
	}

	$ajax_landing_pattern_file = SITE_BLOCKS_DIR . 'patterns/ajax-alarm-systems-page.php';

	if ( file_exists( $ajax_landing_pattern_file ) ) {
		$ajax_landing_pattern = include $ajax_landing_pattern_file;

		if ( is_array( $ajax_landing_pattern ) ) {
			register_block_pattern( 'site/ajax-alarm-systems-page', $ajax_landing_pattern );
		}
	}

	$cctv_pattern_file = SITE_BLOCKS_DIR . 'patterns/cctv-page.php';

	if ( file_exists( $cctv_pattern_file ) ) {
		$cctv_pattern = include $cctv_pattern_file;

		if ( is_array( $cctv_pattern ) ) {
			register_block_pattern( 'site/cctv-page', $cctv_pattern );
		}
	}

	$intercom_pattern_file = SITE_BLOCKS_DIR . 'patterns/intercom-systems-page.php';

	if ( file_exists( $intercom_pattern_file ) ) {
		$intercom_pattern = include $intercom_pattern_file;

		if ( is_array( $intercom_pattern ) ) {
			register_block_pattern( 'site/intercom-systems-page', $intercom_pattern );
		}
	}

	$access_control_pattern_file = SITE_BLOCKS_DIR . 'patterns/access-control-page.php';

	if ( file_exists( $access_control_pattern_file ) ) {
		$access_control_pattern = include $access_control_pattern_file;

		if ( is_array( $access_control_pattern ) ) {
			register_block_pattern( 'site/access-control-page', $access_control_pattern );
		}
	}
}
add_action( 'init', 'site_blocks_register_patterns' );
