<?php
/**
 * Access Control — what is access control.
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
		'id'            => 'sg-access-control-what-heading',
		'class'         => 'sg-access-control-what',
		'title_before'  => __( 'What is ', 'site-blocks' ),
		'title_accent'  => __( 'access control', 'site-blocks' ),
		'title_after'   => __( '?', 'site-blocks' ),
		'intro'         => __( 'Electronic access replaces physical keys with credentials you can issue, schedule and revoke.', 'site-blocks' ),
		'paragraphs'    => array(
			__( 'Instead of copying keys every time someone joins or leaves, you manage who can open which doors — and when — from one system.', 'site-blocks' ),
			__( 'Every entry is logged, so you know who came in and when. Lost a card? Disable it in seconds without rekeying the building.', 'site-blocks' ),
		),
		'visual'        => static function (): void {
			site_blocks_access_control_image(
				'images/access-control/what.webp',
				__( 'Access control card reader on a commercial door in Sydney', 'site-blocks' )
			);
		},
	)
);
