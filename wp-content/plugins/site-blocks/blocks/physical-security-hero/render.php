<?php
/**
 * Physical Security hero block.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/physical-security-config.php';
require_once SITE_BLOCKS_DIR . 'inc/physical-security-media.php';
require_once SITE_BLOCKS_DIR . 'inc/pillar-hero.php';

$page_key = site_blocks_get_physical_security_page_key();

if ( null === $page_key ) {
	return;
}

$config = site_blocks_physical_security_hero_config( $page_key );

if ( null === $config ) {
	return;
}

$hero_image = (string) $config['hero_image'];
$hero_alt   = (string) $config['hero_alt'];

site_blocks_render_pillar_hero(
	array(
		'id'              => (string) $config['id'],
		'class'           => (string) $config['class'],
		'breadcrumb'      => $config['breadcrumb'],
		'badge'           => (string) $config['badge'],
		'title_before'    => (string) $config['title_before'],
		'title_accent'    => (string) $config['title_accent'],
		'title_after'     => (string) $config['title_after'],
		'lead'            => (string) $config['lead'],
		'primary_label'   => (string) $config['primary_label'],
		'primary_url'     => (string) $config['primary_url'],
		'secondary_label' => (string) $config['secondary_label'],
		'secondary_url'   => (string) $config['secondary_url'],
		'visual'          => static function () use ( $hero_image, $hero_alt ): void {
			site_blocks_physical_security_hero_image( $hero_image, $hero_alt );
		},
	)
);

$trust_strip = $config['trust_strip'] ?? array();

if ( $trust_strip !== array() ) {
	site_blocks_render_trust_strip(
		array(
			'items'         => $trust_strip,
			'section_class' => 'sg-ps-trust-strip',
		)
	);
}
