<?php
/**
 * Alarm Systems — Safeguard + Ajax band render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow    = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : __( 'Safeguard + Ajax', 'site-blocks' );
$headline   = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : __( 'Wireless done properly', 'site-blocks' );
$lead       = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : __( 'Ajax is the system we\'ll design with you end-to-end online — wireless, refined, genuinely smart. The hardware is only half of it; the difference is how it\'s specified, installed and supported.', 'site-blocks' );
$cta_label  = isset( $attributes['ctaLabel'] ) ? (string) $attributes['ctaLabel'] : __( 'Explore Ajax with Safeguard', 'site-blocks' );
$cta_url    = isset( $attributes['ctaUrl'] ) ? (string) $attributes['ctaUrl'] : '/ajax-alarm-systems/';

$ajax_img = site_blocks_asset_url( 'images/alarm/ajax-alarm-trimmed.png' );
?>
<section class="sg-alarm-ajax alignfull" aria-labelledby="sg-alarm-ajax-heading">
	<div class="sg-container sg-alarm-ajax__grid">
		<div class="sg-alarm-ajax__copy">
			<p class="sg-alarm-ajax__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h2 id="sg-alarm-ajax-heading" class="sg-alarm-ajax__title"><?php echo esc_html( $headline ); ?></h2>
			<p class="sg-alarm-ajax__lead"><?php echo esc_html( $lead ); ?></p>
			<a class="sg-alarm-ajax__link" href="<?php echo esc_url( home_url( $cta_url ) ); ?>">
				<?php echo esc_html( $cta_label ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		</div>

		<div class="sg-alarm-ajax__visual">
			<img
				class="sg-alarm-ajax__img"
				src="<?php echo esc_url( $ajax_img ); ?>"
				alt="<?php esc_attr_e( 'Ajax security hardware including keypad, hub, motion sensor and key fob', 'site-blocks' ); ?>"
				width="1200"
				height="400"
				loading="eager"
				decoding="async"
			/>
		</div>
	</div>
</section>
