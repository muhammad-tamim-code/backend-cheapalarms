<?php
/**
 * Physical Security silo, copy, SEO, and section data per page key.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared CTA URLs for Physical Security pages.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_physical_security_ctas(): array {
	return array(
		'primary_label'   => __( 'Request a Quote', 'site-blocks' ),
		'primary_url'     => home_url( '/contact/' ),
		'secondary_label' => __( 'Speak to Our Team', 'site-blocks' ),
		'secondary_url'   => 'tel:1300225276',
	);
}

/**
 * Page keys in this silo.
 *
 * @return array<string, string> key => hierarchical slug path.
 */
function site_blocks_physical_security_page_slugs(): array {
	return array(
		'hub'            => 'physical-security',
		'static-guards'  => 'physical-security/static-guards',
		'mobile-patrols' => 'physical-security/mobile-patrols',
	);
}

/**
 * Whether the current request is a Physical Security silo page.
 */
function site_blocks_is_physical_security_page(): bool {
	return null !== site_blocks_get_physical_security_page_key();
}

/**
 * Detect the active Physical Security page key.
 */
function site_blocks_get_physical_security_page_key(): ?string {
	foreach ( site_blocks_physical_security_page_slugs() as $key => $path ) {
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
function site_blocks_physical_security_page_sections(): array {
	return array(
		'hub'            => array( 'covers', 'services', 'integration', 'sites', 'why', 'process', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
		'static-guards'  => array( 'intro', 'duties', 'industries', 'integration', 'why', 'compare', 'process', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
		'mobile-patrols' => array( 'intro', 'duties', 'industries', 'integration', 'why', 'compare', 'process', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
	);
}

/**
 * Whether a section applies to the given page key.
 */
function site_blocks_physical_security_section_applies( string $page_key, string $section ): bool {
	$sections = site_blocks_physical_security_page_sections();

	return isset( $sections[ $page_key ] ) && in_array( $section, $sections[ $page_key ], true );
}

/**
 * Document title per page key.
 */
function site_blocks_physical_security_document_title( string $page_key ): string {
	$titles = array(
		'hub'            => __( 'Security Guards Sydney | Physical Security | Safeguard', 'site-blocks' ),
		'static-guards'  => __( 'Static Security Guards Sydney | On-Site Guarding | Safeguard', 'site-blocks' ),
		'mobile-patrols' => __( 'Mobile Patrol Services Sydney | Security Patrols | Safeguard', 'site-blocks' ),
	);

	return $titles[ $page_key ] ?? '';
}

/**
 * Meta description per page key.
 */
function site_blocks_physical_security_get_meta_description( string $page_key ): string {
	$descriptions = array(
		'hub'            => __( 'Licensed security guards and patrols across Sydney, static guarding, mobile patrols, alarm response and event security, backed by CCTV, alarms and monitoring.', 'site-blocks' ),
		'static-guards'  => __( 'Licensed static security guards across Sydney, a constant on-site presence for retail, offices, warehouses, strata and events, backed by CCTV and monitoring.', 'site-blocks' ),
		'mobile-patrols' => __( 'GPS-tracked mobile security patrols across Sydney, scheduled and random checks, lock-ups, alarm response and after-hours cover, with clear digital reporting.', 'site-blocks' ),
	);

	return $descriptions[ $page_key ] ?? '';
}

/**
 * Hero config per page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_physical_security_hero_config( string $page_key ): ?array {
	$ctas = site_blocks_physical_security_ctas();
	$hub  = home_url( '/physical-security/' );

	$configs = array(
		'hub' => array_merge(
			$ctas,
			array(
				'id'            => 'sg-physical-security-hero-heading',
				'class'         => 'sg-physical-security-hero',
				'breadcrumb'    => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Physical Security', 'site-blocks' ), 'current' => true ),
				),
				'badge'         => __( 'Physical Security · Sydney', 'site-blocks' ),
				'title_before'  => __( 'People on the ground, backed by ', 'site-blocks' ),
				'title_accent'  => __( 'technology', 'site-blocks' ),
				'title_after'   => __( '.', 'site-blocks' ),
				'lead'          => __( 'Safeguard provides licensed security guards and patrols across Sydney, static guarding, mobile patrols, alarm response, event and retail security. Because we also design your cameras, alarms and access control, our officers arrive backed by the technology watching your site, not working blind.', 'site-blocks' ),
				'trust_strip'   => array(
					__( 'Licensed personnel', 'site-blocks' ),
					__( 'Master Licence #000103619', 'site-blocks' ),
					__( 'ASIAL member', 'site-blocks' ),
					__( 'Compliant with AS 4421', 'site-blocks' ),
				),
				'hero_image'    => 'hub-hero.webp',
				'hero_alt'      => __( 'Licensed security guard at a Sydney commercial entrance', 'site-blocks' ),
			)
		),
		'static-guards' => array_merge(
			$ctas,
			array(
				'id'            => 'sg-static-guards-hero-heading',
				'class'         => 'sg-physical-security-hero sg-static-guards-hero',
				'breadcrumb'    => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Physical Security', 'site-blocks' ), 'url' => $hub ),
					array( 'label' => __( 'Static Security Guards', 'site-blocks' ), 'current' => true ),
				),
				'badge'         => __( 'Static Guarding · Sydney', 'site-blocks' ),
				'title_before'  => __( 'A constant presence, exactly where you ', 'site-blocks' ),
				'title_accent'  => __( 'need', 'site-blocks' ),
				'title_after'   => __( ' it.', 'site-blocks' ),
				'lead'          => __( 'Safeguard provides licensed static security guards across Sydney, a dedicated, visible officer on your site for the whole shift, managing access, deterring trouble and responding the moment something happens. Backed by our CCTV, alarms and monitoring.', 'site-blocks' ),
				'trust_strip'   => array(
					__( 'Licensed officers', 'site-blocks' ),
					__( 'Master Licence #000103619', 'site-blocks' ),
					__( 'ASIAL member', 'site-blocks' ),
					__( 'AS 4421 compliant', 'site-blocks' ),
				),
				'hero_image'    => 'static-hero.webp',
				'hero_alt'      => __( 'Static security guard at a Sydney building entrance', 'site-blocks' ),
			)
		),
		'mobile-patrols' => array_merge(
			$ctas,
			array(
				'id'            => 'sg-mobile-patrols-hero-heading',
				'class'         => 'sg-physical-security-hero sg-mobile-patrols-hero',
				'breadcrumb'    => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Physical Security', 'site-blocks' ), 'url' => $hub ),
					array( 'label' => __( 'Mobile Patrols', 'site-blocks' ), 'current' => true ),
				),
				'badge'         => __( 'Mobile Patrols · Sydney', 'site-blocks' ),
				'title_before'  => __( 'Cost-effective cover, on a schedule they can\'t ', 'site-blocks' ),
				'title_accent'  => __( 'predict', 'site-blocks' ),
				'title_after'   => __( '.', 'site-blocks' ),
				'lead'          => __( 'Safeguard\'s mobile patrols give your site professional protection without a full-time guard. GPS-tracked officers make scheduled and random checks across your premises, perimeter rounds, lock-ups, alarm response and welfare checks, with clear digital reporting after every visit.', 'site-blocks' ),
				'trust_strip'   => array(
					__( 'Licensed officers', 'site-blocks' ),
					__( 'Master Licence #000103619', 'site-blocks' ),
					__( 'ASIAL member', 'site-blocks' ),
					__( 'GPS-tracked & reported', 'site-blocks' ),
				),
				'hero_image'    => 'mobile-hero.webp',
				'hero_alt'      => __( 'Mobile security patrol vehicle at a Sydney site', 'site-blocks' ),
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
function site_blocks_physical_security_faq_items( string $page_key ): array {
	$faqs = array(
		'hub' => array(
			array(
				'q' => __( 'Static guards or mobile patrols: which do I need?', 'site-blocks' ),
				'a' => __( 'Static suits sites needing a constant presence and immediate response; mobile suits multiple sites or after-hours checks at lower cost. Many sites use both. We advise at the assessment.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Are your guards licensed?', 'site-blocks' ),
				'a' => __( 'Yes, every officer holds the required NSW licence, and Safeguard operates under Master Licence #000103619 to AS 4421 standards.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can guards work with my existing cameras and alarms?', 'site-blocks' ),
				'a' => __( 'Yes, that\'s our strength. Officers coordinate with CCTV, alarms, access control and monitoring for one connected response.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How quickly can you deploy?', 'site-blocks' ),
				'a' => __( 'For most Sydney sites, guards can be arranged at short notice; emergency cover is available.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you cover construction sites?', 'site-blocks' ),
				'a' => __( 'Yes, after-hours static or patrol cover during high-risk build phases, WHS-aware.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you provide event security?', 'site-blocks' ),
				'a' => __( 'Yes, crowd management, access and safety, compliant with venue licensing.', 'site-blocks' ),
			),
		),
		'static-guards' => array(
			array(
				'q' => __( 'How many guards do I need?', 'site-blocks' ),
				'a' => __( 'It depends on your site size, hours and risk, we recommend the right number at your assessment.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Are the guards licensed and insured?', 'site-blocks' ),
				'a' => __( 'Yes, licensed under NSW requirements, under Master Licence #000103619, fully insured.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can I have a guard only at certain hours?', 'site-blocks' ),
				'a' => __( 'Yes, overnight, weekends, event-only or ongoing; we build the roster around you.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Will the same guard be assigned?', 'site-blocks' ),
				'a' => __( 'We aim for consistency so officers learn your site, with trained relief cover.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can guards manage my CCTV and access control?', 'site-blocks' ),
				'a' => __( 'Yes, that\'s our strength as an integrated provider.', 'site-blocks' ),
			),
		),
		'mobile-patrols' => array(
			array(
				'q' => __( 'How often will you patrol?', 'site-blocks' ),
				'a' => __( 'As scheduled, plus deliberately random visits, set to your risk and budget.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can patrols respond to my alarm?', 'site-blocks' ),
				'a' => __( 'Yes, alarm response is included; an officer attends to verify and act.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Will I get proof of visits?', 'site-blocks' ),
				'a' => __( 'Yes, GPS tracking, time stamps, body-worn cameras and digital reports.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Is mobile cheaper than a static guard?', 'site-blocks' ),
				'a' => __( 'Usually, yes, it shares coverage across time and sites; we\'ll compare both for you.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you cover multiple locations?', 'site-blocks' ),
				'a' => __( 'Yes, one patrol program can cover many sites.', 'site-blocks' ),
			),
		),
	);

	return $faqs[ $page_key ] ?? array();
}

/**
 * Hub service cards (6 spokes).
 *
 * @return array<int, array{title: string, desc: string, url: string}>
 */
function site_blocks_physical_security_hub_services(): array {
	$hub = home_url( '/physical-security/' );

	return array(
		array(
			'title' => __( 'Static Security Guards', 'site-blocks' ),
			'desc'  => __( 'A constant, on-site presence for entrances, retail, offices and residential.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/static-guards/' ),
		),
		array(
			'title' => __( 'Mobile Patrols', 'site-blocks' ),
			'desc'  => __( 'GPS-tracked vehicle patrols, lock-ups and after-hours checks across multiple sites.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/mobile-patrols/' ),
		),
		array(
			'title' => __( 'Alarm Response', 'site-blocks' ),
			'desc'  => __( 'A licensed officer dispatched to verify and act when your alarm triggers.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/back-to-base/' ),
		),
		array(
			'title' => __( 'Event & Crowd Control', 'site-blocks' ),
			'desc'  => __( 'Crowd management, access and safety for functions and venues.', 'site-blocks' ),
			'url'   => home_url( '/contact/' ),
		),
		array(
			'title' => __( 'Concierge Security', 'site-blocks' ),
			'desc'  => __( 'Front-of-house professionalism with trained vigilance.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/static-guards/' ),
		),
		array(
			'title' => __( 'Retail Security & Loss Prevention', 'site-blocks' ),
			'desc'  => __( 'Reduce shrinkage and protect staff and stock.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/static-guards/' ),
		),
	);
}

/**
 * Cross-link integration services (CCTV, alarms, access).
 *
 * @return array<int, array{title: string, desc: string, url: string, icon: string}>
 */
function site_blocks_physical_security_integration_links(): array {
	return array(
		array(
			'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
			'desc'  => __( 'See and record activity across your site.', 'site-blocks' ),
			'url'   => home_url( '/cctv-security-cameras/' ),
			'icon'  => 'cctv',
		),
		array(
			'title' => __( 'Alarm Monitoring', 'site-blocks' ),
			'desc'  => __( 'Professional eyes on your alerts 24/7.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/' ),
			'icon'  => 'monitoring',
		),
		array(
			'title' => __( 'Access Control', 'site-blocks' ),
			'desc'  => __( 'Manage who enters and when.', 'site-blocks' ),
			'url'   => home_url( '/access-control/' ),
			'icon'  => 'access-control',
		),
	);
}

/**
 * Related services card strip for Physical Security integration sections.
 *
 * @param array<string, mixed> $split_config Split section config.
 * @return array<string, mixed>|null
 */
function site_blocks_physical_security_related_services_config( array $split_config ): ?array {
	$physical_hub = home_url( '/physical-security/' );

	$base = array(
		'eyebrow'           => __( 'Related services', 'site-blocks' ),
		'title_before'      => __( 'Explore the ', 'site-blocks' ),
		'title_accent'      => __( 'connected', 'site-blocks' ),
		'title_after'       => __( ' services', 'site-blocks' ),
		'intro'             => __( 'If you need another part of the system, these are the most common next steps.', 'site-blocks' ),
		'use_brand_icons'   => false,
		'hub_link'          => array(
			'label' => __( 'View all related services', 'site-blocks' ),
			'url'   => $physical_hub,
		),
	);

	if ( ! empty( $split_config['cross_label'] ) ) {
		return array_merge(
			$base,
			array(
				'cards' => array(
					array(
						'title' => __( 'Alarm Monitoring', 'site-blocks' ),
						'desc'  => __( 'Professional eyes on your alerts 24/7.', 'site-blocks' ),
						'url'   => home_url( '/monitoring/' ),
						'icon'  => 'monitoring',
					),
					array(
						'title' => __( 'Alarm Response', 'site-blocks' ),
						'desc'  => __( 'Licensed officers dispatched to verify and act.', 'site-blocks' ),
						'url'   => home_url( '/physical-security/#alarm-response' ),
						'icon'  => 'alarm-response',
					),
				),
			)
		);
	}

	$icon_map = array(
		'cctv'            => 'cctv',
		'monitoring'      => 'monitoring',
		'access-control'  => 'access-control',
	);

	$cards = array();
	foreach ( site_blocks_physical_security_integration_links() as $link ) {
		$icon_key = (string) ( $link['icon'] ?? 'service' );
		$cards[]  = array(
			'title' => $link['title'],
			'desc'  => $link['desc'],
			'url'   => $link['url'],
			'icon'  => $icon_map[ $icon_key ] ?? $icon_key,
		);
	}

	return array_merge( $base, array( 'cards' => $cards ) );
}

/**
 * Split-section data for a page key and section id.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_physical_security_split_config( string $page_key, string $section ): ?array {
	$splits = array(
		'hub' => array(
			'covers' => array(
				'id'           => 'sg-ps-covers-heading',
				'class'        => 'sg-physical-security-covers',
				'title_before' => __( 'A visible presence that ', 'site-blocks' ),
				'title_accent' => __( 'deters', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Cameras record and alarms alert, but a trained officer decides, responds, and reassures in the moment. A visible security presence is one of the strongest deterrents available: it discourages theft, trespass and anti-social behaviour before they start, and puts a real person on scene when something happens. Safeguard supplies that presence for Sydney homes, businesses, sites and events, from a single guard on a door to coordinated teams across multiple locations, matched to your site\'s risk, hours and budget.', 'site-blocks' ),
				),
				'image'        => 'hub-covers.webp',
				'alt'          => __( 'Security officer on site patrol in Sydney', 'site-blocks' ),
				'reverse'      => false,
			),
			'integration' => array(
				'id'           => 'sg-ps-integration-heading',
				'class'        => 'sg-physical-security-integration',
				'band'         => 'white',
				'title_before' => __( 'Officers backed by your own ', 'site-blocks' ),
				'title_accent' => __( 'systems', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'This is where Safeguard is different. Most guard companies only supply people; most alarm companies only supply hardware. We do both, so your officers work with your CCTV, alarms, access control and monitoring instead of around them. A camera flags movement, monitoring verifies it, and a guard responds. It\'s one coordinated response, not three vendors pointing at each other.', 'site-blocks' ),
				),
				'image'        => 'hub-integration.webp',
				'alt'          => __( 'Guard monitoring CCTV in a Sydney site office', 'site-blocks' ),
				'reverse'      => false,
			),
			'sites' => array(
				'id'           => 'sg-ps-sites-heading',
				'class'        => 'sg-physical-security-sites',
				'title_before' => __( 'Every kind of ', 'site-blocks' ),
				'title_accent' => __( 'site', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Retail', 'site-blocks' ), 'desc' => __( 'Loss prevention, staff and customer safety.', 'site-blocks' ) ),
					array( 'title' => __( 'Construction', 'site-blocks' ), 'desc' => __( 'After-hours cover against equipment and copper theft, WHS-aware.', 'site-blocks' ) ),
					array( 'title' => __( 'Warehouses & logistics', 'site-blocks' ), 'desc' => __( 'Access control and perimeter over large sites.', 'site-blocks' ) ),
					array( 'title' => __( 'Strata & residential', 'site-blocks' ), 'desc' => __( 'Resident, visitor and common-area security.', 'site-blocks' ) ),
					array( 'title' => __( 'Corporate & commercial', 'site-blocks' ), 'desc' => __( 'Reception, access and incident response.', 'site-blocks' ) ),
					array( 'title' => __( 'Events & venues', 'site-blocks' ), 'desc' => __( 'Crowd management and licensed-venue compliance.', 'site-blocks' ) ),
				),
				'image'        => 'hub-sites.webp',
				'alt'          => __( 'Static and mobile security across Sydney sites', 'site-blocks' ),
				'reverse'      => true,
			),
			'why' => array(
				'id'           => 'sg-ps-why-heading',
				'class'        => 'sg-physical-security-why',
				'title_before' => __( 'Licensed, trained and ', 'site-blocks' ),
				'title_accent' => __( 'accountable', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Fully licensed', 'site-blocks' ), 'desc' => __( 'Master Licence #000103619; officers licensed under NSW requirements.', 'site-blocks' ) ),
					array( 'title' => __( 'Industry-accredited', 'site-blocks' ), 'desc' => __( 'ASIAL member; work delivered to AS 4421 (Guards and Patrols).', 'site-blocks' ) ),
					array( 'title' => __( 'Integrated', 'site-blocks' ), 'desc' => __( 'Guards coordinated with your CCTV, alarms and monitoring.', 'site-blocks' ) ),
					array( 'title' => __( 'Responsive', 'site-blocks' ), 'desc' => __( '24/7 availability and rapid deployment across Sydney.', 'site-blocks' ) ),
				),
				'image'        => 'hub-why.webp',
				'alt'          => __( 'Safeguard security team Sydney', 'site-blocks' ),
				'reverse'      => false,
			),
		),
		'static-guards' => array(
			'intro' => array(
				'id'           => 'sg-static-intro-heading',
				'class'        => 'sg-static-guards-intro',
				'title_before' => __( 'One officer, one site, full ', 'site-blocks' ),
				'title_accent' => __( 'attention', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'A static guard stays dedicated to your location for the duration of their shift. Unlike a patrol that moves between sites, a static officer is there the moment an incident occurs, able to act immediately, not arrive later. They also learn your site: its layout, its routines, and what "normal" looks like, so they spot what\'s out of place. For high-value stock, busy entrances or sites that simply need someone present, a constant on-site guard is the strongest deterrent available.', 'site-blocks' ),
				),
				'image'        => 'static-intro.webp',
				'alt'          => __( 'Guard managing visitor access in Sydney', 'site-blocks' ),
				'reverse'      => false,
			),
			'duties' => array(
				'id'           => 'sg-static-duties-heading',
				'class'        => 'sg-static-guards-duties',
				'title_before' => __( 'Trained to protect, and to ', 'site-blocks' ),
				'title_accent' => __( 'represent', 'site-blocks' ),
				'title_after'  => __( ' you', 'site-blocks' ),
				'list'         => array(
					array( 'title' => __( 'Control access', 'site-blocks' ), 'desc' => __( 'Manage staff, visitors and contractors at entry points.', 'site-blocks' ) ),
					array( 'title' => __( 'Deter and respond', 'site-blocks' ), 'desc' => __( 'A visible presence, with immediate incident response.', 'site-blocks' ) ),
					array( 'title' => __( 'Patrol on-site', 'site-blocks' ), 'desc' => __( 'Internal and perimeter checks throughout the shift.', 'site-blocks' ) ),
					array( 'title' => __( 'Monitor CCTV', 'site-blocks' ), 'desc' => __( 'Watch live feeds from a gatehouse or office.', 'site-blocks' ) ),
					array( 'title' => __( 'Support emergencies', 'site-blocks' ), 'desc' => __( 'Assist with evacuation and emergency procedures.', 'site-blocks' ) ),
					array( 'title' => __( 'Front-of-house', 'site-blocks' ), 'desc' => __( 'Greet and direct people, protecting your reputation as well as your premises.', 'site-blocks' ) ),
				),
				'image'        => 'static-duties.webp',
				'alt'          => __( 'Security officer patrolling a Sydney retail floor', 'site-blocks' ),
				'reverse'      => true,
			),
			'integration' => array(
				'id'           => 'sg-static-integration-heading',
				'class'        => 'sg-static-guards-integration',
				'band'         => 'white',
				'title_before' => __( 'Stronger with your ', 'site-blocks' ),
				'title_accent' => __( 'cameras', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Because Safeguard also designs your electronic security, a static guard doesn\'t work blind. They watch your CCTV, respond to your alarms, and manage your access control as one system, turning separate tools into a single, coordinated response. Most guard-only firms can\'t offer that.', 'site-blocks' ),
				),
				'image'        => 'static-integration.webp',
				'alt'          => __( 'Guard monitoring CCTV on a Sydney site', 'site-blocks' ),
				'reverse'      => false,
			),
			'why' => array(
				'id'           => 'sg-static-why-heading',
				'class'        => 'sg-static-guards-why',
				'title_before' => __( 'Licensed, trained and ', 'site-blocks' ),
				'title_accent' => __( 'accountable', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Fully licensed', 'site-blocks' ), 'desc' => __( 'Officers under Master Licence #000103619.', 'site-blocks' ) ),
					array( 'title' => __( 'Industry-accredited', 'site-blocks' ), 'desc' => __( 'ASIAL member; delivered to AS 4421 (Guards and Patrols).', 'site-blocks' ) ),
					array( 'title' => __( 'Integrated', 'site-blocks' ), 'desc' => __( 'Coordinated with your CCTV, alarms and monitoring.', 'site-blocks' ) ),
					array( 'title' => __( 'Responsive', 'site-blocks' ), 'desc' => __( '24/7 availability and rapid deployment across Sydney.', 'site-blocks' ) ),
				),
				'image'        => 'static-why.webp',
				'alt'          => __( 'Licensed Safeguard security officer Sydney', 'site-blocks' ),
				'reverse'      => true,
			),
		),
		'mobile-patrols' => array(
			'intro' => array(
				'id'           => 'sg-mobile-intro-heading',
				'class'        => 'sg-mobile-patrols-intro',
				'title_before' => __( 'Presence without the full-time ', 'site-blocks' ),
				'title_accent' => __( 'cost', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Not every site needs a guard standing there all shift. A mobile patrol delivers a strong, visible presence at a fraction of the cost, a licensed officer arriving at scheduled and deliberately random intervals, so would-be offenders never know when the next check is coming. That unpredictability is the deterrent. Between visits, your cameras and alarms keep watch; when they trigger, a patrol responds. It\'s the efficient choice for multiple sites, large areas and after-hours cover.', 'site-blocks' ),
				),
				'image'        => 'mobile-intro.webp',
				'alt'          => __( 'Patrol officer checking a perimeter gate in Sydney', 'site-blocks' ),
				'reverse'      => false,
			),
			'duties' => array(
				'id'           => 'sg-mobile-duties-heading',
				'class'        => 'sg-mobile-patrols-duties',
				'title_before' => __( 'More than a ', 'site-blocks' ),
				'title_accent' => __( 'drive-by', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Perimeter and internal checks', 'site-blocks' ), 'desc' => __( 'Fence lines, doors, windows, signs of entry.', 'site-blocks' ) ),
					array( 'title' => __( 'Lock-up & unlock', 'site-blocks' ), 'desc' => __( 'Securing or opening your premises to schedule.', 'site-blocks' ) ),
					array( 'title' => __( 'Alarm response', 'site-blocks' ), 'desc' => __( 'A licensed officer dispatched to verify and act.', 'site-blocks' ) ),
					array( 'title' => __( 'Welfare & hazard checks', 'site-blocks' ), 'desc' => __( 'Reporting faults, hazards and anomalies.', 'site-blocks' ) ),
					array( 'title' => __( 'Incident response & reporting', 'site-blocks' ), 'desc' => __( 'First response, evidence, and a clear log.', 'site-blocks' ) ),
					array( 'title' => __( 'GPS + bodycam', 'site-blocks' ), 'desc' => __( 'Every visit tracked, timed and documented.', 'site-blocks' ) ),
				),
				'image'        => 'mobile-duties.webp',
				'alt'          => __( 'Night perimeter check by a Sydney patrol officer', 'site-blocks' ),
				'reverse'      => true,
			),
			'integration' => array(
				'id'           => 'sg-mobile-tracked-heading',
				'class'        => 'sg-mobile-patrols-tracked',
				'band'         => 'white',
				'title_before' => __( 'Proof of every ', 'site-blocks' ),
				'title_accent' => __( 'visit', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'You shouldn\'t have to take patrols on trust. Every Safeguard patrol is GPS-tracked and time-stamped, with body-worn cameras and digital incident reports, so you get real evidence of when officers attended, what they checked, and anything they found. And because we run your cameras and alarms too, patrol and technology work as one.', 'site-blocks' ),
				),
				'image'        => 'mobile-tracked.webp',
				'alt'          => __( 'GPS patrol reporting dashboard', 'site-blocks' ),
				'reverse'      => false,
			),
			'why' => array(
				'id'           => 'sg-mobile-why-heading',
				'class'        => 'sg-mobile-patrols-why',
				'title_before' => __( 'Licensed, tracked and ', 'site-blocks' ),
				'title_accent' => __( 'accountable', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Fully licensed', 'site-blocks' ), 'desc' => __( 'Officers under Master Licence #000103619; ASIAL member.', 'site-blocks' ) ),
					array( 'title' => __( 'Tracked patrols', 'site-blocks' ), 'desc' => __( 'GPS-tracked patrols with digital reporting you can review.', 'site-blocks' ) ),
					array( 'title' => __( 'Integrated', 'site-blocks' ), 'desc' => __( 'Coordinated with your CCTV, alarms and monitoring.', 'site-blocks' ) ),
					array( 'title' => __( 'Responsive', 'site-blocks' ), 'desc' => __( '24/7 across Sydney, with rapid alarm response.', 'site-blocks' ) ),
				),
				'image'        => 'mobile-why.webp',
				'alt'          => __( 'Licensed Safeguard mobile patrol officer Sydney', 'site-blocks' ),
				'reverse'      => true,
			),
		),
	);

	return $splits[ $page_key ][ $section ] ?? null;
}

/**
 * Industry grid items per child page key.
 *
 * @return array<int, array{title: string, desc: string}>
 */
function site_blocks_physical_security_industry_items( string $page_key ): array {
	$items = array(
		'static-guards' => array(
			array( 'title' => __( 'Retail', 'site-blocks' ), 'desc' => __( 'Entrances, high-value stock, loss prevention.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Offices & corporate', 'site-blocks' ), 'desc' => __( 'Reception, access and incident response.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
			array( 'title' => __( 'Warehouses & industrial', 'site-blocks' ), 'desc' => __( 'Controlled entry over large sites.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Construction', 'site-blocks' ), 'desc' => __( 'After-hours cover during high-risk build phases.', 'site-blocks' ), 'icon' => 'construction' ),
			array( 'title' => __( 'Strata & residential', 'site-blocks' ), 'desc' => __( 'Lobbies, common areas and resident safety.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
			array( 'title' => __( 'Events & venues', 'site-blocks' ), 'desc' => __( 'Fixed posts, access and crowd points.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
		),
		'mobile-patrols' => array(
			array( 'title' => __( 'Industrial estates & warehouses', 'site-blocks' ), 'desc' => __( 'Large perimeters, after-hours.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Car parks', 'site-blocks' ), 'desc' => __( 'Regular presence and incident checks.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
			array( 'title' => __( 'Multi-site businesses', 'site-blocks' ), 'desc' => __( 'One program covering many locations.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Construction', 'site-blocks' ), 'desc' => __( 'Once a site is locked up and hardened.', 'site-blocks' ), 'icon' => 'construction' ),
			array( 'title' => __( 'Retail precincts', 'site-blocks' ), 'desc' => __( 'Shared patrols across a strip or centre.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Strata & residential', 'site-blocks' ), 'desc' => __( 'Night checks and common-area security.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
		),
	);

	return $items[ $page_key ] ?? array();
}

/**
 * Industry grid heading per child page key.
 *
 * @return array{before: string, accent: string}|null
 */
function site_blocks_physical_security_industry_heading( string $page_key ): ?array {
	$headings = array(
		'static-guards'  => array(
			'before' => __( 'Chosen for busy, high-value ', 'site-blocks' ),
			'accent' => __( 'sites', 'site-blocks' ),
		),
		'mobile-patrols' => array(
			'before' => __( 'Built for wide or multiple ', 'site-blocks' ),
			'accent' => __( 'sites', 'site-blocks' ),
		),
	);

	return $headings[ $page_key ] ?? null;
}

/**
 * Build scenario grid config for physical security industry sections.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_physical_security_industry_scenario_config( string $page_key ): ?array {
	$heading = site_blocks_physical_security_industry_heading( $page_key );
	$items   = site_blocks_physical_security_industry_items( $page_key );

	if ( null === $heading || $items === array() ) {
		return null;
	}

	return array(
		'layout'          => 'default',
		'eyebrow'         => __( 'Industries', 'site-blocks' ),
		'title_before'    => $heading['before'],
		'title_accent'    => $heading['accent'],
		'use_brand_icons' => true,
		'cards'           => $items,
	);
}

/**
 * End-of-page related services grid per Physical Security page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_physical_security_related_page_grid_config( string $page_key ): ?array {
	$grids = array(
		'hub' => array(
			'heading_id'    => 'sg-ps-related-heading',
			'section_class' => 'sg-ps-related',
			'title_before'  => __( 'Related ', 'site-blocks' ),
			'title_accent'  => __( 'services', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Alarm Monitoring', 'site-blocks' ), 'desc' => __( '24/7 centre cover linked to your response plan.', 'site-blocks' ), 'url' => home_url( '/monitoring/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'See and record activity across your site.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Access Control', 'site-blocks' ), 'desc' => __( 'Manage who enters and when.', 'site-blocks' ), 'url' => home_url( '/access-control/' ), 'icon' => 'access-control.png' ),
				array( 'title' => __( 'Alarm Systems', 'site-blocks' ), 'desc' => __( 'Detection that triggers monitoring and response.', 'site-blocks' ), 'url' => home_url( '/alarm-systems/' ), 'icon' => 'alarm-systems.png' ),
			),
		),
		'static-guards' => array(
			'heading_id'    => 'sg-ps-static-related-heading',
			'section_class' => 'sg-ps-static-related',
			'title_before'  => __( 'Often combined ', 'site-blocks' ),
			'title_accent'  => __( 'with', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Mobile Patrols', 'site-blocks' ), 'desc' => __( 'After-hours checks when guards are not on site.', 'site-blocks' ), 'url' => home_url( '/physical-security/mobile-patrols/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'Extra eyes on entries and blind spots.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Access Control', 'site-blocks' ), 'desc' => __( 'Keys, cards and visitor management.', 'site-blocks' ), 'url' => home_url( '/access-control/' ), 'icon' => 'access-control.png' ),
				array( 'title' => __( 'Alarm Monitoring', 'site-blocks' ), 'desc' => __( 'Professional alarm response around the clock.', 'site-blocks' ), 'url' => home_url( '/monitoring/back-to-base/' ), 'icon' => 'support.png' ),
			),
		),
		'mobile-patrols' => array(
			'heading_id'    => 'sg-ps-mobile-related-heading',
			'section_class' => 'sg-ps-mobile-related',
			'title_before'  => __( 'Often combined ', 'site-blocks' ),
			'title_accent'  => __( 'with', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Static Security Guards', 'site-blocks' ), 'desc' => __( 'Constant on-site presence during open hours.', 'site-blocks' ), 'url' => home_url( '/physical-security/static-guards/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Back-to-Base Monitoring', 'site-blocks' ), 'desc' => __( 'Alarm signals with agreed escalation.', 'site-blocks' ), 'url' => home_url( '/monitoring/back-to-base/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Virtual Patrol', 'site-blocks' ), 'desc' => __( 'Remote CCTV tours between vehicle visits.', 'site-blocks' ), 'url' => home_url( '/monitoring/virtual-patrol/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'Cameras that support verification on patrol.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
			),
		),
	);

	return $grids[ $page_key ] ?? null;
}

/**
 * Hub process steps.
 *
 * @return array<int, array{title: string, description: string}>
 */
function site_blocks_physical_security_process_steps(): array {
	return array(
		array(
			'title'       => __( 'Site assessment', 'site-blocks' ),
			'description' => __( 'We review your risk, hours and access.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Tailored plan', 'site-blocks' ),
			'description' => __( 'The right mix of static, mobile and technology.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Licensed officers deployed', 'site-blocks' ),
			'description' => __( 'Trained to your site.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Supervision & reporting', 'site-blocks' ),
			'description' => __( 'GPS, incident logs, clear reporting.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Review', 'site-blocks' ),
			'description' => __( 'We refine coverage as your needs change.', 'site-blocks' ),
		),
	);
}

/**
 * Compare band config per child page key.
 *
 * @return array{before: string, accent: string, body: string, link_label: string, link_url: string}|null
 */
function site_blocks_physical_security_compare_config( string $page_key ): ?array {
	$configs = array(
		'static-guards' => array(
			'before'     => __( 'Not sure which you ', 'site-blocks' ),
			'accent'     => __( 'need', 'site-blocks' ),
			'after'      => __( '?', 'site-blocks' ),
			'body'       => __( 'Choose static for constant presence, immediate response and busy or high-value sites. Choose mobile patrols for multiple sites or after-hours checks at lower cost. Many sites use both.', 'site-blocks' ),
			'link_label' => __( 'Compare Mobile Patrols', 'site-blocks' ),
			'link_url'   => home_url( '/physical-security/mobile-patrols/' ),
		),
		'mobile-patrols' => array(
			'before'     => __( 'Or the ', 'site-blocks' ),
			'accent'     => __( 'best', 'site-blocks' ),
			'after'      => __( ' of both', 'site-blocks' ),
			'body'       => __( 'Mobile patrols cover wide or multiple sites cost-effectively; static guards give constant presence and instant response. Many sites layer both.', 'site-blocks' ),
			'link_label' => __( 'Compare Static Guards', 'site-blocks' ),
			'link_url'   => home_url( '/physical-security/static-guards/' ),
		),
	);

	return $configs[ $page_key ] ?? null;
}

/**
 * Final CTA band per page key.
 *
 * @return array{before: string, accent: string, after?: string, sub: string}|null
 */
function site_blocks_physical_security_cta_config( string $page_key ): ?array {
	$ctas = array(
		'hub' => array(
			'before' => __( 'Put trained people on your ', 'site-blocks' ),
			'accent' => __( 'side', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Tell us about your site and risk. We\'ll design the right mix of guards, patrols and technology, and give you a tailored quote.', 'site-blocks' ),
		),
		'static-guards' => array(
			'before' => __( 'Put a trusted officer on your ', 'site-blocks' ),
			'accent' => __( 'door', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Tell us your site and hours, get a tailored static guarding quote, backed by technology.', 'site-blocks' ),
		),
		'mobile-patrols' => array(
			'before' => __( 'Cover your site, on your ', 'site-blocks' ),
			'accent' => __( 'terms', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Tell us your sites and hours, get a tailored mobile patrol quote with reporting built in.', 'site-blocks' ),
		),
	);

	return $ctas[ $page_key ] ?? null;
}
