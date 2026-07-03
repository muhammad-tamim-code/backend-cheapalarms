<?php
/**
 * Intercom — why an intercom matters.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/intercom-icons.php';

$proofs = array(
	array(
		'title' => __( 'Verify', 'site-blocks' ),
		'desc'  => __( 'See and speak to visitors before the door ever opens.', 'site-blocks' ),
		'icon'  => 'ip-camera.png',
	),
	array(
		'title' => __( 'Answer anywhere', 'site-blocks' ),
		'desc'  => __( 'Take door calls and release entry from your phone.', 'site-blocks' ),
		'icon'  => 'remote-app.png',
	),
	array(
		'title' => __( 'Control access', 'site-blocks' ),
		'desc'  => __( 'Buzz in deliveries and tradespeople without a key handover.', 'site-blocks' ),
		'icon'  => 'access-control.png',
	),
);
?>
<section class="sg-band sg-band--white sg-cctv-intro sg-intercom-intro alignfull" aria-labelledby="sg-intercom-intro-heading">
	<div class="sg-container sg-cctv-intro__grid">
		<div class="sg-cctv-intro__copy">
			<h2 id="sg-intercom-intro-heading" class="sg-section-title sg-section-title--ink">
				<?php esc_html_e( 'Every entry is a ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'decision', 'site-blocks' ); ?></span>
			</h2>
			<p><?php esc_html_e( 'The front door is where security is decided. An intercom lets you see and speak to visitors before anything opens — from the monitor or your phone, wherever you are.', 'site-blocks' ); ?></p>
		</div>
		<div class="sg-cctv-intro__proofs" role="list">
			<?php foreach ( $proofs as $proof ) : ?>
				<div class="sg-cctv-proof" role="listitem">
					<div class="sg-cctv-icon sg-cctv-icon--proof" aria-hidden="true">
						<?php site_blocks_intercom_icon( $proof['icon'] ); ?>
					</div>
					<strong class="sg-cctv-proof__title"><?php echo esc_html( $proof['title'] ); ?></strong>
					<p class="sg-cctv-proof__desc"><?php echo esc_html( $proof['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
