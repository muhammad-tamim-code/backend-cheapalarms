<?php
/**
 * Ajax Alarm Systems hero — Electronic Security product stage hero.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-stage-hero.php';

$eyebrow   = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : __( 'Ajax · Australia', 'site-blocks' );
$title     = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : __( 'Ajax alarm systems, professionally installed by Safeguard.', 'site-blocks' );
$lead      = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : __( 'We design, install and configure Ajax wireless alarm systems for homes, apartments and small businesses across Australia — specified around your property, not sold as a generic kit.', 'site-blocks' );
$cta_label = isset( $attributes['primaryCtaLabel'] ) ? (string) $attributes['primaryCtaLabel'] : __( 'Design my Ajax system', 'site-blocks' );
$cta_url   = isset( $attributes['primaryCtaUrl'] ) ? home_url( (string) $attributes['primaryCtaUrl'] ) : home_url( '/ajax-calculator/' );
$sec_label = isset( $attributes['secondaryCtaLabel'] ) ? (string) $attributes['secondaryCtaLabel'] : __( 'Call 1300 225 276', 'site-blocks' );
$sec_url   = isset( $attributes['secondaryCtaUrl'] ) ? (string) $attributes['secondaryCtaUrl'] : 'tel:1300225276';

site_blocks_render_stage_hero(
	array(
		'id'              => 'sg-ajax-hero-heading',
		'class'           => 'sg-ajax-alarm-systems-hero',
		'breadcrumb'      => array(
			array(
				'label' => __( 'Home', 'site-blocks' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Electronic Security', 'site-blocks' ),
				'url'   => home_url( '/electronic-security/' ),
			),
			array(
				'label'   => __( 'Ajax Alarm Systems', 'site-blocks' ),
				'current' => true,
			),
		),
		'eyebrow'         => $eyebrow,
		'title'           => $title,
		'lead'            => $lead,
		'primary_label'   => $cta_label,
		'primary_url'     => $cta_url,
		'secondary_label' => $sec_label,
		'secondary_url'   => $sec_url,
		'trust'           => array(
			array(
				'icon'  => 'wifi',
				'label' => __( 'Wireless design', 'site-blocks' ),
			),
			array(
				'icon'  => 'smartphone',
				'label' => __( 'App setup & handover', 'site-blocks' ),
			),
			array(
				'icon'  => 'shield',
				'label' => __( 'Monitoring options', 'site-blocks' ),
			),
		),
		'frame_image'     => 'images/ajax/ajax-hero-house.webp',
		'frame_alt'       => __( 'Ajax Hub and wireless security devices protecting a modern Australian home', 'site-blocks' ),
	)
);
