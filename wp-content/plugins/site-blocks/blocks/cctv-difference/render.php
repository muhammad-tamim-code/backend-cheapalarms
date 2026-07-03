<?php
/**
 * CCTV — Safeguard difference + process steps.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$steps = array(
	array(
		'title'       => __( 'Tell us what to cover', 'site-blocks' ),
		'description' => __( 'Entries, blind spots, indoor or outdoor.', 'site-blocks' ),
		'icon'        => 'home-camera.png',
	),
	array(
		'title'       => __( 'Share site details', 'site-blocks' ),
		'description' => __( 'Photos, access and mounting.', 'site-blocks' ),
		'icon'        => 'ip-camera.png',
	),
	array(
		'title'       => __( 'Get a tailored estimate', 'site-blocks' ),
		'description' => __( 'One price for the full system.', 'site-blocks' ),
		'icon'        => 'property-coverage.png',
	),
	array(
		'title'       => __( 'Technician review', 'site-blocks' ),
		'description' => __( 'Checked by a real installer.', 'site-blocks' ),
		'icon'        => 'support.png',
	),
);
?>
<section class="sg-band sg-band--blue sg-cctv-difference alignfull" aria-labelledby="sg-cctv-difference-heading">
	<div class="sg-container">
		<header class="sg-cctv-difference__header">
			<h2 id="sg-cctv-difference-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'One team, from first plan to final ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'handover', 'site-blocks' ); ?></span>
			</h2>
			<div class="sg-cctv-difference__intro sg-section-intro sg-section-intro--center">
				<p><?php esc_html_e( 'Same team from design to install to support — one clear estimate, reviewed by technicians before you approve.', 'site-blocks' ); ?></p>
			</div>
		</header>

		<ol class="sg-alarm-steps__list sg-cctv-difference__steps" role="list">
			<?php foreach ( $steps as $index => $step ) : ?>
				<li class="sg-alarm-step-card">
					<span class="sg-alarm-step-card__num" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
					<div class="sg-alarm-step-card__icon sg-cctv-icon sg-cctv-icon--step" aria-hidden="true">
						<?php site_blocks_cctv_icon( $step['icon'] ); ?>
					</div>
					<h3 class="sg-alarm-step-card__title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="sg-alarm-step-card__desc"><?php echo esc_html( $step['description'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
