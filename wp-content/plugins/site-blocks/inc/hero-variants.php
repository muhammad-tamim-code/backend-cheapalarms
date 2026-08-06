<?php
/**
 * Distinct light hero topologies (V1–V5) for Safeguard service pages.
 *
 * V0 (Ajax split) stays in safeguard-ajax-hero.php. Homepage is untouched.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue shared hero-variant styles (depends on safeguard-home tokens).
 */
function site_blocks_enqueue_hero_variants_assets(): void {
	if ( wp_style_is( 'safeguard-hero-variants', 'enqueued' ) ) {
		return;
	}

	$dep = 'safeguard-home';
	if ( ! wp_style_is( $dep, 'enqueued' ) && ! wp_style_is( $dep, 'registered' ) ) {
		$dep = 'wp-block-library';
	}

	wp_enqueue_style(
		'safeguard-hero-variants',
		SITE_BLOCKS_URL . 'assets/css/safeguard-hero-variants.css',
		array( $dep ),
		SITE_BLOCKS_VERSION
	);
}

/**
 * Render a breadcrumb trail inside a hero variant.
 *
 * @param array<int, array{label: string, url?: string, current?: bool}> $breadcrumb Crumbs.
 */
function site_blocks_hero_variant_breadcrumb( array $breadcrumb ): void {
	if ( $breadcrumb === array() ) {
		return;
	}
	?>
	<nav class="sg-hv__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
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
	<?php
}

/**
 * Render title with optional accent span.
 *
 * @param string $id           Heading element id.
 * @param string $title_before Text before accent.
 * @param string $title_accent Accent text.
 * @param string $title_after  Text after accent.
 * @param string $class        Heading class.
 */
function site_blocks_hero_variant_title( string $id, string $title_before, string $title_accent, string $title_after, string $class ): void {
	?>
	<h1 id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
		<?php echo esc_html( $title_before ); ?>
		<?php if ( '' !== $title_accent ) : ?>
			<span class="sg-hv__accent"><?php echo esc_html( $title_accent ); ?></span>
		<?php endif; ?>
		<?php echo esc_html( $title_after ); ?>
	</h1>
	<?php
}

/**
 * Image placeholder when no asset is available.
 *
 * @param string $label Optional short label (decorative).
 * @param string $class Extra class.
 */
function site_blocks_hero_variant_placeholder( string $label = '', string $class = '' ): void {
	$classes = trim( 'sg-hv__ph ' . $class );
	$label   = '' !== $label ? $label : __( 'Image placeholder', 'site-blocks' );
	printf(
		'<div class="%s" aria-hidden="true"><span>%s</span></div>',
		esc_attr( $classes ),
		esc_html( $label )
	);
}

/**
 * Render media: callable visual, readable asset URL, or placeholder.
 *
 * @param callable|null $visual Optional render callback.
 * @param string        $url    Optional public image URL.
 * @param string        $alt    Alt text when using URL.
 * @param string        $img_class Image class when using URL.
 * @param string        $ph_label Placeholder label.
 */
function site_blocks_hero_variant_media( ?callable $visual, string $url = '', string $alt = '', string $img_class = 'sg-hv__img', string $ph_label = '' ): void {
	if ( is_callable( $visual ) ) {
		$visual();
		return;
	}

	if ( '' !== $url ) {
		printf(
			'<img class="%s" src="%s" alt="%s" width="1600" height="900" loading="eager" decoding="async" />',
			esc_attr( $img_class ),
			esc_url( $url ),
			esc_attr( $alt )
		);
		return;
	}

	site_blocks_hero_variant_placeholder( $ph_label );
}

/**
 * Resolve a plugin asset to a URL only when the file exists on disk.
 *
 * @param string $relative_path Path under assets/.
 */
function site_blocks_hero_variant_asset_url_if_exists( string $relative_path ): string {
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );
	$relative_path = site_blocks_resolve_asset_path( $relative_path );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	if ( ! is_readable( $disk ) ) {
		return '';
	}

	return site_blocks_asset_url( $relative_path );
}

/**
 * Render a distinct hero topology.
 *
 * @param string               $variant One of: stack, bleed, deck, rail, editorial, split.
 * @param array<string, mixed> $args    Hero content (see each variant).
 */
function site_blocks_render_hero_variant( string $variant, array $args ): void {
	site_blocks_enqueue_hero_variants_assets();

	$allowed = array( 'stack', 'bleed', 'deck', 'rail', 'editorial', 'split' );
	if ( ! in_array( $variant, $allowed, true ) ) {
		$variant = 'stack';
	}

	switch ( $variant ) {
		case 'bleed':
			site_blocks_render_hero_variant_bleed( $args );
			break;
		case 'deck':
			site_blocks_render_hero_variant_deck( $args );
			break;
		case 'rail':
			site_blocks_render_hero_variant_rail( $args );
			break;
		case 'editorial':
			site_blocks_render_hero_variant_editorial( $args );
			break;
		case 'split':
			site_blocks_render_hero_variant_split( $args );
			break;
		case 'stack':
		default:
			site_blocks_render_hero_variant_stack( $args );
			break;
	}
}

/**
 * V1 — Vertical stack: copy full width, media band below.
 *
 * @param array<string, mixed> $args Args.
 */
function site_blocks_render_hero_variant_stack( array $args ): void {
	$heading_id   = sanitize_html_class( (string) ( $args['id'] ?? 'sg-hv-stack-heading' ) );
	$class        = trim( 'sg-hv sg-hv--stack alignfull ' . (string) ( $args['class'] ?? '' ) );
	$breadcrumb   = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$title_accent = (string) ( $args['title_accent'] ?? '' );
	$title_after  = (string) ( $args['title_after'] ?? '' );
	$footnote     = (string) ( $args['footnote'] ?? '' );
	$visual       = isset( $args['visual'] ) && is_callable( $args['visual'] ) ? $args['visual'] : null;
	$image_url    = (string) ( $args['image_url'] ?? '' );
	$image_alt    = (string) ( $args['image_alt'] ?? '' );
	?>
	<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container sg-hv--stack__inner">
			<div class="sg-hv--stack__copy">
				<?php site_blocks_hero_variant_breadcrumb( $breadcrumb ); ?>
				<p class="sg-hv__badge"><?php echo esc_html( (string) ( $args['badge'] ?? '' ) ); ?></p>
				<?php
				site_blocks_hero_variant_title(
					$heading_id,
					(string) ( $args['title_before'] ?? '' ),
					$title_accent,
					$title_after,
					'sg-hv__title'
				);
				?>
				<p class="sg-hv__lead"><?php echo esc_html( (string) ( $args['lead'] ?? '' ) ); ?></p>
				<div class="sg-hv__ctas">
					<a class="sg-btn sg-btn--soft-blue" href="<?php echo esc_url( (string) ( $args['primary_url'] ?? '#' ) ); ?>">
						<?php echo esc_html( (string) ( $args['primary_label'] ?? '' ) ); ?>
					</a>
					<a class="sg-btn sg-btn--secondary" href="<?php echo esc_url( (string) ( $args['secondary_url'] ?? '#' ) ); ?>">
						<?php echo esc_html( (string) ( $args['secondary_label'] ?? '' ) ); ?>
					</a>
				</div>
				<?php if ( '' !== $footnote ) : ?>
					<p class="sg-hv__note"><?php echo esc_html( $footnote ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="sg-hv--stack__media" aria-hidden="<?php echo is_callable( $visual ) || '' !== $image_url ? 'false' : 'true'; ?>">
			<?php site_blocks_hero_variant_media( $visual, $image_url, $image_alt, 'sg-hv__img sg-hv--stack__img', __( 'Hero image', 'site-blocks' ) ); ?>
		</div>
	</section>
	<?php
}

/**
 * Light split: copy on white left, full-bleed photo right with soft edge fade.
 * Optional `features` list: array of { icon: lucide-name, label: string }.
 * Optional CTA icons: `primary_icon`, `secondary_icon` (lucide names).
 * Optional `secondary_class` (default `sg-btn--secondary` — transparent blue on light).
 *
 * @param array<string, mixed> $args Args.
 */
function site_blocks_render_hero_variant_split( array $args ): void {
	require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

	$heading_id      = sanitize_html_class( (string) ( $args['id'] ?? 'sg-hv-split-heading' ) );
	$class           = trim( 'sg-hv sg-hv--split alignfull ' . (string) ( $args['class'] ?? '' ) );
	$breadcrumb      = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$title_accent    = (string) ( $args['title_accent'] ?? '' );
	$title_after     = (string) ( $args['title_after'] ?? '' );
	$visual          = isset( $args['visual'] ) && is_callable( $args['visual'] ) ? $args['visual'] : null;
	$image_url       = (string) ( $args['image_url'] ?? '' );
	$image_alt       = (string) ( $args['image_alt'] ?? '' );
	$features        = isset( $args['features'] ) && is_array( $args['features'] ) ? $args['features'] : array();
	$primary_icon    = (string) ( $args['primary_icon'] ?? '' );
	$secondary_icon  = (string) ( $args['secondary_icon'] ?? '' );
	$secondary_class = (string) ( $args['secondary_class'] ?? 'sg-btn--secondary' );
	$has_media       = is_callable( $visual ) || '' !== $image_url;
	?>
	<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container sg-hv--split__inner">
			<div class="sg-hv--split__stage">
				<div class="sg-hv--split__copy">
					<?php site_blocks_hero_variant_breadcrumb( $breadcrumb ); ?>
					<p class="sg-hv__badge"><?php echo esc_html( (string) ( $args['badge'] ?? '' ) ); ?></p>
					<?php
					site_blocks_hero_variant_title(
						$heading_id,
						(string) ( $args['title_before'] ?? '' ),
						$title_accent,
						$title_after,
						'sg-hv__title'
					);
					?>
					<p class="sg-hv__lead"><?php echo esc_html( (string) ( $args['lead'] ?? '' ) ); ?></p>
					<div class="sg-hv__ctas">
						<a class="sg-btn sg-btn--soft-blue sg-hv--split__btn" href="<?php echo esc_url( (string) ( $args['primary_url'] ?? '#' ) ); ?>">
							<?php if ( '' !== $primary_icon ) : ?>
								<span class="sg-hv--split__btn-icon" aria-hidden="true"><?php site_blocks_lucide_icon( $primary_icon, 18 ); ?></span>
							<?php endif; ?>
							<?php echo esc_html( (string) ( $args['primary_label'] ?? '' ) ); ?>
						</a>
						<a class="sg-btn <?php echo esc_attr( $secondary_class ); ?> sg-hv--split__btn" href="<?php echo esc_url( (string) ( $args['secondary_url'] ?? '#' ) ); ?>">
							<?php if ( '' !== $secondary_icon ) : ?>
								<span class="sg-hv--split__btn-icon" aria-hidden="true"><?php site_blocks_lucide_icon( $secondary_icon, 18 ); ?></span>
							<?php endif; ?>
							<?php echo esc_html( (string) ( $args['secondary_label'] ?? '' ) ); ?>
						</a>
					</div>
				</div>
				<div class="sg-hv--split__media" aria-hidden="<?php echo $has_media ? 'false' : 'true'; ?>">
					<?php if ( $has_media ) : ?>
						<?php site_blocks_hero_variant_media( $visual, $image_url, $image_alt, 'sg-hv__img sg-hv--split__img', __( 'Hero image', 'site-blocks' ) ); ?>
					<?php else : ?>
						<?php site_blocks_hero_variant_placeholder( __( 'Hero image', 'site-blocks' ), 'sg-hv--split__ph' ); ?>
					<?php endif; ?>
					<span class="sg-hv--split__fade" aria-hidden="true"></span>
				</div>
			</div>
			<?php if ( $features !== array() ) : ?>
				<div class="sg-hv--split__features" aria-label="<?php esc_attr_e( 'Service highlights', 'site-blocks' ); ?>">
					<ul class="sg-hv--split__features-list" role="list">
						<?php foreach ( $features as $feature ) : ?>
							<?php
							if ( ! is_array( $feature ) ) {
								continue;
							}
							$icon  = (string) ( $feature['icon'] ?? '' );
							$label = (string) ( $feature['label'] ?? '' );
							if ( '' === $label ) {
								continue;
							}
							?>
							<li>
								<?php if ( '' !== $icon ) : ?>
									<span class="sg-hv--split__feature-icon" aria-hidden="true"><?php site_blocks_lucide_icon( $icon, 20 ); ?></span>
								<?php endif; ?>
								<span><?php echo esc_html( $label ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * V2 — Full-bleed photo with light left wash + overlay copy + feature banner.
 *
 * Optional `features` list: array of { icon: lucide-name, label: string }.
 * Optional CTA icons: `primary_icon`, `secondary_icon` (lucide names).
 *
 * @param array<string, mixed> $args Args.
 */
function site_blocks_render_hero_variant_bleed( array $args ): void {
	require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

	$heading_id     = sanitize_html_class( (string) ( $args['id'] ?? 'sg-hv-bleed-heading' ) );
	$class          = trim( 'sg-hv sg-hv--bleed alignfull ' . (string) ( $args['class'] ?? '' ) );
	$breadcrumb     = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$title_accent   = (string) ( $args['title_accent'] ?? '' );
	$title_after    = (string) ( $args['title_after'] ?? '' );
	$footnote       = (string) ( $args['footnote'] ?? '' );
	$visual         = isset( $args['visual'] ) && is_callable( $args['visual'] ) ? $args['visual'] : null;
	$image_url      = (string) ( $args['image_url'] ?? '' );
	$image_alt      = (string) ( $args['image_alt'] ?? '' );
	$features       = isset( $args['features'] ) && is_array( $args['features'] ) ? $args['features'] : array();
	$primary_icon   = (string) ( $args['primary_icon'] ?? '' );
	$secondary_icon = (string) ( $args['secondary_icon'] ?? '' );
	$has_media      = is_callable( $visual ) || '' !== $image_url;
	?>
	<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-hv--bleed__bg" aria-hidden="true">
			<?php if ( $has_media ) : ?>
				<?php site_blocks_hero_variant_media( $visual, $image_url, $image_alt, 'sg-hv--bleed__bg-img', '' ); ?>
			<?php else : ?>
				<?php site_blocks_hero_variant_placeholder( __( 'Background image', 'site-blocks' ), 'sg-hv--bleed__ph' ); ?>
			<?php endif; ?>
			<span class="sg-hv--bleed__wash"></span>
		</div>
		<div class="sg-container sg-hv--bleed__inner">
			<div class="sg-hv--bleed__copy">
				<?php site_blocks_hero_variant_breadcrumb( $breadcrumb ); ?>
				<p class="sg-hv__badge"><?php echo esc_html( (string) ( $args['badge'] ?? '' ) ); ?></p>
				<?php
				site_blocks_hero_variant_title(
					$heading_id,
					(string) ( $args['title_before'] ?? '' ),
					$title_accent,
					$title_after,
					'sg-hv__title'
				);
				?>
				<p class="sg-hv__lead"><?php echo esc_html( (string) ( $args['lead'] ?? '' ) ); ?></p>
				<div class="sg-hv__ctas">
					<a class="sg-btn sg-btn--soft-blue sg-hv--bleed__btn" href="<?php echo esc_url( (string) ( $args['primary_url'] ?? '#' ) ); ?>">
						<?php if ( '' !== $primary_icon ) : ?>
							<span class="sg-hv--bleed__btn-icon" aria-hidden="true"><?php site_blocks_lucide_icon( $primary_icon, 18 ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( (string) ( $args['primary_label'] ?? '' ) ); ?>
					</a>
					<a class="sg-btn sg-btn--secondary sg-hv--bleed__btn" href="<?php echo esc_url( (string) ( $args['secondary_url'] ?? '#' ) ); ?>">
						<?php if ( '' !== $secondary_icon ) : ?>
							<span class="sg-hv--bleed__btn-icon" aria-hidden="true"><?php site_blocks_lucide_icon( $secondary_icon, 18 ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( (string) ( $args['secondary_label'] ?? '' ) ); ?>
					</a>
				</div>
				<?php if ( '' !== $footnote ) : ?>
					<p class="sg-hv__note"><?php echo esc_html( $footnote ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $features !== array() ) : ?>
				<div class="sg-hv--split__features sg-hv--bleed__banner" aria-label="<?php esc_attr_e( 'Service highlights', 'site-blocks' ); ?>">
					<ul class="sg-hv--split__features-list" role="list">
						<?php foreach ( $features as $feature ) : ?>
							<?php
							if ( ! is_array( $feature ) ) {
								continue;
							}
							$icon  = (string) ( $feature['icon'] ?? '' );
							$label = (string) ( $feature['label'] ?? '' );
							if ( '' === $label ) {
								continue;
							}
							?>
							<li>
								<?php if ( '' !== $icon ) : ?>
									<span class="sg-hv--split__feature-icon" aria-hidden="true"><?php site_blocks_lucide_icon( $icon, 20 ); ?></span>
								<?php endif; ?>
								<span><?php echo esc_html( $label ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * V3 — Range deck: intro + cards are the hero.
 *
 * @param array<string, mixed> $args Args. Expects `cards` list.
 */
function site_blocks_render_hero_variant_deck( array $args ): void {
	$heading_id   = sanitize_html_class( (string) ( $args['id'] ?? 'sg-hv-deck-heading' ) );
	$class        = trim( 'sg-hv sg-hv--deck alignfull ' . (string) ( $args['class'] ?? '' ) );
	$breadcrumb   = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$title_accent = (string) ( $args['title_accent'] ?? '' );
	$title_after  = (string) ( $args['title_after'] ?? '' );
	$cards        = isset( $args['cards'] ) && is_array( $args['cards'] ) ? $args['cards'] : array();
	?>
	<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container">
			<?php site_blocks_hero_variant_breadcrumb( $breadcrumb ); ?>
			<div class="sg-hv--deck__head">
				<p class="sg-hv__badge"><?php echo esc_html( (string) ( $args['badge'] ?? '' ) ); ?></p>
				<?php
				site_blocks_hero_variant_title(
					$heading_id,
					(string) ( $args['title_before'] ?? '' ),
					$title_accent,
					$title_after,
					'sg-hv__title'
				);
				?>
				<p class="sg-hv__lead"><?php echo esc_html( (string) ( $args['lead'] ?? '' ) ); ?></p>
				<div class="sg-hv__ctas sg-hv__ctas--center">
					<a class="sg-btn sg-btn--soft-blue" href="<?php echo esc_url( (string) ( $args['primary_url'] ?? '#' ) ); ?>">
						<?php echo esc_html( (string) ( $args['primary_label'] ?? '' ) ); ?>
					</a>
					<a class="sg-btn sg-btn--secondary" href="<?php echo esc_url( (string) ( $args['secondary_url'] ?? '#' ) ); ?>">
						<?php echo esc_html( (string) ( $args['secondary_label'] ?? '' ) ); ?>
					</a>
				</div>
			</div>

			<?php if ( $cards !== array() ) : ?>
				<ul class="sg-hv--deck__grid">
					<?php foreach ( $cards as $card ) : ?>
						<?php
						if ( ! is_array( $card ) ) {
							continue;
						}
						$title   = (string) ( $card['title'] ?? '' );
						$desc    = (string) ( $card['desc'] ?? '' );
						$url     = (string) ( $card['url'] ?? '' );
						$img_url = (string) ( $card['image_url'] ?? '' );
						$img_alt = (string) ( $card['image_alt'] ?? $title );
						$visual  = isset( $card['visual'] ) && is_callable( $card['visual'] ) ? $card['visual'] : null;
						?>
						<li class="sg-hv--deck__tile">
							<?php if ( '' !== $url ) : ?>
								<a class="sg-hv--deck__link" href="<?php echo esc_url( $url ); ?>">
							<?php else : ?>
								<div class="sg-hv--deck__link">
							<?php endif; ?>
								<div class="sg-hv--deck__media">
									<?php site_blocks_hero_variant_media( $visual, $img_url, $img_alt, 'sg-hv__img', $title ); ?>
								</div>
								<div class="sg-hv--deck__body">
									<strong><?php echo esc_html( $title ); ?></strong>
									<?php if ( '' !== $desc ) : ?>
										<span><?php echo esc_html( $desc ); ?></span>
									<?php endif; ?>
								</div>
							<?php if ( '' !== $url ) : ?>
								</a>
							<?php else : ?>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * V4 — Contained navy panel (left) + single image (right). Not full-bleed.
 *
 * @param array<string, mixed> $args Args. Uses visual / image_url like other variants.
 */
function site_blocks_render_hero_variant_rail( array $args ): void {
	$heading_id   = sanitize_html_class( (string) ( $args['id'] ?? 'sg-hv-rail-heading' ) );
	$class        = trim( 'sg-hv sg-hv--rail alignfull ' . (string) ( $args['class'] ?? '' ) );
	$breadcrumb   = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$title_accent = (string) ( $args['title_accent'] ?? '' );
	$title_after  = (string) ( $args['title_after'] ?? '' );
	$lead         = (string) ( $args['lead'] ?? '' );
	$visual       = isset( $args['visual'] ) && is_callable( $args['visual'] ) ? $args['visual'] : null;
	$image_url    = (string) ( $args['image_url'] ?? '' );
	$image_alt    = (string) ( $args['image_alt'] ?? '' );
	?>
	<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container sg-hv--rail__shell">
			<div class="sg-hv--rail__layout">
				<div class="sg-hv--rail__copy">
					<?php site_blocks_hero_variant_breadcrumb( $breadcrumb ); ?>
					<p class="sg-hv__badge sg-hv__badge--on-dark"><?php echo esc_html( (string) ( $args['badge'] ?? '' ) ); ?></p>
					<?php
					site_blocks_hero_variant_title(
						$heading_id,
						(string) ( $args['title_before'] ?? '' ),
						$title_accent,
						$title_after,
						'sg-hv__title sg-hv__title--on-dark'
					);
					?>
					<?php if ( '' !== $lead ) : ?>
						<p class="sg-hv__lead sg-hv__lead--on-dark"><?php echo esc_html( $lead ); ?></p>
					<?php endif; ?>
					<div class="sg-hv__ctas sg-hv__ctas--stack">
						<a class="sg-btn sg-btn--soft-orange" href="<?php echo esc_url( (string) ( $args['primary_url'] ?? '#' ) ); ?>">
							<?php echo esc_html( (string) ( $args['primary_label'] ?? '' ) ); ?>
						</a>
						<a class="sg-btn sg-btn--ghost" href="<?php echo esc_url( (string) ( $args['secondary_url'] ?? '#' ) ); ?>">
							<?php echo esc_html( (string) ( $args['secondary_label'] ?? '' ) ); ?>
						</a>
					</div>
				</div>
				<div class="sg-hv--rail__media">
					<?php site_blocks_hero_variant_media( $visual, $image_url, $image_alt, 'sg-hv__img sg-hv--rail__img', __( 'Hero image', 'site-blocks' ) ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * V5 — Centered editorial (no hero image).
 *
 * @param array<string, mixed> $args Args. Optional `proof` list of {label, value}.
 */
function site_blocks_render_hero_variant_editorial( array $args ): void {
	$heading_id   = sanitize_html_class( (string) ( $args['id'] ?? 'sg-hv-editorial-heading' ) );
	$class        = trim( 'sg-hv sg-hv--editorial alignfull ' . (string) ( $args['class'] ?? '' ) );
	$breadcrumb   = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$title_accent = (string) ( $args['title_accent'] ?? '' );
	$title_after  = (string) ( $args['title_after'] ?? '' );
	$proof        = isset( $args['proof'] ) && is_array( $args['proof'] ) ? $args['proof'] : array();
	$phone_html   = (string) ( $args['phone_html'] ?? '' );
	?>
	<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container sg-hv--editorial__inner">
			<?php site_blocks_hero_variant_breadcrumb( $breadcrumb ); ?>
			<p class="sg-hv__badge"><?php echo esc_html( (string) ( $args['badge'] ?? '' ) ); ?></p>
			<?php
			site_blocks_hero_variant_title(
				$heading_id,
				(string) ( $args['title_before'] ?? '' ),
				$title_accent,
				$title_after,
				'sg-hv__title'
			);
			?>
			<p class="sg-hv__lead"><?php echo esc_html( (string) ( $args['lead'] ?? '' ) ); ?></p>
			<?php if ( '' !== $phone_html ) : ?>
				<p class="sg-hv--editorial__phone"><?php echo $phone_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* by caller. ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $args['primary_url'] ) || ! empty( $args['secondary_url'] ) ) : ?>
				<div class="sg-hv__ctas sg-hv__ctas--center">
					<?php if ( ! empty( $args['primary_url'] ) ) : ?>
						<a class="sg-btn sg-btn--soft-blue" href="<?php echo esc_url( (string) $args['primary_url'] ); ?>">
							<?php echo esc_html( (string) ( $args['primary_label'] ?? '' ) ); ?>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $args['secondary_url'] ) ) : ?>
						<a class="sg-btn sg-btn--secondary" href="<?php echo esc_url( (string) $args['secondary_url'] ); ?>">
							<?php echo esc_html( (string) ( $args['secondary_label'] ?? '' ) ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $proof !== array() ) : ?>
				<ul class="sg-hv--editorial__proof">
					<?php foreach ( $proof as $item ) : ?>
						<?php
						if ( ! is_array( $item ) ) {
							continue;
						}
						?>
						<li>
							<strong><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></strong>
							<span><?php echo esc_html( (string) ( $item['value'] ?? '' ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
