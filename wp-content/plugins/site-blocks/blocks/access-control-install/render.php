<?php
/**
 * Access Control — where we install.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$sectors = array(
	array(
		'title' => __( 'Offices & commercial', 'site-blocks' ),
		'desc'  => __( 'Reception, staff areas, server rooms and after-hours zones.', 'site-blocks' ),
		'icon'  => 'property-coverage.png',
	),
	array(
		'title' => __( 'Retail & hospitality', 'site-blocks' ),
		'desc'  => __( 'Staff-only areas, stockrooms and manager overrides.', 'site-blocks' ),
		'icon'  => 'home-camera.png',
	),
	array(
		'title' => __( 'Warehouses & industrial', 'site-blocks' ),
		'desc'  => __( 'Loading docks, perimeter gates and shift-based access.', 'site-blocks' ),
		'icon'  => 'weatherproof.png',
	),
	array(
		'title' => __( 'Schools & childcare', 'site-blocks' ),
		'desc'  => __( 'Controlled entry for staff, parents and authorised visitors.', 'site-blocks' ),
		'icon'  => 'support.png',
	),
	array(
		'title' => __( 'Strata & apartments', 'site-blocks' ),
		'desc'  => __( 'Common areas, car parks and building-manager access.', 'site-blocks' ),
		'icon'  => 'remote-app.png',
	),
	array(
		'title' => __( 'Healthcare & clinics', 'site-blocks' ),
		'desc'  => __( 'Restricted zones, pharmacy storage and staff scheduling.', 'site-blocks' ),
		'icon'  => 'smart-detection.png',
	),
);
?>
<section class="sg-band sg-band--white sg-access-control-install alignfull" aria-labelledby="sg-access-control-install-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-access-control-install-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'Where we ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'install', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-section-intro sg-section-intro--center">
				<?php esc_html_e( 'From a single door to multi-reader sites across Greater Sydney.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-ac-install__grid" role="list">
			<?php foreach ( $sectors as $sector ) : ?>
				<article class="sg-ac-install__cell" role="listitem">
					<div class="sg-cctv-icon sg-cctv-bento__icon sg-cctv-bento__icon--small" aria-hidden="true">
						<?php site_blocks_cctv_icon( $sector['icon'] ); ?>
					</div>
					<h3 class="sg-cctv-bento__title"><?php echo esc_html( $sector['title'] ); ?></h3>
					<p class="sg-cctv-bento__desc"><?php echo esc_html( $sector['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>

		<aside class="sg-ac-repairs" aria-labelledby="sg-access-control-repairs-heading">
			<h3 id="sg-access-control-repairs-heading" class="sg-ac-repairs__title">
				<?php esc_html_e( 'Repairs, upgrades & takeovers', 'site-blocks' ); ?>
			</h3>
			<p class="sg-ac-repairs__desc">
				<?php esc_html_e( 'Already have access control? We service, expand and migrate existing systems — including reader replacements, controller upgrades and integration with your CCTV and alarms.', 'site-blocks' ); ?>
			</p>
		</aside>
	</div>
</section>
