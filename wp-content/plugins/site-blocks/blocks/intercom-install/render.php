<?php
/**
 * Intercom — what we design and install.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/intercom-icons.php';

$cards = array(
	array(
		'title' => __( 'Video intercoms', 'site-blocks' ),
		'desc'  => __( 'See and speak to visitors on a clear screen before you open up.', 'site-blocks' ),
		'icon'  => 'ip-camera.png',
	),
	array(
		'title' => __( 'Audio intercoms', 'site-blocks' ),
		'desc'  => __( 'Reliable two-way audio entry where video isn\'t needed.', 'site-blocks' ),
		'icon'  => 'support.png',
	),
	array(
		'title' => __( 'Answer on your phone', 'site-blocks' ),
		'desc'  => __( 'Take door and gate calls, and release entry, from a mobile app.', 'site-blocks' ),
		'icon'  => 'remote-app.png',
	),
	array(
		'title' => __( 'Door & gate release', 'site-blocks' ),
		'desc'  => __( 'Electric strikes, magnetic locks and gate motors, wired to work.', 'site-blocks' ),
		'icon'  => 'access-control.png',
	),
	array(
		'title' => __( 'Apartment & strata', 'site-blocks' ),
		'desc'  => __( 'Directory panels, call routing per unit, and building-manager access.', 'site-blocks' ),
		'icon'  => 'property-coverage.png',
	),
	array(
		'title' => __( 'Wired, 2-wire & IP', 'site-blocks' ),
		'desc'  => __( 'New cabling or reusing existing wiring — chosen for your building.', 'site-blocks' ),
		'icon'  => 'home-camera.png',
	),
);
?>
<section class="sg-band sg-band--white sg-cctv-install sg-intercom-install alignfull" aria-labelledby="sg-intercom-install-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-intercom-install-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'What we design and ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'install', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-section-intro sg-section-intro--center">
				<?php esc_html_e( 'We match the system to the entry you actually have, and to how you want to answer it.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-intercom-install__grid" role="list">
			<?php foreach ( $cards as $cell ) : ?>
				<article class="sg-cctv-bento__cell sg-intercom-install__cell" role="listitem">
					<div class="sg-cctv-icon sg-cctv-bento__icon sg-cctv-bento__icon--small" aria-hidden="true">
						<?php site_blocks_intercom_icon( $cell['icon'] ); ?>
					</div>
					<h3 class="sg-cctv-bento__title"><?php echo esc_html( $cell['title'] ); ?></h3>
					<p class="sg-cctv-bento__desc"><?php echo esc_html( $cell['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="sg-intercom-install__brands">
			<?php esc_html_e( 'Trusted brands, matched to your property — not a one-size package.', 'site-blocks' ); ?>
		</p>
	</div>
</section>
