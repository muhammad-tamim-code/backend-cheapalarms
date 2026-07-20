<?php
/**
 * Enterprise Insight single template and layout hooks.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/enterprise-config.php';

/**
 * Load plugin template for enterprise insight singles.
 *
 * @param string $template Current template path.
 * @return string
 */
function site_blocks_enterprise_insight_template_include( string $template ): string {
	if ( is_singular( 'enterprise_insight' ) ) {
		$single = SITE_BLOCKS_DIR . 'templates/single-enterprise_insight.php';
		if ( file_exists( $single ) ) {
			return $single;
		}
	}

	return $template;
}
add_filter( 'template_include', 'site_blocks_enterprise_insight_template_include', 99 );

/**
 * Configure Kadence layout for enterprise insight posts.
 *
 * @param array<string, string> $layout Layout settings.
 * @return array<string, string>
 */
function site_blocks_enterprise_insight_kadence_layout( array $layout ): array {
	if ( is_singular( 'enterprise_insight' ) ) {
		$layout['layout']  = 'fullwidth';
		$layout['boxed']   = 'unboxed';
		$layout['title']   = 'hide';
		$layout['sidebar'] = 'disable';
		$layout['header']  = 'disable';
		$layout['footer']  = 'disable';
	}

	return $layout;
}
add_filter( 'kadence_post_layout', 'site_blocks_enterprise_insight_kadence_layout' );

/**
 * Document title for insight singles.
 */
function site_blocks_enterprise_insight_document_title_filter( string $title ): string {
	if ( ! is_singular( 'enterprise_insight' ) ) {
		return $title;
	}

	$custom = get_the_title() . ' | Safeguard Security';

	return $custom;
}
add_filter( 'pre_get_document_title', 'site_blocks_enterprise_insight_document_title_filter', 20 );

/**
 * Meta description for insight singles.
 */
function site_blocks_enterprise_insight_output_meta_description(): void {
	if ( ! is_singular( 'enterprise_insight' ) ) {
		return;
	}

	$description = get_the_excerpt();
	if ( '' === $description ) {
		$description = wp_trim_words( wp_strip_all_tags( get_the_content() ), 28, '…' );
	}

	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'site_blocks_enterprise_insight_output_meta_description', 2 );

/**
 * BlogPosting schema for insight singles.
 */
function site_blocks_enterprise_insight_schema(): void {
	if ( ! is_singular( 'enterprise_insight' ) ) {
		return;
	}

	$schema = array(
		array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title(),
			'description'      => get_the_excerpt(),
			'author'           => array(
				'@type' => 'Organization',
				'name'  => 'Safeguard Security Services',
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => 'Safeguard Security Services',
			),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'mainEntityOfPage' => get_permalink(),
		),
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Home',
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => 'Enterprise Solutions',
					'item'     => home_url( '/enterprise-solutions/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => get_the_title(),
					'item'     => get_permalink(),
				),
			),
		),
	);

	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	echo '</script>' . "\n";
}
add_action( 'wp_head', 'site_blocks_enterprise_insight_schema', 5 );

/**
 * Whether enterprise insight assets should load.
 */
function site_blocks_is_enterprise_context(): bool {
	return site_blocks_is_enterprise_page() || is_singular( 'enterprise_insight' );
}
