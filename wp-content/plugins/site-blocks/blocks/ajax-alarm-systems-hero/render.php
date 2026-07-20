<?php
/**
 * Ajax Alarm Systems hero block render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-ajax-hero.php';

$config = site_blocks_default_ajax_alarm_hero_config();

if ( isset( $attributes['eyebrow'] ) ) {
	$config['eyebrow'] = (string) $attributes['eyebrow'];
}
if ( isset( $attributes['headline'] ) ) {
	$config['title'] = (string) $attributes['headline'];
}
if ( isset( $attributes['lead'] ) ) {
	$config['lead'] = (string) $attributes['lead'];
}
if ( isset( $attributes['primaryCtaLabel'] ) ) {
	$config['primary_label'] = (string) $attributes['primaryCtaLabel'];
}
if ( isset( $attributes['primaryCtaUrl'] ) ) {
	$config['primary_url'] = home_url( (string) $attributes['primaryCtaUrl'] );
}
if ( isset( $attributes['secondaryCtaLabel'] ) ) {
	$config['secondary_label'] = (string) $attributes['secondaryCtaLabel'];
}
if ( isset( $attributes['secondaryCtaUrl'] ) ) {
	$config['secondary_url'] = (string) $attributes['secondaryCtaUrl'];
}

site_blocks_render_ajax_style_hero( $config );
