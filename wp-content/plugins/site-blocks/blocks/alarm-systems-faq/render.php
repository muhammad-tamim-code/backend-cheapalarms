<?php
/**
 * Alarm Systems FAQ block render.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-faq.php';

$faq_items = site_blocks_get_safeguard_faq_items();
?>
<section class="sg-value-row sg-value-row--peach alignfull" aria-labelledby="sg-alarm-faq-heading">
	<div class="sg-container sg-value-row__grid">
		<div class="sg-value-row__copy">
			<h2 class="sg-value-row__title" id="sg-alarm-faq-heading">
				<?php esc_html_e( 'Frequently asked ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'questions', 'site-blocks' ); ?></span>
			</h2>
		</div>
		<div class="sg-value-row__content sg-value-row__content--faq">
			<div class="sg-value-faq">
				<div class="sg-value-faq__column">
					<?php
					foreach ( array_slice( $faq_items, 0, 3 ) as $faq_index => $faq_item ) {
						site_blocks_render_value_faq_item( $faq_item, $faq_index + 1, 'sg-alarm-faq-' );
					}
					?>
				</div>
				<div class="sg-value-faq__column">
					<?php
					foreach ( array_slice( $faq_items, 3 ) as $faq_index => $faq_item ) {
						site_blocks_render_value_faq_item( $faq_item, $faq_index + 4, 'sg-alarm-faq-' );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
