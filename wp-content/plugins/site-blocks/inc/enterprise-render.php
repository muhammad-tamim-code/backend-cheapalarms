<?php
/**
 * Enterprise shared render helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/enterprise-config.php';
require_once SITE_BLOCKS_DIR . 'inc/enterprise-media.php';
require_once SITE_BLOCKS_DIR . 'inc/access-control-split.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-related-services.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-scenario-grid.php';
require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow-configs.php';

/**
 * Dispatch a section render for the current or given page key.
 */
function site_blocks_render_enterprise_section( string $section, ?string $page_key = null ): void {
	$page_key = $page_key ?? site_blocks_get_enterprise_page_key();

	if ( null === $page_key || ! site_blocks_enterprise_section_applies( $page_key, $section ) ) {
		return;
	}

	switch ( $section ) {
		case 'intro':
		case 'approach':
		case 'integration':
			site_blocks_enterprise_render_split( $page_key, $section );
			break;
		case 'trust':
			site_blocks_enterprise_render_trust();
			break;
		case 'challenges':
			site_blocks_enterprise_render_challenges();
			break;
		case 'solutions':
			site_blocks_enterprise_render_solutions();
			break;
		case 'promo':
			site_blocks_enterprise_render_promo();
			break;
		case 'industries':
			site_blocks_enterprise_render_industries();
			break;
		case 'process':
			site_blocks_enterprise_render_process();
			break;
		case 'capabilities':
			site_blocks_enterprise_render_capabilities();
			break;
		case 'spoke-teasers':
			site_blocks_enterprise_render_spoke_teasers();
			break;
		case 'insights':
			site_blocks_enterprise_render_insights_feed();
			break;
		case 'quote':
			site_blocks_enterprise_render_quote();
			break;
		case 'related-services':
			site_blocks_enterprise_render_related_services_grid( $page_key );
			break;
		case 'faq':
			site_blocks_enterprise_render_faq( $page_key );
			break;
		case 'cta':
			site_blocks_enterprise_render_cta( $page_key );
			break;
	}
}

/**
 * Render a config-driven split row.
 */
function site_blocks_enterprise_render_split( string $page_key, string $section ): void {
	$config = site_blocks_enterprise_split_config( $page_key, $section );

	if ( null === $config ) {
		return;
	}

	$image   = isset( $config['image'] ) ? (string) $config['image'] : '';
	$alt     = isset( $config['alt'] ) ? (string) $config['alt'] : '';
	$reverse = ! empty( $config['reverse'] );
	$band    = isset( $config['band'] ) ? (string) $config['band'] : 'white';
	$visual  = static function () use ( $image, $alt ): void {
		if ( '' !== $image ) {
			site_blocks_enterprise_image( $image, $alt );
			return;
		}

		printf( '<span class="sg-cctv-media-placeholder sg-enterprise-media-placeholder" aria-hidden="true"></span>' );
	};

	site_blocks_render_access_control_split(
		array(
			'id'           => (string) $config['id'],
			'class'        => (string) $config['class'],
			'band'         => $band,
			'reverse'      => $reverse,
			'title_before' => (string) $config['title_before'],
			'title_accent' => (string) ( $config['title_accent'] ?? '' ),
			'title_after'  => (string) ( $config['title_after'] ?? '' ),
			'paragraphs'   => $config['paragraphs'] ?? array(),
			'visual'       => $visual,
		)
	);
}

/**
 * Four pain-point challenge cards.
 */
function site_blocks_enterprise_render_challenges(): void {
	$config = site_blocks_enterprise_challenges_config();
	?>
	<section class="sg-band sg-band--white sg-enterprise-challenges alignfull" aria-labelledby="sg-enterprise-challenges-heading">
		<div class="sg-container">
			<h2 id="sg-enterprise-challenges-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['title'] ); ?>
			</h2>

			<div class="sg-enterprise-challenges__grid" role="list">
				<?php foreach ( $config['cards'] as $card ) : ?>
					<article class="sg-enterprise-challenges__card" role="listitem">
						<h3 class="sg-enterprise-challenges__title"><?php echo esc_html( $card['title'] ); ?></h3>
						<p class="sg-enterprise-challenges__body"><?php echo esc_html( $card['body'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Hub solutions cards grid.
 */
function site_blocks_enterprise_render_solutions(): void {
	$heading = site_blocks_enterprise_solutions_heading();

	site_blocks_render_hub_services_grid(
		array(
			'heading_id'    => 'sg-enterprise-solutions-heading',
			'section_class' => 'sg-enterprise-solutions',
			'title'         => $heading['title'],
			'intro'         => $heading['intro'],
			'services'      => site_blocks_enterprise_hub_solutions(),
			'band'          => 'white',
		)
	);
}

/**
 * Safeguard Solutions promo band.
 */
function site_blocks_enterprise_render_promo(): void {
	$config = site_blocks_enterprise_promo_config();
	?>
	<section class="sg-band sg-band--white sg-enterprise-promo alignfull" aria-labelledby="<?php echo esc_attr( $config['heading_id'] ); ?>">
		<div class="sg-container sg-enterprise-promo__grid">
			<div class="sg-enterprise-promo__copy">
				<h2 id="<?php echo esc_attr( $config['heading_id'] ); ?>" class="sg-section-title sg-section-title--ink">
					<?php echo esc_html( (string) $config['title'] ); ?>
				</h2>
				<?php foreach ( $config['paragraphs'] as $paragraph ) : ?>
					<p class="sg-enterprise-promo__text"><?php echo esc_html( (string) $paragraph ); ?></p>
				<?php endforeach; ?>
				<ul class="sg-enterprise-promo__list" role="list">
					<?php foreach ( $config['bullets'] as $bullet ) : ?>
						<li>
							<span class="sg-enterprise-promo__check" aria-hidden="true">
								<?php site_blocks_lucide_icon( 'circle-check', 18 ); ?>
							</span>
							<?php echo esc_html( (string) $bullet ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( (string) $config['cta_url'] ); ?>">
					<?php echo esc_html( (string) $config['cta_label'] ); ?>
				</a>
			</div>
			<div class="sg-enterprise-promo__visual">
				<?php site_blocks_enterprise_image( 'hub-promo.webp', __( 'Safeguard Solutions multi-site security dashboard', 'site-blocks' ), 'sg-enterprise-promo__img' ); ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Trust / accreditation band (navy, credential chips - no image).
 */
function site_blocks_enterprise_render_trust(): void {
	$config = site_blocks_enterprise_trust_config();
	?>
	<section class="sg-enterprise-trust alignfull" aria-labelledby="sg-enterprise-trust-heading">
		<div class="sg-container sg-enterprise-trust__inner">
			<div class="sg-enterprise-trust__copy">
				<p class="sg-enterprise-trust__eyebrow"><?php echo esc_html( (string) $config['eyebrow'] ); ?></p>
				<h2 id="sg-enterprise-trust-heading" class="sg-enterprise-trust__title">
					<?php echo esc_html( (string) $config['title_before'] ); ?><span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span><?php echo esc_html( (string) ( $config['title_after'] ?? '' ) ); ?>
				</h2>
				<p class="sg-enterprise-trust__body"><?php echo esc_html( (string) $config['body'] ); ?></p>
			</div>
			<ul class="sg-enterprise-trust__grid" role="list">
				<?php foreach ( $config['items'] as $item ) : ?>
					<li class="sg-enterprise-trust__item">
						<span class="sg-enterprise-trust__icon" aria-hidden="true">
							<?php site_blocks_lucide_icon( (string) $item['icon'], 26 ); ?>
						</span>
						<span class="sg-enterprise-trust__value"><?php echo esc_html( (string) $item['value'] ); ?></span>
						<span class="sg-enterprise-trust__label"><?php echo esc_html( (string) $item['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Industry grid for the hub.
 */
function site_blocks_enterprise_render_industries(): void {
	site_blocks_render_scenario_grid( site_blocks_enterprise_industry_scenario_config() );
}

/**
 * Hub four-step process strip.
 */
function site_blocks_enterprise_render_process(): void {
	site_blocks_render_process_flow( site_blocks_process_flow_config( 'enterprise-hub-process' ) );
}

/**
 * Safeguard Solutions capability list.
 */
function site_blocks_enterprise_render_capabilities(): void {
	$items = site_blocks_safeguard_solutions_capabilities();
	?>
	<section class="sg-band sg-band--white sg-enterprise-capabilities alignfull" aria-labelledby="sg-enterprise-capabilities-heading">
		<div class="sg-container">
			<h2 id="sg-enterprise-capabilities-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'What the platform ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'delivers', 'site-blocks' ); ?></span>
			</h2>
			<ul class="sg-enterprise-capabilities__list" role="list">
				<?php foreach ( $items as $item ) : ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Future spoke teaser cards on Safeguard Solutions child hub.
 */
function site_blocks_enterprise_render_spoke_teasers(): void {
	$teasers = site_blocks_safeguard_solutions_spoke_teasers();
	?>
	<section class="sg-band sg-band--white sg-enterprise-spokes alignfull" aria-labelledby="sg-enterprise-spokes-heading">
		<div class="sg-container">
			<header class="sg-alarm-services__header">
				<h2 id="sg-enterprise-spokes-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
					<?php esc_html_e( 'Platform capabilities, ', 'site-blocks' ); ?>
					<span class="sg-accent"><?php esc_html_e( 'in depth', 'site-blocks' ); ?></span>
				</h2>
				<p class="sg-section-intro sg-section-intro--center">
					<?php esc_html_e( 'Dedicated feature pages are coming soon. Talk to our team about any capability below.', 'site-blocks' ); ?>
				</p>
			</header>
			<div class="sg-enterprise-spokes__grid" role="list">
				<?php foreach ( $teasers as $teaser ) : ?>
					<article class="sg-enterprise-spokes__card" role="listitem">
						<?php if ( ! empty( $teaser['image'] ) ) : ?>
							<div class="sg-enterprise-spokes__media">
								<?php
								site_blocks_enterprise_image(
									(string) $teaser['image'],
									(string) ( $teaser['alt'] ?? '' ),
									'sg-enterprise-spokes__img'
								);
								?>
							</div>
						<?php endif; ?>
						<div class="sg-enterprise-spokes__body">
							<h3 class="sg-enterprise-spokes__title"><?php echo esc_html( $teaser['title'] ); ?></h3>
							<p class="sg-enterprise-spokes__desc"><?php echo esc_html( $teaser['desc'] ); ?></p>
							<span class="sg-enterprise-spokes__badge"><?php esc_html_e( 'Coming soon', 'site-blocks' ); ?></span>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Latest enterprise insight posts feed.
 */
function site_blocks_enterprise_render_insights_feed(): void {
	$query = new WP_Query(
		array(
			'post_type'      => 'enterprise_insight',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
	?>
	<section class="sg-band sg-band--white sg-enterprise-insights alignfull" aria-labelledby="sg-enterprise-insights-heading">
		<div class="sg-container">
			<header class="sg-alarm-services__header">
				<h2 id="sg-enterprise-insights-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
					<?php esc_html_e( 'Latest enterprise ', 'site-blocks' ); ?>
					<span class="sg-accent"><?php esc_html_e( 'insights', 'site-blocks' ); ?></span>
				</h2>
				<p class="sg-section-intro sg-section-intro--center">
					<?php esc_html_e( 'Practical thinking on commercial and multi-site security technology.', 'site-blocks' ); ?>
				</p>
			</header>

			<?php if ( $query->have_posts() ) : ?>
				<div class="sg-enterprise-insights__grid" role="list">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$excerpt = get_the_excerpt();
						if ( '' === $excerpt ) {
							$excerpt = wp_trim_words( wp_strip_all_tags( get_the_content() ), 28, '…' );
						}
						?>
						<article class="sg-enterprise-insights__card" role="listitem">
							<h3 class="sg-enterprise-insights__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="sg-enterprise-insights__excerpt"><?php echo esc_html( $excerpt ); ?></p>
							<a class="sg-enterprise-insights__link" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Read article', 'site-blocks' ); ?>
								<span aria-hidden="true">&rsaquo;</span>
							</a>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="sg-section-intro sg-section-intro--center">
					<?php esc_html_e( 'New enterprise insights articles will appear here as they are published.', 'site-blocks' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Site assessment quote band.
 */
function site_blocks_enterprise_render_quote(): void {
	$config = site_blocks_enterprise_quote_config();
	?>
	<section class="sg-band sg-band--white sg-enterprise-quote alignfull" aria-labelledby="sg-enterprise-quote-heading">
		<div class="sg-container sg-enterprise-quote__inner">
			<h2 id="sg-enterprise-quote-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['title'] ); ?>
			</h2>
			<p class="sg-enterprise-quote__body"><?php echo esc_html( $config['body'] ); ?></p>
			<div class="sg-enterprise-quote__links">
				<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( $config['url'] ); ?>">
					<?php echo esc_html( $config['label'] ); ?>
				</a>
				<a class="sg-btn sg-btn--ghost-dark" href="<?php echo esc_url( 'tel:1300225276' ); ?>">
					<?php echo esc_html( $config['phone'] ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php
}

/**
 * FAQ accordion section.
 */
function site_blocks_enterprise_render_faq( string $page_key ): void {
	site_blocks_render_faq_section(
		site_blocks_enterprise_faq_items( $page_key ),
		array(
			'heading_id'     => 'sg-enterprise-faq-heading',
			'heading_before' => __( 'Questions, ', 'site-blocks' ),
			'heading_accent' => __( 'answered', 'site-blocks' ),
			'id_prefix'      => 'sg-enterprise-faq-',
			'section_class'  => 'sg-cctv-faq sg-enterprise-faq',
		)
	);
}

/**
 * End-of-page related services grid.
 */
function site_blocks_enterprise_render_related_services_grid( string $page_key ): void {
	$config = site_blocks_enterprise_related_page_grid_config( $page_key );

	if ( null === $config ) {
		return;
	}

	$config['icon_renderer'] = 'site_blocks_cctv_icon';

	site_blocks_render_related_services_page_grid( $config );
}

/**
 * Final navy CTA band.
 */
function site_blocks_enterprise_render_cta( string $page_key ): void {
	$config = site_blocks_enterprise_cta_config( $page_key );
	$ctas   = 'safeguard-solutions' === $page_key ? site_blocks_safeguard_solutions_ctas() : site_blocks_enterprise_ctas();

	if ( null === $config ) {
		return;
	}

	site_blocks_render_quote_cta(
		array(
			'heading_id'      => 'sg-enterprise-cta-heading',
			'before'          => (string) $config['before'],
			'accent'          => (string) $config['accent'],
			'after'           => (string) ( $config['after'] ?? '' ),
			'sub'             => (string) $config['sub'],
			'primary_label'   => $ctas['primary_label'],
			'primary_url'     => $ctas['primary_url'],
			'secondary_label' => $config['secondary_label'] ?? $ctas['secondary_label'],
			'secondary_url'   => $config['secondary_url'] ?? $ctas['secondary_url'],
			'section_class'   => 'sg-cctv-cta sg-enterprise-cta',
		)
	);
}
