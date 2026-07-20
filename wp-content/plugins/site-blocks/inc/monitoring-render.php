<?php
/**
 * Monitoring shared render helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/monitoring-config.php';
require_once SITE_BLOCKS_DIR . 'inc/monitoring-media.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-related-services.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-scenario-grid.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';

/**
 * Dispatch a section render for the current or given page key.
 */
function site_blocks_render_monitoring_section( string $section, ?string $page_key = null ): void {
	$page_key = $page_key ?? site_blocks_get_monitoring_page_key();

	if ( null === $page_key || ! site_blocks_monitoring_section_applies( $page_key, $section ) ) {
		return;
	}

	switch ( $section ) {
		case 'intro':
		case 'integration':
		case 'response-plans':
		case 'compatible-systems':
		case 'monitoring-integration':
			site_blocks_monitoring_render_split( $page_key, $section );
			break;
		case 'how-it-works':
			if ( 'hub' === $page_key ) {
				site_blocks_render_process_flow( site_blocks_process_flow_config( 'monitoring-hub-how-it-works' ) );
			} else {
				site_blocks_render_process_flow( site_blocks_process_flow_config( 'monitoring-' . $page_key ) );
			}
			break;
		case 'whats-included':
			if ( 'solar-cameras-monitoring' === $page_key ) {
				break;
			}
			site_blocks_monitoring_render_split( $page_key, $section );
			break;
		case 'technical':
			if ( 'solar-cameras-monitoring' === $page_key ) {
				site_blocks_monitoring_render_package_inclusions( $page_key );
			} else {
				site_blocks_monitoring_render_split( $page_key, $section );
			}
			break;
		case 'services':
			site_blocks_monitoring_render_services();
			break;
		case 'paths':
			site_blocks_monitoring_render_paths();
			break;
		case 'process':
			site_blocks_monitoring_render_process();
			break;
		case 'communicators':
			site_blocks_monitoring_render_communicators();
			break;
		case 'compare':
			site_blocks_monitoring_render_compare();
			break;
		case 'features':
			site_blocks_monitoring_render_features();
			break;
		case 'industries':
			site_blocks_monitoring_render_industries( $page_key );
			break;
		case 'use-cases':
			site_blocks_monitoring_render_use_cases();
			break;
		case 'requirements':
			site_blocks_monitoring_render_requirements();
			break;
		case 'portal':
			site_blocks_monitoring_render_portal( $page_key );
			break;
		case 'trust':
			site_blocks_monitoring_render_trust( $page_key );
			break;
		case 'related-services':
			site_blocks_monitoring_render_related_services_grid( $page_key );
			break;
		case 'quote':
			site_blocks_monitoring_render_quote();
			break;
		case 'faq':
			site_blocks_monitoring_render_faq( $page_key );
			break;
		case 'cta':
			site_blocks_monitoring_render_cta( $page_key );
			break;
	}
}

/**
 * Solar package inclusions section with media, cards, and support checks.
 */
function site_blocks_monitoring_render_package_inclusions( string $page_key ): void {
	$config = site_blocks_monitoring_package_inclusions_config( $page_key );

	if ( null === $config ) {
		return;
	}
	?>
	<section class="sg-band sg-band--white sg-monitoring-package alignfull" aria-labelledby="sg-monitoring-package-heading">
		<div class="sg-container">
			<div class="sg-monitoring-package__intro-row">
				<div class="sg-monitoring-package__copy">
					<?php if ( ! empty( $config['eyebrow'] ) ) : ?>
						<p class="sg-monitoring-package__eyebrow"><?php echo esc_html( (string) $config['eyebrow'] ); ?></p>
					<?php endif; ?>
					<h2 id="sg-monitoring-package-heading" class="sg-section-title sg-section-title--ink sg-monitoring-package__title">
						<?php echo esc_html( (string) $config['title_before'] ); ?>
						<span class="sg-accent"><?php echo esc_html( (string) $config['title_accent'] ); ?></span>
					</h2>
					<?php if ( ! empty( $config['intro'] ) ) : ?>
						<p class="sg-monitoring-package__intro"><?php echo esc_html( (string) $config['intro'] ); ?></p>
					<?php endif; ?>
				</div>
				<div class="sg-monitoring-package__visual">
					<?php site_blocks_monitoring_image( (string) $config['image'], (string) $config['alt'], 'sg-monitoring-package__img' ); ?>
				</div>
			</div>

			<div class="sg-monitoring-package__grid" role="list">
				<?php foreach ( $config['cards'] as $card ) : ?>
					<article class="sg-monitoring-package__card" role="listitem">
						<div class="sg-monitoring-package__card-head">
							<div class="sg-monitoring-package__icon" aria-hidden="true">
								<?php site_blocks_monitoring_render_package_icon( (string) ( $card['icon'] ?? '' ) ); ?>
							</div>
							<div class="sg-monitoring-package__card-copy">
								<?php if ( ! empty( $card['eyebrow'] ) ) : ?>
									<p class="sg-monitoring-package__card-eyebrow"><?php echo esc_html( (string) $card['eyebrow'] ); ?></p>
								<?php endif; ?>
								<h3 class="sg-monitoring-package__card-title"><?php echo esc_html( (string) $card['title'] ); ?></h3>
							</div>
						</div>
						<p class="sg-monitoring-package__card-desc"><?php echo esc_html( (string) $card['desc'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $config['checks'] ) ) : ?>
				<div class="sg-monitoring-package__checks" role="list">
					<?php foreach ( $config['checks'] as $check ) : ?>
						<div class="sg-monitoring-package__check" role="listitem">
							<span class="sg-monitoring-package__check-icon" aria-hidden="true">
								<?php site_blocks_lucide_icon( 'circle-check', 18 ); ?>
							</span>
							<span><?php echo esc_html( (string) $check ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Render Lucide icon for solar package cards.
 */
function site_blocks_monitoring_render_package_icon( string $icon ): void {
	if ( ! function_exists( 'site_blocks_lucide_icon_from_legacy' ) ) {
		require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
	}

	site_blocks_lucide_icon_from_legacy( $icon, 32 );
}

/**
 * Render Lucide icon for monitoring communication paths.
 */
function site_blocks_monitoring_render_communicator_icon( string $icon ): void {
	if ( ! function_exists( 'site_blocks_lucide_icon_from_legacy' ) ) {
		require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
	}

	site_blocks_lucide_icon_from_legacy( $icon, 32 );
}

/**
 * Render a config-driven split row.
 */
function site_blocks_monitoring_render_split( string $page_key, string $section ): void {
	$config = site_blocks_monitoring_split_config( $page_key, $section );

	if ( null === $config ) {
		return;
	}

	$image   = isset( $config['image'] ) ? (string) $config['image'] : '';
	$alt     = isset( $config['alt'] ) ? (string) $config['alt'] : '';
	$reverse = ! empty( $config['reverse'] );
	$band    = isset( $config['band'] ) ? (string) $config['band'] : 'white';
	$visual  = static function () use ( $image, $alt ): void {
		if ( '' !== $image ) {
			site_blocks_monitoring_image( $image, $alt );
			return;
		}

		printf( '<span class="sg-cctv-media-placeholder sg-monitoring-media-placeholder" aria-hidden="true"></span>' );
	};

	$footer = null;
	if ( ! empty( $config['cross_links'] ) ) {
		$footer = static function () use ( $config ): void {
			site_blocks_monitoring_render_cross_links( (string) $config['cross_links'] );
		};
	}

	require_once SITE_BLOCKS_DIR . 'inc/access-control-split.php';

	site_blocks_render_access_control_split(
		array(
			'id'              => (string) $config['id'],
			'class'           => (string) $config['class'],
			'band'            => $band,
			'reverse'         => $reverse,
			'title_before'    => (string) $config['title_before'],
			'title_accent'    => (string) ( $config['title_accent'] ?? '' ),
			'title_after'     => (string) ( $config['title_after'] ?? '' ),
			'intro'           => isset( $config['intro'] ) ? (string) $config['intro'] : '',
			'paragraphs'      => $config['paragraphs'] ?? array(),
			'paragraphs_html' => ! empty( $config['paragraphs_html'] ),
			'list'            => $config['list'] ?? array(),
			'visual'          => $visual,
			'footer'          => $footer,
		)
	);
}

/**
 * Related services card strip after integration-style splits.
 *
 * @param bool|string $variant Cross-link variant key.
 */
function site_blocks_monitoring_render_cross_links( $variant = 'hub' ): void {
	site_blocks_render_related_services_band( site_blocks_monitoring_related_services_config( $variant ) );
}

/**
 * Hub service cards grid.
 */
function site_blocks_monitoring_render_services(): void {
	$heading = site_blocks_monitoring_services_heading();

	site_blocks_render_hub_services_grid(
		array(
			'heading_id'    => 'sg-monitoring-services-heading',
			'section_class' => 'sg-monitoring-services',
			'title'         => $heading['title'],
			'intro'         => $heading['intro'],
			'services'      => site_blocks_monitoring_hub_services(),
		)
	);
}

/**
 * Hub three levels of cover cards.
 */
function site_blocks_monitoring_render_paths(): void {
	$config = site_blocks_monitoring_paths_config();
	?>
	<section class="sg-band sg-band--white sg-monitoring-paths alignfull" aria-labelledby="sg-monitoring-paths-heading">
		<div class="sg-container">
			<header class="sg-alarm-services__header">
				<h2 id="sg-monitoring-paths-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
					<?php echo esc_html( $config['title'] ); ?>
				</h2>
				<p class="sg-section-intro sg-section-intro--center"><?php echo esc_html( $config['intro'] ); ?></p>
			</header>

			<div class="sg-monitoring-paths__grid" role="list">
				<?php foreach ( $config['columns'] as $column ) : ?>
					<article class="sg-monitoring-paths__card" role="listitem">
						<h3 class="sg-monitoring-paths__title"><?php echo esc_html( $column['title'] ); ?></h3>
						<p class="sg-monitoring-paths__body"><?php echo esc_html( $column['body'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>

			<p class="sg-monitoring-paths__note"><em><?php echo esc_html( $config['note'] ); ?></em></p>
		</div>
	</section>
	<?php
}

/**
 * Hub process strip (four-step skeleton flow).
 */
function site_blocks_monitoring_render_process(): void {
	site_blocks_render_process_flow( site_blocks_process_flow_config( 'monitoring-hub-process' ) );
}

/**
 * Spoke numbered steps section (skeleton process flow).
 */
function site_blocks_monitoring_render_steps( string $page_key ): void {
	site_blocks_render_process_flow( site_blocks_process_flow_config( 'monitoring-' . $page_key ) );
}

/**
 * Back-to-base communicators table.
 */
function site_blocks_monitoring_render_communicators(): void {
	$config = site_blocks_monitoring_communicators_config();
	?>
	<section class="sg-band sg-band--white sg-monitoring-communicators alignfull" aria-labelledby="sg-monitoring-communicators-heading">
		<div class="sg-container">
			<h2 id="sg-monitoring-communicators-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['title'] ); ?>
			</h2>

			<div class="sg-monitoring-communicators__cards" role="list" aria-label="<?php esc_attr_e( 'Monitoring path types', 'site-blocks' ); ?>">
				<?php foreach ( $config['rows'] as $row ) : ?>
					<article class="sg-monitoring-communicators__card" role="listitem">
						<div class="sg-monitoring-communicators__card-icon">
							<?php site_blocks_monitoring_render_communicator_icon( (string) $row['icon'] ); ?>
						</div>
						<div class="sg-monitoring-communicators__card-copy">
							<h3 class="sg-monitoring-communicators__card-title"><?php echo esc_html( $row['path'] ); ?></h3>
							<p class="sg-monitoring-communicators__card-text"><?php echo esc_html( $row['card_summary'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="sg-monitoring-compare-table-wrap" role="region" aria-label="<?php esc_attr_e( 'Monitoring path comparison', 'site-blocks' ); ?>">
				<table class="sg-monitoring-compare-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Path', 'site-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Best for', 'site-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Summary', 'site-blocks' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $config['rows'] as $row ) : ?>
							<tr>
								<th scope="row">
									<div class="sg-monitoring-communicators__path">
										<span class="sg-monitoring-communicators__path-icon" aria-hidden="true">
											<?php site_blocks_monitoring_render_communicator_icon( (string) $row['icon'] ); ?>
										</span>
										<span class="sg-monitoring-communicators__path-label"><?php echo esc_html( $row['path'] ); ?></span>
									</div>
								</th>
								<td><?php echo esc_html( $row['best_for'] ); ?></td>
								<td><?php echo esc_html( $row['summary'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="sg-monitoring-communicators__note" role="note">
				<span class="sg-monitoring-communicators__note-icon" aria-hidden="true">
					<?php site_blocks_monitoring_render_communicator_icon( 'info' ); ?>
				</span>
				<p class="sg-monitoring-communicators__note-text"><?php echo esc_html( $config['note'] ); ?></p>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Virtual patrol comparison table.
 */
function site_blocks_monitoring_render_compare(): void {
	$config = site_blocks_monitoring_compare_config();
	?>
	<section class="sg-band sg-band--white sg-monitoring-compare alignfull" aria-labelledby="sg-monitoring-compare-heading">
		<div class="sg-container">
			<h2 id="sg-monitoring-compare-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['title'] ); ?>
			</h2>

			<div class="sg-monitoring-compare-table-wrap" role="region" aria-label="<?php esc_attr_e( 'Virtual patrol vs mobile patrol', 'site-blocks' ); ?>">
				<table class="sg-monitoring-compare-table sg-monitoring-compare-table--vp">
					<thead>
						<tr>
							<?php foreach ( $config['headers'] as $header ) : ?>
								<th scope="col"><?php echo esc_html( $header ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $config['rows'] as $row ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
								<td>
									<?php
									if ( ! empty( $row['links'] ) ) {
										?>
										<a href="<?php echo esc_url( $row['virtual'] ); ?>"><?php esc_html_e( 'Virtual Patrol', 'site-blocks' ); ?></a>
										<?php
									} else {
										echo esc_html( $row['virtual'] );
									}
									?>
								</td>
								<td>
									<?php
									if ( ! empty( $row['links'] ) ) {
										?>
										<a href="<?php echo esc_url( $row['mobile'] ); ?>"><?php esc_html_e( 'Mobile Patrols', 'site-blocks' ); ?></a>
										<?php
									} else {
										echo esc_html( $row['mobile'] );
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<p class="sg-monitoring-compare__clarifier"><?php echo wp_kses_post( $config['clarifier_html'] ); ?></p>
		</div>
	</section>
	<?php
}

/**
 * Virtual patrol features list.
 */
function site_blocks_monitoring_render_features(): void {
	$features = site_blocks_monitoring_features_list();
	?>
	<section class="sg-band sg-band--blue sg-monitoring-features alignfull" aria-labelledby="sg-monitoring-features-heading">
		<div class="sg-container">
			<h2 id="sg-monitoring-features-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'What virtual patrol ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'delivers', 'site-blocks' ); ?></span>
			</h2>

			<ul class="sg-monitoring-features__list" role="list">
				<?php foreach ( $features as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Industry grid per page key.
 */
function site_blocks_monitoring_render_industries( string $page_key ): void {
	site_blocks_render_scenario_grid( site_blocks_monitoring_industry_scenario_config( $page_key ) );
}

/**
 * Solar use-cases grid.
 */
function site_blocks_monitoring_render_use_cases(): void {
	site_blocks_render_scenario_grid( site_blocks_monitoring_use_cases_config() );
}

/**
 * Virtual patrol requirements list.
 */
function site_blocks_monitoring_render_requirements(): void {
	$config = site_blocks_monitoring_requirements_config();
	?>
	<section class="sg-band sg-band--white sg-monitoring-requirements alignfull" aria-labelledby="sg-monitoring-requirements-heading">
		<div class="sg-container">
			<h2 id="sg-monitoring-requirements-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['title'] ); ?>
			</h2>

			<ul class="sg-monitoring-features__list" role="list">
				<?php foreach ( $config['items'] as $item ) : ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Back-to-base quote section (no prices).
 */
function site_blocks_monitoring_render_quote(): void {
	$config = site_blocks_monitoring_quote_config();
	?>
	<section class="sg-band sg-band--blue sg-monitoring-quote alignfull" aria-labelledby="sg-monitoring-quote-heading">
		<div class="sg-container sg-monitoring-quote__inner">
			<h2 id="sg-monitoring-quote-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['title'] ); ?>
			</h2>
			<p class="sg-monitoring-quote__body"><?php echo esc_html( $config['body'] ); ?></p>
			<div class="sg-monitoring-quote__links">
				<?php foreach ( $config['links'] as $link ) : ?>
					<a
						class="sg-btn <?php echo ! empty( $link['primary'] ) ? 'sg-btn--primary' : 'sg-btn--ghost-dark'; ?>"
						href="<?php echo esc_url( $link['url'] ); ?>"
					>
						<?php echo esc_html( $link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * FAQ accordion section.
 */
function site_blocks_monitoring_render_faq( string $page_key ): void {
	site_blocks_render_faq_section(
		site_blocks_monitoring_faq_items( $page_key ),
		array(
			'heading_id'     => 'sg-monitoring-faq-heading',
			'heading_before' => __( 'Questions, ', 'site-blocks' ),
			'heading_accent' => __( 'answered', 'site-blocks' ),
			'id_prefix'      => 'sg-monitoring-faq-',
			'section_class'  => 'sg-cctv-faq sg-monitoring-faq',
		)
	);
}

/**
 * Customer portal band for monitoring pages where quotes apply.
 */
function site_blocks_monitoring_render_portal( string $page_key ): void {
	$configs = array(
		'hub' => array(
			'heading_id'    => 'sg-monitoring-hub-portal-heading',
			'section_class' => 'sg-monitoring-hub-portal',
			'title_before'  => __( 'Monitoring quotes and updates in ', 'site-blocks' ),
			'title_accent'  => __( 'one portal', 'site-blocks' ),
			'intro'         => __( 'Request monitoring cover online, upload site details and approve scope without waiting on callbacks.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Track your monitoring quote progress', 'site-blocks' ),
				__( 'Share communicator and response plan details', 'site-blocks' ),
				__( 'Message our team and approve your estimate', 'site-blocks' ),
			),
		),
		'back-to-base' => array(
			'heading_id'    => 'sg-monitoring-b2b-portal-heading',
			'section_class' => 'sg-monitoring-b2b-portal',
			'title_before'  => __( 'Back-to-base quotes in ', 'site-blocks' ),
			'title_accent'  => __( 'one place', 'site-blocks' ),
			'intro'         => __( 'Start your monitoring application online and keep communicator paths, keyholders and response notes organised in our portal.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Document IP, Wi-Fi and 4G communicator options', 'site-blocks' ),
				__( 'Upload floor plans and entry photos', 'site-blocks' ),
				__( 'Approve monitoring scope before go-live', 'site-blocks' ),
			),
		),
		'virtual-patrol' => array(
			'heading_id'    => 'sg-monitoring-vp-portal-heading',
			'section_class' => 'sg-monitoring-vp-portal',
			'title_before'  => __( 'Virtual patrol assessments in ', 'site-blocks' ),
			'title_accent'  => __( 'one portal', 'site-blocks' ),
			'intro'         => __( 'Share camera layouts, tour schedules and escalation contacts so operators can review your site before cover begins.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Upload site maps and camera positions', 'site-blocks' ),
				__( 'Confirm after-hours tour requirements', 'site-blocks' ),
				__( 'Approve patrol scope and reporting format', 'site-blocks' ),
			),
		),
		'solar-cameras-monitoring' => array(
			'heading_id'    => 'sg-monitoring-solar-portal-heading',
			'section_class' => 'sg-monitoring-solar-portal',
			'title_before'  => __( 'Remote site quotes and updates in ', 'site-blocks' ),
			'title_accent'  => __( 'one portal', 'site-blocks' ),
			'intro'         => __( 'Share site photos, approve estimates and message our team without chasing email threads, especially helpful for construction and rural projects.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Track your solar monitoring quote progress', 'site-blocks' ),
				__( 'Upload site maps and access notes', 'site-blocks' ),
				__( 'Approve scope before cameras go live', 'site-blocks' ),
			),
		),
	);

	$config = $configs[ $page_key ] ?? null;

	if ( null === $config ) {
		return;
	}

	site_blocks_render_portal_band( $config );
}

/**
 * Trust panel for monitoring pages.
 */
function site_blocks_monitoring_render_trust( string $page_key ): void {
	/**
	 * @param string $icon Lucide icon key.
	 */
	$renderer = static function ( string $icon ): void {
		site_blocks_lucide_icon( $icon, 24 );
	};

	$items = array(
		array(
			'title' => __( 'Licensed monitoring centre', 'site-blocks' ),
			'desc'  => __( 'Professional operators following agreed response plans.', 'site-blocks' ),
			'icon'  => 'shield-check',
		),
		array(
			'title' => __( 'Installer + monitor', 'site-blocks' ),
			'desc'  => __( 'One team for alarms, cameras, access and monitoring paths.', 'site-blocks' ),
			'icon'  => 'layers',
		),
		array(
			'title' => __( 'Clear escalation', 'site-blocks' ),
			'desc'  => __( 'Keyholders, patrols and authorities per your documented plan.', 'site-blocks' ),
			'icon'  => 'phone',
		),
		array(
			'title' => __( 'Sydney-wide cover', 'site-blocks' ),
			'desc'  => __( 'Residential, commercial, strata and remote sites.', 'site-blocks' ),
			'icon'  => 'map-pin',
		),
	);

	site_blocks_render_trust_panel(
		array(
			'heading_id'    => 'sg-monitoring-trust-heading-' . sanitize_html_class( $page_key ),
			'section_class' => 'sg-monitoring-trust sg-monitoring-trust--' . sanitize_html_class( $page_key ),
			'title_before'  => __( 'Monitoring backed by experienced ', 'site-blocks' ),
			'title_accent'  => __( 'operators', 'site-blocks' ),
			'items'         => $items,
			'icon_renderer' => $renderer,
		)
	);
}

/**
 * End-of-page related services grid.
 */
function site_blocks_monitoring_render_related_services_grid( string $page_key ): void {
	$config = site_blocks_monitoring_related_page_grid_config( $page_key );

	if ( null === $config ) {
		return;
	}

	$config['icon_renderer'] = 'site_blocks_cctv_icon';

	site_blocks_render_related_services_page_grid( $config );
}

/**
 * Final navy CTA band.
 */
function site_blocks_monitoring_render_cta( string $page_key ): void {
	$config = site_blocks_monitoring_cta_config( $page_key );
	$ctas   = site_blocks_monitoring_ctas();

	if ( null === $config ) {
		return;
	}

	site_blocks_render_quote_cta(
		array(
			'heading_id'      => 'sg-monitoring-cta-heading',
			'before'          => (string) $config['before'],
			'accent'          => (string) $config['accent'],
			'after'           => (string) ( $config['after'] ?? '' ),
			'sub'             => (string) $config['sub'],
			'primary_label'   => $ctas['primary_label'],
			'primary_url'     => $ctas['primary_url'],
			'secondary_label' => $config['secondary_label'] ?? $ctas['secondary_label'],
			'secondary_url'   => $config['secondary_url'] ?? $ctas['secondary_url'],
			'section_class'   => 'sg-cctv-cta sg-monitoring-cta',
		)
	);
}
