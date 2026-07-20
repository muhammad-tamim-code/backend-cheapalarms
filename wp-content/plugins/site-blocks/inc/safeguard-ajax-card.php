<?php
/**
 * Safeguard + Ajax promo card (homepage aside pattern).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-section-icons.php';

/**
 * @param array{quote_url?: string, ajax_url?: string, calc_url?: string} $args Optional URLs.
 */
function site_blocks_render_safeguard_ajax_card( array $args = array() ): void {
	$quote_url = isset( $args['quote_url'] ) ? (string) $args['quote_url'] : home_url( '/get-an-instant-quote/' );
	$ajax_url  = isset( $args['ajax_url'] ) ? (string) $args['ajax_url'] : home_url( '/ajax-alarm-systems/' );
	$calc_url  = isset( $args['calc_url'] ) ? (string) $args['calc_url'] : home_url( '/ajax-calculator/' );

	$ajax_img = site_blocks_asset_url( 'images/ajax/ajax-products.png' );
	?>
	<div class="sg-ajax-card">
		<h2 class="sg-ajax-card__title">
			<?php esc_html_e( 'Safeguard + Ajax,', 'site-blocks' ); ?><br>
			<?php esc_html_e( 'professionally installed.', 'site-blocks' ); ?>
		</h2>
		<p class="sg-ajax-card__intro">
			<?php esc_html_e( 'We partner with Ajax Systems to deliver intelligent, reliable security, installed and supported by experienced technicians.', 'site-blocks' ); ?>
		</p>
		<div class="sg-ajax-visual">
			<img
				src="<?php echo esc_url( $ajax_img ); ?>"
				alt="<?php esc_attr_e( 'Ajax security hardware including motion sensor, hub, smartphone app and siren', 'site-blocks' ); ?>"
				width="750"
				height="563"
				loading="lazy"
				decoding="async"
			/>
		</div>
		<ul class="sg-ajax-trust" role="list">
			<li>
				<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_grade2(); ?></span>
				<span class="sg-ajax-trust__label"><?php esc_html_e( 'Grade 2 security', 'site-blocks' ); ?></span>
			</li>
			<li>
				<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_encrypted(); ?></span>
				<span class="sg-ajax-trust__label"><?php esc_html_e( 'Encrypted end-to-end', 'site-blocks' ); ?></span>
			</li>
			<li>
				<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_scalable(); ?></span>
				<span class="sg-ajax-trust__label"><?php esc_html_e( 'Scalable and future-ready', 'site-blocks' ); ?></span>
			</li>
			<li>
				<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_europe(); ?></span>
				<span class="sg-ajax-trust__label"><?php esc_html_e( 'Designed in Europe', 'site-blocks' ); ?></span>
			</li>
		</ul>
		<div class="sg-ajax-card__actions">
			<a class="sg-btn sg-btn--ajax-primary" href="<?php echo esc_url( $calc_url ); ?>">
				<?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?>
				<?php site_blocks_lucide_icon( 'arrow-right', 16 ); ?>
			</a>
			<a class="sg-btn sg-btn--ajax-outline" href="<?php echo esc_url( $ajax_url ); ?>">
				<?php esc_html_e( 'Explore Ajax', 'site-blocks' ); ?>
				<?php site_blocks_lucide_icon( 'arrow-right', 16 ); ?>
			</a>
		</div>
	</div>
	<?php
}
