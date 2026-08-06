<?php
/**
 * Access Control, credential options grid (diagonal media cards + CTA strip).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-sections.php';

site_blocks_render_photo_options_grid(
	array(
		'heading_id'    => 'sg-access-control-options-heading',
		'section_class' => 'sg-access-control-options',
		'band'          => 'white',
		'eyebrow'       => __( 'Access Control', 'site-blocks' ),
		'title_before'  => __( 'Access options for every ', 'site-blocks' ),
		'title_accent'  => __( 'entry point', 'site-blocks' ),
		'intro'         => __( 'One system. Multiple ways in. Match credentials to how each door is used.', 'site-blocks' ),
		'items'         => array(
			array(
				'title' => __( 'Card & fob', 'site-blocks' ),
				'desc'  => __( 'Reliable tap-and-go entry for staff and regular visitors.', 'site-blocks' ),
				'best'  => __( 'offices, warehouses, strata common areas', 'site-blocks' ),
				'image' => 'images/access-control/option-01.webp',
				'alt'   => __( 'Hand holding an access card against a door reader', 'site-blocks' ),
				'icon'  => 'id-card',
			),
			array(
				'title' => __( 'PIN codes', 'site-blocks' ),
				'desc'  => __( 'Keypad entry without issuing physical credentials.', 'site-blocks' ),
				'best'  => __( 'after-hours staff, shared service doors', 'site-blocks' ),
				'image' => 'images/access-control/option-02.webp',
				'alt'   => __( 'Numeric access control keypad on a wall', 'site-blocks' ),
				'icon'  => 'calculator',
			),
			array(
				'title' => __( 'Mobile credentials', 'site-blocks' ),
				'desc'  => __( 'Unlock doors from a smartphone, no card to lose or replace.', 'site-blocks' ),
				'best'  => __( 'agile teams, hot-desking, visitor passes', 'site-blocks' ),
				'image' => 'images/access-control/option-03.webp',
				'alt'   => __( 'Smartphone used as a mobile access credential', 'site-blocks' ),
				'icon'  => 'smartphone',
			),
			array(
				'title' => __( 'Biometric', 'site-blocks' ),
				'desc'  => __( 'Fingerprint or facial recognition for high-security zones.', 'site-blocks' ),
				'best'  => __( 'server rooms, pharmacies, restricted areas', 'site-blocks' ),
				'image' => 'images/access-control/option-04.webp',
				'alt'   => __( 'Fingerprint biometric reader for restricted access', 'site-blocks' ),
				'icon'  => 'fingerprint',
			),
			array(
				'title' => __( 'Intercom entry', 'site-blocks' ),
				'desc'  => __( 'Verify visitors on video or audio before releasing the door.', 'site-blocks' ),
				'best'  => __( 'reception, apartment lobbies, after-hours deliveries', 'site-blocks' ),
				'image' => 'images/access-control/option-05.webp',
				'alt'   => __( 'Video intercom station at a building entrance', 'site-blocks' ),
				'icon'  => 'video',
			),
			array(
				'title' => __( 'Gate & perimeter', 'site-blocks' ),
				'desc'  => __( 'Readers on car park gates, boom barriers and external doors.', 'site-blocks' ),
				'best'  => __( 'industrial sites, strata car parks, loading bays', 'site-blocks' ),
				'image' => 'images/access-control/option-06.webp',
				'alt'   => __( 'Vehicle at a boom barrier with access control', 'site-blocks' ),
				'icon'  => 'car',
			),
		),
		'cta'           => array(
			'title'         => __( 'One system. Total control.', 'site-blocks' ),
			'text'          => __( 'Manage every door, user and access rule from a single platform—secure, scalable and easy to manage.', 'site-blocks' ),
			'button_label'  => __( 'Get a Quote', 'site-blocks' ),
			'button_url'    => home_url( '/contact/' ),
			'icon'          => 'shield-check',
		),
	)
);
