<?php
/**
 * Access Control — projects gallery placeholder.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="sg-band sg-band--white sg-access-control-gallery alignfull" aria-labelledby="sg-access-control-gallery-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-access-control-gallery-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'Recent ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'projects', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-section-intro sg-section-intro--center">
				<?php esc_html_e( 'A selection of access control installations across Sydney — photos coming soon.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-ac-gallery__grid" role="list" aria-label="<?php esc_attr_e( 'Project photo placeholders', 'site-blocks' ); ?>">
			<?php for ( $i = 1; $i <= 8; $i++ ) : ?>
				<div class="sg-ac-gallery__cell" role="listitem">
					<span class="sg-cctv-media-placeholder" aria-hidden="true"></span>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>
