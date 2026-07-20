<?php
/**
 * CCTV as part of a layered system.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-ajax-card.php';
require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$links = array(
	array(
		'title' => __( 'Alarm Systems', 'site-blocks' ),
		'desc'  => __( 'Detect and respond to intrusion.', 'site-blocks' ),
		'url'   => home_url( '/alarm-systems/' ),
		'icon'  => 'alarm-systems.png',
	),
	array(
		'title' => __( 'Access Control', 'site-blocks' ),
		'desc'  => __( 'Decide who can enter, and when.', 'site-blocks' ),
		'url'   => home_url( '/access-control/' ),
		'icon'  => 'access-control.png',
	),
	array(
		'title' => __( 'Monitoring & Response', 'site-blocks' ),
		'desc'  => __( 'Eyes on your alerts around the clock.', 'site-blocks' ),
		'url'   => home_url( '/monitoring/' ),
		'icon'  => 'support.png',
	),
);
?>
<section class="sg-band sg-band--white sg-cctv-layered alignfull" aria-labelledby="sg-cctv-layered-heading">
	<div class="sg-container sg-cctv-layered__inner">
		<header class="sg-cctv-layered__header">
			<h2 id="sg-cctv-layered-heading" class="sg-section-title sg-section-title--ink sg-cctv-layered__title">
				<?php esc_html_e( 'CCTV works best as part of a ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'layered', 'site-blocks' ); ?></span>
				<?php esc_html_e( ' system', 'site-blocks' ); ?>
			</h2>
			<span class="sg-cctv-layered__rule" aria-hidden="true"></span>
			<p class="sg-cctv-layered__lead">
				<?php esc_html_e( 'Cameras work best with alarms and access control, layered security, one team, one plan.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-cctv-layered__split">
			<div class="sg-cctv-layered__diagram">
				<div class="sg-cctv-layered__hub" aria-hidden="true">
					<div class="sg-cctv-layered__hub-ring">
						<div class="sg-cctv-icon sg-cctv-layered__hub-icon">
							<?php site_blocks_cctv_icon( 'ip-camera.png' ); ?>
						</div>
					</div>
					<p class="sg-cctv-layered__hub-label"><?php esc_html_e( 'Works with CCTV', 'site-blocks' ); ?></p>
				</div>

				<div class="sg-cctv-layered__flow">
					<div class="sg-cctv-layered__spine" aria-hidden="true"></div>
					<div class="sg-cctv-layered__hub-link" aria-hidden="true"></div>

					<div class="sg-cctv-layered__cards" role="list">
						<?php foreach ( $links as $link ) : ?>
							<a class="sg-cctv-layered__card" href="<?php echo esc_url( $link['url'] ); ?>" role="listitem">
								<span class="sg-cctv-layered__card-branch" aria-hidden="true"></span>
								<span class="sg-cctv-layered__card-bar" aria-hidden="true"></span>
								<span class="sg-cctv-icon sg-cctv-layered__card-icon" aria-hidden="true">
									<?php site_blocks_cctv_icon( $link['icon'] ); ?>
								</span>
								<span class="sg-cctv-layered__card-body">
									<span class="sg-cctv-layered__card-title"><?php echo esc_html( $link['title'] ); ?></span>
									<span class="sg-cctv-layered__card-desc"><?php echo esc_html( $link['desc'] ); ?></span>
								</span>
								<span class="sg-cctv-layered__card-chevron" aria-hidden="true">&rsaquo;</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<aside class="sg-cctv-layered__aside" aria-label="<?php esc_attr_e( 'Safeguard + Ajax', 'site-blocks' ); ?>">
				<?php site_blocks_render_safeguard_ajax_card(); ?>
			</aside>
		</div>
	</div>
</section>
