<?php
/**
 * Shared pillar-page hero (homepage layout: dot grid, balanced columns, CTAs).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a service pillar hero matching the Safeguard homepage pattern.
 *
 * @param array{
 *   id: string,
 *   class?: string,
 *   breadcrumb?: array<int, array{label: string, url?: string, current?: bool}>,
 *   badge: string,
 *   title_before: string,
 *   title_accent?: string,
 *   title_after?: string,
 *   lead: string,
 *   primary_label: string,
 *   primary_url: string,
 *   secondary_label: string,
 *   secondary_url: string,
 *   footnote?: string,
 *   visual: callable,
 * } $args Hero content.
 */
function site_blocks_render_pillar_hero( array $args ): void {
	$heading_id = sanitize_html_class( (string) $args['id'] );
	$section_class = 'sg-hero sg-hero--dark sg-hero--pillar alignfull';

	if ( ! empty( $args['class'] ) ) {
		$section_class .= ' ' . esc_attr( (string) $args['class'] );
	}

	$title_accent = isset( $args['title_accent'] ) ? (string) $args['title_accent'] : '';
	$title_after  = isset( $args['title_after'] ) ? (string) $args['title_after'] : '';
	$footnote     = isset( $args['footnote'] ) ? (string) $args['footnote'] : '';
	$breadcrumb   = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$visual       = $args['visual'];
	?>
	<section class="<?php echo esc_attr( $section_class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-hero__dark-bg" aria-hidden="true"></div>
		<div class="sg-container sg-hero__grid sg-hero__grid--dark">
			<div class="sg-hero__copy sg-hero__copy--dark">
				<?php if ( $breadcrumb !== array() ) : ?>
					<nav class="sg-pillar-hero__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
						<?php foreach ( $breadcrumb as $index => $crumb ) : ?>
							<?php if ( $index > 0 ) : ?>
								<span aria-hidden="true">›</span>
							<?php endif; ?>
							<?php if ( ! empty( $crumb['url'] ) && empty( $crumb['current'] ) ) : ?>
								<a href="<?php echo esc_url( (string) $crumb['url'] ); ?>"><?php echo esc_html( (string) $crumb['label'] ); ?></a>
							<?php else : ?>
								<span<?php echo ! empty( $crumb['current'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( (string) $crumb['label'] ); ?></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

				<p class="sg-hero__badge"><?php echo esc_html( (string) $args['badge'] ); ?></p>

				<h1 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-hero__title sg-hero__title--dark">
					<?php echo esc_html( (string) $args['title_before'] ); ?>
					<?php if ( '' !== $title_accent ) : ?>
						<span class="sg-hero__title-accent"><?php echo esc_html( $title_accent ); ?></span>
					<?php endif; ?>
					<?php echo esc_html( $title_after ); ?>
				</h1>

				<p class="sg-hero__sub sg-hero__sub--dark"><?php echo esc_html( (string) $args['lead'] ); ?></p>

				<div class="sg-hero__ctas">
					<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( (string) $args['primary_url'] ); ?>">
						<?php echo esc_html( (string) $args['primary_label'] ); ?>
					</a>
					<a class="sg-btn sg-btn--ghost-dark" href="<?php echo esc_url( (string) $args['secondary_url'] ); ?>">
						<?php echo esc_html( (string) $args['secondary_label'] ); ?>
					</a>
				</div>

				<?php if ( '' !== $footnote ) : ?>
					<p class="sg-pillar-hero__note"><?php echo esc_html( $footnote ); ?></p>
				<?php endif; ?>
			</div>

			<div class="sg-hero__visual sg-hero__visual--dark sg-hero__visual--pillar">
				<?php
				if ( is_callable( $visual ) ) {
					$visual();
				}
				?>
			</div>
		</div>
	</section>
	<?php
}
