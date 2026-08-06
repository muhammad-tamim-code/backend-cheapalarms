<?php
/**
 * Logo marquee block, reusable portfolio / partner strip.
 *
 * @package Site_Blocks
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-logo-marquee.php';

$attributes = isset( $attributes ) && is_array( $attributes ) ? $attributes : array();

$source = isset( $attributes['source'] ) && $attributes['source'] === 'partners' ? 'partners' : 'portfolio';
$variant = isset( $attributes['variant'] ) && $attributes['variant'] === 'light' ? 'light' : 'wash';

$heading_id = 'sg-logo-marquee-' . wp_unique_id();

site_blocks_render_logo_marquee(
	array(
		'title'        => isset( $attributes['title'] ) ? (string) $attributes['title'] : __( 'Clients Portfolio', 'site-blocks' ),
		'subtitle'     => isset( $attributes['subtitle'] ) ? (string) $attributes['subtitle'] : '',
		'source'       => $source,
		'variant'      => $variant,
		'show_heading' => true,
		'class'        => 'alignfull',
		'heading_id'   => $heading_id,
	)
);
