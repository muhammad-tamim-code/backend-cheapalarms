<?php
/**
 * Access Control — complete control and remote management.
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
		'id'           => 'sg-access-control-remote-heading',
		'class'        => 'sg-access-control-remote',
		'title_before' => __( 'Complete control, from ', 'site-blocks' ),
		'title_accent' => __( 'anywhere', 'site-blocks' ),
		'paragraphs'   => array(
			__( 'Add a new employee, revoke a lost fob, or extend a contractor\'s access — without visiting the site or calling a locksmith.', 'site-blocks' ),
			__( 'Cloud-managed systems give you a live audit trail of every entry, plus alerts when doors are forced or held open.', 'site-blocks' ),
			__( 'Multi-site businesses manage every location from one dashboard — consistent rules, one support team.', 'site-blocks' ),
		),
		'visual'       => static function (): void {
			site_blocks_access_control_image(
				'images/access-control/remote.webp',
				__( 'Mobile app showing remote door unlock and access event history', 'site-blocks' )
			);
		},
	)
);
