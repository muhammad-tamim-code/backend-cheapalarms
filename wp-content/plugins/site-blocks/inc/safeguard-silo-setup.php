<?php
/**
 * Unified Safeguard page asset enqueue and body classes.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Google Fonts for Safeguard service pages.
 *
 * @param string $handle Unique style handle prefix.
 */
function site_blocks_enqueue_safeguard_fonts( string $handle ): void {
	wp_enqueue_style(
		$handle,
		'https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&family=Montserrat:wght@500;600;700&display=swap',
		array(),
		null
	);
}

/**
 * Enqueue safeguard-home.js once.
 */
function site_blocks_enqueue_safeguard_home_script(): void {
	wp_enqueue_script(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/js/safeguard-home.js',
		array(),
		SITE_BLOCKS_VERSION,
		true
	);
}

/**
 * Enqueue a CSS file in the Safeguard stack.
 *
 * @param string $handle   Style handle.
 * @param string $file     Path under assets/css/.
 * @param string $dep      Dependency handle.
 */
function site_blocks_enqueue_safeguard_style( string $handle, string $file, string $dep ): string {
	wp_enqueue_style(
		$handle,
		SITE_BLOCKS_URL . 'assets/css/' . $file,
		array( $dep ),
		SITE_BLOCKS_VERSION
	);

	return $handle;
}

/**
 * Signature icon pack (navy circle + white glyph) — loads after Safeguard stack.
 */
function site_blocks_enqueue_signature_icons(): void {
	if ( ! wp_style_is( 'safeguard-home', 'enqueued' ) ) {
		return;
	}

	$deps = array( 'safeguard-home' );
	foreach ( array(
		'safeguard-alarm-systems',
		'safeguard-cctv',
		'safeguard-access-control',
		'safeguard-related-services',
		'safeguard-scenario-grid',
		'safeguard-hub-services',
		'safeguard-ajax-hero',
		'safeguard-ajax-alarm-systems',
		'safeguard-enterprise',
		'safeguard-contact',
		'safeguard-monitoring',
		'safeguard-physical-security',
		'safeguard-process-flow',
	) as $maybe ) {
		if ( wp_style_is( $maybe, 'enqueued' ) ) {
			$deps[] = $maybe;
		}
	}

	wp_enqueue_style(
		'safeguard-signature-icons',
		SITE_BLOCKS_URL . 'assets/css/safeguard-signature-icons.css',
		$deps,
		SITE_BLOCKS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_signature_icons', 45 );

/**
 * Enqueue skeleton process flow styles after base Safeguard stack.
 *
 * @param string $dep Parent style handle.
 */
function site_blocks_enqueue_process_flow_styles( string $dep ): void {
	site_blocks_enqueue_safeguard_style( 'safeguard-process-flow', 'safeguard-process-flow.css', $dep );
}

/**
 * Enqueue shared Safeguard page assets based on current page.
 */
function site_blocks_enqueue_safeguard_silo_assets(): void {
	if ( function_exists( 'site_blocks_is_ajax_alarm_systems_page' ) && site_blocks_is_ajax_alarm_systems_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-ajax-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-ajax-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-ajax-quote-cta', 'safeguard-ajax-quote-cta.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-ajax-alarm-systems', 'ajax-alarm-systems.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_ajax_calculator_page' ) && site_blocks_is_ajax_calculator_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-ajax-calculator-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-ajax-calculator-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-ajax-hero', 'safeguard-ajax-hero.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-ajax-quote-cta', 'safeguard-ajax-quote-cta.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-ajax-alarm-systems', 'ajax-alarm-systems.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_alarm_systems_page' ) && site_blocks_is_alarm_systems_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-alarm-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-alarm-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_cctv_page' ) && site_blocks_is_cctv_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-cctv-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-cctv-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_intercom_page' ) && site_blocks_is_intercom_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-intercom-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-intercom-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-intercom', 'intercom.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_access_control_page' ) && site_blocks_is_access_control_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-access-control-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-access-control-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-access-control', 'access-control.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_monitoring_page' ) && site_blocks_is_monitoring_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-monitoring-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-monitoring-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-access-control', 'access-control.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-scenario-grid', 'safeguard-scenario-grid.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hub-services', 'safeguard-hub-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-ajax-hero', 'safeguard-ajax-hero.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-monitoring', 'monitoring.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_physical_security_page' ) && site_blocks_is_physical_security_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-physical-security-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-physical-security-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-access-control', 'access-control.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-scenario-grid', 'safeguard-scenario-grid.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hub-services', 'safeguard-hub-services.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-physical-security', 'physical-security.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_electronic_security_page' ) && site_blocks_is_electronic_security_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-electronic-security-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-electronic-security-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-access-control', 'access-control.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hub-services', 'safeguard-hub-services.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-electronic-security', 'electronic-security.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_manpower_page' ) && site_blocks_is_manpower_page() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-manpower-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-manpower-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-access-control', 'access-control.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hub-services', 'safeguard-hub-services.css', $dep );
		site_blocks_enqueue_safeguard_style( 'safeguard-manpower', 'manpower.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
		return;
	}

	if ( function_exists( 'site_blocks_is_enterprise_context' ) && site_blocks_is_enterprise_context() ) {
		site_blocks_enqueue_safeguard_fonts( 'safeguard-enterprise-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-enterprise-fonts' );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hero-variants', 'safeguard-hero-variants.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-alarm-systems', 'alarm-systems.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-cctv', 'cctv.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-access-control', 'access-control.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-related-services', 'safeguard-related-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-scenario-grid', 'safeguard-scenario-grid.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-hub-services', 'safeguard-hub-services.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-ajax-hero', 'safeguard-ajax-hero.css', $dep );
		$dep = site_blocks_enqueue_safeguard_style( 'safeguard-enterprise', 'enterprise.css', $dep );
		site_blocks_enqueue_process_flow_styles( $dep );
		site_blocks_enqueue_safeguard_home_script();
	}
}
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_safeguard_silo_assets', 30 );

/**
 * Unified body class injection for Safeguard service pages.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_safeguard_silo_body_class( array $classes ): array {
	if ( function_exists( 'site_blocks_is_monitoring_page' ) && site_blocks_is_monitoring_page() ) {
		$classes[] = 'safeguard-monitoring-page';
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_physical_security_page' ) && site_blocks_is_physical_security_page() ) {
		$classes[] = 'safeguard-physical-security-page';
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_electronic_security_page' ) && site_blocks_is_electronic_security_page() ) {
		$classes[] = 'safeguard-electronic-security-page';
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_manpower_page' ) && site_blocks_is_manpower_page() ) {
		$classes[] = 'safeguard-manpower-page';
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_access_control_page' ) && site_blocks_is_access_control_page() ) {
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_intercom_page' ) && site_blocks_is_intercom_page() ) {
		$classes[] = 'safeguard-intercom-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_cctv_page' ) && site_blocks_is_cctv_page() ) {
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_alarm_systems_page' ) && site_blocks_is_alarm_systems_page() ) {
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	if ( function_exists( 'site_blocks_is_ajax_calculator_page' ) && site_blocks_is_ajax_calculator_page() ) {
		$classes[] = 'safeguard-ajax-page';
		$classes[] = 'safeguard-homepage';
		$classes[] = 'sg-ajax-calculator-page';
	}

	if ( function_exists( 'site_blocks_is_enterprise_context' ) && site_blocks_is_enterprise_context() ) {
		$classes[] = 'safeguard-enterprise-page';
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}

	return $classes;
}
add_filter( 'body_class', 'site_blocks_safeguard_silo_body_class' );
