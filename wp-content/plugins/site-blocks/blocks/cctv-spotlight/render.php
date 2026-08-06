<?php
/**
 * CCTV hub — architectural product spotlight.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-media.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

$quote_url = home_url( '/contact/' );
$phone     = '1300225276';
$phone_fmt = '1300 225 276';

$features = array(
	array(
		'icon'  => 'shield',
		'title' => __( 'Commercial Grade', 'site-blocks' ),
		'desc'  => __( 'Hardware specified for Australian sites.', 'site-blocks' ),
	),
	array(
		'icon'  => 'smartphone',
		'title' => __( 'Remote Access', 'site-blocks' ),
		'desc'  => __( 'Live view and playback from any device.', 'site-blocks' ),
	),
	array(
		'icon'  => 'cpu',
		'title' => __( 'AI Analytics', 'site-blocks' ),
		'desc'  => __( 'Smarter detection with fewer false alerts.', 'site-blocks' ),
	),
	array(
		'icon'  => 'eye',
		'title' => __( 'NDAA Compliant', 'site-blocks' ),
		'desc'  => __( 'Procurement-ready for regulated environments.', 'site-blocks' ),
	),
);

$image_path = 'images/cctv/spotlight-camera.webp';
if ( ! is_readable( SITE_BLOCKS_DIR . 'assets/' . $image_path ) ) {
	$image_path = 'images/cctv/commercial.webp';
}
?>
<section class="sg-product-spotlight alignfull" aria-labelledby="sg-cctv-spotlight-heading">
	<div class="sg-product-spotlight__container">
		<div class="sg-product-spotlight__stage">
			<div class="sg-product-spotlight__surface" aria-hidden="true">
				<span class="sg-product-spotlight__blueprint"></span>
			</div>

			<div class="sg-product-spotlight__layout">
				<div class="sg-product-spotlight__media">
					<figure class="sg-product-spotlight__frame">
						<div class="sg-product-spotlight__frame-inner">
							<?php
							site_blocks_cctv_image(
								$image_path,
								__( 'Bullet CCTV camera mounted on a Sydney home exterior at dusk', 'site-blocks' ),
								'sg-product-spotlight__img',
								'lazy'
							);
							?>
						</div>
					</figure>
				</div>

				<div class="sg-product-spotlight__content">
					<h2 id="sg-cctv-spotlight-heading" class="sg-product-spotlight__title">
						<?php esc_html_e( 'Commercial CCTV designed for complete visibility.', 'site-blocks' ); ?>
					</h2>

					<ul class="sg-product-spotlight__features" role="list">
						<?php foreach ( $features as $feature ) : ?>
							<li>
								<span class="sg-product-spotlight__feature-icon" aria-hidden="true">
									<?php site_blocks_lucide_icon( $feature['icon'], 18 ); ?>
								</span>
								<span class="sg-product-spotlight__feature-copy">
									<span class="sg-product-spotlight__feature-title"><?php echo esc_html( $feature['title'] ); ?></span>
									<span class="sg-product-spotlight__feature-desc"><?php echo esc_html( $feature['desc'] ); ?></span>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>

				<div class="sg-product-spotlight__ctas">
					<a class="sg-btn sg-btn--primary sg-product-spotlight__btn" href="<?php echo esc_url( 'tel:' . $phone ); ?>">
						<span class="sg-btn__icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'phone', 16 ); ?></span>
						<?php echo esc_html( $phone_fmt ); ?>
					</a>
					<a class="sg-btn sg-btn--ghost sg-product-spotlight__btn" href="<?php echo esc_url( $quote_url ); ?>">
						<?php esc_html_e( 'Request a Quote', 'site-blocks' ); ?>
					</a>
				</div>
				</div>
			</div>
		</div>
	</div>
</section>
