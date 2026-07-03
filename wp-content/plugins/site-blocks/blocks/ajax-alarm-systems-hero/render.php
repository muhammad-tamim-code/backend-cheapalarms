<?php
/**
 * Ajax Alarm Systems hero block render.
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

$eyebrow   = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : __( 'Ajax alarm systems · Australia', 'site-blocks' );
$headline  = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : __( 'Ajax alarm systems, professionally installed by Safeguard', 'site-blocks' );
$lead      = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : __( 'We design, install and configure Ajax wireless alarm systems for homes, apartments and small businesses across Australia — specified around your property, not sold as a generic kit.', 'site-blocks' );
$cta_label = isset( $attributes['primaryCtaLabel'] ) ? (string) $attributes['primaryCtaLabel'] : __( 'Design my Ajax system', 'site-blocks' );
$cta_url   = isset( $attributes['primaryCtaUrl'] ) ? (string) $attributes['primaryCtaUrl'] : '/ajax-calculator/';
$sec_label = isset( $attributes['secondaryCtaLabel'] ) ? (string) $attributes['secondaryCtaLabel'] : __( 'Call 1300 225 276', 'site-blocks' );
$sec_url   = isset( $attributes['secondaryCtaUrl'] ) ? (string) $attributes['secondaryCtaUrl'] : 'tel:1300225276';

$hero_img = site_blocks_asset_url( 'images/ajax/ajax-hero-house.webp' );

$trust_items = array(
	array(
		'icon'  => 'wifi.png',
		'line1' => __( 'Wireless alarm', 'site-blocks' ),
		'line2' => __( 'design', 'site-blocks' ),
	),
	array(
		'icon'  => 'app.png',
		'line1' => __( 'App setup and', 'site-blocks' ),
		'line2' => __( 'handover', 'site-blocks' ),
	),
	array(
		'icon'  => 'shield.png',
		'line1' => __( 'Monitoring', 'site-blocks' ),
		'line2' => __( 'options', 'site-blocks' ),
	),
	array(
		'icon'  => 'wired.png',
		'line1' => __( 'Wired upgrade', 'site-blocks' ),
		'line2' => __( 'path', 'site-blocks' ),
	),
);

$system_notes = array(
	array(
		'icon'  => 'hub.png',
		'title' => __( 'Hub', 'site-blocks' ),
		'desc'  => __( 'Central control', 'site-blocks' ),
	),
	array(
		'icon'  => 'jeweller.png',
		'title' => __( 'Jeweller', 'site-blocks' ),
		'desc'  => __( 'Wireless devices', 'site-blocks' ),
	),
	array(
		'icon'  => '4g.png',
		'title' => __( '4G / IP', 'site-blocks' ),
		'desc'  => __( 'Monitoring paths', 'site-blocks' ),
	),
);
?>
<section class="sg-ajax-hero" aria-labelledby="sg-ajax-hero-heading">
	<div class="sg-ajax-hero__grid-bg" aria-hidden="true"></div>
	<div class="sg-container">
		<div class="sg-ajax-hero__grid">
			<div class="sg-ajax-hero__copy">
				<p class="sg-ajax-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<h1 id="sg-ajax-hero-heading" class="sg-ajax-hero__title"><?php echo esc_html( $headline ); ?></h1>
				<p class="sg-ajax-hero__lead"><?php echo esc_html( $lead ); ?></p>

				<div class="sg-ajax-hero__actions">
					<div class="sg-hero__ctas sg-ajax-hero__ctas">
						<a class="sg-btn sg-btn--primary sg-ajax-hero__cta" href="<?php echo esc_url( home_url( $cta_url ) ); ?>">
							<?php echo esc_html( $cta_label ); ?>
						</a>
						<a class="sg-btn sg-btn--ghost-dark sg-ajax-hero__cta" href="<?php echo esc_url( $sec_url ); ?>">
							<?php echo esc_html( $sec_label ); ?>
						</a>
					</div>

					<div class="sg-ajax-hero__trust" aria-label="<?php esc_attr_e( 'What we include', 'site-blocks' ); ?>">
						<ul class="sg-ajax-hero__trust-list">
							<?php foreach ( $trust_items as $item ) : ?>
								<li class="sg-ajax-hero__trust-item">
									<span class="sg-ajax-hero__trust-icon" aria-hidden="true">
										<?php site_blocks_ajax_hero_icon( $item['icon'] ); ?>
									</span>
									<span class="sg-ajax-hero__trust-label">
										<span class="sg-ajax-hero__trust-line"><?php echo esc_html( $item['line1'] ); ?></span>
										<span class="sg-ajax-hero__trust-line"><?php echo esc_html( $item['line2'] ); ?></span>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<div class="sg-ajax-hero__visual">
				<div class="sg-ajax-hero__visual-frame">
					<img
						class="sg-ajax-hero__img"
						src="<?php echo esc_url( $hero_img ); ?>"
						alt="<?php esc_attr_e( 'Ajax Hub and wireless security devices protecting a modern Australian home', 'site-blocks' ); ?>"
						width="840"
						height="600"
						loading="eager"
						decoding="async"
					/>
					<div class="sg-ajax-hero__caption">
						<p class="sg-ajax-hero__caption-title"><?php esc_html_e( 'Designed as a complete system, not a box of parts', 'site-blocks' ); ?></p>
						<ul class="sg-ajax-hero__caption-list">
							<?php foreach ( $system_notes as $note ) : ?>
								<li class="sg-ajax-hero__caption-item">
									<span class="sg-ajax-hero__caption-icon" aria-hidden="true">
										<?php site_blocks_ajax_hero_icon( $note['icon'] ); ?>
									</span>
									<span class="sg-ajax-hero__caption-label"><?php echo esc_html( $note['title'] ); ?></span>
									<span class="sg-ajax-hero__caption-desc"><?php echo esc_html( $note['desc'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
