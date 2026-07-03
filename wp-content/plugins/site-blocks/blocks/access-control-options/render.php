<?php
/**
 * Access Control — credential options grid.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = array(
	array(
		'title' => __( 'Card & fob', 'site-blocks' ),
		'desc'  => __( 'Reliable tap-and-go entry for staff and regular visitors.', 'site-blocks' ),
		'best'  => __( 'Best for: offices, warehouses, strata common areas', 'site-blocks' ),
	),
	array(
		'title' => __( 'PIN codes', 'site-blocks' ),
		'desc'  => __( 'Keypad entry without issuing physical credentials.', 'site-blocks' ),
		'best'  => __( 'Best for: after-hours staff, shared service doors', 'site-blocks' ),
	),
	array(
		'title' => __( 'Mobile credentials', 'site-blocks' ),
		'desc'  => __( 'Unlock doors from a smartphone — no card to lose or replace.', 'site-blocks' ),
		'best'  => __( 'Best for: agile teams, hot-desking, visitor passes', 'site-blocks' ),
	),
	array(
		'title' => __( 'Biometric', 'site-blocks' ),
		'desc'  => __( 'Fingerprint or facial recognition for high-security zones.', 'site-blocks' ),
		'best'  => __( 'Best for: server rooms, pharmacies, restricted areas', 'site-blocks' ),
	),
	array(
		'title' => __( 'Intercom entry', 'site-blocks' ),
		'desc'  => __( 'Verify visitors on video or audio before releasing the door.', 'site-blocks' ),
		'best'  => __( 'Best for: reception, apartment lobbies, after-hours deliveries', 'site-blocks' ),
	),
	array(
		'title' => __( 'Gate & perimeter', 'site-blocks' ),
		'desc'  => __( 'Readers on car park gates, boom barriers and external doors.', 'site-blocks' ),
		'best'  => __( 'Best for: industrial sites, strata car parks, loading bays', 'site-blocks' ),
	),
);
?>
<section class="sg-band sg-band--blue sg-access-control-options alignfull" aria-labelledby="sg-access-control-options-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-access-control-options-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'Access options for every ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'entry point', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-section-intro sg-section-intro--center">
				<?php esc_html_e( 'Mix credentials on the same system — matched to how each door is actually used.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-ac-options__grid" role="list">
			<?php foreach ( $options as $option ) : ?>
				<article class="sg-ac-options__cell" role="listitem">
					<h3 class="sg-ac-options__title"><?php echo esc_html( $option['title'] ); ?></h3>
					<p class="sg-ac-options__desc"><?php echo esc_html( $option['desc'] ); ?></p>
					<p class="sg-ac-options__best"><?php echo esc_html( $option['best'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
