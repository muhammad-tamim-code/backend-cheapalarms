<?php
/**
 * Shared Safeguard FAQ accordion helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default FAQ items used on the homepage and alarm page.
 *
 * @return array<int, array{q: string, a: string}>
 */
function site_blocks_get_safeguard_faq_items(): array {
	return array(
		array(
			'q' => __( 'How does the online quote process work?', 'site-blocks' ),
			'a' => __( 'You answer a few questions about your property, upload photos and site details, and our team reviews everything remotely. We then prepare one clear estimate for the complete solution.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Do you visit my site before providing a quote?', 'site-blocks' ),
			'a' => __( 'We review remotely wherever possible. If a site visit is needed to confirm details or access, we arrange that before finalising your estimate.', 'site-blocks' ),
		),
		array(
			'q' => __( 'What if I\'m not sure what system I need?', 'site-blocks' ),
			'a' => __( 'Use our guided flow and our team will recommend the right combination of alarm, CCTV, access and monitoring for your property and goals.', 'site-blocks' ),
		),
		array(
			'q' => __( 'What brands and systems do you use?', 'site-blocks' ),
			'a' => __( 'We design solutions using trusted industry brands, including Ajax Systems, selected to suit your site and professionally installed by our technicians.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Is there any obligation when I request a quote?', 'site-blocks' ),
			'a' => __( 'No. Requesting a quote is free and there is no obligation to proceed until you are ready to approve your estimate.', 'site-blocks' ),
		),
	);
}

/**
 * Render one FAQ accordion item in the value stack.
 *
 * @param array{q: string, a: string} $item       Question and answer pair.
 * @param int                         $index      Stable item index for IDs.
 * @param string                      $id_prefix  ID prefix for trigger/panel elements.
 */
function site_blocks_render_value_faq_item( array $item, int $index, string $id_prefix = 'sg-faq-' ): void {
	$trigger_id = $id_prefix . 'trigger-' . $index;
	$panel_id   = $id_prefix . 'panel-' . $index;
	?>
	<div class="sg-value-faq__item">
		<button
			type="button"
			class="sg-value-faq__trigger"
			id="<?php echo esc_attr( $trigger_id ); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
		>
			<span class="sg-value-faq__question"><?php echo esc_html( $item['q'] ); ?></span>
			<span class="sg-value-faq__chevron" aria-hidden="true"></span>
		</button>
		<div
			class="sg-value-faq__panel"
			id="<?php echo esc_attr( $panel_id ); ?>"
			role="region"
			aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
			hidden
		>
			<p><?php echo esc_html( $item['a'] ); ?></p>
		</div>
	</div>
	<?php
}
