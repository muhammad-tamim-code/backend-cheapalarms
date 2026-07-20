<?php
/**
 * Access Control, who, where, when.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/access-control-media.php';
require_once SITE_BLOCKS_DIR . 'inc/access-control-split.php';

site_blocks_render_access_control_split(
	array(
		'id'           => 'sg-access-control-www-heading',
		'class'        => 'sg-access-control-www',
		'reverse'      => true,
		'title_before' => __( 'Who gets in, where, and ', 'site-blocks' ),
		'title_accent' => __( 'when', 'site-blocks' ),
		'list'         => array(
			array(
				'title' => __( 'Who', 'site-blocks' ),
				'desc'  => __( 'Staff, contractors, visitors and cleaners, each with credentials matched to their role.', 'site-blocks' ),
			),
			array(
				'title' => __( 'Where', 'site-blocks' ),
				'desc'  => __( 'Front doors, server rooms, car parks, loading docks and perimeter gates, door by door.', 'site-blocks' ),
			),
			array(
				'title' => __( 'When', 'site-blocks' ),
				'desc'  => __( 'Business hours only, after-hours access, or one-off visitor windows, scheduled automatically.', 'site-blocks' ),
			),
		),
		'visual'       => static function (): void {
			site_blocks_access_control_image(
				'images/access-control/www.webp',
				__( 'Access control schedule and user permissions on a management dashboard', 'site-blocks' )
			);
		},
	)
);
