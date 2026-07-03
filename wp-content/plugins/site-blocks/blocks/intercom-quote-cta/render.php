<?php
/**
 * Intercom closing quote CTA.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quote_url  = home_url( '/get-an-instant-quote/' );
$design_url = home_url( '/design-my-solution/' );
?>
<section class="sg-cta sg-cctv-cta sg-intercom-cta alignfull" aria-labelledby="sg-intercom-cta-heading">
	<div class="sg-container">
		<div class="sg-cta-card">
			<h2 id="sg-intercom-cta-heading" class="sg-cta-card__head">
				<?php esc_html_e( 'See who\'s there. Decide from ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'anywhere', 'site-blocks' ); ?></span>
				<?php esc_html_e( '.', 'site-blocks' ); ?>
			</h2>
			<p class="sg-cta-card__text">
				<?php esc_html_e( 'Tell us your entries and how you\'d like to answer them. Get a tailored intercom estimate, reviewed by our technicians, without waiting days for a salesperson.', 'site-blocks' ); ?>
			</p>
			<div class="sg-cta-card__btns">
				<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( $quote_url ); ?>">
					<?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?>
				</a>
				<a class="sg-btn sg-btn--cta-ghost" href="<?php echo esc_url( $design_url ); ?>">
					<?php esc_html_e( 'Help Me Choose', 'site-blocks' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
