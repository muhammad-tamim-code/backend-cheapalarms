<?php
/**
 * CCTV — customer portal.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-homepage-markup.php';

$portal_img = site_blocks_asset_url( 'images/portal/portal-dashboard.png' );
?>
<section class="sg-band sg-portal-band sg-cctv-portal alignfull" aria-labelledby="sg-cctv-portal-heading">
	<div class="sg-container sg-portal-band__grid">
		<div class="sg-portal-band__copy">
			<h2 id="sg-cctv-portal-heading" class="sg-portal-band__title">
				<?php esc_html_e( 'Your quote, photos and approvals in ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'one place', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-portal-band__intro">
				<?php esc_html_e( 'Start online, then manage your CCTV quote in our secure portal — no waiting days for a callback.', 'site-blocks' ); ?>
			</p>
			<ul class="sg-portal-band__list" role="list">
				<li>
					<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
					<?php esc_html_e( 'Track your quote progress in real time', 'site-blocks' ); ?>
				</li>
				<li>
					<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
					<?php esc_html_e( 'Upload site photos and documents', 'site-blocks' ); ?>
				</li>
				<li>
					<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
					<?php esc_html_e( 'Message our team and approve your estimate', 'site-blocks' ); ?>
				</li>
			</ul>
		</div>
		<div class="sg-portal-band__visual">
			<img
				class="sg-portal-band__img"
				src="<?php echo esc_url( $portal_img ); ?>"
				alt="<?php esc_attr_e( 'Safeguard customer portal showing quote status, uploaded photos, messages and estimate approval', 'site-blocks' ); ?>"
				width="928"
				height="458"
				loading="lazy"
				decoding="async"
			/>
		</div>
	</div>
</section>
