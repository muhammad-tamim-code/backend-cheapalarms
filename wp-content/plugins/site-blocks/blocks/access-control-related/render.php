<?php
/**
 * Access Control — related services.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

$services = array(
	array(
		'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
		'desc'  => __( 'See and record every entry.', 'site-blocks' ),
		'url'   => home_url( '/cctv-security-cameras/' ),
		'icon'  => 'ip-camera.png',
	),
	array(
		'title' => __( 'Alarm Systems', 'site-blocks' ),
		'desc'  => __( 'Detect and respond to intrusion.', 'site-blocks' ),
		'url'   => home_url( '/alarm-systems/' ),
		'icon'  => 'alarm-systems.png',
	),
	array(
		'title' => __( 'Intercom Systems', 'site-blocks' ),
		'desc'  => __( 'Verify visitors before you open up.', 'site-blocks' ),
		'url'   => home_url( '/intercom-systems/' ),
		'icon'  => 'access-control.png',
	),
	array(
		'title' => __( 'Alarm Monitoring', 'site-blocks' ),
		'desc'  => __( 'Professional response around the clock.', 'site-blocks' ),
		'url'   => home_url( '/monitoring/' ),
		'icon'  => 'support.png',
	),
);
?>
<section class="sg-band sg-band--white sg-access-control-related alignfull" aria-labelledby="sg-access-control-related-heading">
	<div class="sg-container">
		<h2 id="sg-access-control-related-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
			<?php esc_html_e( 'Related ', 'site-blocks' ); ?>
			<span class="sg-accent"><?php esc_html_e( 'services', 'site-blocks' ); ?></span>
		</h2>

		<ul class="sg-ac-related__grid" role="list">
			<?php foreach ( $services as $service ) : ?>
				<li>
					<a class="sg-ac-related__card" href="<?php echo esc_url( $service['url'] ); ?>">
						<span class="sg-cctv-icon sg-cctv-bento__icon sg-cctv-bento__icon--small" aria-hidden="true">
							<?php site_blocks_cctv_icon( $service['icon'] ); ?>
						</span>
						<h3 class="sg-ac-related__title"><?php echo esc_html( $service['title'] ); ?></h3>
						<p class="sg-ac-related__desc"><?php echo esc_html( $service['desc'] ); ?></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
