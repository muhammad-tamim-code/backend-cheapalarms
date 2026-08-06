<?php
/**
 * CCTV intro — why well-placed cameras + capability feature bar.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

$proofs = array(
	array(
		'title' => __( 'Deterrence', 'site-blocks' ),
		'desc'  => __( 'Visible cameras discourage opportunistic entry.', 'site-blocks' ),
		'icon'  => 'ip-camera.png',
	),
	array(
		'title' => __( 'Evidence', 'site-blocks' ),
		'desc'  => __( 'Clear footage for police and insurance.', 'site-blocks' ),
		'icon'  => 'smart-detection.png',
	),
	array(
		'title' => __( 'Oversight', 'site-blocks' ),
		'desc'  => __( 'Live view and alerts on your phone.', 'site-blocks' ),
		'icon'  => 'remote-app.png',
	),
);

$capabilities = array(
	array(
		'label' => __( '4K Recording', 'site-blocks' ),
		'icon'  => 'monitor',
	),
	array(
		'label' => __( 'Remote Access', 'site-blocks' ),
		'icon'  => 'smartphone',
	),
	array(
		'label' => __( 'AI Detection', 'site-blocks' ),
		'icon'  => 'scan-eye',
	),
	array(
		'label' => __( 'NDAA', 'site-blocks' ),
		'icon'  => 'shield-check',
	),
	array(
		'label' => __( 'ONVIF', 'site-blocks' ),
		'icon'  => 'badge-check',
	),
	array(
		'label' => __( '24/7 Recording', 'site-blocks' ),
		'icon'  => 'refresh-cw',
	),
);
?>
<section class="sg-band sg-band--white sg-cctv-intro alignfull" aria-labelledby="sg-cctv-intro-heading">
	<div class="sg-container">
		<div class="sg-cctv-intro__grid">
			<div class="sg-cctv-intro__copy">
				<h2 id="sg-cctv-intro-heading" class="sg-cctv-intro__title">
					<?php esc_html_e( 'Why well-placed cameras work', 'site-blocks' ); ?>
				</h2>
				<p>
					<?php esc_html_e( 'Cameras deter, record and let you check in remotely, but only when they\'re placed, angled and wired correctly. That\'s what we design for.', 'site-blocks' ); ?>
				</p>
			</div>

			<div class="sg-cctv-intro__proofs" role="list">
				<?php foreach ( $proofs as $proof ) : ?>
					<div class="sg-cctv-proof" role="listitem">
						<div class="sg-cctv-icon sg-cctv-icon--proof" aria-hidden="true">
							<?php site_blocks_cctv_icon( $proof['icon'] ); ?>
						</div>
						<strong class="sg-cctv-proof__title"><?php echo esc_html( $proof['title'] ); ?></strong>
						<p class="sg-cctv-proof__desc"><?php echo esc_html( $proof['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="sg-cctv-intro__bar">
			<div class="sg-cctv-intro__media" aria-hidden="true">
				<span class="sg-cctv-media-placeholder sg-cctv-intro__placeholder"></span>
			</div>

			<ul class="sg-cctv-intro__caps" role="list">
				<?php foreach ( $capabilities as $cap ) : ?>
					<li class="sg-cctv-intro__cap">
						<span class="sg-cctv-intro__cap-icon" aria-hidden="true">
							<?php site_blocks_lucide_icon( $cap['icon'], 28 ); ?>
						</span>
						<span class="sg-cctv-intro__cap-label"><?php echo esc_html( $cap['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
