<?php
/**
 * Access Control FAQ block.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-faq.php';
require_once SITE_BLOCKS_DIR . 'inc/access-control-faq.php';

$faq_items = site_blocks_get_access_control_faq_items();
?>
<section class="sg-value-row sg-value-row--peach sg-cctv-faq sg-access-control-faq alignfull" aria-labelledby="sg-access-control-faq-heading">
	<div class="sg-container sg-value-row__grid">
		<div class="sg-value-row__copy">
			<h2 class="sg-value-row__title" id="sg-access-control-faq-heading">
				<?php esc_html_e( 'Questions, ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'answered', 'site-blocks' ); ?></span>
			</h2>
		</div>
		<div class="sg-value-row__content sg-value-row__content--faq">
			<div class="sg-value-faq">
				<div class="sg-value-faq__column">
					<?php
					foreach ( array_slice( $faq_items, 0, 3 ) as $faq_index => $faq_item ) {
						site_blocks_render_value_faq_item( $faq_item, $faq_index + 1, 'sg-access-control-faq-' );
					}
					?>
				</div>
				<div class="sg-value-faq__column">
					<?php
					foreach ( array_slice( $faq_items, 3 ) as $faq_index => $faq_item ) {
						site_blocks_render_value_faq_item( $faq_item, $faq_index + 4, 'sg-access-control-faq-' );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
