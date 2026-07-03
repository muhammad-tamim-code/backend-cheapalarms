<?php
/**
 * Ajax Alarm Systems FAQ block render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-faq.php';
require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-faq.php';

$faq_items = site_blocks_get_ajax_alarm_faq_items();
$midpoint  = (int) ceil( count( $faq_items ) / 2 );
?>
<section class="sg-value-row sg-value-row--peach alignfull" aria-labelledby="sg-ajax-faq-heading">
	<div class="sg-container sg-value-row__grid">
		<div class="sg-value-row__copy">
			<h2 class="sg-value-row__title" id="sg-ajax-faq-heading">
				<?php esc_html_e( 'Ajax alarm system ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'FAQs.', 'site-blocks' ); ?></span>
			</h2>
		</div>
		<div class="sg-value-row__content sg-value-row__content--faq">
			<div class="sg-value-faq">
				<div class="sg-value-faq__column">
					<?php
					foreach ( array_slice( $faq_items, 0, $midpoint ) as $faq_index => $faq_item ) {
						site_blocks_render_value_faq_item( $faq_item, $faq_index + 1, 'sg-ajax-faq-' );
					}
					?>
				</div>
				<div class="sg-value-faq__column">
					<?php
					foreach ( array_slice( $faq_items, $midpoint ) as $faq_index => $faq_item ) {
						site_blocks_render_value_faq_item( $faq_item, $faq_index + 1 + $midpoint, 'sg-ajax-faq-' );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
