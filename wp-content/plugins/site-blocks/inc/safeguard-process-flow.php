<?php
/**
 * Shared four-step skeleton process flow section.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * Render the skeleton process flow section.
 *
 * @param array{
 *   heading_id?: string,
 *   section_class?: string,
 *   eyebrow?: string,
 *   title_before: string,
 *   title_accent: string,
 *   title_after?: string,
 *   steps: array<int, array{label: string, title: string, badge: string, skeleton: string}>
 * }|null $config Section content.
 */
function site_blocks_render_process_flow( ?array $config ): void {
	if ( null === $config ) {
		return;
	}

	$steps = $config['steps'] ?? array();

	if ( $steps === array() ) {
		return;
	}

	$heading_id    = (string) ( $config['heading_id'] ?? 'sg-process-flow-heading' );
	$section_class = trim( 'sg-band sg-band--white sg-process-flow ' . (string) ( $config['section_class'] ?? '' ) . ' alignfull' );
	$eyebrow       = (string) ( $config['eyebrow'] ?? __( 'How it works', 'site-blocks' ) );
	?>
	<section class="<?php echo esc_attr( $section_class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container">
			<header class="sg-process-flow__header">
				<p class="sg-process-flow__eyebrow">
					<span class="sg-process-flow__eyebrow-pill">
						<?php site_blocks_lucide_icon( 'signal' ); ?>
						<?php echo esc_html( $eyebrow ); ?>
					</span>
				</p>
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-section-title sg-section-title--center sg-section-title--ink">
					<?php echo esc_html( (string) $config['title_before'] ); ?>
					<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
					<?php echo esc_html( (string) ( $config['title_after'] ?? '' ) ); ?>
				</h2>
			</header>

			<div class="sg-process-flow__track" role="list">
				<?php
				$last_index = count( $steps ) - 1;

				foreach ( $steps as $index => $step ) :
					?>
					<article class="sg-process-flow__card" role="listitem">
						<div class="sg-process-flow__head">
							<p class="sg-process-flow__label"><?php echo esc_html( strtoupper( (string) $step['label'] ) ); ?></p>
							<h3 class="sg-process-flow__title"><?php echo esc_html( (string) $step['title'] ); ?></h3>
						</div>
						<div class="sg-process-flow__visual" aria-hidden="true">
							<?php site_blocks_render_process_flow_skeleton( (string) $step['skeleton'] ); ?>
						</div>
						<p class="sg-process-flow__badge">
							<span class="sg-process-flow__badge-pill">
								<?php site_blocks_lucide_icon( 'shield-check' ); ?>
								<span><?php echo esc_html( (string) $step['badge'] ); ?></span>
							</span>
						</p>
					</article>
					<?php if ( $index < $last_index ) : ?>
						<span class="sg-process-flow__arrow" aria-hidden="true">
							<span class="sg-process-flow__arrow-btn">
								<?php site_blocks_lucide_icon( 'chevron-right' ); ?>
							</span>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * CSS skeleton mockup for a process flow card.
 */
function site_blocks_render_process_flow_skeleton( string $type ): void {
	switch ( $type ) {
		case 'schedule':
			?>
			<div class="sg-pf-sk sg-pf-sk--schedule">
				<div class="sg-pf-sk__mock">
					<div class="sg-pf-sk__mock-row">
						<span class="sg-pf-sk__mock-icon"><?php site_blocks_lucide_icon( 'calendar-clock' ); ?></span>
						<span class="sg-pf-sk__bar sg-pf-sk__bar--lg"></span>
					</div>
					<div class="sg-pf-sk__mock-row sg-pf-sk__mock-row--fields">
						<span class="sg-pf-sk__field"></span>
						<span class="sg-pf-sk__field"></span>
					</div>
					<div class="sg-pf-sk__mock-row sg-pf-sk__mock-row--foot">
						<span class="sg-pf-sk__bar sg-pf-sk__bar--sm"></span>
						<span class="sg-pf-sk__btn"><?php esc_html_e( 'Set', 'site-blocks' ); ?></span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'quote':
			?>
			<div class="sg-pf-sk sg-pf-sk--quote">
				<div class="sg-pf-sk__mock">
					<div class="sg-pf-sk__mock-row">
						<span class="sg-pf-sk__mock-icon"><?php site_blocks_lucide_icon( 'shield-check' ); ?></span>
						<span class="sg-pf-sk__bar sg-pf-sk__bar--lg"></span>
					</div>
					<div class="sg-pf-sk__mock-row sg-pf-sk__mock-row--fields">
						<span class="sg-pf-sk__field"></span>
						<span class="sg-pf-sk__field"></span>
					</div>
					<div class="sg-pf-sk__mock-row sg-pf-sk__mock-row--foot">
						<span class="sg-pf-sk__bar sg-pf-sk__bar--sm"></span>
						<span class="sg-pf-sk__btn"><?php esc_html_e( 'Quote', 'site-blocks' ); ?></span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'photos':
			?>
			<div class="sg-pf-sk sg-pf-sk--photos">
				<div class="sg-pf-sk__mock">
					<div class="sg-pf-sk__mock-row">
						<span class="sg-pf-sk__mock-icon"><?php site_blocks_lucide_icon( 'cctv' ); ?></span>
						<span class="sg-pf-sk__bar sg-pf-sk__bar--lg"></span>
					</div>
					<div class="sg-pf-sk__photo-grid">
						<span class="sg-pf-sk__photo-cell"></span>
						<span class="sg-pf-sk__photo-cell"></span>
						<span class="sg-pf-sk__photo-cell"></span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'assess':
			?>
			<div class="sg-pf-sk sg-pf-sk--assess">
				<div class="sg-pf-sk__mock sg-pf-sk__mock--assess">
					<div class="sg-pf-sk__screen-map">
						<span class="sg-pf-sk__map-line"></span>
						<span class="sg-pf-sk__map-line sg-pf-sk__map-line--short"></span>
						<span class="sg-pf-sk__map-pin"></span>
					</div>
					<div class="sg-pf-sk__checklist">
						<span class="sg-pf-sk__check-item"><?php site_blocks_lucide_icon( 'shield-check' ); ?></span>
						<span class="sg-pf-sk__check-item"><?php site_blocks_lucide_icon( 'shield-check' ); ?></span>
						<span class="sg-pf-sk__check-item"><?php site_blocks_lucide_icon( 'shield-check' ); ?></span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'install':
			?>
			<div class="sg-pf-sk sg-pf-sk--install">
				<div class="sg-pf-sk__mock sg-pf-sk__mock--install">
					<span class="sg-pf-sk__device sg-pf-sk__device--cam"><?php site_blocks_lucide_icon( 'cctv' ); ?></span>
					<span class="sg-pf-sk__device sg-pf-sk__device--hub"><?php site_blocks_lucide_icon( 'router' ); ?></span>
					<span class="sg-pf-sk__device sg-pf-sk__device--phone"><?php site_blocks_lucide_icon( 'smartphone' ); ?></span>
				</div>
			</div>
			<?php
			break;
		case 'signal':
			?>
			<div class="sg-pf-sk sg-pf-sk--signal">
				<div class="sg-pf-sk__mock">
					<div class="sg-pf-sk__mock-row">
						<span class="sg-pf-sk__mock-icon"><?php site_blocks_lucide_icon( 'bell-ring' ); ?></span>
						<span class="sg-pf-sk__bar sg-pf-sk__bar--lg"></span>
					</div>
					<div class="sg-pf-sk__mock-row sg-pf-sk__mock-row--fields">
						<span class="sg-pf-sk__field"></span>
					</div>
					<div class="sg-pf-sk__signal-bars">
						<span></span><span></span><span></span><span></span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'tour':
			?>
			<div class="sg-pf-sk sg-pf-sk--tour">
				<div class="sg-pf-sk__mock sg-pf-sk__mock--feeds">
					<div class="sg-pf-sk__feed-grid">
						<span class="sg-pf-sk__feed-cell"></span>
						<span class="sg-pf-sk__feed-cell"></span>
						<span class="sg-pf-sk__feed-cell"></span>
						<span class="sg-pf-sk__feed-cell sg-pf-sk__feed-cell--live">
							<?php site_blocks_lucide_icon( 'cctv' ); ?>
						</span>
					</div>
					<span class="sg-pf-sk__feed-check"><?php site_blocks_lucide_icon( 'shield-check' ); ?></span>
				</div>
			</div>
			<?php
			break;
		case 'flag':
			?>
			<div class="sg-pf-sk sg-pf-sk--flag">
				<div class="sg-pf-sk__mock sg-pf-sk__mock--screen">
					<div class="sg-pf-sk__screen-map">
						<span class="sg-pf-sk__map-line"></span>
						<span class="sg-pf-sk__map-line sg-pf-sk__map-line--short"></span>
						<span class="sg-pf-sk__map-pin"></span>
					</div>
					<div class="sg-pf-sk__alert-toast">
						<span class="sg-pf-sk__alert-icon"><?php site_blocks_lucide_icon( 'bell-ring' ); ?></span>
						<span class="sg-pf-sk__alert-copy">
							<strong><?php esc_html_e( 'ALERT', 'site-blocks' ); ?></strong>
							<em><?php esc_html_e( 'Activity flagged', 'site-blocks' ); ?></em>
						</span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'report':
			?>
			<div class="sg-pf-sk sg-pf-sk--report">
				<div class="sg-pf-sk__mock sg-pf-sk__mock--report">
					<div class="sg-pf-sk__report-lines">
						<span class="sg-pf-sk__bar sg-pf-sk__bar--lg"></span>
						<span class="sg-pf-sk__bar"></span>
						<span class="sg-pf-sk__bar sg-pf-sk__bar--sm"></span>
					</div>
					<div class="sg-pf-sk__report-done">
						<?php site_blocks_lucide_icon( 'clipboard-list' ); ?>
						<span><?php esc_html_e( 'Log ready', 'site-blocks' ); ?></span>
					</div>
				</div>
			</div>
			<?php
			break;
		case 'support':
			?>
			<div class="sg-pf-sk sg-pf-sk--support">
				<div class="sg-pf-sk__mock">
					<div class="sg-pf-sk__mock-row">
						<span class="sg-pf-sk__mock-icon"><?php site_blocks_lucide_icon( 'phone' ); ?></span>
						<span class="sg-pf-sk__bar sg-pf-sk__bar--lg"></span>
					</div>
					<div class="sg-pf-sk__mock-row sg-pf-sk__mock-row--fields">
						<span class="sg-pf-sk__field"></span>
						<span class="sg-pf-sk__field sg-pf-sk__field--short"></span>
					</div>
				</div>
			</div>
			<?php
			break;
	}
}
