<?php
/**
 * CCTV residential vs commercial segments.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-media.php';

$panels = array(
	array(
		'title' => __( 'Residential CCTV', 'site-blocks' ),
		'desc'  => __( 'Discreet coverage where it counts — front door, driveway and gates — without invading privacy.', 'site-blocks' ),
		'link'  => __( 'See home security cameras', 'site-blocks' ),
		'url'   => home_url( '/cctv-security-cameras/' ),
		'image' => 'images/cctv/residential.webp',
	),
	array(
		'title' => __( 'Commercial CCTV', 'site-blocks' ),
		'desc'  => __( 'Entries, stock and yards covered — built to work with alarms and access control.', 'site-blocks' ),
		'link'  => __( 'See commercial CCTV', 'site-blocks' ),
		'url'   => home_url( '/contact/' ),
		'image' => 'images/cctv/commercial.webp',
	),
);
?>
<section class="sg-band sg-band--blue sg-cctv-segments alignfull" aria-labelledby="sg-cctv-segments-heading">
	<div class="sg-container">
		<h2 id="sg-cctv-segments-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
			<?php esc_html_e( 'Homes and businesses, planned ', 'site-blocks' ); ?>
			<span class="sg-accent"><?php esc_html_e( 'differently', 'site-blocks' ); ?></span>
		</h2>

		<div class="sg-cctv-segments__grid">
			<?php foreach ( $panels as $panel ) : ?>
				<article class="sg-cctv-segment">
					<div class="sg-cctv-segment__media">
						<?php site_blocks_cctv_image( $panel['image'], $panel['title'] ); ?>
					</div>
					<div class="sg-cctv-segment__body">
						<h3 class="sg-cctv-segment__title"><?php echo esc_html( $panel['title'] ); ?></h3>
						<p class="sg-cctv-segment__desc"><?php echo esc_html( $panel['desc'] ); ?></p>
						<a class="sg-cctv-segment__link" href="<?php echo esc_url( $panel['url'] ); ?>">
							<?php echo esc_html( $panel['link'] ); ?>
							<span aria-hidden="true">→</span>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
