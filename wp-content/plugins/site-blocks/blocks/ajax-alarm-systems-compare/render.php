<?php
/**
 * Ajax Alarm Systems, comparison table.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows = array(
	array(
		'label'        => __( 'Installation', 'site-blocks' ),
		'ajax'         => __( 'Professional design and install', 'site-blocks' ),
		'traditional'  => __( 'Complex, invasive installation', 'site-blocks' ),
	),
	array(
		'label'        => __( 'Best for', 'site-blocks' ),
		'ajax'         => __( 'Homes, apartments, small business', 'site-blocks' ),
		'traditional'  => __( 'Large or high-risk commercial sites', 'site-blocks' ),
	),
	array(
		'label'        => __( 'Support', 'site-blocks' ),
		'ajax'         => __( 'Local installer + ongoing support', 'site-blocks' ),
		'traditional'  => __( 'Varies, often limited', 'site-blocks' ),
	),
	array(
		'label'        => __( 'Monitoring', 'site-blocks' ),
		'ajax'         => __( 'IP and 4G monitoring options', 'site-blocks' ),
		'traditional'  => __( 'Usually needs special lines', 'site-blocks' ),
	),
	array(
		'label'        => __( 'Expansion', 'site-blocks' ),
		'ajax'         => __( 'Easy to add wireless and wired devices', 'site-blocks' ),
		'traditional'  => __( 'Difficult and costly', 'site-blocks' ),
	),
);
?>
<section class="sg-ajax-section sg-ajax-compare alignfull" aria-labelledby="sg-ajax-compare-heading">
	<div class="sg-container">
		<header class="sg-ajax-section__header">
			<p class="sg-ajax-section__eyebrow"><?php esc_html_e( 'Compare', 'site-blocks' ); ?></p>
			<h2 id="sg-ajax-compare-heading" class="sg-ajax-section__title">
				<?php esc_html_e( 'Ajax vs traditional wired alarms.', 'site-blocks' ); ?>
			</h2>
		</header>

		<div class="sg-ajax-compare__scroll" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Alarm system comparison table', 'site-blocks' ); ?>">
			<table class="sg-ajax-compare__table">
				<thead>
					<tr>
						<th class="sg-ajax-compare__corner" scope="col"><span class="sg-sr-only"><?php esc_html_e( 'Feature', 'site-blocks' ); ?></span></th>
						<th class="sg-ajax-compare__head sg-ajax-compare__head--ajax" scope="col"><?php esc_html_e( 'Ajax with Safeguard', 'site-blocks' ); ?></th>
						<th class="sg-ajax-compare__head sg-ajax-compare__head--traditional" scope="col"><?php esc_html_e( 'Traditional wired alarms', 'site-blocks' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<th class="sg-ajax-compare__label" scope="row"><?php echo esc_html( $row['label'] ); ?></th>
							<td class="sg-ajax-compare__cell sg-ajax-compare__cell--ajax"><?php echo esc_html( $row['ajax'] ); ?></td>
							<td class="sg-ajax-compare__cell"><?php echo esc_html( $row['traditional'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
