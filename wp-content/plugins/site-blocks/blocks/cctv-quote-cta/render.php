<?php
/**
 * CCTV closing quote CTA.
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
<section class="sg-cta sg-cctv-cta alignfull" aria-labelledby="sg-cctv-cta-heading">
	<div class="sg-container">
		<div class="sg-cta-card">
			<h2 id="sg-cctv-cta-heading" class="sg-cta-card__head">
				<?php esc_html_e( 'A clearer view starts ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'here', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-cta-card__text">
				<?php esc_html_e( 'Tell us what to protect. Get a tailored estimate — no waiting on a salesperson.', 'site-blocks' ); ?>
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
