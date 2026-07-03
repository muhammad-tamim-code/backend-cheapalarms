<?php
/**
 * Ajax Alarm Systems — quote CTA band.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-icons.php';

$bg_url      = site_blocks_asset_url( 'images/ajax/ajax-bg.webp' );
$calc_url    = home_url( '/ajax-calculator/' );
$phone_href  = 'tel:1300225276';
$phone_label = '1300 225 276';

$features = array(
	array(
		'title'       => __( 'Price on the spot', 'site-blocks' ),
		'description' => __( 'Get instant pricing for most properties.', 'site-blocks' ),
		'icon'        => 'dollar.png',
		'accent'      => 'orange',
	),
	array(
		'title'       => __( 'Technician-reviewed', 'site-blocks' ),
		'description' => __( 'Our experts design the right system for complex sites.', 'site-blocks' ),
		'icon'        => 'technician.png',
		'accent'      => 'blue',
	),
	array(
		'title'       => __( 'Track everything', 'site-blocks' ),
		'description' => __( 'Manage your system in your secure online portal.', 'site-blocks' ),
		'icon'        => 'secure.png',
		'accent'      => 'blue',
	),
);

$trust_items = array(
	array(
		'label' => __( 'Installer/dealer of Ajax Systems products', 'site-blocks' ),
		'type'  => 'safeguard-logo',
	),
	array(
		'label' => __( 'Australian owned and operated', 'site-blocks' ),
		'icon'  => 'australia-map.png',
	),
	array(
		'label' => __( 'Licensed and insured', 'site-blocks' ),
		'icon'  => 'secure.png',
	),
);

$safeguard_logo = site_blocks_asset_url( 'images/brand/safeguard-logo-footer.png' );
?>
<section class="sg-ajax-quote-cta alignfull" aria-labelledby="sg-ajax-quote-cta-heading">
	<div class="sg-container">
		<div class="sg-ajax-quote-cta__panel" style="--sg-ajax-quote-bg: url('<?php echo esc_url( $bg_url ); ?>')">
			<div class="sg-ajax-quote-cta__body">
				<div class="sg-ajax-quote-cta__copy">
					<p class="sg-ajax-quote-cta__eyebrow"><?php esc_html_e( 'Start your quote', 'site-blocks' ); ?></p>
					<h2 id="sg-ajax-quote-cta-heading" class="sg-ajax-quote-cta__title">
						<?php esc_html_e( 'Design your Ajax system with Safeguard.', 'site-blocks' ); ?>
					</h2>
					<p class="sg-ajax-quote-cta__lead">
						<?php esc_html_e( 'Start with the Ajax calculator, get pricing quickly, and we\'ll design the right system for your property.', 'site-blocks' ); ?>
					</p>
					<div class="sg-ajax-quote-cta__actions">
						<a class="sg-btn sg-ajax-quote-cta__btn sg-ajax-quote-cta__btn--primary" href="<?php echo esc_url( $calc_url ); ?>">
							<?php esc_html_e( 'Open Ajax calculator', 'site-blocks' ); ?>
							<svg class="sg-btn__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
						</a>
						<a class="sg-btn sg-ajax-quote-cta__btn sg-ajax-quote-cta__btn--outline" href="<?php echo esc_attr( $phone_href ); ?>">
							<svg class="sg-btn__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 4h4l2 5-3 2a12 12 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>
							<?php echo esc_html( sprintf( __( 'Call %s', 'site-blocks' ), $phone_label ) ); ?>
						</a>
					</div>
				</div>

				<div class="sg-ajax-quote-cta__features" aria-label="<?php esc_attr_e( 'Why start your quote with Safeguard', 'site-blocks' ); ?>">
					<?php foreach ( $features as $feature ) : ?>
						<article class="sg-ajax-quote-cta__feature">
							<span class="sg-ajax-quote-cta__feature-icon sg-ajax-quote-cta__feature-icon--<?php echo esc_attr( $feature['accent'] ); ?>" aria-hidden="true">
								<?php site_blocks_ajax_cta_icon( $feature['icon'] ); ?>
							</span>
							<div class="sg-ajax-quote-cta__feature-copy">
								<h3 class="sg-ajax-quote-cta__feature-title"><?php echo esc_html( $feature['title'] ); ?></h3>
								<p class="sg-ajax-quote-cta__feature-desc"><?php echo esc_html( $feature['description'] ); ?></p>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="sg-ajax-quote-cta__trust" aria-label="<?php esc_attr_e( 'Safeguard credentials', 'site-blocks' ); ?>">
				<ul class="sg-ajax-quote-cta__trust-list" role="list">
					<?php foreach ( $trust_items as $item ) : ?>
						<li class="sg-ajax-quote-cta__trust-item<?php echo ( isset( $item['type'] ) && 'safeguard-logo' === $item['type'] ) ? ' sg-ajax-quote-cta__trust-item--brand' : ''; ?>">
							<?php if ( isset( $item['type'] ) && 'safeguard-logo' === $item['type'] ) : ?>
								<span class="sg-ajax-quote-cta__trust-logo" aria-hidden="true">
									<img
										class="sg-ajax-quote-cta__brand-logo"
										src="<?php echo esc_url( $safeguard_logo ); ?>"
										alt="<?php esc_attr_e( 'Safeguard Security Services', 'site-blocks' ); ?>"
										width="220"
										height="52"
										loading="lazy"
										decoding="async"
									/>
								</span>
							<?php else : ?>
								<span class="sg-ajax-quote-cta__trust-icon" aria-hidden="true">
									<?php site_blocks_ajax_cta_icon( $item['icon'] ); ?>
								</span>
							<?php endif; ?>
							<span class="sg-ajax-quote-cta__trust-label"><?php echo esc_html( $item['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
					<li class="sg-ajax-quote-cta__trust-item sg-ajax-quote-cta__trust-item--phone">
						<a class="sg-ajax-quote-cta__phone" href="<?php echo esc_attr( $phone_href ); ?>">
							<span class="sg-ajax-quote-cta__trust-icon" aria-hidden="true">
								<?php site_blocks_ajax_cta_icon( 'call.png' ); ?>
							</span>
							<span class="sg-ajax-quote-cta__phone-num"><?php echo esc_html( $phone_label ); ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</section>
