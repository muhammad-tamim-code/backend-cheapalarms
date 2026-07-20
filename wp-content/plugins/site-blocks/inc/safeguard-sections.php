<?php
/**
 * Shared Safeguard page section renderers (FAQ, quote CTA, etc.).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-faq.php';

/**
 * Default quote CTA button labels and URLs.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_default_quote_ctas(): array {
	return array(
		'primary_label'   => __( 'Start My Quote', 'site-blocks' ),
		'primary_url'     => home_url( '/get-an-instant-quote/' ),
		'secondary_label' => __( 'Help Me Choose', 'site-blocks' ),
		'secondary_url'   => home_url( '/design-my-solution/' ),
	);
}

/**
 * Render a two-column FAQ section.
 *
 * @param array<int, array{q: string, a: string}> $items FAQ items.
 * @param array{
 *   heading_id: string,
 *   heading_before: string,
 *   heading_accent: string,
 *   heading_after?: string,
 *   id_prefix: string,
 *   section_class?: string,
 *   columns_split?: int|null,
 *   element?: string,
 *   alignfull?: bool,
 * } $args Section options.
 */
function site_blocks_render_faq_section( array $items, array $args ): void {
	if ( $items === array() ) {
		return;
	}

	$heading_id     = (string) $args['heading_id'];
	$heading_before = (string) $args['heading_before'];
	$heading_accent = (string) $args['heading_accent'];
	$heading_after  = isset( $args['heading_after'] ) ? (string) $args['heading_after'] : '';
	$id_prefix      = (string) $args['id_prefix'];
	$section_class  = isset( $args['section_class'] ) ? (string) $args['section_class'] : '';
	$element        = isset( $args['element'] ) ? (string) $args['element'] : 'section';
	$alignfull      = ! isset( $args['alignfull'] ) || (bool) $args['alignfull'];

	$split = isset( $args['columns_split'] ) && null !== $args['columns_split']
		? (int) $args['columns_split']
		: (int) ceil( count( $items ) / 2 );

	$classes = trim( 'sg-value-row sg-value-row--peach ' . $section_class . ( $alignfull ? ' alignfull' : '' ) );
	?>
	<<?php echo tag_escape( $element ); ?> class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container sg-value-row__grid">
			<div class="sg-value-row__copy">
				<h2 class="sg-value-row__title" id="<?php echo esc_attr( $heading_id ); ?>">
					<?php echo esc_html( $heading_before ); ?>
					<span class="sg-accent"><?php echo esc_html( $heading_accent ); ?></span>
					<?php echo esc_html( $heading_after ); ?>
				</h2>
			</div>
			<div class="sg-value-row__content sg-value-row__content--faq">
				<div class="sg-value-faq">
					<div class="sg-value-faq__column">
						<?php
						foreach ( array_slice( $items, 0, $split ) as $faq_index => $faq_item ) {
							site_blocks_render_value_faq_item( $faq_item, $faq_index + 1, $id_prefix );
						}
						?>
					</div>
					<div class="sg-value-faq__column">
						<?php
						foreach ( array_slice( $items, $split ) as $faq_index => $faq_item ) {
							site_blocks_render_value_faq_item( $faq_item, $faq_index + 1 + $split, $id_prefix );
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</<?php echo tag_escape( $element ); ?>>
	<?php
}

/**
 * Render the standard navy quote CTA band (.sg-cta-card).
 *
 * @param array{
 *   heading_id: string,
 *   before: string,
 *   accent: string,
 *   after?: string,
 *   sub: string,
 *   primary_label?: string,
 *   primary_url?: string,
 *   secondary_label?: string,
 *   secondary_url?: string,
 *   section_class?: string,
 *   alignfull?: bool,
 * } $config CTA content and button targets.
 */
function site_blocks_render_quote_cta( array $config ): void {
	$defaults = site_blocks_default_quote_ctas();

	$heading_id      = (string) $config['heading_id'];
	$before          = (string) $config['before'];
	$accent          = (string) $config['accent'];
	$after           = isset( $config['after'] ) ? (string) $config['after'] : '';
	$sub             = (string) $config['sub'];
	$primary_label   = isset( $config['primary_label'] ) ? (string) $config['primary_label'] : $defaults['primary_label'];
	$primary_url     = isset( $config['primary_url'] ) ? (string) $config['primary_url'] : $defaults['primary_url'];
	$secondary_label = isset( $config['secondary_label'] ) ? (string) $config['secondary_label'] : $defaults['secondary_label'];
	$secondary_url   = isset( $config['secondary_url'] ) ? (string) $config['secondary_url'] : $defaults['secondary_url'];
	$section_class   = isset( $config['section_class'] ) ? (string) $config['section_class'] : '';
	$alignfull       = ! isset( $config['alignfull'] ) || (bool) $config['alignfull'];

	$classes = trim( 'sg-cta ' . $section_class . ( $alignfull ? ' alignfull' : '' ) );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container">
			<div class="sg-cta-card">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-cta-card__head">
					<?php echo esc_html( $before ); ?>
					<span class="sg-accent"><?php echo esc_html( $accent ); ?></span>
					<?php echo esc_html( $after ); ?>
				</h2>
				<p class="sg-cta-card__text"><?php echo esc_html( $sub ); ?></p>
				<div class="sg-cta-card__btns">
					<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( $primary_url ); ?>">
						<?php echo esc_html( $primary_label ); ?>
					</a>
					<a class="sg-btn sg-btn--cta-ghost" href="<?php echo esc_url( $secondary_url ); ?>">
						<?php echo esc_html( $secondary_label ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render hero trust credential strip (.sg-ac-trust-strip).
 *
 * @param array{
 *   items: array<int, string|array{html: string}>,
 *   section_class?: string,
 *   aria_label?: string,
 *   alignfull?: bool,
 * } $config Strip content.
 */
function site_blocks_render_trust_strip( array $config ): void {
	$items = $config['items'] ?? array();

	if ( $items === array() ) {
		return;
	}

	$section_class = isset( $config['section_class'] ) ? (string) $config['section_class'] : '';
	$aria_label    = isset( $config['aria_label'] ) ? (string) $config['aria_label'] : __( 'Trust credentials', 'site-blocks' );
	$alignfull     = ! isset( $config['alignfull'] ) || (bool) $config['alignfull'];
	$classes       = trim( 'sg-ac-trust-strip ' . $section_class . ( $alignfull ? ' alignfull' : '' ) );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
		<div class="sg-container">
			<ul class="sg-ac-trust-strip__list" role="list">
				<?php foreach ( $items as $item ) : ?>
					<li>
						<?php
						if ( is_array( $item ) && isset( $item['html'] ) ) {
							echo wp_kses_post( (string) $item['html'] );
						} else {
							echo esc_html( (string) $item );
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Hub spoke link cards (chevron cards grid).
 *
 * @param array{
 *   heading_id: string,
 *   section_class: string,
 *   band?: string,
 *   title_before?: string,
 *   title_accent?: string,
 *   title_after?: string,
 *   title?: string,
 *   intro?: string,
 *   services: array<int, array{title: string, desc: string, url: string}>,
 * } $config Section content.
 */
function site_blocks_render_hub_services_grid( array $config ): void {
	$services = $config['services'] ?? array();

	if ( $services === array() ) {
		return;
	}

	$heading_id    = (string) $config['heading_id'];
	$section_class = (string) $config['section_class'];
	$band          = isset( $config['band'] ) ? (string) $config['band'] : 'blue';
	$intro         = isset( $config['intro'] ) ? (string) $config['intro'] : '';
	$classes       = trim( 'sg-band sg-band--' . $band . ' sg-hub-services ' . $section_class . ' alignfull' );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container">
			<header class="sg-alarm-services__header">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-section-title sg-section-title--center sg-section-title--ink">
					<?php if ( isset( $config['title'] ) ) : ?>
						<?php echo esc_html( (string) $config['title'] ); ?>
					<?php else : ?>
						<?php echo esc_html( (string) ( $config['title_before'] ?? '' ) ); ?>
						<?php if ( ! empty( $config['title_accent'] ) ) : ?>
							<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( (string) ( $config['title_after'] ?? '' ) ); ?>
					<?php endif; ?>
				</h2>
				<?php if ( '' !== $intro ) : ?>
					<p class="sg-section-intro sg-section-intro--center"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
			</header>

			<div class="sg-hub-services__grid" role="list">
				<?php foreach ( $services as $service ) : ?>
					<a class="sg-hub-services__card" href="<?php echo esc_url( (string) $service['url'] ); ?>" role="listitem">
						<h3 class="sg-hub-services__title"><?php echo esc_html( (string) $service['title'] ); ?></h3>
						<p class="sg-hub-services__desc"><?php echo esc_html( (string) $service['desc'] ); ?></p>
						<span class="sg-hub-services__chevron" aria-hidden="true">&rsaquo;</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Process / how-it-works step strip.
 *
 * @param array{
 *   heading_id: string,
 *   section_class: string,
 *   layout: 'hero-dark'|'band-icons',
 *   band?: string,
 *   eyebrow?: string,
 *   title_before: string,
 *   title_accent: string,
 *   title_after?: string,
 *   intro?: string,
 *   steps: array<int, array{title: string, description: string, icon?: string|callable}>,
 *   icon_renderer?: callable(string): void,
 *   icon_wrapper_class?: string,
 * } $config Section content.
 */
function site_blocks_render_process_steps( array $config ): void {
	$steps = $config['steps'] ?? array();

	if ( $steps === array() ) {
		return;
	}

	$heading_id      = (string) $config['heading_id'];
	$section_class   = (string) $config['section_class'];
	$layout          = (string) ( $config['layout'] ?? 'hero-dark' );
	$eyebrow         = isset( $config['eyebrow'] ) ? (string) $config['eyebrow'] : '';
	$title_before    = (string) $config['title_before'];
	$title_accent    = (string) $config['title_accent'];
	$title_after     = isset( $config['title_after'] ) ? (string) $config['title_after'] : '';
	$intro           = isset( $config['intro'] ) ? (string) $config['intro'] : '';
	$icon_renderer   = $config['icon_renderer'] ?? null;
	$icon_wrap_class = isset( $config['icon_wrapper_class'] ) ? (string) $config['icon_wrapper_class'] : 'sg-alarm-step-card__icon';

	if ( 'band-icons' === $layout ) {
		$band    = isset( $config['band'] ) ? (string) $config['band'] : 'blue';
		$classes = trim( 'sg-band sg-band--' . $band . ' sg-cctv-difference ' . $section_class . ' alignfull' );
		?>
		<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
			<div class="sg-container">
				<header class="sg-cctv-difference__header">
					<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-section-title sg-section-title--center sg-section-title--ink">
						<?php echo esc_html( $title_before ); ?>
						<span class="sg-accent"><?php echo esc_html( $title_accent ); ?></span>
						<?php echo esc_html( $title_after ); ?>
					</h2>
					<?php if ( '' !== $intro ) : ?>
						<div class="sg-cctv-difference__intro sg-section-intro sg-section-intro--center">
							<p><?php echo esc_html( $intro ); ?></p>
						</div>
					<?php endif; ?>
				</header>

				<ol class="sg-alarm-steps__list sg-cctv-difference__steps" role="list">
					<?php foreach ( $steps as $index => $step ) : ?>
						<li class="sg-alarm-step-card">
							<span class="sg-alarm-step-card__num" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
							<?php if ( ! empty( $step['icon'] ) || is_callable( $icon_renderer ) ) : ?>
								<div class="<?php echo esc_attr( $icon_wrap_class ); ?>" aria-hidden="true">
									<?php
									if ( isset( $step['icon'] ) && is_callable( $step['icon'] ) ) {
										call_user_func( $step['icon'] );
									} elseif ( isset( $step['icon'] ) && is_callable( $icon_renderer ) ) {
										call_user_func( $icon_renderer, (string) $step['icon'] );
									}
									?>
								</div>
							<?php endif; ?>
							<h3 class="sg-alarm-step-card__title"><?php echo esc_html( (string) $step['title'] ); ?></h3>
							<p class="sg-alarm-step-card__desc"><?php echo esc_html( (string) $step['description'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
		<?php
		return;
	}

	$classes = trim( 'sg-alarm-steps ' . $section_class . ' alignfull' );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-alarm-steps__bg" aria-hidden="true"></div>
		<div class="sg-container sg-alarm-steps__inner">
			<header class="sg-alarm-steps__header">
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="sg-alarm-steps__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-alarm-steps__title">
					<?php echo esc_html( $title_before ); ?>
					<span class="sg-accent"><?php echo esc_html( $title_accent ); ?></span>
					<?php echo esc_html( $title_after ); ?>
				</h2>
				<?php if ( '' !== $intro ) : ?>
					<p class="sg-alarm-steps__intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
			</header>

			<ol class="sg-alarm-steps__list" role="list">
				<?php foreach ( $steps as $index => $step ) : ?>
					<li class="sg-alarm-step-card">
						<span class="sg-alarm-step-card__num" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
						<?php if ( ! empty( $step['icon'] ) ) : ?>
							<div class="<?php echo esc_attr( $icon_wrap_class ); ?>" aria-hidden="true">
								<?php
								if ( is_callable( $step['icon'] ) ) {
									call_user_func( $step['icon'] );
								} elseif ( is_callable( $icon_renderer ) && is_string( $step['icon'] ) ) {
									call_user_func( $icon_renderer, $step['icon'] );
								}
								?>
							</div>
						<?php endif; ?>
						<h3 class="sg-alarm-step-card__title"><?php echo esc_html( (string) $step['title'] ); ?></h3>
						<p class="sg-alarm-step-card__desc"><?php echo esc_html( (string) $step['description'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php
}

/**
 * Prose + 3-column proof stack (.sg-cctv-intro).
 *
 * @param array{
 *   heading_id: string,
 *   section_class: string,
 *   title_before: string,
 *   title_accent: string,
 *   body: string,
 *   proofs: array<int, array{title: string, desc: string, icon: string}>,
 *   icon_renderer: callable(string): void,
 * } $config Section content.
 */
function site_blocks_render_intro_proofs( array $config ): void {
	$proofs = $config['proofs'] ?? array();

	if ( $proofs === array() ) {
		return;
	}

	$icon_renderer = $config['icon_renderer'] ?? null;
	?>
	<section class="<?php echo esc_attr( 'sg-band sg-band--white sg-cctv-intro ' . (string) $config['section_class'] . ' alignfull' ); ?>" aria-labelledby="<?php echo esc_attr( (string) $config['heading_id'] ); ?>">
		<div class="sg-container sg-cctv-intro__grid">
			<div class="sg-cctv-intro__copy">
				<h2 id="<?php echo esc_attr( (string) $config['heading_id'] ); ?>" class="sg-section-title sg-section-title--ink">
					<?php echo esc_html( (string) $config['title_before'] ); ?>
					<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
				</h2>
				<p><?php echo esc_html( (string) $config['body'] ); ?></p>
			</div>
			<div class="sg-cctv-intro__proofs" role="list">
				<?php foreach ( $proofs as $proof ) : ?>
					<div class="sg-cctv-proof" role="listitem">
						<div class="sg-cctv-icon sg-cctv-icon--proof" aria-hidden="true">
							<?php
							if ( is_callable( $icon_renderer ) ) {
								call_user_func( $icon_renderer, (string) $proof['icon'] );
							}
							?>
						</div>
						<strong class="sg-cctv-proof__title"><?php echo esc_html( (string) $proof['title'] ); ?></strong>
						<p class="sg-cctv-proof__desc"><?php echo esc_html( (string) $proof['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Customer portal promo band (.sg-portal-band).
 *
 * @param array{
 *   heading_id: string,
 *   section_class: string,
 *   title_before: string,
 *   title_accent: string,
 *   intro: string,
 *   bullets: array<int, string>,
 *   image_path?: string,
 *   image_alt?: string,
 * } $config Section content.
 */
function site_blocks_render_portal_band( array $config ): void {
	require_once SITE_BLOCKS_DIR . 'inc/safeguard-homepage-markup.php';

	$image_path = isset( $config['image_path'] ) ? (string) $config['image_path'] : 'images/portal/portal-dashboard.png';
	$image_alt  = isset( $config['image_alt'] ) ? (string) $config['image_alt'] : __( 'Safeguard customer portal showing quote status, uploaded photos, messages and estimate approval', 'site-blocks' );
	$portal_img = site_blocks_asset_url( $image_path );
	?>
	<section class="<?php echo esc_attr( 'sg-band sg-portal-band ' . (string) $config['section_class'] . ' alignfull' ); ?>" aria-labelledby="<?php echo esc_attr( (string) $config['heading_id'] ); ?>">
		<div class="sg-container sg-portal-band__grid">
			<div class="sg-portal-band__copy">
				<h2 id="<?php echo esc_attr( (string) $config['heading_id'] ); ?>" class="sg-portal-band__title">
					<?php echo esc_html( (string) $config['title_before'] ); ?>
					<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
				</h2>
				<p class="sg-portal-band__intro"><?php echo esc_html( (string) $config['intro'] ); ?></p>
				<ul class="sg-portal-band__list" role="list">
					<?php foreach ( $config['bullets'] as $bullet ) : ?>
						<li>
							<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
							<?php echo esc_html( (string) $bullet ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="sg-portal-band__visual">
				<img
					class="sg-portal-band__img"
					src="<?php echo esc_url( $portal_img ); ?>"
					alt="<?php echo esc_attr( $image_alt ); ?>"
					width="928"
					height="458"
					loading="lazy"
					decoding="async"
				/>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Four-item trust panel (.sg-alarm-why + icon grid).
 *
 * @param array{
 *   heading_id: string,
 *   section_class: string,
 *   panel_class?: string,
 *   title_before: string,
 *   title_accent: string,
 *   items: array<int, array{title: string, desc: string, icon: string}>,
 *   icon_renderer: callable(string): void,
 *   icon_wrapper_class?: string,
 * } $config Section content.
 */
function site_blocks_render_trust_panel( array $config ): void {
	$items         = $config['items'] ?? array();
	$icon_renderer = $config['icon_renderer'] ?? null;
	$icon_class    = isset( $config['icon_wrapper_class'] ) ? (string) $config['icon_wrapper_class'] : 'sg-alarm-why__icon sg-cctv-icon sg-cctv-icon--trust';
	$panel_class   = isset( $config['panel_class'] ) ? (string) $config['panel_class'] : 'sg-alarm-why__panel';

	if ( $items === array() ) {
		return;
	}
	?>
	<section class="<?php echo esc_attr( 'sg-band sg-band--blue sg-alarm-why ' . (string) $config['section_class'] . ' alignfull' ); ?>" aria-labelledby="<?php echo esc_attr( (string) $config['heading_id'] ); ?>">
		<div class="sg-container">
			<header class="sg-alarm-why__header">
				<h2 id="<?php echo esc_attr( (string) $config['heading_id'] ); ?>" class="sg-alarm-why__title">
					<?php echo esc_html( (string) $config['title_before'] ); ?>
					<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
				</h2>
			</header>

			<div class="<?php echo esc_attr( $panel_class ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<article class="sg-alarm-why__item">
						<div class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true">
							<?php
							if ( is_callable( $icon_renderer ) ) {
								call_user_func( $icon_renderer, (string) $item['icon'] );
							}
							?>
						</div>
						<h3 class="sg-alarm-why__item-title"><?php echo esc_html( (string) $item['title'] ); ?></h3>
						<p class="sg-alarm-why__item-desc"><?php echo esc_html( (string) $item['desc'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Enqueue styles for the dark Ajax-style quote CTA panel.
 */
function site_blocks_enqueue_ajax_quote_cta_assets(): void {
	static $done = false;

	if ( $done ) {
		return;
	}

	$done = true;

	if ( ! function_exists( 'site_blocks_enqueue_safeguard_fonts' ) ) {
		return;
	}

	site_blocks_enqueue_safeguard_fonts( 'safeguard-ajax-quote-cta-fonts' );
	$dep = site_blocks_enqueue_safeguard_style( 'safeguard-home', 'safeguard-home.css', 'safeguard-ajax-quote-cta-fonts' );
	site_blocks_enqueue_safeguard_style( 'safeguard-ajax-quote-cta', 'safeguard-ajax-quote-cta.css', $dep );
}

/**
 * Default content for the Ajax-style dark quote CTA panel.
 *
 * @return array<string, mixed>
 */
function site_blocks_default_ajax_quote_cta_config(): array {
	$phone_label = '1300 225 276';

	return array(
		'heading_id'         => 'sg-ajax-quote-cta-heading',
		'eyebrow'            => __( 'Start your quote', 'site-blocks' ),
		'title'              => __( 'Design your Ajax system with Safeguard.', 'site-blocks' ),
		'lead'               => __( 'Start with the Ajax calculator, get pricing quickly, and we\'ll design the right system for your property.', 'site-blocks' ),
		'primary_label'      => __( 'Open Ajax calculator', 'site-blocks' ),
		'primary_url'        => home_url( '/ajax-calculator/' ),
		'secondary_label'    => sprintf(
			/* translators: %s: phone number */
			__( 'Call %s', 'site-blocks' ),
			$phone_label
		),
		'secondary_url'      => 'tel:1300225276',
		'phone_href'         => 'tel:1300225276',
		'phone_label'        => $phone_label,
		'bg_image'           => site_blocks_asset_url( 'images/ajax/ajax-bg.webp' ),
		'safeguard_logo'     => site_blocks_asset_url( 'images/brand/safeguard-logo-footer.png' ),
		'features_aria_label'=> __( 'Why start your quote with Safeguard', 'site-blocks' ),
		'trust_aria_label'   => __( 'Safeguard credentials', 'site-blocks' ),
		'section_class'      => '',
		'alignfull'          => true,
		'features'           => array(
			array(
				'title'       => __( 'Price on the spot', 'site-blocks' ),
				'description' => __( 'Get instant pricing for most properties.', 'site-blocks' ),
				'icon'        => 'dollar.png',
				'accent'      => 'orange',
			),
			array(
				'title'       => __( 'Technician-reviewed', 'site-blocks' ),
				'description' => __( 'Our experts design the right system for complex sites.', 'site-blocks' ),
				'icon'        => 'technician.png',
				'accent'      => 'blue',
			),
			array(
				'title'       => __( 'Track everything', 'site-blocks' ),
				'description' => __( 'Manage your system in your secure online portal.', 'site-blocks' ),
				'icon'        => 'secure.png',
				'accent'      => 'blue',
			),
		),
		'trust_items'        => array(
			array(
				'label' => __( 'Installer/dealer of Ajax Systems products', 'site-blocks' ),
				'type'  => 'safeguard-logo',
			),
			array(
				'label' => __( 'Australian owned and operated', 'site-blocks' ),
				'icon'  => 'australia-map.png',
			),
			array(
				'label' => __( 'Licensed and insured', 'site-blocks' ),
				'icon'  => 'secure.png',
			),
		),
	);
}

/**
 * Render the dark Ajax-style quote CTA panel (copy + feature cards + trust bar).
 *
 * Reuse on any page, override copy, features, trust items, and `bg_image` per page
 * (full URL or pass through `site_blocks_asset_url( 'images/…' )`).
 *
 * @param array{
 *   heading_id?: string,
 *   eyebrow?: string,
 *   title?: string,
 *   lead?: string,
 *   primary_label?: string,
 *   primary_url?: string,
 *   secondary_label?: string,
 *   secondary_url?: string,
 *   phone_href?: string,
 *   phone_label?: string,
 *   bg_image?: string,
 *   safeguard_logo?: string,
 *   features?: array<int, array{title: string, description: string, icon: string, accent?: string}>,
 *   trust_items?: array<int, array{label: string, type?: string, icon?: string}>,
 *   features_aria_label?: string,
 *   trust_aria_label?: string,
 *   section_class?: string,
 *   alignfull?: bool,
 * } $config Section content; merged with site_blocks_default_ajax_quote_cta_config().
 */
function site_blocks_render_ajax_quote_cta( array $config ): void {
	require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-icons.php';

	site_blocks_enqueue_ajax_quote_cta_assets();

	$config = array_merge( site_blocks_default_ajax_quote_cta_config(), $config );

	$heading_id          = (string) $config['heading_id'];
	$eyebrow             = (string) $config['eyebrow'];
	$title               = (string) $config['title'];
	$lead                = (string) $config['lead'];
	$primary_label       = (string) $config['primary_label'];
	$primary_url         = (string) $config['primary_url'];
	$secondary_label     = (string) $config['secondary_label'];
	$secondary_url       = (string) $config['secondary_url'];
	$phone_href          = (string) $config['phone_href'];
	$phone_label         = (string) $config['phone_label'];
	$bg_image            = (string) $config['bg_image'];
	$safeguard_logo      = (string) $config['safeguard_logo'];
	$features_aria_label = (string) $config['features_aria_label'];
	$trust_aria_label    = (string) $config['trust_aria_label'];
	$section_class       = (string) $config['section_class'];
	$alignfull           = ! isset( $config['alignfull'] ) || (bool) $config['alignfull'];
	$features            = is_array( $config['features'] ) ? $config['features'] : array();
	$trust_items         = is_array( $config['trust_items'] ) ? $config['trust_items'] : array();

	$classes = trim( 'sg-ajax-quote-cta ' . $section_class . ( $alignfull ? ' alignfull' : '' ) );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
		<div class="sg-container">
			<div class="sg-ajax-quote-cta__panel" style="--sg-ajax-quote-bg: url('<?php echo esc_url( $bg_image ); ?>')">
				<div class="sg-ajax-quote-cta__body">
					<div class="sg-ajax-quote-cta__copy">
						<p class="sg-ajax-quote-cta__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
						<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-ajax-quote-cta__title">
							<?php echo esc_html( $title ); ?>
						</h2>
						<p class="sg-ajax-quote-cta__lead"><?php echo esc_html( $lead ); ?></p>
						<div class="sg-ajax-quote-cta__actions">
							<a class="sg-btn sg-ajax-quote-cta__btn sg-ajax-quote-cta__btn--primary" href="<?php echo esc_url( $primary_url ); ?>">
								<?php echo esc_html( $primary_label ); ?>
								<?php site_blocks_lucide_icon( 'arrow-right', 16, 'sg-btn__icon' ); ?>
							</a>
							<?php if ( '' !== $secondary_label && '' !== $secondary_url ) : ?>
								<a class="sg-btn sg-ajax-quote-cta__btn sg-ajax-quote-cta__btn--outline" href="<?php echo esc_attr( $secondary_url ); ?>">
									<?php site_blocks_lucide_icon( 'phone', 16, 'sg-btn__icon' ); ?>
									<?php echo esc_html( $secondary_label ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( $features !== array() ) : ?>
						<div class="sg-ajax-quote-cta__features" aria-label="<?php echo esc_attr( $features_aria_label ); ?>">
							<?php foreach ( $features as $feature ) : ?>
								<article class="sg-ajax-quote-cta__feature">
									<span class="sg-ajax-quote-cta__feature-icon sg-ajax-quote-cta__feature-icon--<?php echo esc_attr( (string) ( $feature['accent'] ?? 'blue' ) ); ?>" aria-hidden="true">
										<?php site_blocks_ajax_cta_icon( (string) ( $feature['icon'] ?? '' ) ); ?>
									</span>
									<div class="sg-ajax-quote-cta__feature-copy">
										<h3 class="sg-ajax-quote-cta__feature-title"><?php echo esc_html( (string) ( $feature['title'] ?? '' ) ); ?></h3>
										<p class="sg-ajax-quote-cta__feature-desc"><?php echo esc_html( (string) ( $feature['description'] ?? '' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $trust_items !== array() || ( '' !== $phone_href && '' !== $phone_label ) ) : ?>
					<div class="sg-ajax-quote-cta__trust" aria-label="<?php echo esc_attr( $trust_aria_label ); ?>">
						<ul class="sg-ajax-quote-cta__trust-list" role="list">
							<?php foreach ( $trust_items as $item ) : ?>
								<li class="sg-ajax-quote-cta__trust-item<?php echo ( isset( $item['type'] ) && 'safeguard-logo' === $item['type'] ) ? ' sg-ajax-quote-cta__trust-item--brand' : ''; ?>">
									<?php if ( isset( $item['type'] ) && 'safeguard-logo' === $item['type'] ) : ?>
										<span class="sg-ajax-quote-cta__trust-logo" aria-hidden="true">
											<img
												class="sg-ajax-quote-cta__brand-logo"
												src="<?php echo esc_url( $safeguard_logo ); ?>"
												alt="<?php esc_attr_e( 'Safeguard Security Services', 'site-blocks' ); ?>"
												width="220"
												height="52"
												loading="lazy"
												decoding="async"
											/>
										</span>
									<?php elseif ( ! empty( $item['icon'] ) ) : ?>
										<span class="sg-ajax-quote-cta__trust-icon" aria-hidden="true">
											<?php site_blocks_ajax_cta_icon( (string) $item['icon'] ); ?>
										</span>
									<?php endif; ?>
									<span class="sg-ajax-quote-cta__trust-label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></span>
								</li>
							<?php endforeach; ?>
							<?php if ( '' !== $phone_href && '' !== $phone_label ) : ?>
								<li class="sg-ajax-quote-cta__trust-item sg-ajax-quote-cta__trust-item--phone">
									<a class="sg-ajax-quote-cta__phone" href="<?php echo esc_attr( $phone_href ); ?>">
										<span class="sg-ajax-quote-cta__trust-icon" aria-hidden="true">
											<?php site_blocks_ajax_cta_icon( 'call.png' ); ?>
										</span>
										<span class="sg-ajax-quote-cta__phone-num"><?php echo esc_html( $phone_label ); ?></span>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
