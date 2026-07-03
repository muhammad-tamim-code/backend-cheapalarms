<?php
/**
 * Ajax calculator review embed (static HTML + WP page).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public URL for the standalone calculator HTML.
 */
function site_blocks_ajax_calculator_asset_url(): string {
	return site_blocks_asset_url( 'calculators/ajax-calculator/index.html' );
}

/**
 * Shortcode: full-width iframe embed for the Ajax system calculator.
 *
 * @return string
 */
function site_blocks_ajax_calculator_shortcode(): string {
	$url = site_blocks_ajax_calculator_asset_url();
	if ( function_exists( 'rest_url' ) ) {
		$url = add_query_arg( 'apiBase', rawurlencode( rest_url( 'ca/v1' ) ), $url );
	}

	return sprintf(
		'<div class="sg-ajax-calculator-embed"><iframe class="sg-ajax-calculator-frame" src="%s" title="%s" loading="eager"></iframe></div>',
		esc_url( $url ),
		esc_attr__( 'Design my Ajax system', 'site-blocks' )
	);
}
add_shortcode( 'sg_ajax_calculator', 'site_blocks_ajax_calculator_shortcode' );

/**
 * Enqueue embed layout styles on the calculator page.
 */
function site_blocks_ajax_calculator_enqueue(): void {
	if ( ! is_page( 'ajax-calculator' ) ) {
		return;
	}

	wp_enqueue_style(
		'site-blocks-ajax-calculator',
		SITE_BLOCKS_URL . 'assets/css/ajax-calculator-embed.css',
		array(),
		SITE_BLOCKS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'site_blocks_ajax_calculator_enqueue', 30 );

/**
 * Return block markup for the Ajax calculator page.
 *
 * @return string
 */
function site_blocks_get_ajax_calculator_page_content(): string {
	return '<!-- wp:shortcode -->[sg_ajax_calculator]<!-- /wp:shortcode -->';
}

/**
 * Create or update the published Ajax calculator page.
 */
function site_blocks_create_ajax_calculator_page(): void {
	$content = site_blocks_get_ajax_calculator_page_content();

	if ( '' === $content ) {
		return;
	}

	$existing = get_page_by_path( 'ajax-calculator' );

	$page_data = array(
		'post_title'   => __( 'Design my Ajax system', 'site-blocks' ),
		'post_name'    => 'ajax-calculator',
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
