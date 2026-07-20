<?php
/**
 * Intercom, homes, apartments and business segments.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/intercom-media.php';

$panels = array(
	array(
		'title' => __( 'Homes', 'site-blocks' ),
		'desc'  => __( 'Door station, indoor monitor and mobile answering, add a gate release if you need one.', 'site-blocks' ),
		'image' => 'images/ajax/property/home.webp',
		'alt'   => __( 'Intercom installation for a Sydney home', 'site-blocks' ),
	),
	array(
		'title' => __( 'Apartments & strata', 'site-blocks' ),
		'desc'  => __( 'Directory panels, per-unit call routing and building-manager access, planned for strata and fire-egress rules.', 'site-blocks' ),
		'image' => 'images/ajax/property/apartments.webp',
		'alt'   => __( 'Apartment and strata intercom installation Sydney', 'site-blocks' ),
	),
	array(
		'title' => __( 'Business', 'site-blocks' ),
		'desc'  => __( 'Verified entry, remote release for deliveries, and integration with your CCTV, alarm and access control.', 'site-blocks' ),
		'image' => 'images/ajax/property/small-business.webp',
		'alt'   => __( 'Commercial intercom installation Sydney', 'site-blocks' ),
	),
);
?>
<section class="sg-band sg-band--blue sg-cctv-segments sg-intercom-segments alignfull" aria-labelledby="sg-intercom-segments-heading">
	<div class="sg-container">
		<h2 id="sg-intercom-segments-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
			<?php esc_html_e( 'Homes, apartments and businesses, planned ', 'site-blocks' ); ?>
			<span class="sg-accent"><?php esc_html_e( 'differently', 'site-blocks' ); ?></span>
		</h2>

		<div class="sg-cctv-segments__grid sg-intercom-segments__grid">
			<?php foreach ( $panels as $panel ) : ?>
				<article class="sg-cctv-segment sg-intercom-segment">
					<div class="sg-cctv-segment__media">
						<?php site_blocks_intercom_image( $panel['image'], $panel['alt'] ); ?>
					</div>
					<div class="sg-cctv-segment__body">
						<h3 class="sg-cctv-segment__title"><?php echo esc_html( $panel['title'] ); ?></h3>
						<p class="sg-cctv-segment__desc"><?php echo esc_html( $panel['desc'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
