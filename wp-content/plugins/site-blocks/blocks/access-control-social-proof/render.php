<?php
/**
 * Access Control, client logos and reviews placeholders.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo_count = 6;
$reviews    = array(
	array(
		'quote' => __( 'Placeholder review, client permission required before publishing.', 'site-blocks' ),
		'meta'  => __( 'Commercial client, Sydney', 'site-blocks' ),
	),
	array(
		'quote' => __( 'Placeholder review, client permission required before publishing.', 'site-blocks' ),
		'meta'  => __( 'Strata building, Greater Sydney', 'site-blocks' ),
	),
);
?>
<section class="sg-band sg-band--blue sg-access-control-social-proof alignfull" aria-labelledby="sg-access-control-social-heading">
	<div class="sg-container">
		<h2 id="sg-access-control-social-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
			<?php esc_html_e( 'Trusted by Sydney ', 'site-blocks' ); ?>
			<span class="sg-accent"><?php esc_html_e( 'businesses', 'site-blocks' ); ?></span>
		</h2>

		<div class="sg-ac-social__logos" role="list" aria-label="<?php esc_attr_e( 'Client logo placeholders', 'site-blocks' ); ?>">
			<?php for ( $i = 0; $i < $logo_count; $i++ ) : ?>
				<span class="sg-ac-social__logo-placeholder" role="listitem" aria-hidden="true"></span>
			<?php endfor; ?>
		</div>
		<p class="sg-ac-social__note">
			<?php esc_html_e( 'Client logos and testimonials require permission before publication.', 'site-blocks' ); ?>
		</p>

		<div class="sg-ac-social__reviews" role="list" aria-label="<?php esc_attr_e( 'Customer reviews', 'site-blocks' ); ?>">
			<?php foreach ( $reviews as $review ) : ?>
				<blockquote class="sg-ac-social__review" role="listitem">
					<p class="sg-ac-social__review-quote"><?php echo esc_html( $review['quote'] ); ?></p>
					<cite class="sg-ac-social__review-meta"><?php echo esc_html( $review['meta'] ); ?></cite>
				</blockquote>
			<?php endforeach; ?>
		</div>
	</div>
</section>
