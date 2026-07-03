<?php
/**
 * Access Control — replace keys comparison.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/access-control-media.php';

$rows = array(
	array(
		'label'    => __( 'Lost credential', 'site-blocks' ),
		'keys'     => __( 'Rekey locks, replace every copy', 'site-blocks' ),
		'access'   => __( 'Disable one card in seconds', 'site-blocks' ),
	),
	array(
		'label'    => __( 'Staff turnover', 'site-blocks' ),
		'keys'     => __( 'Collect keys, hope none are copied', 'site-blocks' ),
		'access'   => __( 'Revoke access instantly, audit who entered', 'site-blocks' ),
	),
	array(
		'label'    => __( 'After-hours access', 'site-blocks' ),
		'keys'     => __( 'Hand out keys or leave doors unlocked', 'site-blocks' ),
		'access'   => __( 'Time schedules and one-off visitor codes', 'site-blocks' ),
	),
	array(
		'label'    => __( 'Audit trail', 'site-blocks' ),
		'keys'     => __( 'No record of who entered', 'site-blocks' ),
		'access'   => __( 'Timestamped log of every entry event', 'site-blocks' ),
	),
);
?>
<section class="sg-band sg-band--white sg-access-control-keys alignfull" aria-labelledby="sg-access-control-keys-heading">
	<div class="sg-container">
		<div class="sg-ac-keys__grid">
			<div class="sg-ac-keys__copy">
				<h2 id="sg-access-control-keys-heading" class="sg-section-title sg-section-title--ink">
					<?php esc_html_e( 'Replace keys, not just ', 'site-blocks' ); ?>
					<span class="sg-accent"><?php esc_html_e( 'locks', 'site-blocks' ); ?></span>
				</h2>
				<p class="sg-ac-split__intro">
					<?php esc_html_e( 'Physical keys create hidden risk — copies you can\'t track, rekeying costs, and no record of who came in after hours.', 'site-blocks' ); ?>
				</p>
				<div class="sg-ac-keys__table-wrap">
					<table class="sg-ac-keys__table">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Scenario', 'site-blocks' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Traditional keys', 'site-blocks' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Access control', 'site-blocks' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
									<td><?php echo esc_html( $row['keys'] ); ?></td>
									<td><?php echo esc_html( $row['access'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="sg-ac-keys__visual">
				<?php
				site_blocks_access_control_image(
					'images/access-control/keys.webp',
					__( 'Comparison of traditional keys versus electronic access credentials', 'site-blocks' )
				);
				?>
			</div>
		</div>
	</div>
</section>
