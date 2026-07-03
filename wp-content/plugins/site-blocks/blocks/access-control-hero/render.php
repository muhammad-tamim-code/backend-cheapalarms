<?php
/**
 * Access Control hero block.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/access-control-media.php';
require_once SITE_BLOCKS_DIR . 'inc/pillar-hero.php';

site_blocks_render_pillar_hero(
	array(
		'id'              => 'sg-access-control-hero-heading',
		'class'           => 'sg-access-control-hero',
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
				'label'   => __( 'Access Control', 'site-blocks' ),
				'current' => true,
			),
		),
		'badge'           => __( 'Access Control · Sydney', 'site-blocks' ),
		'title_before'    => __( 'Replace keys with access you can ', 'site-blocks' ),
		'title_accent'    => __( 'control', 'site-blocks' ),
		'title_after'     => __( '.', 'site-blocks' ),
		'lead'            => __( 'Cards, mobile credentials, PINs and biometrics — designed, installed and supported across Sydney. Start your quote online, reviewed by our technicians.', 'site-blocks' ),
		'primary_label'   => __( 'Start My Quote', 'site-blocks' ),
		'primary_url'     => home_url( '/get-an-instant-quote/' ),
		'secondary_label' => __( 'Help Me Choose', 'site-blocks' ),
		'secondary_url'   => home_url( '/design-my-solution/' ),
		'footnote'        => __( 'Licensed installers · Master Licence #000103619 · ASIAL member', 'site-blocks' ),
		'visual'          => static function (): void {
			site_blocks_access_control_hero_image();
		},
	)
);
?>
<section class="sg-ac-trust-strip alignfull" aria-label="<?php esc_attr_e( 'Trust credentials', 'site-blocks' ); ?>">
	<div class="sg-container">
		<ul class="sg-ac-trust-strip__list" role="list">
			<li><?php esc_html_e( 'Master Licence #000103619', 'site-blocks' ); ?></li>
			<li><?php esc_html_e( 'ASIAL member', 'site-blocks' ); ?></li>
			<li><?php esc_html_e( 'Commercial & residential', 'site-blocks' ); ?></li>
			<li>
				<a href="tel:1300225276"><?php esc_html_e( '1300 225 276', 'site-blocks' ); ?></a>
			</li>
		</ul>
	</div>
</section>
