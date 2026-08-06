<?php
/**
 * Shared use-cases / scenario grid (icon cards, optional split photo, optional banner).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * Render a scenario icon or placeholder.
 *
 * @param string $icon_key        Icon key from card config.
 * @param bool   $use_brand_icons When false, always show placeholder.
 * @param bool   $use_lucide_icons Unused legacy param (Lucide is always used when icons are enabled).
 */
function site_blocks_scenario_grid_render_icon( string $icon_key, bool $use_brand_icons, bool $use_lucide_icons = false ): void {
	unset( $use_lucide_icons );

	if ( ! $use_brand_icons || '' === $icon_key ) {
		echo '<span class="sg-scenario-grid__icon-placeholder" aria-hidden="true"></span>';
		return;
	}

	site_blocks_lucide_icon_from_legacy( $icon_key, 88 );
}

/**
 * Render a scenario photo from assets/images/{subdir}/.
 *
 * @param array{file: string, alt?: string, dir?: string} $photo Photo config.
 */
function site_blocks_scenario_grid_render_photo( array $photo ): void {
	$subdir   = isset( $photo['dir'] ) ? trim( (string) $photo['dir'], '/' ) . '/' : 'monitoring/';
	$relative = 'images/' . $subdir . ltrim( (string) $photo['file'], '/' );

	if ( function_exists( 'site_blocks_print_managed_image' ) && site_blocks_print_managed_image( $relative, (string) ( $photo['alt'] ?? '' ), 'sg-scenario-grid__photo-img', 'lazy' ) ) {
		return;
	}

	echo '<span class="sg-scenario-grid__photo-placeholder" aria-hidden="true"></span>';
}

/**
 * Render the scenario / use-cases grid section.
 *
 * @param array{
 *   layout?: string,
 *   eyebrow?: string,
 *   title_before: string,
 *   title_accent: string,
 *   title_after?: string,
 *   intro?: string,
 *   use_brand_icons?: bool,
 *   photo?: array{file: string, alt?: string, dir?: string}|null,
 *   banner?: array{file: string, alt?: string, dir?: string}|null,
 *   cards: array<int, array{title: string, desc: string, icon?: string}>
 * }|null $config Section config.
 */
function site_blocks_render_scenario_grid( ?array $config ): void {
	if ( null === $config || empty( $config['cards'] ) ) {
		return;
	}

	$layout    = (string) ( $config['layout'] ?? 'default' );
	$use_icons = ! empty( $config['use_brand_icons'] );
	$use_lucide = ! empty( $config['use_lucide_icons'] );
	$banner    = $config['banner'] ?? null;
	$photo     = $config['photo'] ?? null;
	$card_count = count( $config['cards'] );

	$layout_class = 'default' === $layout ? '' : ' sg-scenario-grid--' . sanitize_html_class( $layout );
	?>
	<section class="sg-band sg-band--white sg-scenario-grid-section alignfull" aria-labelledby="sg-scenario-grid-heading">
		<div class="sg-container">
			<div class="sg-scenario-grid<?php echo esc_attr( $layout_class ); ?>">
				<?php if ( is_array( $banner ) && ! empty( $banner['file'] ) && 'banner' === $layout ) : ?>
					<div class="sg-scenario-grid__banner">
						<?php site_blocks_scenario_grid_render_photo( $banner ); ?>
					</div>
				<?php endif; ?>

				<?php if ( 'split' === $layout && is_array( $photo ) && ! empty( $photo['file'] ) ) : ?>
					<div class="sg-scenario-grid__intro-row">
						<div class="sg-scenario-grid__heading">
							<?php site_blocks_scenario_grid_render_heading( $config ); ?>
						</div>
						<div class="sg-scenario-grid__photo">
							<?php site_blocks_scenario_grid_render_photo( $photo ); ?>
						</div>
					</div>
				<?php else : ?>
					<div class="sg-scenario-grid__heading">
						<?php site_blocks_scenario_grid_render_heading( $config ); ?>
					</div>
				<?php endif; ?>

				<div class="sg-scenario-grid__grid sg-scenario-grid__grid--<?php echo esc_attr( (string) min( $card_count, 6 ) ); ?>" role="list">
					<?php foreach ( $config['cards'] as $card ) : ?>
						<article class="sg-scenario-grid__card" role="listitem">
							<div class="sg-scenario-grid__icon" aria-hidden="true">
								<?php site_blocks_scenario_grid_render_icon( (string) ( $card['icon'] ?? '' ), $use_icons, $use_lucide ); ?>
							</div>
							<h3 class="sg-scenario-grid__card-title"><?php echo esc_html( (string) $card['title'] ); ?></h3>
							<p class="sg-scenario-grid__card-desc"><?php echo esc_html( (string) $card['desc'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render eyebrow, title, and optional intro.
 *
 * @param array<string, mixed> $config Section config.
 */
function site_blocks_scenario_grid_render_heading( array $config ): void {
	?>
	<?php if ( ! empty( $config['eyebrow'] ) ) : ?>
		<p class="sg-scenario-grid__eyebrow"><?php echo esc_html( (string) $config['eyebrow'] ); ?></p>
	<?php endif; ?>
	<h2 id="sg-scenario-grid-heading" class="sg-scenario-grid__title">
		<?php echo esc_html( (string) $config['title_before'] ); ?>
		<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span><?php echo esc_html( (string) ( $config['title_after'] ?? '' ) ); ?>
	</h2>
	<?php if ( ! empty( $config['intro'] ) ) : ?>
		<p class="sg-scenario-grid__intro"><?php echo esc_html( (string) $config['intro'] ); ?></p>
	<?php endif; ?>
	<?php
}
