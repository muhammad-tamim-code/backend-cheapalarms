<?php
/**
 * Intercom — why Safeguard trust panel.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/intercom-icons.php';

$items = array(
	array(
		'title' => __( 'Licensed and accredited', 'site-blocks' ),
		'desc'  => __( 'Master Licence · ASIAL member.', 'site-blocks' ),
		'icon'  => 'weatherproof.png',
	),
	array(
		'title' => __( 'Homes, strata and commercial', 'site-blocks' ),
		'desc'  => __( 'From a single front door to multi-unit buildings.', 'site-blocks' ),
		'icon'  => 'property-coverage.png',
	),
	array(
		'title' => __( 'Built for complex sites', 'site-blocks' ),
		'desc'  => __( 'Heritage terraces, long gate runs, existing-cabling upgrades.', 'site-blocks' ),
		'icon'  => 'home-camera.png',
	),
	array(
		'title' => __( 'Support after install', 'site-blocks' ),
		'desc'  => __( 'App setup, user handover, maintenance and monitoring.', 'site-blocks' ),
		'icon'  => 'support.png',
	),
);
?>
<section class="sg-band sg-band--blue sg-alarm-why sg-cctv-trust sg-intercom-trust alignfull" aria-labelledby="sg-intercom-trust-heading">
	<div class="sg-container">
		<header class="sg-alarm-why__header">
			<h2 id="sg-intercom-trust-heading" class="sg-alarm-why__title">
				<?php esc_html_e( 'Designed and supported by experienced ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'technicians', 'site-blocks' ); ?></span>
			</h2>
		</header>

		<div class="sg-alarm-why__panel sg-cctv-trust__panel">
			<?php foreach ( $items as $item ) : ?>
				<article class="sg-alarm-why__item">
					<div class="sg-alarm-why__icon sg-cctv-icon sg-cctv-icon--trust" aria-hidden="true">
						<?php site_blocks_intercom_icon( $item['icon'] ); ?>
					</div>
					<h3 class="sg-alarm-why__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="sg-alarm-why__item-desc"><?php echo esc_html( $item['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
