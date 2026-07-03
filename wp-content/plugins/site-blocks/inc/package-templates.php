<?php
/**
 * Archive and single templates for security packages.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin templates for security packages.
 *
 * @param string $template Current template path.
 * @return string
 */
function site_blocks_package_template_include( string $template ): string {
	if ( is_post_type_archive( 'security_package' ) ) {
		$archive = SITE_BLOCKS_DIR . 'templates/archive-security_package.php';
		if ( file_exists( $archive ) ) {
			return $archive;
		}
	}

	if ( is_singular( 'security_package' ) ) {
		$single = SITE_BLOCKS_DIR . 'templates/single-security_package.php';
		if ( file_exists( $single ) ) {
			return $single;
		}
	}

	if ( is_tax( 'package_type' ) ) {
		$taxonomy = SITE_BLOCKS_DIR . 'templates/taxonomy-package_type.php';
		if ( file_exists( $taxonomy ) ) {
			return $taxonomy;
		}
	}

	return $template;
}
add_filter( 'template_include', 'site_blocks_package_template_include', 99 );

/**
 * Configure Kadence layout for package pages.
 *
 * @param array<string, string> $layout Layout settings.
 * @return array<string, string>
 */
function site_blocks_package_kadence_layout( array $layout ): array {
	if ( is_post_type_archive( 'security_package' ) || is_singular( 'security_package' ) || is_tax( 'package_type' ) ) {
		$layout['layout']  = 'fullwidth';
		$layout['boxed']   = 'unboxed';
		$layout['title']   = 'hide';
		$layout['sidebar'] = 'disable';
	}
	return $layout;
}
add_filter( 'kadence_post_layout', 'site_blocks_package_kadence_layout' );

/**
 * Add body classes for package pages.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_package_body_class( array $classes ): array {
	if ( is_post_type_archive( 'security_package' ) || is_singular( 'security_package' ) || is_tax( 'package_type' ) ) {
		$classes[] = 'hallmark-packages';
		$classes[] = 'is-package-page';
	}
	return $classes;
}
add_filter( 'body_class', 'site_blocks_package_body_class' );

/**
 * Set Atelier theme scope on package pages.
 */
function site_blocks_package_html_data_theme(): void {
	if ( is_post_type_archive( 'security_package' ) || is_singular( 'security_package' ) || is_tax( 'package_type' ) ) {
		echo '<script>document.documentElement.setAttribute("data-theme","atelier");</script>' . "\n";
	}
}
add_action( 'wp_head', 'site_blocks_package_html_data_theme', 1 );
