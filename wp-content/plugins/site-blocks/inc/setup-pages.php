<?php
/**
 * Create contact page on activation.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence per-page layout meta for Safeguard full-page templates.
 *
 * @param int $page_id Page ID.
 */
function site_blocks_apply_safeguard_page_meta( int $page_id ): void {
	update_post_meta( $page_id, '_kad_post_layout', 'fullwidth' );
	update_post_meta( $page_id, '_kad_post_content_style', 'unboxed' );
	update_post_meta( $page_id, '_kad_post_title', 'hide' );
	update_post_meta( $page_id, '_kad_post_header', true );
	update_post_meta( $page_id, '_kad_post_footer', true );
}

/**
 * Return pattern markup for the contact page.
 *
 * @return string
 */
function site_blocks_get_contact_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/contact-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the published contact page.
 */
function site_blocks_create_contact_page(): void {
	$content = site_blocks_get_contact_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'contact' );

	$page_data = array(
		'post_title'   => __( 'Contact', 'site-blocks' ),
		'post_name'    => 'contact',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );
}

/**
 * Return pattern markup for the homepage.
 *
 * @return string
 */
function site_blocks_get_home_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/home-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the homepage and set as front page.
 */
function site_blocks_create_home_page(): void {
	$content = site_blocks_get_home_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'home' );

	$page_data = array(
		'post_title'   => __( 'Home', 'site-blocks' ),
		'post_name'    => 'home',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $page_id );
}

/**
 * Return pattern markup for the Alarm Systems page.
 *
 * @return string
 */
function site_blocks_get_alarm_systems_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/alarm-systems-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the Alarm Systems service page.
 */
function site_blocks_create_alarm_systems_page(): void {
	$content = site_blocks_get_alarm_systems_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'alarm-systems' );

	$page_data = array(
		'post_title'   => __( 'Alarm Systems', 'site-blocks' ),
		'post_name'    => 'alarm-systems',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );
}

/**
 * Return pattern markup for the Ajax Alarm Systems landing page.
 *
 * @return string
 */
function site_blocks_get_ajax_alarm_systems_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/ajax-alarm-systems-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the Ajax Alarm Systems SEO landing page.
 */
function site_blocks_create_ajax_alarm_systems_page(): void {
	$content = site_blocks_get_ajax_alarm_systems_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'ajax-alarm-systems' );

	$page_data = array(
		'post_title'   => __( 'Ajax Alarm Systems', 'site-blocks' ),
		'post_name'    => 'ajax-alarm-systems',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );
}

/**
 * Return pattern markup for the CCTV category page.
 *
 * @return string
 */
function site_blocks_get_cctv_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/cctv-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the CCTV category page.
 */
function site_blocks_create_cctv_page(): void {
	$content = site_blocks_get_cctv_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'cctv-security-cameras' );

	$page_data = array(
		'post_title'   => __( 'CCTV & Security Cameras', 'site-blocks' ),
		'post_name'    => 'cctv-security-cameras',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );
}

/**
 * Return pattern markup for the Intercom Systems category page.
 *
 * @return string
 */
function site_blocks_get_intercom_systems_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/intercom-systems-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the Intercom Systems category page.
 */
function site_blocks_create_intercom_systems_page(): void {
	$content = site_blocks_get_intercom_systems_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'intercom-systems' );

	$page_data = array(
		'post_title'   => __( 'Intercom Systems', 'site-blocks' ),
		'post_name'    => 'intercom-systems',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );
}

/**
 * Redirect legacy /intercoms/ slug to /intercom-systems/.
 */
function site_blocks_create_intercoms_redirect_page(): void {
	$target = get_page_by_path( 'intercom-systems' );
	if ( ! $target instanceof WP_Post ) {
		return;
	}

	$existing = get_page_by_path( 'intercoms' );
	$redirect_markup = sprintf(
		'<!-- wp:html --><meta http-equiv="refresh" content="0;url=%s" /><script>window.location.replace("%s");</script><!-- /wp:html -->',
		esc_url( home_url( '/intercom-systems/' ) ),
		esc_url( home_url( '/intercom-systems/' ) )
	);

	$page_data = array(
		'post_title'   => __( 'Intercoms', 'site-blocks' ),
		'post_name'    => 'intercoms',
		'post_content' => $redirect_markup,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		wp_update_post( $page_data, true );
	} else {
		wp_insert_post( $page_data, true );
	}
}

/**
 * Return pattern markup for the Access Control category page.
 *
 * @return string
 */
function site_blocks_get_access_control_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/access-control-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update the Access Control category page.
 */
function site_blocks_create_access_control_page(): void {
	$content = site_blocks_get_access_control_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'access-control' );

	$page_data = array(
		'post_title'   => __( 'Access Control', 'site-blocks' ),
		'post_name'    => 'access-control',
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);

	if ( $existing instanceof WP_Post ) {
		$page_data['ID'] = $existing->ID;
		$page_id         = wp_update_post( $page_data, true );
	} else {
		$page_id = wp_insert_post( $page_data, true );
	}

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $page_id );
}

/**
 * Return pattern markup for the Physical Security hub page.
 */
function site_blocks_get_physical_security_hub_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/physical-security-hub-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Return pattern markup for a Physical Security spoke page.
 */
function site_blocks_get_physical_security_spoke_page_content( string $pattern_basename ): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/' . $pattern_basename . '.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update Physical Security hub + spoke pages.
 */
function site_blocks_create_physical_security_pages(): void {
	$hub_content = site_blocks_get_physical_security_hub_page_content();

	if ( '' === $hub_content ) {
		return;
	}

	$existing_hub = get_page_by_path( 'physical-security' );

	$hub_data = array(
		'post_title'   => __( 'Physical Security', 'site-blocks' ),
		'post_name'    => 'physical-security',
		'post_content' => $hub_content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
		'post_parent'  => 0,
	);

	if ( $existing_hub instanceof WP_Post ) {
		$hub_data['ID'] = $existing_hub->ID;
		$hub_id         = wp_update_post( $hub_data, true );
	} else {
		$hub_id = wp_insert_post( $hub_data, true );
	}

	if ( is_wp_error( $hub_id ) || ! $hub_id ) {
		return;
	}

	$hub_id = (int) $hub_id;
	site_blocks_apply_safeguard_page_meta( $hub_id );

	$spokes = array(
		array(
			'slug'    => 'static-guards',
			'title'   => __( 'Static Security Guards', 'site-blocks' ),
			'pattern' => 'physical-security-static-guards-page',
		),
		array(
			'slug'    => 'mobile-patrols',
			'title'   => __( 'Mobile Patrols', 'site-blocks' ),
			'pattern' => 'physical-security-mobile-patrols-page',
		),
	);

	foreach ( $spokes as $spoke ) {
		$content = site_blocks_get_physical_security_spoke_page_content( $spoke['pattern'] );
		if ( '' === $content ) {
			continue;
		}

		$path     = 'physical-security/' . $spoke['slug'];
		$existing = get_page_by_path( $path );

		$page_data = array(
			'post_title'   => $spoke['title'],
			'post_name'    => $spoke['slug'],
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
			'post_parent'  => $hub_id,
		);

		if ( $existing instanceof WP_Post ) {
			$page_data['ID'] = $existing->ID;
			$page_id         = wp_update_post( $page_data, true );
		} else {
			$page_id = wp_insert_post( $page_data, true );
		}

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			continue;
		}

		site_blocks_apply_safeguard_page_meta( (int) $page_id );
	}
}

/**
 * Return pattern markup for the Monitoring hub page.
 */
function site_blocks_get_monitoring_hub_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/monitoring-hub-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Return pattern markup for a Monitoring spoke page.
 */
function site_blocks_get_monitoring_spoke_page_content( string $pattern_basename ): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/' . $pattern_basename . '.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update Monitoring hub + spoke pages.
 */
function site_blocks_create_monitoring_pages(): void {
	$hub_content = site_blocks_get_monitoring_hub_page_content();

	if ( '' === $hub_content ) {
		return;
	}

	$existing_hub = get_page_by_path( 'monitoring' );

	$hub_data = array(
		'post_title'   => __( 'Monitoring & Response', 'site-blocks' ),
		'post_name'    => 'monitoring',
		'post_content' => $hub_content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
		'post_parent'  => 0,
	);

	if ( $existing_hub instanceof WP_Post ) {
		$hub_data['ID'] = $existing_hub->ID;
		$hub_id         = wp_update_post( $hub_data, true );
	} else {
		$hub_id = wp_insert_post( $hub_data, true );
	}

	if ( is_wp_error( $hub_id ) || ! $hub_id ) {
		return;
	}

	$hub_id = (int) $hub_id;
	site_blocks_apply_safeguard_page_meta( $hub_id );

	$spokes = array(
		array(
			'slug'    => 'back-to-base',
			'title'   => __( 'Back-to-Base Monitoring', 'site-blocks' ),
			'pattern' => 'monitoring-back-to-base-page',
		),
		array(
			'slug'    => 'virtual-patrol',
			'title'   => __( 'Virtual Patrol', 'site-blocks' ),
			'pattern' => 'monitoring-virtual-patrol-page',
		),
		array(
			'slug'    => 'solar-cameras-monitoring',
			'title'   => __( 'Solar Cameras with Monitoring', 'site-blocks' ),
			'pattern' => 'monitoring-solar-cameras-monitoring-page',
		),
	);

	foreach ( $spokes as $spoke ) {
		$content = site_blocks_get_monitoring_spoke_page_content( $spoke['pattern'] );
		if ( '' === $content ) {
			continue;
		}

		$path     = 'monitoring/' . $spoke['slug'];
		$existing = get_page_by_path( $path );

		$page_data = array(
			'post_title'   => $spoke['title'],
			'post_name'    => $spoke['slug'],
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
			'post_parent'  => $hub_id,
		);

		if ( $existing instanceof WP_Post ) {
			$page_data['ID'] = $existing->ID;
			$page_id         = wp_update_post( $page_data, true );
		} else {
			$page_id = wp_insert_post( $page_data, true );
		}

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			continue;
		}

		site_blocks_apply_safeguard_page_meta( (int) $page_id );
	}
}
