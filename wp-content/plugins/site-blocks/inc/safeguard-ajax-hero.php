<?php
/**
 * Reusable Ajax-style split hero (dot grid, trust chips, floating caption).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Ajax-style hero styles.
 */
function site_blocks_enqueue_ajax_style_hero_assets(): void {
	static $done = false;

	if ( $done ) {
		return;
	}

	$done = true;

	if ( ! function_exists( 'site_blocks_enqueue_safeguard_fonts' ) ) {
		return;
	}

	site_blocks_enqueue_safeguard_fonts( 'safeguard-ajax-hero-fonts' );
	$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-ajax-hero-fonts' );
	site_blocks_enqueue_safeguard_style( 'safeguard-ajax-hero', 'safeguard-ajax-hero.css', $dep );
}

/**
 * Default Ajax alarm systems hero content.
 *
 * @return array<string, mixed>
 */
function site_blocks_default_ajax_alarm_hero_config(): array {
	return array(
		'heading_id'      => 'sg-ajax-hero-heading',
		'section_class'   => '',
		'eyebrow'         => __( 'Ajax alarm systems · Australia', 'site-blocks' ),
		'title'           => __( 'Ajax alarm systems, professionally installed by Safeguard', 'site-blocks' ),
		'lead'            => __( 'We design, install and configure Ajax wireless alarm systems for homes, apartments and small businesses across Australia, specified around your property, not sold as a generic kit.', 'site-blocks' ),
		'primary_label'   => __( 'Design my Ajax system', 'site-blocks' ),
		'primary_url'     => home_url( '/ajax-calculator/' ),
		'secondary_label' => __( 'Call 1300 225 276', 'site-blocks' ),
		'secondary_url'   => 'tel:1300225276',
		'trust_aria_label'=> __( 'What we include', 'site-blocks' ),
		'trust_chips'     => array(
			array(
				'icon'  => 'wifi.png',
				'line1' => __( 'Wireless alarm', 'site-blocks' ),
				'line2' => __( 'design', 'site-blocks' ),
			),
			array(
				'icon'  => 'app.png',
				'line1' => __( 'App setup and', 'site-blocks' ),
				'line2' => __( 'handover', 'site-blocks' ),
			),
			array(
				'icon'  => 'shield.png',
				'line1' => __( 'Monitoring', 'site-blocks' ),
				'line2' => __( 'options', 'site-blocks' ),
			),
			array(
				'icon'  => 'wired.png',
				'line1' => __( 'Wired upgrade', 'site-blocks' ),
				'line2' => __( 'path', 'site-blocks' ),
			),
		),
		'caption_title'   => __( 'Designed as a complete system, not a box of parts', 'site-blocks' ),
		'caption_items'   => array(
			array(
				'icon'  => 'hub.png',
				'title' => __( 'Hub', 'site-blocks' ),
				'desc'  => __( 'Central control', 'site-blocks' ),
			),
			array(
				'icon'  => 'jeweller.png',
				'title' => __( 'Jeweller', 'site-blocks' ),
				'desc'  => __( 'Wireless devices', 'site-blocks' ),
			),
			array(
				'icon'  => '4g.png',
				'title' => __( '4G / IP', 'site-blocks' ),
				'desc'  => __( 'Monitoring paths', 'site-blocks' ),
			),
		),
		'hero_image_url'  => site_blocks_asset_url( 'images/ajax/ajax-hero-house.webp' ),
		'hero_alt'        => __( 'Ajax Hub and wireless security devices protecting a modern Australian home', 'site-blocks' ),
		'icon_renderer'   => 'site_blocks_ajax_hero_icon',
	);
}

/**
 * Render a trust/caption icon or placeholder.
 *
 * @param string        $icon      Icon filename or empty for placeholder.
 * @param callable|null $renderer  Optional render callback.
 */
function site_blocks_ajax_style_hero_icon( string $icon, $renderer = null ): void {
	if ( is_string( $renderer ) && function_exists( $renderer ) && '' !== $icon ) {
		call_user_func( $renderer, $icon );
		return;
	}

	if ( is_callable( $renderer ) && '' !== $icon ) {
		call_user_func( $renderer, $icon );
		return;
	}

	if ( '' !== $icon && function_exists( 'site_blocks_ajax_hero_icon' ) ) {
		site_blocks_ajax_hero_icon( $icon );
		return;
	}

	echo '<span class="sg-ajax-hero__icon-placeholder" aria-hidden="true"></span>';
}

/**
 * Render Ajax-style split hero.
 *
 * Pass `title` OR `title_before` + optional `title_accent` + `title_after`.
 * Pass `hero_render` callable OR `hero_image_url` + `hero_alt`.
 *
 * @param array<string, mixed> $config Hero content.
 */
function site_blocks_render_ajax_style_hero( array $config ): void {
	site_blocks_enqueue_ajax_style_hero_assets();

	$heading_id       = (string) $config['heading_id'];
	$section_class    = isset( $config['section_class'] ) ? (string) $config['section_class'] : '';
	$eyebrow          = (string) $config['eyebrow'];
	$lead             = (string) $config['lead'];
	$primary_label    = (string) $config['primary_label'];
	$primary_url      = (string) $config['primary_url'];
	$secondary_label  = (string) $config['secondary_label'];
	$secondary_url    = (string) $config['secondary_url'];
	$trust_aria_label = isset( $config['trust_aria_label'] ) ? (string) $config['trust_aria_label'] : __( 'What we include', 'site-blocks' );
	$caption_title    = isset( $config['caption_title'] ) ? (string) $config['caption_title'] : '';
	$trust_chips      = is_array( $config['trust_chips'] ?? null ) ? $config['trust_chips'] : array();
	$caption_items    = is_array( $config['caption_items'] ?? null ) ? $config['caption_items'] : array();
	$icon_renderer    = $config['icon_renderer'] ?? null;
	$hero_alt         = isset( $config['hero_alt'] ) ? (string) $config['hero_alt'] : '';
	$hero_image_url   = isset( $config['hero_image_url'] ) ? (string) $config['hero_image_url'] : '';
	$hero_render      = $config['hero_render'] ?? null;

	if ( isset( $config['title'] ) ) {
		$title_html = esc_html( (string) $config['title'] );
	} else {
		$title_before = (string) ( $config['title_before'] ?? '' );
		$title_accent = (string) ( $config['title_accent'] ?? '' );
		$title_after  = (string) ( $config['title_after'] ?? '' );
		$title_html   = esc_html( $title_before );
		if ( '' !== $title_accent ) {
			$title_html .= '<span class="sg-accent">' . esc_html( $title_accent ) . '</span>';
		}
		$title_html .= esc_html( $title_after );
	}

	$classes = trim( 'sg-ajax-hero ' . $section_class );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-ajax-hero__grid-bg" aria-hidden="true"></div>
		<div class="sg-container">
			<div class="sg-ajax-hero__grid">
				<div class="sg-ajax-hero__copy">
					<p class="sg-ajax-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<h1 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-ajax-hero__title"><?php echo $title_html; ?></h1>
					<p class="sg-ajax-hero__lead"><?php echo esc_html( $lead ); ?></p>

					<div class="sg-ajax-hero__actions">
						<div class="sg-hero__ctas sg-ajax-hero__ctas">
							<a class="sg-btn sg-btn--soft-blue sg-ajax-hero__cta" href="<?php echo esc_url( $primary_url ); ?>">
								<?php echo esc_html( $primary_label ); ?>
							</a>
							<a class="sg-btn sg-btn--secondary sg-ajax-hero__cta" href="<?php echo esc_attr( $secondary_url ); ?>">
								<?php echo esc_html( $secondary_label ); ?>
							</a>
						</div>

						<?php if ( $trust_chips !== array() ) : ?>
							<div class="sg-ajax-hero__trust" aria-label="<?php echo esc_attr( $trust_aria_label ); ?>">
								<ul class="sg-ajax-hero__trust-list" role="list">
									<?php foreach ( $trust_chips as $item ) : ?>
										<li class="sg-ajax-hero__trust-item">
											<span class="sg-ajax-hero__trust-icon" aria-hidden="true">
												<?php site_blocks_ajax_style_hero_icon( (string) ( $item['icon'] ?? '' ), $icon_renderer ); ?>
											</span>
											<span class="sg-ajax-hero__trust-label">
												<span class="sg-ajax-hero__trust-line"><?php echo esc_html( (string) ( $item['line1'] ?? '' ) ); ?></span>
												<span class="sg-ajax-hero__trust-line"><?php echo esc_html( (string) ( $item['line2'] ?? '' ) ); ?></span>
											</span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="sg-ajax-hero__visual">
					<div class="sg-ajax-hero__visual-frame">
						<?php
						if ( is_callable( $hero_render ) ) {
							call_user_func( $hero_render );
						} elseif ( '' !== $hero_image_url ) {
							printf(
								'<img class="sg-ajax-hero__img" src="%s" alt="%s" width="840" height="600" loading="eager" decoding="async" />',
								esc_url( $hero_image_url ),
								esc_attr( $hero_alt )
							);
						} else {
							echo '<span class="sg-cctv-media-placeholder sg-ajax-hero__img-placeholder" aria-hidden="true"></span>';
						}
						?>

						<?php if ( '' !== $caption_title && $caption_items !== array() ) : ?>
							<div class="sg-ajax-hero__caption">
								<p class="sg-ajax-hero__caption-title"><?php echo esc_html( $caption_title ); ?></p>
								<ul class="sg-ajax-hero__caption-list" role="list">
									<?php foreach ( $caption_items as $note ) : ?>
										<li class="sg-ajax-hero__caption-item">
											<span class="sg-ajax-hero__caption-icon" aria-hidden="true">
												<?php site_blocks_ajax_style_hero_icon( (string) ( $note['icon'] ?? '' ), $icon_renderer ); ?>
											</span>
											<span class="sg-ajax-hero__caption-label"><?php echo esc_html( (string) ( $note['title'] ?? '' ) ); ?></span>
											<span class="sg-ajax-hero__caption-desc"><?php echo esc_html( (string) ( $note['desc'] ?? '' ) ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}
