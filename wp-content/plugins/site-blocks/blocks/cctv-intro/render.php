<?php
/**
 * CCTV — why well-placed cameras (prose + proof stack).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

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
?>
<section class="sg-band sg-band--white sg-cctv-intro alignfull" aria-labelledby="sg-cctv-intro-heading">
	<div class="sg-container sg-cctv-intro__grid">
		<div class="sg-cctv-intro__copy">
			<h2 id="sg-cctv-intro-heading" class="sg-section-title sg-section-title--ink">
				<?php esc_html_e( 'Why well-placed cameras ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'work', 'site-blocks' ); ?></span>
			</h2>
			<p><?php esc_html_e( 'Cameras deter, record and let you check in remotely — but only when they\'re placed, angled and wired correctly. That\'s what we design for.', 'site-blocks' ); ?></p>
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
</section>
