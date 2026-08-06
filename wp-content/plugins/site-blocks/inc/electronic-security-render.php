<?php
/**
 * Electronic Security shared render helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/electronic-security-config.php';
require_once SITE_BLOCKS_DIR . 'inc/electronic-security-media.php';
require_once SITE_BLOCKS_DIR . 'inc/access-control-split.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-related-services.php';
require_once SITE_BLOCKS_DIR . 'inc/cctv-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow-configs.php';

/**
 * Dispatch a section render for the current or given page key.
 */
function site_blocks_render_electronic_security_section( string $section, ?string $page_key = null ): void {
	$page_key = $page_key ?? site_blocks_get_electronic_security_page_key();

	if ( null === $page_key || ! site_blocks_electronic_security_section_applies( $page_key, $section ) ) {
		return;
	}

	switch ( $section ) {
		case 'covers':
		case 'integration':
		case 'why':
			site_blocks_electronic_security_render_split( $page_key, $section );
			break;
		case 'services':
			site_blocks_electronic_security_render_services();
			break;
		case 'process':
			site_blocks_electronic_security_render_process();
			break;
		case 'portal':
			site_blocks_electronic_security_render_portal( $page_key );
			break;
		case 'trust':
			site_blocks_electronic_security_render_trust( $page_key );
			break;
		case 'related-services':
			site_blocks_electronic_security_render_related_services_grid( $page_key );
			break;
		case 'faq':
			site_blocks_electronic_security_render_faq( $page_key );
			break;
		case 'cta':
			site_blocks_electronic_security_render_cta( $page_key );
			break;
	}
}

/**
 * Render a config-driven split row.
 */
function site_blocks_electronic_security_render_split( string $page_key, string $section ): void {
	$config = site_blocks_electronic_security_split_config( $page_key, $section );

	if ( null === $config ) {
		return;
	}

	$image   = (string) $config['image'];
	$alt     = (string) $config['alt'];
	$reverse = ! empty( $config['reverse'] );
	$band    = isset( $config['band'] ) ? (string) $config['band'] : 'white';
	$visual  = static function () use ( $image, $alt ): void {
		site_blocks_electronic_security_image( $image, $alt );
	};

	site_blocks_render_access_control_split(
		array(
			'id'              => (string) $config['id'],
			'class'           => (string) $config['class'],
			'band'            => $band,
			'reverse'         => $reverse,
			'title_before'    => (string) $config['title_before'],
			'title_accent'    => (string) ( $config['title_accent'] ?? '' ),
			'title_after'     => (string) ( $config['title_after'] ?? '' ),
			'paragraphs'      => $config['paragraphs'] ?? array(),
			'paragraphs_html' => ! empty( $config['paragraphs_html'] ),
			'list'            => $config['list'] ?? array(),
			'visual'          => $visual,
		)
	);
}

/**
 * Hub service cards grid (photo-options template).
 */
function site_blocks_electronic_security_render_services(): void {
	site_blocks_render_photo_options_grid(
		array(
			'heading_id'    => 'sg-es-services-heading',
			'section_class' => 'sg-es-services',
			'band'          => 'white',
			'eyebrow'       => __( 'Electronic Security', 'site-blocks' ),
			'title_before'  => __( 'Electronic security, matched to your ', 'site-blocks' ),
			'title_accent'  => __( 'site', 'site-blocks' ),
			'intro'         => __( 'Alarms, CCTV, access control, intercoms and monitoring designed to work together across Sydney.', 'site-blocks' ),
			'items'         => site_blocks_electronic_security_hub_photo_services(),
			'cta'           => array(
				'title'        => __( 'One system. Total control.', 'site-blocks' ),
				'checks'       => array(
					__( 'Master Licence Holder', 'site-blocks' ),
					__( 'ASIAL Member', 'site-blocks' ),
					__( '24/7 Monitoring Ready', 'site-blocks' ),
					__( 'Integrated Design', 'site-blocks' ),
					__( 'Portal Support', 'site-blocks' ),
				),
				'button_label' => __( 'Request a Quote', 'site-blocks' ),
				'button_url'   => home_url( '/contact/' ),
				'icon'         => 'shield-check',
			),
		)
	);
}

/**
 * Process strip (skeleton process flow).
 */
function site_blocks_electronic_security_render_process(): void {
	$config = site_blocks_process_flow_config( 'electronic-security' );

	if ( null !== $config ) {
		site_blocks_render_process_flow( $config );
	}
}

/**
 * Customer portal band.
 */
function site_blocks_electronic_security_render_portal( string $page_key ): void {
	$configs = array(
		'hub' => array(
			'heading_id'    => 'sg-es-hub-portal-heading',
			'section_class' => 'sg-es-hub-portal',
			'title_before'  => __( 'Quotes and scope in ', 'site-blocks' ),
			'title_accent'  => __( 'one portal', 'site-blocks' ),
			'intro'         => __( 'Request alarms, CCTV or access control online, share site photos and approve scope without chasing email threads.', 'site-blocks' ),
			'bullets'       => array(
				__( 'Document rooms, entries and existing equipment', 'site-blocks' ),
				__( 'Upload floor plans, photos and access notes', 'site-blocks' ),
				__( 'Message our team and approve your quote', 'site-blocks' ),
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
 * Trust panel for Electronic Security pages.
 */
function site_blocks_electronic_security_render_trust( string $page_key ): void {
	$renderer = static function ( string $icon ): void {
		site_blocks_lucide_icon( $icon, 24 );
	};

	site_blocks_render_trust_panel(
		array(
			'heading_id'    => 'sg-es-trust-heading-' . sanitize_html_class( $page_key ),
			'section_class' => 'sg-es-trust sg-es-trust--' . sanitize_html_class( $page_key ),
			'title_before'  => __( 'Electronic security delivered by licensed ', 'site-blocks' ),
			'title_accent'  => __( 'professionals', 'site-blocks' ),
			'items'         => array(
				array( 'title' => __( 'Master Licence holder', 'site-blocks' ), 'desc' => __( 'NSW-licensed install and monitoring.', 'site-blocks' ), 'icon' => 'badge-check' ),
				array( 'title' => __( 'Integrated systems', 'site-blocks' ), 'desc' => __( 'Alarms, CCTV, access and monitoring together.', 'site-blocks' ), 'icon' => 'layers' ),
				array( 'title' => __( 'Commercial & residential', 'site-blocks' ), 'desc' => __( 'From apartments to multi-site programs.', 'site-blocks' ), 'icon' => 'building-2' ),
				array( 'title' => __( 'ASIAL member', 'site-blocks' ), 'desc' => __( 'Industry-accredited security operations.', 'site-blocks' ), 'icon' => 'award' ),
			),
			'icon_renderer' => $renderer,
		)
	);
}

/**
 * End-of-page related services grid.
 */
function site_blocks_electronic_security_render_related_services_grid( string $page_key ): void {
	$config = site_blocks_electronic_security_related_page_grid_config( $page_key );

	if ( null === $config ) {
		return;
	}

	$config['icon_renderer'] = 'site_blocks_cctv_icon';

	site_blocks_render_related_services_page_grid( $config );
}

/**
 * FAQ accordion section.
 */
function site_blocks_electronic_security_render_faq( string $page_key ): void {
	site_blocks_render_faq_section(
		site_blocks_electronic_security_faq_items( $page_key ),
		array(
			'heading_id'     => 'sg-es-faq-heading',
			'heading_before' => __( 'Questions, ', 'site-blocks' ),
			'heading_accent' => __( 'answered', 'site-blocks' ),
			'id_prefix'      => 'sg-es-faq-',
			'section_class'  => 'sg-cctv-faq sg-es-faq',
		)
	);
}

/**
 * Final navy CTA band.
 */
function site_blocks_electronic_security_render_cta( string $page_key ): void {
	$config = site_blocks_electronic_security_cta_config( $page_key );
	$ctas   = site_blocks_electronic_security_ctas();

	if ( null === $config ) {
		return;
	}

	site_blocks_render_quote_cta(
		array(
			'heading_id'      => 'sg-es-cta-heading',
			'before'          => (string) $config['before'],
			'accent'          => (string) $config['accent'],
			'after'           => (string) ( $config['after'] ?? '' ),
			'sub'             => (string) $config['sub'],
			'primary_label'   => $ctas['primary_label'],
			'primary_url'     => $ctas['primary_url'],
			'secondary_label' => $ctas['secondary_label'],
			'secondary_url'   => $ctas['secondary_url'],
			'section_class'   => 'sg-cctv-cta sg-es-cta',
		)
	);
}
