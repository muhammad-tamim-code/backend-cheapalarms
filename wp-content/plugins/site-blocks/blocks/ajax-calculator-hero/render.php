<?php
/**
 * Ajax calculator page hero.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quote = esc_url( home_url( '/get-an-instant-quote/' ) );
?>
<section class="sg-band sg-band--white sg-ajax-calculator-hero alignfull" aria-labelledby="sg-ajax-calculator-hero-heading">
	<div class="sg-container">
		<p class="sg-ajax-section__eyebrow"><?php esc_html_e( 'Ajax system design', 'site-blocks' ); ?></p>
		<h1 id="sg-ajax-calculator-hero-heading" class="sg-ajax-section__title">
			<?php esc_html_e( 'Design your ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'Ajax', 'site-blocks' ); ?></span><?php esc_html_e( ' system online.', 'site-blocks' ); ?>
		</h1>
		<p class="sg-ajax-section__intro">
			<?php esc_html_e( 'Use the calculator below to explore hub, sensors and keypad options for your property. When you are ready, continue in our portal for a technician-reviewed estimate.', 'site-blocks' ); ?>
		</p>
		<p class="sg-ajax-calculator-hero__cta">
			<a class="sg-btn sg-btn--soft-blue" href="<?php echo $quote; ?>"><?php esc_html_e( 'Start full quote instead', 'site-blocks' ); ?></a>
		</p>
	</div>
</section>
