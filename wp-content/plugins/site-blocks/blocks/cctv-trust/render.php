<?php
/**
 * CCTV — why Safeguard trust panel.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$items = array(
	array(
		'title' => __( 'Licensed and accredited', 'site-blocks' ),
		'desc'  => __( 'Licensed installers — ASIAL member.', 'site-blocks' ),
		'icon'  => 'weatherproof.png',
	),
	array(
		'title' => __( 'Residential and commercial', 'site-blocks' ),
		'desc'  => __( 'Homes to multi-site business systems.', 'site-blocks' ),
		'icon'  => 'property-coverage.png',
	),
	array(
		'title' => __( 'Built for complex sites', 'site-blocks' ),
		'desc'  => __( 'Heritage, no-cavity and large properties.', 'site-blocks' ),
		'icon'  => 'home-camera.png',
	),
	array(
		'title' => __( 'Support after install', 'site-blocks' ),
		'desc'  => __( 'Documentation, maintenance and monitoring.', 'site-blocks' ),
		'icon'  => 'support.png',
	),
);
?>
<section class="sg-band sg-band--blue sg-alarm-why sg-cctv-trust alignfull" aria-labelledby="sg-cctv-trust-heading">
	<div class="sg-container">
		<header class="sg-alarm-why__header">
			<h2 id="sg-cctv-trust-heading" class="sg-alarm-why__title">
				<?php esc_html_e( 'Designed and supported by experienced ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'technicians', 'site-blocks' ); ?></span>
			</h2>
		</header>

		<div class="sg-alarm-why__panel sg-cctv-trust__panel">
			<?php foreach ( $items as $item ) : ?>
				<article class="sg-alarm-why__item">
					<div class="sg-alarm-why__icon sg-cctv-icon sg-cctv-icon--trust" aria-hidden="true">
						<?php site_blocks_cctv_icon( $item['icon'] ); ?>
					</div>
					<h3 class="sg-alarm-why__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="sg-alarm-why__item-desc"><?php echo esc_html( $item['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
