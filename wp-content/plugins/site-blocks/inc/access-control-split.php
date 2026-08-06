<?php
/**
 * Access Control alternating image/text row helper.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a two-column split section (image + copy).
 *
 * @param array{
 *   id: string,
 *   class?: string,
 *   band?: string,
 *   reverse?: bool,
 *   title_before: string,
 *   title_accent?: string,
 *   title_after?: string,
 *   intro?: string,
 *   paragraphs?: array<int, string>,
 *   paragraphs_html?: bool,
 *   list?: array<int, array{title: string, desc: string, html?: bool}>,
 *   primary_label?: string,
 *   primary_url?: string,
 *   secondary_label?: string,
 *   secondary_url?: string,
 *   visual: callable,
 *   footer?: callable,
 * } $args Section content.
 */
function site_blocks_render_access_control_split( array $args ): void {
	$heading_id    = sanitize_html_class( (string) $args['id'] );
	$band          = isset( $args['band'] ) ? (string) $args['band'] : 'white';
	$section_class = 'sg-band sg-band--' . $band . ' sg-ac-split alignfull';

	if ( ! empty( $args['class'] ) ) {
		$section_class .= ' ' . esc_attr( (string) $args['class'] );
	}

	if ( ! empty( $args['reverse'] ) ) {
		$section_class .= ' sg-ac-split--reverse';
	}

	$title_accent    = isset( $args['title_accent'] ) ? (string) $args['title_accent'] : '';
	$title_after     = isset( $args['title_after'] ) ? (string) $args['title_after'] : '';
	$intro           = isset( $args['intro'] ) ? (string) $args['intro'] : '';
	$paragraphs      = isset( $args['paragraphs'] ) && is_array( $args['paragraphs'] ) ? $args['paragraphs'] : array();
	$paragraphs_html = ! empty( $args['paragraphs_html'] );
	$list            = isset( $args['list'] ) && is_array( $args['list'] ) ? $args['list'] : array();
	$visual          = $args['visual'];
	$footer          = $args['footer'] ?? null;
	$primary_label   = (string) ( $args['primary_label'] ?? '' );
	$primary_url     = (string) ( $args['primary_url'] ?? '' );
	$secondary_label = (string) ( $args['secondary_label'] ?? '' );
	$secondary_url   = (string) ( $args['secondary_url'] ?? '' );
	$title_before    = (string) $args['title_before'];
	?>
	<section class="<?php echo esc_attr( $section_class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container sg-ac-split__grid">
			<div class="sg-ac-split__visual">
				<?php
				if ( is_callable( $visual ) ) {
					$visual();
				}
				?>
			</div>
			<div class="sg-ac-split__copy">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-section-title sg-section-title--ink">
					<?php echo esc_html( $title_before ); ?>
					<?php if ( '' !== $title_accent ) : ?>
						<span class="sg-accent"><?php echo esc_html( $title_accent ); ?></span>
					<?php endif; ?>
					<?php echo esc_html( $title_after ); ?>
				</h2>
				<?php if ( '' !== $intro ) : ?>
					<p class="sg-ac-split__intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
				<?php foreach ( $paragraphs as $paragraph ) : ?>
					<p>
						<?php
						if ( $paragraphs_html ) {
							echo wp_kses_post( (string) $paragraph );
						} else {
							echo esc_html( (string) $paragraph );
						}
						?>
					</p>
				<?php endforeach; ?>
				<?php if ( $list !== array() ) : ?>
					<div class="sg-ac-split__list" role="list">
						<?php foreach ( $list as $item ) : ?>
							<div class="sg-cctv-proof sg-ac-split__list-item" role="listitem">
								<strong class="sg-cctv-proof__title"><?php echo esc_html( (string) $item['title'] ); ?></strong>
								<?php if ( ! empty( $item['desc'] ) ) : ?>
									<p class="sg-cctv-proof__desc">
										<?php
										if ( ! empty( $item['html'] ) ) {
											echo wp_kses_post( (string) $item['desc'] );
										} else {
											echo esc_html( (string) $item['desc'] );
										}
										?>
									</p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $primary_url || '' !== $secondary_url ) : ?>
					<div class="sg-ac-split__ctas">
						<?php if ( '' !== $primary_url ) : ?>
							<a class="sg-btn sg-btn--orange" href="<?php echo esc_url( $primary_url ); ?>">
								<?php echo esc_html( $primary_label ); ?>
							</a>
						<?php endif; ?>
						<?php if ( '' !== $secondary_url ) : ?>
							<a class="sg-btn sg-btn--ghost" href="<?php echo esc_url( $secondary_url ); ?>">
								<?php echo esc_html( $secondary_label ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		if ( is_callable( $footer ) ) {
			$footer();
		}
		?>
	</section>
	<?php
}
