<?php
/**
 * Ajax Alarm Systems — Safeguard Difference block render.
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

$portal_img = site_blocks_asset_url( 'images/portal/portal-2.webp' );

$bullets = array(
	__( 'Property-specific device selection', 'site-blocks' ),
	__( 'Clean installation and thoughtful device placement', 'site-blocks' ),
	__( 'App setup, handover and testing', 'site-blocks' ),
	__( 'CCTV and intercom add-on pathways', 'site-blocks' ),
);
?>
<section class="sg-ajax-difference alignfull" aria-labelledby="sg-ajax-difference-heading">
	<div class="sg-container">
		<div class="sg-ajax-difference__grid">
			<div class="sg-ajax-difference__copy">
				<p class="sg-ajax-difference__eyebrow"><?php esc_html_e( 'The Safeguard difference', 'site-blocks' ); ?></p>
				<h2 id="sg-ajax-difference-heading" class="sg-ajax-difference__title">
					<?php esc_html_e( 'An Ajax installer should do ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'more', 'site-blocks' ); ?></span><br class="sg-ajax-difference__title-break" aria-hidden="true"><?php esc_html_e( 'than sell a starter kit.', 'site-blocks' ); ?>
				</h2>
				<p class="sg-ajax-difference__intro">
					<?php esc_html_e( 'We design, install and support Ajax systems that actually suit your property — not a generic kit picked off a shelf.', 'site-blocks' ); ?>
				</p>
				<p class="sg-ajax-difference__intro">
					<?php esc_html_e( 'Start with a free instant online quote, then manage everything in your secure portal — no waiting days for a callback or a salesperson at your door.', 'site-blocks' ); ?>
				</p>
				<ul class="sg-ajax-difference__list" role="list">
					<?php foreach ( $bullets as $bullet ) : ?>
						<li>
							<span class="sg-ajax-difference__check" aria-hidden="true"><?php site_blocks_ajax_difference_check(); ?></span>
							<?php echo esc_html( $bullet ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="sg-ajax-difference__visual">
				<img
					class="sg-ajax-difference__img"
					src="<?php echo esc_url( $portal_img ); ?>"
					alt="<?php esc_attr_e( 'Safeguard customer portal showing quote summary, installation progress, messages and quote approval', 'site-blocks' ); ?>"
					loading="lazy"
					decoding="async"
				/>
			</div>
		</div>
	</div>
</section>
