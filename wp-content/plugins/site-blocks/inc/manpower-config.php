<?php
/**
 * ManPower silo, copy, SEO, and section data per page key.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared CTA URLs for ManPower pages.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_manpower_ctas(): array {
	return array(
		'primary_label'   => __( 'Request a Quote', 'site-blocks' ),
		'primary_url'     => home_url( '/contact/' ),
		'secondary_label' => __( 'Speak to Us', 'site-blocks' ),
		'secondary_url'   => 'tel:1300225276',
	);
}

/**
 * Page keys in this silo.
 *
 * @return array<string, string> key => hierarchical slug path.
 */
function site_blocks_manpower_page_slugs(): array {
	return array(
		'hub' => 'manpower',
	);
}

/**
 * Whether the current request is a ManPower silo page.
 */
function site_blocks_is_manpower_page(): bool {
	return null !== site_blocks_get_manpower_page_key();
}

/**
 * Detect the active ManPower page key.
 */
function site_blocks_get_manpower_page_key(): ?string {
	foreach ( site_blocks_manpower_page_slugs() as $key => $path ) {
		if ( is_page( $path ) ) {
			return $key;
		}
	}

	return null;
}

/**
 * Sections available per page key.
 *
 * @return array<string, array<int, string>>
 */
function site_blocks_manpower_page_sections(): array {
	return array(
		'hub' => array( 'covers', 'services', 'integration', 'why', 'process', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
	);
}

/**
 * Whether a section applies to the given page key.
 */
function site_blocks_manpower_section_applies( string $page_key, string $section ): bool {
	$sections = site_blocks_manpower_page_sections();

	return isset( $sections[ $page_key ] ) && in_array( $section, $sections[ $page_key ], true );
}

/**
 * Document title per page key.
 */
function site_blocks_manpower_document_title( string $page_key ): string {
	$titles = array(
		'hub' => __( 'Security ManPower Sydney | Guards & Staffing | Safeguard', 'site-blocks' ),
	);

	return $titles[ $page_key ] ?? '';
}

/**
 * Meta description per page key.
 */
function site_blocks_manpower_get_meta_description( string $page_key ): string {
	$descriptions = array(
		'hub' => __( 'Licensed security manpower across Sydney, static guards, mobile patrols, alarm response and short-notice cover, under Master Licence #000103619.', 'site-blocks' ),
	);

	return $descriptions[ $page_key ] ?? '';
}

/**
 * Hero config per page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_manpower_hero_config( string $page_key ): ?array {
	$ctas = site_blocks_manpower_ctas();

	$configs = array(
		'hub' => array(
			'id'            => 'sg-mp-hero-heading',
			'class'         => 'sg-mp-hero',
			'breadcrumb'    => array(
				array(
					'label' => __( 'Home', 'site-blocks' ),
					'url'   => home_url( '/' ),
				),
				array(
					'label' => __( 'Services', 'site-blocks' ),
					'url'   => home_url( '/physical-security/' ),
				),
				array(
					'label'   => __( 'ManPower', 'site-blocks' ),
					'current' => true,
				),
			),
			'badge'         => '',
			'title_before'  => __( 'Professional people. Real protection.', 'site-blocks' ),
			'title_accent'  => '',
			'title_after'   => '',
			'leads'         => array(
				__( 'Our officers are screened, licensed and trained to protect people, property and reputation — not just fill a roster.', 'site-blocks' ),
				__( 'From static presence to mobile patrols and alarm response, we match manpower to your risk, hours and site layout across Greater Sydney.', 'site-blocks' ),
			),
			'lead'          => '',
			'primary_label' => __( 'Hire ManPower', 'site-blocks' ),
			'primary_url'   => home_url( '/get-an-instant-quote/' ),
			'features'      => array(
				array(
					'icon'  => 'user-check',
					'label' => __( 'Static Security Guards', 'site-blocks' ),
					'desc'  => __( 'Constant on-site presence for entrances, retail, offices and residential.', 'site-blocks' ),
				),
				array(
					'icon'  => 'car',
					'label' => __( 'Mobile Patrols', 'site-blocks' ),
					'desc'  => __( 'GPS-tracked vehicle patrols, lock-ups and after-hours site checks.', 'site-blocks' ),
				),
				array(
					'icon'  => 'bell-ring',
					'label' => __( 'Alarm Response', 'site-blocks' ),
					'desc'  => __( 'A licensed officer dispatched to verify and act when alarms trigger.', 'site-blocks' ),
				),
			),
			'stats'         => array(
				array(
					'value' => '13+',
					'label' => __( 'Years Experience', 'site-blocks' ),
				),
				array(
					'value' => '250+',
					'label' => __( 'Sites Protected', 'site-blocks' ),
				),
				array(
					'value' => '99.8%',
					'label' => __( 'Client Satisfaction', 'site-blocks' ),
				),
			),
			'trust_strip'   => array(),
			'hero_image'    => 'hub-hero.webp',
			'hero_alt'      => __( 'Licensed Safeguard security officer in uniform', 'site-blocks' ),
		),
	);

	return $configs[ $page_key ] ?? null;
}

/**
 * FAQ items per page key.
 *
 * @return array<int, array{q: string, a: string}>
 */
function site_blocks_manpower_faq_items( string $page_key ): array {
	$faqs = array(
		'hub' => array(
			array(
				'q' => __( 'Are your security officers licensed?', 'site-blocks' ),
				'a' => __( 'Yes. Every officer holds the required NSW licence, and Safeguard operates under Master Licence #000103619.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Static guards or mobile patrols: which do I need?', 'site-blocks' ),
				'a' => __( 'Static suits sites needing constant presence and immediate response; mobile suits multiple sites or after-hours checks at lower cost. Many sites use both. We advise at the assessment.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How quickly can you deploy cover?', 'site-blocks' ),
				'a' => __( 'For most Sydney sites, officers can be arranged at short notice. Emergency and short-notice cover is available when risk spikes.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Is cover available 24/7?', 'site-blocks' ),
				'a' => __( 'Yes. We roster static guards, mobile patrols and alarm response around the clock across Greater Sydney.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you cover all of Sydney?', 'site-blocks' ),
				'a' => __( 'Yes. We supply manpower across Greater Sydney, from single-site guarding to multi-location patrol programs.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can officers work with my cameras and alarms?', 'site-blocks' ),
				'a' => __( 'Yes. Because Safeguard also installs electronic security, officers can coordinate with CCTV, alarms and monitoring for one connected response.', 'site-blocks' ),
			),
		),
	);

	return $faqs[ $page_key ] ?? array();
}

/**
 * Hub service cards for the simple text grid.
 *
 * @return array<int, array{title: string, desc: string, url: string}>
 */
function site_blocks_manpower_hub_services(): array {
	$items = site_blocks_manpower_hub_photo_services();
	$out   = array();

	foreach ( $items as $item ) {
		$out[] = array(
			'title' => (string) $item['title'],
			'desc'  => (string) $item['desc'],
			'url'   => (string) $item['url'],
		);
	}

	return $out;
}

/**
 * Hub service cards for the photo-options template.
 *
 * @return array<int, array{title: string, desc: string, url: string, image: string, alt: string, icon: string}>
 */
function site_blocks_manpower_hub_photo_services(): array {
	return array(
		array(
			'title' => __( 'Static Security Guards', 'site-blocks' ),
			'desc'  => __( 'A constant, on-site presence for entrances, retail, offices and residential.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/static-guards/' ),
			'image' => 'images/physical-security/static-hero.webp',
			'alt'   => __( 'Licensed static security guard on duty in Sydney', 'site-blocks' ),
			'icon'  => 'user',
		),
		array(
			'title' => __( 'Mobile Patrols', 'site-blocks' ),
			'desc'  => __( 'GPS-tracked vehicle patrols, lock-ups and after-hours checks across multiple sites.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/mobile-patrols/' ),
			'image' => 'images/physical-security/mobile-hero.webp',
			'alt'   => __( 'Safeguard mobile patrol vehicle on site', 'site-blocks' ),
			'icon'  => 'car',
		),
		array(
			'title' => __( 'Alarm Response', 'site-blocks' ),
			'desc'  => __( 'A licensed officer dispatched to verify and act when your alarm triggers.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/back-to-base/' ),
			'image' => 'images/physical-security/mobile-tracked.webp',
			'alt'   => __( 'Security monitoring and alarm response operations', 'site-blocks' ),
			'icon'  => 'bell-ring',
		),
		array(
			'title' => __( 'Event & Crowd Control', 'site-blocks' ),
			'desc'  => __( 'Crowd management, access and safety for functions and venues.', 'site-blocks' ),
			'url'   => home_url( '/contact/' ),
			'image' => 'images/physical-security/hub-covers.webp',
			'alt'   => __( 'Security officers managing crowd access at an event', 'site-blocks' ),
			'icon'  => 'users',
		),
		array(
			'title' => __( 'Concierge / Front of House', 'site-blocks' ),
			'desc'  => __( 'Front-of-house professionalism with trained vigilance.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/static-guards/' ),
			'image' => 'images/physical-security/static-duties.webp',
			'alt'   => __( 'Concierge security officer at a building reception desk', 'site-blocks' ),
			'icon'  => 'briefcase',
		),
		array(
			'title' => __( 'Short-notice Cover', 'site-blocks' ),
			'desc'  => __( 'Rapid deployment when risk spikes or staff are unavailable.', 'site-blocks' ),
			'url'   => home_url( '/contact/' ),
			'image' => 'images/physical-security/hub-why.webp',
			'alt'   => __( 'Security team ready for short-notice deployment', 'site-blocks' ),
			'icon'  => 'clock',
		),
	);
}

/**
 * Split-section data for a page key and section id.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_manpower_split_config( string $page_key, string $section ): ?array {
	$electronic_url = home_url( '/electronic-security/' );

	$splits = array(
		'hub' => array(
			'covers' => array(
				'id'           => 'sg-mp-covers-heading',
				'class'        => 'sg-mp-covers',
				'band'         => 'white',
				'title_before' => __( 'People where your site needs ', 'site-blocks' ),
				'title_accent' => __( 'them', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'ManPower covers licensed security personnel on your site: static guards at entrances and high-value areas, mobile patrols across perimeters and multiple locations, alarm response when monitoring dispatches an officer, event and crowd control, concierge-style front-of-house cover, and short-notice staffing when plans change. Safeguard matches roster size, hours and supervision to your risk, site layout and budget.', 'site-blocks' ),
				),
				'image'        => 'covers.webp',
				'alt'          => __( 'Security officers on patrol at a Sydney commercial site', 'site-blocks' ),
				'reverse'      => false,
			),
			'integration' => array(
				'id'           => 'sg-mp-integration-heading',
				'class'        => 'sg-mp-integration',
				'band'         => 'white',
				'title_before' => __( 'Manpower plus electronic ', 'site-blocks' ),
				'title_accent' => __( 'security', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Officers are strongest when cameras, alarms and access control support them. Safeguard supplies both manpower and electronic security under one Master Licence, so patrol routes, alarm response and on-site guarding share the same contacts, escalation rules and reporting.', 'site-blocks' ),
					sprintf(
						/* translators: %s: Electronic Security link */
						__( 'See %s for alarms, CCTV, access control and monitoring designed to work with your guarding plan.', 'site-blocks' ),
						'<a href="' . esc_url( $electronic_url ) . '">' . esc_html__( 'Electronic Security', 'site-blocks' ) . '</a>'
					),
				),
				'paragraphs_html' => true,
				'image'           => 'integration.webp',
				'alt'             => __( 'Guard coordinating with CCTV and alarm monitoring in Sydney', 'site-blocks' ),
				'reverse'           => true,
			),
			'why' => array(
				'id'           => 'sg-mp-why-heading',
				'class'        => 'sg-mp-why',
				'band'         => 'white',
				'title_before' => __( 'Licensed, compliant and ', 'site-blocks' ),
				'title_accent' => __( 'flexible', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Fully licensed', 'site-blocks' ), 'desc' => __( 'Master Licence #000103619; officers licensed under NSW requirements.', 'site-blocks' ) ),
					array( 'title' => __( 'Permanent or casual', 'site-blocks' ), 'desc' => __( 'Ongoing rosters or short-notice cover when your site needs flexible manpower.', 'site-blocks' ) ),
					array( 'title' => __( 'NSW compliance', 'site-blocks' ), 'desc' => __( 'Work delivered to industry standards with clear supervision and reporting.', 'site-blocks' ) ),
					array( 'title' => __( 'ASIAL member', 'site-blocks' ), 'desc' => __( 'Industry-accredited security operations across Greater Sydney.', 'site-blocks' ) ),
				),
				'image'        => 'why.webp',
				'alt'          => __( 'Safeguard security team briefing in Sydney', 'site-blocks' ),
				'reverse'      => false,
			),
		),
	);

	return $splits[ $page_key ][ $section ] ?? null;
}

/**
 * End-of-page related services grid per page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_manpower_related_page_grid_config( string $page_key ): ?array {
	$grids = array(
		'hub' => array(
			'heading_id'    => 'sg-mp-related-heading',
			'section_class' => 'sg-mp-related',
			'title_before'  => __( 'Related ', 'site-blocks' ),
			'title_accent'  => __( 'services', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Electronic Security', 'site-blocks' ), 'desc' => __( 'Alarms, CCTV, access and monitoring for your site.', 'site-blocks' ), 'url' => home_url( '/electronic-security/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Physical Security', 'site-blocks' ), 'desc' => __( 'Guarding and patrols matched to your site risk.', 'site-blocks' ), 'url' => home_url( '/physical-security/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Monitoring', 'site-blocks' ), 'desc' => __( '24/7 alarm monitoring and coordinated response.', 'site-blocks' ), 'url' => home_url( '/monitoring/' ), 'icon' => 'alarm-systems.png' ),
				array( 'title' => __( 'Contact', 'site-blocks' ), 'desc' => __( 'Speak to our team about manpower cover.', 'site-blocks' ), 'url' => home_url( '/contact/' ), 'icon' => 'access-control.png' ),
			),
		),
	);

	return $grids[ $page_key ] ?? null;
}

/**
 * Final CTA band per page key.
 *
 * @return array{before: string, accent: string, after?: string, sub: string}|null
 */
function site_blocks_manpower_cta_config( string $page_key ): ?array {
	$ctas = array(
		'hub' => array(
			'before' => __( 'Put licensed people on your ', 'site-blocks' ),
			'accent' => __( 'site', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Tell us about your site, hours and risk. We\'ll recommend the right mix of static, mobile and alarm response cover, and provide a tailored manpower quote.', 'site-blocks' ),
		),
	);

	return $ctas[ $page_key ] ?? null;
}
