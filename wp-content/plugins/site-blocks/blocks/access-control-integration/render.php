<?php
/**
 * Access Control, system integration.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/access-control-media.php';
require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$links = array(
	array(
		'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
		'desc'  => __( 'See and record every entry event.', 'site-blocks' ),
		'url'   => home_url( '/cctv-security-cameras/' ),
		'icon'  => 'ip-camera.png',
	),
	array(
		'title' => __( 'Alarm Systems', 'site-blocks' ),
		'desc'  => __( 'Detect intrusion and respond automatically.', 'site-blocks' ),
		'url'   => home_url( '/alarm-systems/' ),
		'icon'  => 'alarm-systems.png',
	),
	array(
		'title' => __( 'Intercom Systems', 'site-blocks' ),
		'desc'  => __( 'Verify visitors before you release the door.', 'site-blocks' ),
		'url'   => home_url( '/intercom-systems/' ),
		'icon'  => 'access-control.png',
	),
	array(
		'title' => __( 'Monitoring & Response', 'site-blocks' ),
		'desc'  => __( 'Professional eyes on your alerts 24/7.', 'site-blocks' ),
		'url'   => home_url( '/monitoring/' ),
		'icon'  => 'support.png',
	),
);
?>
<section class="sg-band sg-band--blue sg-access-control-integration alignfull" aria-labelledby="sg-access-control-integration-heading">
	<div class="sg-container">
		<h2 id="sg-access-control-integration-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
			<?php esc_html_e( 'Works with your wider ', 'site-blocks' ); ?>
			<span class="sg-accent"><?php esc_html_e( 'security stack', 'site-blocks' ); ?></span>
		</h2>
		<p class="sg-section-intro sg-section-intro--center">
			<?php esc_html_e( 'Access control is strongest as part of a layered system, one team plans, installs and supports it all.', 'site-blocks' ); ?>
		</p>

		<div class="sg-ac-integration__grid">
			<div class="sg-ac-integration__visual">
				<?php
				site_blocks_access_control_image(
					'images/access-control/integration.webp',
					__( 'Integrated access control, CCTV and alarm system diagram', 'site-blocks' )
				);
				?>
			</div>
			<ul class="sg-ac-integration__links" role="list">
				<?php foreach ( $links as $link ) : ?>
					<li>
						<a class="sg-ac-integration__link" href="<?php echo esc_url( $link['url'] ); ?>">
							<span class="sg-cctv-icon sg-cctv-layered__card-icon" aria-hidden="true">
								<?php site_blocks_cctv_icon( $link['icon'] ); ?>
							</span>
							<span class="sg-cctv-layered__card-body">
								<span class="sg-cctv-layered__card-title"><?php echo esc_html( $link['title'] ); ?></span>
								<span class="sg-cctv-layered__card-desc"><?php echo esc_html( $link['desc'] ); ?></span>
							</span>
							<span class="sg-cctv-layered__card-chevron" aria-hidden="true">&rsaquo;</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
