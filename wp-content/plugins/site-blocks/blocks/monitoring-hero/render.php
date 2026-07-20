<?php
/**
 * Monitoring hero block.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/monitoring-config.php';
require_once SITE_BLOCKS_DIR . 'inc/monitoring-media.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-ajax-hero.php';

$page_key = site_blocks_get_monitoring_page_key();

if ( null === $page_key ) {
	return;
}

$config = site_blocks_monitoring_hero_config( $page_key );

if ( null === $config ) {
	return;
}

$hero_image = (string) $config['hero_image'];
$hero_alt   = (string) $config['hero_alt'];

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
		'secondary_label'  => (string) ( $config['secondary_label'] ?? site_blocks_monitoring_ctas()['secondary_label'] ),
		'secondary_url'    => (string) ( $config['secondary_url'] ?? site_blocks_monitoring_ctas()['secondary_url'] ),
		'trust_aria_label' => (string) ( $config['trust_aria_label'] ?? __( 'Why Safeguard monitoring', 'site-blocks' ) ),
		'trust_chips'      => $config['trust_chips'] ?? array(),
		'caption_title'    => (string) ( $config['caption_title'] ?? '' ),
		'caption_items'    => $config['caption_items'] ?? array(),
		'icon_renderer'    => 'site_blocks_monitoring_hero_icon',
		'hero_render'      => static function () use ( $hero_image, $hero_alt ): void {
			site_blocks_silo_image( 'monitoring', $hero_image, $hero_alt, 'sg-ajax-hero__img', 'eager' );
		},
	)
);
