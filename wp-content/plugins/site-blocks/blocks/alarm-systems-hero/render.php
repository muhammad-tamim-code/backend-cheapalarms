<?php
/**
 * Alarm Systems hero block render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/pillar-hero.php';

$eyebrow   = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : __( 'Alarm Systems · Sydney', 'site-blocks' );
$cta_label = isset( $attributes['primaryCtaLabel'] ) ? (string) $attributes['primaryCtaLabel'] : __( 'Start My Quote', 'site-blocks' );
$cta_url   = isset( $attributes['primaryCtaUrl'] ) ? (string) $attributes['primaryCtaUrl'] : '/get-an-instant-quote/';
$sec_label = isset( $attributes['secondaryCtaLabel'] ) ? (string) $attributes['secondaryCtaLabel'] : __( 'Help Me Choose', 'site-blocks' );
$sec_url   = isset( $attributes['secondaryCtaUrl'] ) ? (string) $attributes['secondaryCtaUrl'] : '/design-my-solution/';

$hero_img = site_blocks_asset_url( 'images/alarm/hero.webp' );
$hero_alt = __( 'Ajax alarm hardware with smartphone app and Sydney home at dusk', 'site-blocks' );

site_blocks_render_pillar_hero(
	array(
		'id'              => 'sg-alarm-hero-heading',
		'class'           => 'sg-alarm-systems-hero',
		'breadcrumb'      => array(
			array(
				'label' => __( 'Home', 'site-blocks' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Services', 'site-blocks' ),
			),
			array(
				'label'   => __( 'Alarm Systems', 'site-blocks' ),
				'current' => true,
			),
		),
		'badge'           => $eyebrow,
		'title_before'    => __( 'Alarm systems that ', 'site-blocks' ),
		'title_accent'    => __( 'earn their keep', 'site-blocks' ),
		'lead'            => __( 'Wireless and smart alarms designed around your property — not an off-the-shelf kit. Start your quote online, reviewed by our technicians.', 'site-blocks' ),
		'primary_label'   => $cta_label,
		'primary_url'     => home_url( $cta_url ),
		'secondary_label' => $sec_label,
		'secondary_url'   => home_url( $sec_url ),
		'footnote'        => __( 'Licensed installers · ASIAL member · Homes & commercial', 'site-blocks' ),
		'visual'          => static function () use ( $hero_img, $hero_alt ): void {
			printf(
				'<img class="sg-hero__pillar-img" src="%s" alt="%s" width="1600" height="900" loading="eager" decoding="async" />',
				esc_url( $hero_img ),
				esc_attr( $hero_alt )
			);
		},
	)
);
