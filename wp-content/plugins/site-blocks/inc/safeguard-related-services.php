<?php
/**
 * Shared “Related services” card strip (integration cross-links).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * Known icon filenames under assets/images/icons/related-services/.
 *
 * @return array<string, string>
 */
function site_blocks_related_services_icon_files(): array {
	return array(
		'back-to-base'    => 'back-to-base.png',
		'virtual-patrol'  => 'virtual-patrol.png',
		'cctv'            => 'cctv.png',
		'mobile-patrols'  => 'mobile-patrols.png',
	);
}

/**
 * Render a related-services icon or placeholder.
 *
 * @param string $icon_key Icon key from card config.
 * @param bool   $use_brand_icons When false, always show placeholder.
 */
function site_blocks_related_services_render_icon( string $icon_key, bool $use_brand_icons ): void {
	if ( ! $use_brand_icons || '' === $icon_key ) {
		echo '<span class="sg-related-services__icon-placeholder" aria-hidden="true"></span>';
		return;
	}

	if ( ! function_exists( 'site_blocks_lucide_icon_from_legacy' ) ) {
		require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
	}

	site_blocks_lucide_icon_from_legacy( $icon_key, 88 );
}

/**
 * Render the related services block.
 *
 * @param array{
 *   eyebrow?: string,
 *   title_before: string,
 *   title_accent: string,
 *   title_after?: string,
 *   intro: string,
 *   hub_link?: array{label: string, url: string}|null,
 *   use_brand_icons?: bool,
 *   cards: array<int, array{title: string, desc: string, url: string, icon: string}>
 * }|null $config Section config.
 */
function site_blocks_render_related_services( ?array $config ): void {
	if ( null === $config || empty( $config['cards'] ) ) {
		return;
	}

	$use_icons = ! empty( $config['use_brand_icons'] );
	$hub_link  = $config['hub_link'] ?? null;
	?>
	<div class="sg-related-services" aria-labelledby="sg-related-services-heading">
		<div class="sg-related-services__header">
			<div class="sg-related-services__heading">
				<?php if ( ! empty( $config['eyebrow'] ) ) : ?>
					<p class="sg-related-services__eyebrow"><?php echo esc_html( (string) $config['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h3 id="sg-related-services-heading" class="sg-related-services__title">
					<?php echo esc_html( (string) $config['title_before'] ); ?>
					<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span><?php echo esc_html( (string) ( $config['title_after'] ?? '' ) ); ?>
				</h3>
				<?php if ( ! empty( $config['intro'] ) ) : ?>
					<p class="sg-related-services__intro"><?php echo esc_html( (string) $config['intro'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( is_array( $hub_link ) && ! empty( $hub_link['url'] ) ) : ?>
				<a class="sg-related-services__hub-link" href="<?php echo esc_url( (string) $hub_link['url'] ); ?>">
					<?php echo esc_html( (string) ( $hub_link['label'] ?? __( 'View all related services', 'site-blocks' ) ) ); ?>
					<span aria-hidden="true"> →</span>
				</a>
			<?php endif; ?>
		</div>

		<div class="sg-related-services__grid<?php echo 3 === count( $config['cards'] ) ? ' sg-related-services__grid--3' : ''; ?>" role="list">
			<?php foreach ( $config['cards'] as $card ) : ?>
				<a class="sg-related-services__card" href="<?php echo esc_url( (string) $card['url'] ); ?>" role="listitem">
					<div class="sg-related-services__icon" aria-hidden="true">
						<?php site_blocks_related_services_render_icon( (string) $card['icon'], $use_icons ); ?>
					</div>
					<h4 class="sg-related-services__card-title"><?php echo esc_html( (string) $card['title'] ); ?></h4>
					<p class="sg-related-services__card-desc"><?php echo esc_html( (string) $card['desc'] ); ?></p>
					<span class="sg-related-services__arrow" aria-hidden="true"><?php site_blocks_lucide_icon( 'arrow-right', 22 ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Related services inside .sg-container (split footer band).
 *
 * @param array<string, mixed>|null $config Same shape as site_blocks_render_related_services().
 */
function site_blocks_render_related_services_band( ?array $config ): void {
	if ( null === $config ) {
		return;
	}

	echo '<div class="sg-container">';
	site_blocks_render_related_services( $config );
	echo '</div>';
}

/**
 * Full-width related services page grid (Access Control style).
 *
 * @param array{
 *   heading_id: string,
 *   section_class: string,
 *   title_before: string,
 *   title_accent: string,
 *   cards: array<int, array{title: string, desc: string, url: string, icon: string}>,
 *   icon_renderer: callable(string): void,
 * } $config Section content.
 */
function site_blocks_render_related_services_page_grid( array $config ): void {
	$cards = $config['cards'] ?? array();

	if ( $cards === array() ) {
		return;
	}

	$icon_renderer = $config['icon_renderer'] ?? null;
	?>
	<section class="<?php echo esc_attr( 'sg-band sg-band--white ' . (string) $config['section_class'] . ' alignfull' ); ?>" aria-labelledby="<?php echo esc_attr( (string) $config['heading_id'] ); ?>">
		<div class="sg-container">
			<h2 id="<?php echo esc_attr( (string) $config['heading_id'] ); ?>" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( (string) $config['title_before'] ); ?>
				<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
			</h2>

			<ul class="sg-ac-related__grid" role="list">
				<?php foreach ( $cards as $card ) : ?>
					<li>
						<a class="sg-ac-related__card" href="<?php echo esc_url( (string) $card['url'] ); ?>">
							<span class="sg-cctv-icon sg-cctv-bento__icon sg-cctv-bento__icon--small" aria-hidden="true">
								<?php
								if ( is_callable( $icon_renderer ) ) {
									call_user_func( $icon_renderer, (string) $card['icon'] );
								}
								?>
							</span>
							<h3 class="sg-ac-related__title"><?php echo esc_html( (string) $card['title'] ); ?></h3>
							<p class="sg-ac-related__desc"><?php echo esc_html( (string) $card['desc'] ); ?></p>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}
