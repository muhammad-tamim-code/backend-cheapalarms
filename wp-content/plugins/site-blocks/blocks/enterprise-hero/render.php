<?php
/**
 * Enterprise hero block.
 *
 * Hub keeps Ajax-style V0. Safeguard Solutions uses V3 range deck.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/enterprise-config.php';
require_once SITE_BLOCKS_DIR . 'inc/enterprise-media.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-ajax-hero.php';
require_once SITE_BLOCKS_DIR . 'inc/hero-variants.php';

$page_key = site_blocks_get_enterprise_page_key();

if ( null === $page_key ) {
	return;
}

$config = site_blocks_enterprise_hero_config( $page_key );

if ( null === $config ) {
	return;
}

$hero_image = (string) $config['hero_image'];
$hero_alt   = (string) $config['hero_alt'];

if ( 'safeguard-solutions' === $page_key ) {
	$deck_cards = array();
	foreach ( array_slice( site_blocks_safeguard_solutions_spoke_teasers(), 0, 3 ) as $teaser ) {
		$deck_cards[] = array(
			'title' => (string) $teaser['title'],
			'desc'  => (string) $teaser['desc'],
			'url'   => home_url( '/contact/' ),
		);
	}

	site_blocks_render_hero_variant(
		'deck',
		array(
			'id'              => (string) $config['id'],
			'class'           => (string) $config['class'],
			'breadcrumb'      => $config['breadcrumb'] ?? array(),
			'badge'           => (string) $config['badge'],
			'title_before'    => (string) $config['title_before'],
			'title_accent'    => (string) $config['title_accent'],
			'title_after'     => (string) ( $config['title_after'] ?? '' ),
			'lead'            => (string) $config['lead'],
			'primary_label'   => (string) $config['primary_label'],
			'primary_url'     => (string) $config['primary_url'],
			'secondary_label' => (string) $config['secondary_label'],
			'secondary_url'   => (string) $config['secondary_url'],
			'cards'           => $deck_cards,
		)
	);
	return;
}

site_blocks_render_ajax_style_hero(
	array(
		'heading_id'       => (string) $config['id'],
		'section_class'    => (string) $config['class'],
		'eyebrow'          => (string) $config['badge'],
		'title_before'     => (string) $config['title_before'],
		'title_accent'     => (string) $config['title_accent'],
		'title_after'      => (string) ( $config['title_after'] ?? '' ),
		'lead'             => (string) $config['lead'],
		'primary_label'    => (string) $config['primary_label'],
		'primary_url'      => (string) $config['primary_url'],
		'secondary_label'  => (string) ( $config['secondary_label'] ?? site_blocks_enterprise_ctas()['secondary_label'] ),
		'secondary_url'    => (string) ( $config['secondary_url'] ?? site_blocks_enterprise_ctas()['secondary_url'] ),
		'trust_aria_label' => (string) ( $config['trust_aria_label'] ?? __( 'Enterprise security credentials', 'site-blocks' ) ),
		'trust_chips'      => $config['trust_chips'] ?? array(),
		'icon_renderer'    => 'site_blocks_enterprise_hero_icon',
		'hero_render'      => static function () use ( $hero_image, $hero_alt ): void {
			site_blocks_silo_image( 'enterprise', $hero_image, $hero_alt, 'sg-ajax-hero__img', 'eager' );
		},
	)
);
