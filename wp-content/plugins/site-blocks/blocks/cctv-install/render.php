<?php
/**
 * CCTV, what we design and install (bento grid).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$large = array(
	array(
		'title' => __( 'IP & 4K cameras', 'site-blocks' ),
		'desc'  => __( 'Sharp enough for faces and number plates.', 'site-blocks' ),
		'icon'  => 'ip-camera.png',
	),
	array(
		'title' => __( 'NVR & PoE recording', 'site-blocks' ),
		'desc'  => __( 'Reliable local recording, one cable per camera.', 'site-blocks' ),
		'icon'  => 'property-coverage.png',
	),
);

$small = array(
	array(
		'title' => __( 'Day & night coverage', 'site-blocks' ),
		'desc'  => __( 'Infrared and colour night vision.', 'site-blocks' ),
		'icon'  => 'night-vision.png',
	),
	array(
		'title' => __( 'Smart detection', 'site-blocks' ),
		'desc'  => __( 'Person and vehicle alerts, fewer false alarms.', 'site-blocks' ),
		'icon'  => 'smart-detection.png',
	),
	array(
		'title' => __( 'Indoor & outdoor', 'site-blocks' ),
		'desc'  => __( 'Rated for Sydney heat, rain and coastal air.', 'site-blocks' ),
		'icon'  => 'weatherproof.png',
	),
	array(
		'title' => __( 'App & remote view', 'site-blocks' ),
		'desc'  => __( 'Live view, playback and alerts on your phone.', 'site-blocks' ),
		'icon'  => 'remote-app.png',
	),
);
?>
<section class="sg-band sg-band--white sg-cctv-install alignfull" aria-labelledby="sg-cctv-install-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-cctv-install-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'What we design and ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'install', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-section-intro sg-section-intro--center">
				<?php esc_html_e( 'Cameras, recording, network and app, designed as one system.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-cctv-bento">
			<div class="sg-cctv-bento__large">
				<?php foreach ( $large as $cell ) : ?>
					<article class="sg-cctv-bento__cell sg-cctv-bento__cell--large">
						<div class="sg-cctv-icon sg-cctv-bento__icon" aria-hidden="true">
							<?php site_blocks_cctv_icon( $cell['icon'] ); ?>
						</div>
						<h3 class="sg-cctv-bento__title"><?php echo esc_html( $cell['title'] ); ?></h3>
						<p class="sg-cctv-bento__desc"><?php echo esc_html( $cell['desc'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
			<div class="sg-cctv-bento__small">
				<?php foreach ( $small as $cell ) : ?>
					<article class="sg-cctv-bento__cell">
						<div class="sg-cctv-icon sg-cctv-bento__icon sg-cctv-bento__icon--small" aria-hidden="true">
							<?php site_blocks_cctv_icon( $cell['icon'] ); ?>
						</div>
						<h3 class="sg-cctv-bento__title"><?php echo esc_html( $cell['title'] ); ?></h3>
						<p class="sg-cctv-bento__desc"><?php echo esc_html( $cell['desc'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
