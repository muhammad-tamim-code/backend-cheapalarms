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
		'alarm-systems-portal',
		'alarm-systems-trust',
		'alarm-systems-related',
		'alarm-systems-quote-cta',
		'contact-portal',
		'contact-related',
		'contact-faq',
		'contact-quote-cta',
		'ajax-calculator-hero',
		'ajax-calculator-embed',
		'ajax-calculator-process',
		'ajax-calculator-portal',
		'ajax-calculator-faq',
		'ajax-calculator-quote-cta',
		'ajax-alarm-systems-hero',
		'ajax-alarm-systems-process',
		'ajax-alarm-systems-difference',
		'ajax-alarm-systems-products',
		'ajax-alarm-systems-monitoring',
		'ajax-alarm-systems-property-fit',
		'ajax-alarm-systems-compare',
		'ajax-alarm-systems-faq',
		'ajax-alarm-systems-quote-cta',
		'ajax-alarm-systems-portal',
		'ajax-alarm-systems-related',
		'cctv-hero',
		'cctv-intro',
		'cctv-spotlight',
		'cctv-difference',
		'cctv-install',
		'cctv-photo-band',
		'cctv-segments',
		'cctv-layered',
		'cctv-ajax-promo',
		'cctv-portal',
		'cctv-trust',
		'cctv-faq',
		'cctv-related',
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
		'intercom-related',
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
		'access-control-portal',
		'access-control-quote-cta',
		'logo-marquee',
		'physical-security-hero',
		'physical-security-section',
		'electronic-security-hero',
		'electronic-security-section',
		'manpower-hero',
		'manpower-section',
		'monitoring-hero',
		'monitoring-section',
		'enterprise-hero',
		'enterprise-section',
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

	$physical_hub_pattern_file = SITE_BLOCKS_DIR . 'patterns/physical-security-hub-page.php';
	if ( file_exists( $physical_hub_pattern_file ) ) {
		$physical_hub_pattern = include $physical_hub_pattern_file;
		if ( is_array( $physical_hub_pattern ) ) {
			register_block_pattern( 'site/physical-security-hub-page', $physical_hub_pattern );
		}
	}

	$physical_static_pattern_file = SITE_BLOCKS_DIR . 'patterns/physical-security-static-guards-page.php';
	if ( file_exists( $physical_static_pattern_file ) ) {
		$physical_static_pattern = include $physical_static_pattern_file;
		if ( is_array( $physical_static_pattern ) ) {
			register_block_pattern( 'site/physical-security-static-guards-page', $physical_static_pattern );
		}
	}

	$physical_mobile_pattern_file = SITE_BLOCKS_DIR . 'patterns/physical-security-mobile-patrols-page.php';
	if ( file_exists( $physical_mobile_pattern_file ) ) {
		$physical_mobile_pattern = include $physical_mobile_pattern_file;
		if ( is_array( $physical_mobile_pattern ) ) {
			register_block_pattern( 'site/physical-security-mobile-patrols-page', $physical_mobile_pattern );
		}
	}

	$electronic_hub_pattern_file = SITE_BLOCKS_DIR . 'patterns/electronic-security-hub-page.php';
	if ( file_exists( $electronic_hub_pattern_file ) ) {
		$electronic_hub_pattern = include $electronic_hub_pattern_file;
		if ( is_array( $electronic_hub_pattern ) ) {
			register_block_pattern( 'site/electronic-security-hub-page', $electronic_hub_pattern );
		}
	}

	$manpower_hub_pattern_file = SITE_BLOCKS_DIR . 'patterns/manpower-hub-page.php';
	if ( file_exists( $manpower_hub_pattern_file ) ) {
		$manpower_hub_pattern = include $manpower_hub_pattern_file;
		if ( is_array( $manpower_hub_pattern ) ) {
			register_block_pattern( 'site/manpower-hub-page', $manpower_hub_pattern );
		}
	}

	$monitoring_hub_pattern_file = SITE_BLOCKS_DIR . 'patterns/monitoring-hub-page.php';
	if ( file_exists( $monitoring_hub_pattern_file ) ) {
		$monitoring_hub_pattern = include $monitoring_hub_pattern_file;
		if ( is_array( $monitoring_hub_pattern ) ) {
			register_block_pattern( 'site/monitoring-hub-page', $monitoring_hub_pattern );
		}
	}

	$monitoring_btb_pattern_file = SITE_BLOCKS_DIR . 'patterns/monitoring-back-to-base-page.php';
	if ( file_exists( $monitoring_btb_pattern_file ) ) {
		$monitoring_btb_pattern = include $monitoring_btb_pattern_file;
		if ( is_array( $monitoring_btb_pattern ) ) {
			register_block_pattern( 'site/monitoring-back-to-base-page', $monitoring_btb_pattern );
		}
	}

	$monitoring_vp_pattern_file = SITE_BLOCKS_DIR . 'patterns/monitoring-virtual-patrol-page.php';
	if ( file_exists( $monitoring_vp_pattern_file ) ) {
		$monitoring_vp_pattern = include $monitoring_vp_pattern_file;
		if ( is_array( $monitoring_vp_pattern ) ) {
			register_block_pattern( 'site/monitoring-virtual-patrol-page', $monitoring_vp_pattern );
		}
	}

	$monitoring_solar_pattern_file = SITE_BLOCKS_DIR . 'patterns/monitoring-solar-cameras-monitoring-page.php';
	if ( file_exists( $monitoring_solar_pattern_file ) ) {
		$monitoring_solar_pattern = include $monitoring_solar_pattern_file;
		if ( is_array( $monitoring_solar_pattern ) ) {
			register_block_pattern( 'site/monitoring-solar-cameras-monitoring-page', $monitoring_solar_pattern );
		}
	}

	$enterprise_hub_pattern_file = SITE_BLOCKS_DIR . 'patterns/enterprise-hub-page.php';
	if ( file_exists( $enterprise_hub_pattern_file ) ) {
		$enterprise_hub_pattern = include $enterprise_hub_pattern_file;
		if ( is_array( $enterprise_hub_pattern ) ) {
			register_block_pattern( 'site/enterprise-hub-page', $enterprise_hub_pattern );
		}
	}

	$safeguard_solutions_pattern_file = SITE_BLOCKS_DIR . 'patterns/safeguard-solutions-page.php';
	if ( file_exists( $safeguard_solutions_pattern_file ) ) {
		$safeguard_solutions_pattern = include $safeguard_solutions_pattern_file;
		if ( is_array( $safeguard_solutions_pattern ) ) {
			register_block_pattern( 'site/safeguard-solutions-page', $safeguard_solutions_pattern );
		}
	}

	$ajax_calculator_pattern_file = SITE_BLOCKS_DIR . 'patterns/ajax-calculator-page.php';
	if ( file_exists( $ajax_calculator_pattern_file ) ) {
		$ajax_calculator_pattern = include $ajax_calculator_pattern_file;
		if ( is_array( $ajax_calculator_pattern ) ) {
			register_block_pattern( 'site/ajax-calculator-page', $ajax_calculator_pattern );
		}
	}
}
add_action( 'init', 'site_blocks_register_patterns' );
