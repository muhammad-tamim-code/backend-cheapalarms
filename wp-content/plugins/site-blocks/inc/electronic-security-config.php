<?php
/**
 * Electronic Security silo, copy, SEO, and section data per page key.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared CTA URLs for Electronic Security pages.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_electronic_security_ctas(): array {
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
function site_blocks_electronic_security_page_slugs(): array {
	return array(
		'hub' => 'electronic-security',
	);
}

/**
 * Whether the current request is an Electronic Security silo page.
 */
function site_blocks_is_electronic_security_page(): bool {
	return null !== site_blocks_get_electronic_security_page_key();
}

/**
 * Detect the active Electronic Security page key.
 */
function site_blocks_get_electronic_security_page_key(): ?string {
	foreach ( site_blocks_electronic_security_page_slugs() as $key => $path ) {
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
function site_blocks_electronic_security_page_sections(): array {
	return array(
		'hub' => array( 'covers', 'services', 'integration', 'why', 'process', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
	);
}

/**
 * Whether a section applies to the given page key.
 */
function site_blocks_electronic_security_section_applies( string $page_key, string $section ): bool {
	$sections = site_blocks_electronic_security_page_sections();

	return isset( $sections[ $page_key ] ) && in_array( $section, $sections[ $page_key ], true );
}

/**
 * Document title per page key.
 */
function site_blocks_electronic_security_document_title( string $page_key ): string {
	$titles = array(
		'hub' => __( 'Electronic Security Systems Sydney | Safeguard', 'site-blocks' ),
	);

	return $titles[ $page_key ] ?? '';
}

/**
 * Meta description per page key.
 */
function site_blocks_electronic_security_get_meta_description( string $page_key ): string {
	$descriptions = array(
		'hub' => __( 'Alarms, CCTV, access control, intercoms and monitoring for Sydney homes and businesses, designed, installed and supported by Safeguard under Master Licence #000103619.', 'site-blocks' ),
	);

	return $descriptions[ $page_key ] ?? '';
}

/**
 * Hero config per page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_electronic_security_hero_config( string $page_key ): ?array {
	$ctas = site_blocks_electronic_security_ctas();

	$configs = array(
		'hub' => array_merge(
			$ctas,
			array(
				'id'              => 'sg-es-hero-heading',
				'class'           => 'sg-es-hero',
				'breadcrumb'      => array(),
				'badge'           => __( 'Electronic Security', 'site-blocks' ),
				'title_before'    => __( 'Smarter technology. Stronger protection. All in one place.', 'site-blocks' ),
				'title_accent'    => '',
				'title_after'     => '',
				'lead'            => __( 'Alarms, CCTV, access control, intercoms and monitoring — designed, installed and supported as one connected system across Greater Sydney.', 'site-blocks' ),
				'primary_label'   => __( 'Explore Solutions', 'site-blocks' ),
				'primary_url'     => home_url( '/get-an-instant-quote/' ),
				'secondary_label' => '1300 225 276',
				'secondary_url'   => 'tel:1300225276',
				'primary_icon'    => '',
				'secondary_icon'  => 'phone',
				'features'        => array(
					array(
						'icon'  => 'shield-check',
						'label' => __( 'Trusted Solutions', 'site-blocks' ),
					),
					array(
						'icon'  => 'settings',
						'label' => __( 'Expert Installation', 'site-blocks' ),
					),
					array(
						'icon'  => 'headset',
						'label' => __( 'Ongoing Support', 'site-blocks' ),
					),
					array(
						'icon'  => 'award',
						'label' => __( 'Quality Guaranteed', 'site-blocks' ),
					),
				),
				'trust_strip'     => array(),
				'hero_image'      => 'hub-hero.webp',
				'hero_alt'        => __( 'Electronic security hardware: CCTV camera, alarm keypad, wireless hub and video intercom', 'site-blocks' ),
			)
		),
	);

	return $configs[ $page_key ] ?? null;
}

/**
 * FAQ items per page key.
 *
 * @return array<int, array{q: string, a: string}>
 */
function site_blocks_electronic_security_faq_items( string $page_key ): array {
	$faqs = array(
		'hub' => array(
			array(
				'q' => __( 'Do you install and monitor alarm systems?', 'site-blocks' ),
				'a' => __( 'Yes. Safeguard designs, installs and can connect alarms to professional monitoring, with clear response plans agreed before go-live.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can CCTV work with access control and intercoms?', 'site-blocks' ),
				'a' => __( 'Yes. We plan detection, entry and communication as one system so cameras, doors and intercoms support the same workflows.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Are you licensed for electronic security work?', 'site-blocks' ),
				'a' => __( 'Yes. Safeguard operates under NSW Master Licence #000103619 and is an ASIAL member.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you cover both homes and commercial sites?', 'site-blocks' ),
				'a' => __( 'Yes. From apartments and townhouses to retail, offices and industrial sites across Greater Sydney.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can you upgrade existing cameras or alarms?', 'site-blocks' ),
				'a' => __( 'Often, yes. We assess what you have, what can be reused and what should be replaced for reliable monitoring and response.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How do I get a quote?', 'site-blocks' ),
				'a' => __( 'Request a quote online or call our team. We review your property, risk and goals, then provide a tailored scope and price.', 'site-blocks' ),
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
function site_blocks_electronic_security_hub_services(): array {
	$items = site_blocks_electronic_security_hub_photo_services();
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
function site_blocks_electronic_security_hub_photo_services(): array {
	return array(
		array(
			'title' => __( 'Alarm Systems', 'site-blocks' ),
			'desc'  => __( 'Detection that triggers monitoring and on-site response.', 'site-blocks' ),
			'url'   => home_url( '/alarm-systems/' ),
			'image' => 'images/alarm/alarm-hero.webp',
			'alt'   => __( 'Professionally installed alarm system for Sydney properties', 'site-blocks' ),
			'icon'  => 'bell-ring',
		),
		array(
			'title' => __( 'Ajax Alarm Systems', 'site-blocks' ),
			'desc'  => __( 'Wireless alarm systems with app control and professional monitoring.', 'site-blocks' ),
			'url'   => home_url( '/ajax-alarm-systems/' ),
			'image' => 'images/ajax/ajax-hero-house.webp',
			'alt'   => __( 'Ajax wireless alarm system protecting a Sydney home', 'site-blocks' ),
			'icon'  => 'smartphone',
		),
		array(
			'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
			'desc'  => __( 'See and record activity across your site.', 'site-blocks' ),
			'url'   => home_url( '/cctv-security-cameras/' ),
			'image' => 'images/cctv/commercial.webp',
			'alt'   => __( 'Commercial CCTV cameras covering a Sydney site', 'site-blocks' ),
			'icon'  => 'cctv',
		),
		array(
			'title' => __( 'Access Control', 'site-blocks' ),
			'desc'  => __( 'Manage who enters and when with cards, codes or mobile credentials.', 'site-blocks' ),
			'url'   => home_url( '/access-control/' ),
			'image' => 'images/access-control/hero.webp',
			'alt'   => __( 'Access control card reader at a Sydney building entrance', 'site-blocks' ),
			'icon'  => 'lock',
		),
		array(
			'title' => __( 'Intercom Systems', 'site-blocks' ),
			'desc'  => __( 'Video entry and communication for doors and gates.', 'site-blocks' ),
			'url'   => home_url( '/intercom-systems/' ),
			'image' => 'images/intercom/hero.webp',
			'alt'   => __( 'Akuvox video intercom outdoor station and indoor monitor', 'site-blocks' ),
			'icon'  => 'video',
		),
		array(
			'title' => __( 'Monitoring & Response', 'site-blocks' ),
			'desc'  => __( '24/7 alarm monitoring and coordinated response across Sydney.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/' ),
			'image' => 'images/monitoring/hub-hero.webp',
			'alt'   => __( '24/7 professional alarm monitoring centre', 'site-blocks' ),
			'icon'  => 'headset',
		),
	);
}

/**
 * Split-section data for a page key and section id.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_electronic_security_split_config( string $page_key, string $section ): ?array {
	$manpower_url = home_url( '/manpower/' );
	$physical_url = home_url( '/physical-security/' );

	$splits = array(
		'hub' => array(
			'covers' => array(
				'id'           => 'sg-es-covers-heading',
				'class'        => 'sg-es-covers',
				'band'         => 'white',
				'title_before' => __( 'Detection, entry and response in one ', 'site-blocks' ),
				'title_accent' => __( 'system', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Electronic security covers the technology that watches your site: alarm detection, CCTV recording, access control at doors and gates, intercoms for visitors, and monitoring that links alerts to action. Safeguard designs these layers to work together so a break-in, forced door or after-hours movement triggers the right response, not a string of disconnected notifications.', 'site-blocks' ),
				),
				'image'        => 'covers.webp',
				'alt'          => __( 'Alarm panel and CCTV monitor in a Sydney site office', 'site-blocks' ),
				'reverse'      => false,
			),
			'integration' => array(
				'id'           => 'sg-es-integration-heading',
				'class'        => 'sg-es-integration',
				'band'         => 'white',
				'title_before' => __( 'Electronics plus people on the ', 'site-blocks' ),
				'title_accent' => __( 'ground', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Cameras and alarms are strongest when someone can verify and act. Safeguard connects electronic security with licensed manpower and physical security so monitoring, patrols and on-site guards share the same plans and contacts.', 'site-blocks' ),
					sprintf(
						/* translators: 1: ManPower link, 2: Physical Security link */
						__( 'Explore %1$s for static guards, mobile patrols and alarm response, and %2$s for guarding matched to your site risk.', 'site-blocks' ),
						'<a href="' . esc_url( $manpower_url ) . '">' . esc_html__( 'ManPower', 'site-blocks' ) . '</a>',
						'<a href="' . esc_url( $physical_url ) . '">' . esc_html__( 'Physical Security', 'site-blocks' ) . '</a>'
					),
				),
				'paragraphs_html' => true,
				'image'           => 'integration.webp',
				'alt'             => __( 'Security officer reviewing CCTV alongside alarm monitoring', 'site-blocks' ),
				'reverse'           => true,
			),
			'why' => array(
				'id'           => 'sg-es-why-heading',
				'class'        => 'sg-es-why',
				'band'         => 'white',
				'title_before' => __( 'Licensed, integrated and ', 'site-blocks' ),
				'title_accent' => __( 'accountable', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Master Licence holder', 'site-blocks' ), 'desc' => __( 'NSW Master Licence #000103619 for install and monitoring work.', 'site-blocks' ) ),
					array( 'title' => __( 'One accountable partner', 'site-blocks' ), 'desc' => __( 'Design, install, monitoring and response under one team.', 'site-blocks' ) ),
					array( 'title' => __( 'Tested products', 'site-blocks' ), 'desc' => __( 'Reliable alarm, camera and access platforms chosen for Sydney conditions.', 'site-blocks' ) ),
					array( 'title' => __( 'ASIAL member', 'site-blocks' ), 'desc' => __( 'Industry-accredited security operations across Greater Sydney.', 'site-blocks' ) ),
				),
				'image'        => 'why.webp',
				'alt'          => __( 'Safeguard technician commissioning electronic security in Sydney', 'site-blocks' ),
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
function site_blocks_electronic_security_related_page_grid_config( string $page_key ): ?array {
	$grids = array(
		'hub' => array(
			'heading_id'    => 'sg-es-related-heading',
			'section_class' => 'sg-es-related',
			'title_before'  => __( 'Related ', 'site-blocks' ),
			'title_accent'  => __( 'services', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Physical Security', 'site-blocks' ), 'desc' => __( 'Licensed guards and patrols backed by your systems.', 'site-blocks' ), 'url' => home_url( '/physical-security/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'ManPower', 'site-blocks' ), 'desc' => __( 'Static guards, patrols and short-notice cover.', 'site-blocks' ), 'url' => home_url( '/manpower/' ), 'icon' => 'alarm-systems.png' ),
				array( 'title' => __( 'Enterprise Solutions', 'site-blocks' ), 'desc' => __( 'Multi-site programs and managed security.', 'site-blocks' ), 'url' => home_url( '/enterprise-solutions/' ), 'icon' => 'access-control.png' ),
				array( 'title' => __( 'Contact', 'site-blocks' ), 'desc' => __( 'Speak to our team about your property.', 'site-blocks' ), 'url' => home_url( '/contact/' ), 'icon' => 'ip-camera.png' ),
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
function site_blocks_electronic_security_cta_config( string $page_key ): ?array {
	$ctas = array(
		'hub' => array(
			'before' => __( 'Design electronic security for your ', 'site-blocks' ),
			'accent' => __( 'site', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Tell us about your property, risk and existing systems. We\'ll recommend the right mix of alarms, cameras, access and monitoring, and provide a tailored quote.', 'site-blocks' ),
		),
	);

	return $ctas[ $page_key ] ?? null;
}
