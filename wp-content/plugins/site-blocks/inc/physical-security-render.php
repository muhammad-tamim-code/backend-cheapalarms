<?php
/**
 * Physical Security shared render helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/physical-security-config.php';
require_once SITE_BLOCKS_DIR . 'inc/physical-security-media.php';
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
function site_blocks_render_physical_security_section( string $section, ?string $page_key = null ): void {
	$page_key = $page_key ?? site_blocks_get_physical_security_page_key();

	if ( null === $page_key || ! site_blocks_physical_security_section_applies( $page_key, $section ) ) {
		return;
	}

	switch ( $section ) {
		case 'covers':
		case 'intro':
		case 'integration':
		case 'sites':
		case 'why':
		case 'duties':
			site_blocks_physical_security_render_split( $page_key, $section );
			break;
		case 'services':
			site_blocks_physical_security_render_services();
			break;
		case 'industries':
			site_blocks_physical_security_render_industries( $page_key );
			break;
		case 'process':
			site_blocks_physical_security_render_process( $page_key );
			break;
		case 'portal':
			site_blocks_physical_security_render_portal( $page_key );
			break;
		case 'trust':
			site_blocks_physical_security_render_trust( $page_key );
			break;
		case 'related-services':
			site_blocks_physical_security_render_related_services_grid( $page_key );
			break;
		case 'compare':
			site_blocks_physical_security_render_compare( $page_key );
			break;
		case 'faq':
			site_blocks_physical_security_render_faq( $page_key );
			break;
		case 'cta':
			site_blocks_physical_security_render_cta( $page_key );
			break;
	}
}

/**
 * Render a config-driven split row.
 */
function site_blocks_physical_security_render_split( string $page_key, string $section ): void {
	$config = site_blocks_physical_security_split_config( $page_key, $section );

	if ( null === $config ) {
		return;
	}

	$image    = (string) $config['image'];
	$alt      = (string) $config['alt'];
	$reverse  = ! empty( $config['reverse'] );
	$band     = isset( $config['band'] ) ? (string) $config['band'] : 'white';
	$visual   = static function () use ( $image, $alt ): void {
		site_blocks_physical_security_image( $image, $alt );
	};

	$footer = null;
	if ( ! empty( $config['cross_links'] ) ) {
		$footer = static function () use ( $config ): void {
			site_blocks_physical_security_render_cross_links( $config );
		};
	}

	$split_args = array(
		'id'           => (string) $config['id'],
		'class'        => (string) $config['class'],
		'band'         => $band,
		'reverse'      => $reverse,
		'layout'       => (string) ( $config['layout'] ?? '' ),
		'title_before' => (string) $config['title_before'],
		'title_accent' => (string) ( $config['title_accent'] ?? '' ),
		'title_after'  => (string) ( $config['title_after'] ?? '' ),
		'paragraphs'   => $config['paragraphs'] ?? array(),
		'list'         => $config['list'] ?? array(),
		'visual'       => $visual,
		'footer'       => $footer,
	);

	if ( ! empty( $config['show_ctas'] ) ) {
		$ctas                        = site_blocks_physical_security_ctas();
		$split_args['primary_label'] = (string) $ctas['primary_label'];
		$split_args['primary_url']   = (string) $ctas['primary_url'];
		$split_args['secondary_label'] = (string) $ctas['secondary_label'];
		$split_args['secondary_url']   = (string) $ctas['secondary_url'];
	}

	site_blocks_render_access_control_split( $split_args );
}

/**
 * Related services card strip after integration-style splits.
 *
 * @param array<string, mixed> $config Split config.
 */
function site_blocks_physical_security_render_cross_links( array $config ): void {
	site_blocks_render_related_services_band( site_blocks_physical_security_related_services_config( $config ) );
}

/**
 * Hub service cards grid (photo-options template).
 */
function site_blocks_physical_security_render_services(): void {
	site_blocks_render_photo_options_grid(
		array(
			'heading_id'    => 'sg-ps-services-heading',
			'section_class' => 'sg-physical-security-services',
			'band'          => 'white',
			'eyebrow'       => __( 'Physical Security', 'site-blocks' ),
			'title_before'  => __( 'Guarding, matched to your ', 'site-blocks' ),
			'title_accent'  => __( 'site', 'site-blocks' ),
			'intro'         => __( 'Professional security services to protect people, property and operations across Sydney.', 'site-blocks' ),
			'items'         => site_blocks_physical_security_hub_photo_services(),
			'cta'           => array(
				'title'         => __( 'Security you can rely on.', 'site-blocks' ),
				'checks'        => array(
					__( 'Licensed NSW Officers', 'site-blocks' ),
					__( '24/7 Operations', 'site-blocks' ),
					__( 'Integrated Alarm Response', 'site-blocks' ),
					__( 'GPS Patrol Tracking', 'site-blocks' ),
					__( 'Digital Incident Reports', 'site-blocks' ),
				),
				'button_label'  => __( 'Request a Quote', 'site-blocks' ),
				'button_url'    => home_url( '/contact/' ),
				'icon'          => 'shield-check',
			),
		)
	);
}

/**
 * Child page industry grid.
 */
function site_blocks_physical_security_render_industries( string $page_key ): void {
	site_blocks_render_scenario_grid( site_blocks_physical_security_industry_scenario_config( $page_key ) );
}

/**
 * Process strip (skeleton process flow).
 */
function site_blocks_physical_security_render_process( string $page_key ): void {
	$config_keys = array(
		'static-guards'  => 'physical-security-static-guards',
		'mobile-patrols' => 'physical-security-mobile-patrols',
	);
	$config_key  = $config_keys[ $page_key ] ?? 'physical-security';
	$config      = site_blocks_process_flow_config( $config_key );

	if ( null !== $config ) {
		site_blocks_render_process_flow( $config );
	}
}

/**
 * Customer portal band.
 */
function site_blocks_physical_security_render_portal( string $page_key ): void {
	$configs = array(
		'hub' => array(
			'heading_id'    => 'sg-ps-hub-portal-heading',
			'section_class' => 'sg-ps-hub-portal',
			'title_before'  => __( 'Guarding quotes and scope in ', 'site-blocks' ),
			'title_accent'  => __( 'one portal', 'site-blocks' ),
			'intro'         => __( 'Request static or mobile cover online, share site briefs and approve scope without chasing email threads.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Document sites, hours and access requirements', 'site-blocks' ),
				__( 'Upload maps, photos and induction notes', 'site-blocks' ),
				__( 'Message our team and approve your quote', 'site-blocks' ),
			),
		),
		'static-guards' => array(
			'heading_id'    => 'sg-ps-static-portal-heading',
			'section_class' => 'sg-ps-static-portal',
			'title_before'  => __( 'Static guard quotes in ', 'site-blocks' ),
			'title_accent'  => __( 'one place', 'site-blocks' ),
			'intro'         => __( 'Share roster needs, entry points and site rules so we can quote the right on-site cover.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Outline hours, posts and reporting needs', 'site-blocks' ),
				__( 'Upload site photos and access instructions', 'site-blocks' ),
				__( 'Approve scope before officers are rostered', 'site-blocks' ),
			),
		),
		'mobile-patrols' => array(
			'heading_id'    => 'sg-ps-mobile-portal-heading',
			'section_class' => 'sg-ps-mobile-portal',
			'title_before'  => __( 'Patrol quotes and schedules in ', 'site-blocks' ),
			'title_accent'  => __( 'one portal', 'site-blocks' ),
			'intro'         => __( 'Document multiple sites, lock-up times and escalation contacts for GPS-tracked mobile patrols.', 'site-blocks' ),
			'bullets'       => array(
				__( 'List sites, keys and check frequencies', 'site-blocks' ),
				__( 'Share alarm response and attendance rules', 'site-blocks' ),
				__( 'Approve patrol scope before go-live', 'site-blocks' ),
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
 * Trust panel for Physical Security pages.
 */
function site_blocks_physical_security_render_trust( string $page_key ): void {
	$renderer = static function ( string $icon ): void {
		site_blocks_lucide_icon( $icon, 24 );
	};

	site_blocks_render_trust_panel(
		array(
			'heading_id'    => 'sg-ps-trust-heading-' . sanitize_html_class( $page_key ),
			'section_class' => 'sg-ps-trust sg-ps-trust--' . sanitize_html_class( $page_key ),
			'title_before'  => __( 'Physical security delivered by licensed ', 'site-blocks' ),
			'title_accent'  => __( 'professionals', 'site-blocks' ),
			'items'         => array(
				array( 'title' => __( 'Master Licence holder', 'site-blocks' ), 'desc' => __( 'NSW-licensed security operations.', 'site-blocks' ), 'icon' => 'badge-check' ),
				array( 'title' => __( 'GPS-tracked patrols', 'site-blocks' ), 'desc' => __( 'Verified attendance and reporting.', 'site-blocks' ), 'icon' => 'map-pin' ),
				array( 'title' => __( 'Integrated with tech', 'site-blocks' ), 'desc' => __( 'Alarms, CCTV and access where required.', 'site-blocks' ), 'icon' => 'layers' ),
				array( 'title' => __( 'Supervised teams', 'site-blocks' ), 'desc' => __( 'Clear escalation to management.', 'site-blocks' ), 'icon' => 'users' ),
			),
			'icon_renderer' => $renderer,
		)
	);
}

/**
 * End-of-page related services grid.
 */
function site_blocks_physical_security_render_related_services_grid( string $page_key ): void {
	$config = site_blocks_physical_security_related_page_grid_config( $page_key );

	if ( null === $config ) {
		return;
	}

	$config['icon_renderer'] = 'site_blocks_cctv_icon';

	site_blocks_render_related_services_page_grid( $config );
}

/**
 * Static vs mobile compare band.
 */
function site_blocks_physical_security_render_compare( string $page_key ): void {
	$config = site_blocks_physical_security_compare_config( $page_key );

	if ( null === $config ) {
		return;
	}
	?>
	<section class="sg-band sg-band--white sg-ps-compare alignfull" aria-labelledby="sg-ps-compare-heading">
		<div class="sg-container sg-ps-compare__inner">
			<h2 id="sg-ps-compare-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $config['before'] ); ?>
				<span class="sg-accent"><?php echo esc_html( $config['accent'] ); ?></span><?php echo isset( $config['after'] ) ? esc_html( (string) $config['after'] ) : ''; ?>
			</h2>
			<p class="sg-ps-compare__body"><?php echo esc_html( $config['body'] ); ?></p>
			<p class="sg-ps-compare__link-wrap">
				<a class="sg-ps-compare__link" href="<?php echo esc_url( $config['link_url'] ); ?>">
					<?php echo esc_html( $config['link_label'] ); ?>
					<span aria-hidden="true"> →</span>
				</a>
			</p>
		</div>
	</section>
	<?php
}

/**
 * FAQ accordion section.
 */
function site_blocks_physical_security_render_faq( string $page_key ): void {
	site_blocks_render_faq_section(
		site_blocks_physical_security_faq_items( $page_key ),
		array(
			'heading_id'     => 'sg-ps-faq-heading',
			'heading_before' => __( 'Questions, ', 'site-blocks' ),
			'heading_accent' => __( 'answered', 'site-blocks' ),
			'id_prefix'      => 'sg-ps-faq-',
			'section_class'  => 'sg-cctv-faq sg-physical-security-faq',
		)
	);
}

/**
 * Final navy CTA band.
 */
function site_blocks_physical_security_render_cta( string $page_key ): void {
	$config = site_blocks_physical_security_cta_config( $page_key );
	$ctas   = site_blocks_physical_security_ctas();

	if ( null === $config ) {
		return;
	}

	site_blocks_render_quote_cta(
		array(
			'heading_id'      => 'sg-ps-cta-heading',
			'before'          => (string) $config['before'],
			'accent'          => (string) $config['accent'],
			'after'           => (string) ( $config['after'] ?? '' ),
			'sub'             => (string) $config['sub'],
			'primary_label'   => $ctas['primary_label'],
			'primary_url'     => $ctas['primary_url'],
			'secondary_label' => $ctas['secondary_label'],
			'secondary_url'   => $ctas['secondary_url'],
			'section_class'   => 'sg-cctv-cta sg-physical-security-cta',
		)
	);
}
