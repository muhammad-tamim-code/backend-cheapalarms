<?php
/**
 * Enterprise silo - copy, SEO, and section data per page key.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared CTA URLs for Enterprise pages.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_enterprise_ctas(): array {
	return array(
		'primary_label'   => __( 'Book a site assessment', 'site-blocks' ),
		'primary_url'     => home_url( '/contact/' ),
		'secondary_label' => __( 'Explore Safeguard Solutions', 'site-blocks' ),
		'secondary_url'   => home_url( '/safeguard-solutions/' ),
	);
}

/**
 * Safeguard Solutions child page CTAs.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_safeguard_solutions_ctas(): array {
	return array(
		'primary_label'   => __( 'Book a platform demo', 'site-blocks' ),
		'primary_url'     => home_url( '/contact/' ),
		'secondary_label' => __( 'Enterprise Solutions', 'site-blocks' ),
		'secondary_url'   => home_url( '/enterprise-solutions/' ),
	);
}

/**
 * Page keys in this silo.
 *
 * @return array<string, string> key => hierarchical slug path.
 */
function site_blocks_enterprise_page_slugs(): array {
	return array(
		'hub'                 => 'enterprise-solutions',
		'safeguard-solutions' => 'safeguard-solutions',
	);
}

/**
 * Whether the current request is an Enterprise silo page.
 */
function site_blocks_is_enterprise_page(): bool {
	return null !== site_blocks_get_enterprise_page_key();
}

/**
 * Detect the active Enterprise page key.
 */
function site_blocks_get_enterprise_page_key(): ?string {
	foreach ( site_blocks_enterprise_page_slugs() as $key => $path ) {
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
function site_blocks_enterprise_page_sections(): array {
	return array(
		'hub'                 => array( 'intro', 'challenges', 'approach', 'solutions', 'promo', 'industries', 'process', 'integration', 'trust', 'insights', 'quote', 'related-services', 'faq', 'cta' ),
		'safeguard-solutions' => array( 'capabilities', 'spoke-teasers', 'faq', 'cta' ),
	);
}

/**
 * Whether a section applies to the given page key.
 */
function site_blocks_enterprise_section_applies( string $page_key, string $section ): bool {
	$sections = site_blocks_enterprise_page_sections();

	return isset( $sections[ $page_key ] ) && in_array( $section, $sections[ $page_key ], true );
}

/**
 * Document title per page key.
 */
function site_blocks_enterprise_document_title( string $page_key ): string {
	$titles = array(
		'hub'                 => __( 'Commercial & Enterprise Security Sydney | Safeguard', 'site-blocks' ),
		'safeguard-solutions' => __( 'Safeguard Solutions | Cloud Security Platform Sydney', 'site-blocks' ),
	);

	return $titles[ $page_key ] ?? '';
}

/**
 * Meta description per page key.
 */
function site_blocks_enterprise_get_meta_description( string $page_key ): string {
	$descriptions = array(
		'hub'                 => __( 'Integrated commercial security across Sydney: CCTV, access control, monitoring and licensed guards from one team. Master Licence 000103619. Book an assessment.', 'site-blocks' ),
		'safeguard-solutions' => __( 'Safeguard Solutions is our cloud security platform: video, access, AI analytics and multi-site management in one console. Built for Sydney business. Book a demo.', 'site-blocks' ),
	);

	return $descriptions[ $page_key ] ?? '';
}

/**
 * Hero config per page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_enterprise_hero_config( string $page_key ): ?array {
	$hub_url = home_url( '/enterprise-solutions/' );

	$configs = array(
		'hub' => array_merge(
			site_blocks_enterprise_ctas(),
			array(
				'id'               => 'sg-enterprise-hero-heading',
				'class'            => 'sg-enterprise-hero',
				'badge'            => __( 'Australian owned · Master Licence #000103619 · ASIAL member', 'site-blocks' ),
				'title_before'     => __( 'One team for your ', 'site-blocks' ),
				'title_accent'     => __( 'entire security operation', 'site-blocks' ),
				'title_after'      => '',
				'lead'             => __( 'From a single office to a national multi-site portfolio, Safeguard designs, installs, monitors and guards commercial premises across Sydney and NSW, so your cameras, access control, alarms, monitoring and personnel all answer to one accountable partner.', 'site-blocks' ),
				'trust_aria_label' => __( 'Enterprise security credentials', 'site-blocks' ),
				'trust_chips'      => array(
					array(
						'icon'  => 'map-pin',
						'line1' => __( 'Local Sydney', 'site-blocks' ),
						'line2' => __( 'team', 'site-blocks' ),
					),
					array(
						'icon'  => 'clock',
						'line1' => __( '24/7 Australian', 'site-blocks' ),
						'line2' => __( 'monitoring', 'site-blocks' ),
					),
					array(
						'icon'  => 'layers',
						'line1' => __( 'Install + monitor', 'site-blocks' ),
						'line2' => __( '+ guard', 'site-blocks' ),
					),
					array(
						'icon'  => 'building-2',
						'line1' => __( 'Multi-site', 'site-blocks' ),
						'line2' => __( 'ready', 'site-blocks' ),
					),
				),
				'hero_image'       => 'hub-hero.webp',
				'hero_alt'         => __( 'Male security professional reviewing multi-site CCTV on a tablet at a Sydney commercial site', 'site-blocks' ),
			)
		),
		'safeguard-solutions' => array_merge(
			site_blocks_safeguard_solutions_ctas(),
			array(
				'id'               => 'sg-safeguard-solutions-hero-heading',
				'class'            => 'sg-enterprise-hero sg-safeguard-solutions-hero',
				'breadcrumb'       => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Enterprise Solutions', 'site-blocks' ), 'url' => $hub_url ),
					array( 'label' => __( 'Safeguard Solutions', 'site-blocks' ), 'current' => true ),
				),
				'badge'            => __( 'Safeguard Solutions · Cloud platform', 'site-blocks' ),
				'title_before'     => __( 'Safeguard Solutions: One Platform for ', 'site-blocks' ),
				'title_accent'     => __( 'Every Site', 'site-blocks' ),
				'title_after'      => '',
				'lead'             => __( 'Safeguard Solutions is the cloud-managed platform behind our commercial systems: the single console where your video, access control, alerts and analytics come together. Open it from a desk or a phone, see every site at once, and turn hours of footage into answers in seconds.', 'site-blocks' ),
				'trust_aria_label' => __( 'Platform capabilities', 'site-blocks' ),
				'trust_chips'      => array(
					array(
						'icon'  => 'cloud',
						'line1' => __( 'Cloud video', 'site-blocks' ),
						'line2' => __( 'multi-site', 'site-blocks' ),
					),
					array(
						'icon'  => 'scan-search',
						'line1' => __( 'AI-assisted', 'site-blocks' ),
						'line2' => __( 'video search', 'site-blocks' ),
					),
					array(
						'icon'  => 'key-round',
						'line1' => __( 'Access events', 'site-blocks' ),
						'line2' => __( 'in one view', 'site-blocks' ),
					),
					array(
						'icon'  => 'radio',
						'line1' => __( 'Smart sensor', 'site-blocks' ),
						'line2' => __( 'alerts', 'site-blocks' ),
					),
				),
				'hero_image'       => 'safeguard-solutions-hero.webp',
				'hero_alt'         => __( 'Male facilities manager viewing cloud security dashboard across multiple Sydney sites', 'site-blocks' ),
			)
		),
	);

	return $configs[ $page_key ] ?? null;
}

/**
 * Split / intro section config.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_enterprise_split_config( string $page_key, string $section ): ?array {
	$splits = array(
		'hub' => array(
			'intro' => array(
				'id'           => 'sg-enterprise-intro-heading',
				'class'        => 'sg-enterprise-intro',
				'band'         => 'white',
				'title_before' => __( 'Built for commercial ', 'site-blocks' ),
				'title_accent' => __( 'decision-makers', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Enterprise Solutions is built for the people responsible for keeping commercial sites safe and running: facilities and operations managers, business owners, strata committees, and procurement teams. If you manage more than one location, juggle several security vendors, or need electronic systems and physical presence to work as one, this is where Safeguard brings it together. Everything here is commercial-grade: designed for offices, retail, logistics, healthcare, construction and multi-tenant buildings, not home alarms.', 'site-blocks' ),
				),
				'image'        => 'hub-intro.webp',
				'alt'          => __( 'Facilities manager reviewing security plans at a Sydney commercial building', 'site-blocks' ),
				'reverse'      => false,
			),
			'approach' => array(
				'id'           => 'sg-enterprise-approach-heading',
				'class'        => 'sg-enterprise-approach',
				'band'         => 'white',
				'title_before' => __( 'Design, install, monitor, guard: ', 'site-blocks' ),
				'title_accent' => __( 'one team', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Most providers do one part of the picture. National guarding firms subcontract the technology; local installers cannot field a guard at 2am. Safeguard runs the whole stack in-house: we assess your risk, design the system, install the electronics, connect it to our Australian monitoring, and deploy licensed static guards or mobile patrols where people beat pixels. Tie it together with the Safeguard Solutions platform and you get one operation, one console, one accountable partner, sized for a single site or a national footprint.', 'site-blocks' ),
				),
				'image'        => 'hub-approach.webp',
				'alt'          => __( 'Safeguard technician and security officer coordinating at a Sydney commercial site', 'site-blocks' ),
				'reverse'      => true,
			),
			'integration' => array(
				'id'           => 'sg-enterprise-integration-heading',
				'class'        => 'sg-enterprise-integration',
				'band'         => 'white',
				'title_before' => __( 'Where electronic, physical and monitoring ', 'site-blocks' ),
				'title_accent' => __( 'meet', 'site-blocks' ),
				'title_after'  => '',
				'paragraphs'   => array(
					__( 'Real enterprise security is not a pile of products; it is coordination. A door forced after hours triggers an alarm, the nearest camera surfaces verified video to our monitoring centre, and a mobile patrol or guard is dispatched, all recorded for later review. Because Safeguard installs the electronics, runs the monitoring and fields the personnel, those handoffs happen inside one team instead of across three contracts. The Safeguard Solutions platform is the connective tissue that makes it visible and searchable.', 'site-blocks' ),
				),
				'image'        => 'hub-integration.webp',
				'alt'          => __( 'Integrated security workflow across CCTV, access control and monitoring', 'site-blocks' ),
				'reverse'      => false,
			),
		),
	);

	return $splits[ $page_key ][ $section ] ?? null;
}

/**
 * Trust / accreditation band config (navy credential band, no image).
 *
 * @return array<string, mixed>
 */
function site_blocks_enterprise_trust_config(): array {
	return array(
		'eyebrow'      => __( 'Licensed & accredited', 'site-blocks' ),
		'title_before' => __( 'A licensed, Australian-owned ', 'site-blocks' ),
		'title_accent' => __( 'partner', 'site-blocks' ),
		'title_after'  => '',
		'body'         => __( 'Safeguard Security Services is Australian owned and operated, working with commercial clients across Sydney and NSW from our Ingleburn base, proud of the brands we have worked with across retail, logistics, strata and corporate sectors. Licence-backed accountability is not a formality here; it is what your insurer, board and auditors expect, and what we are built to provide.', 'site-blocks' ),
		'items'        => array(
			array(
				'icon'  => 'id-card',
				'value' => __( '#000103619', 'site-blocks' ),
				'label' => __( 'NSW Master Licence', 'site-blocks' ),
			),
			array(
				'icon'  => 'award',
				'value' => __( 'ASIAL', 'site-blocks' ),
				'label' => __( 'Full member', 'site-blocks' ),
			),
			array(
				'icon'  => 'shield-check',
				'value' => __( 'Australian', 'site-blocks' ),
				'label' => __( 'Owned & operated', 'site-blocks' ),
			),
			array(
				'icon'  => 'map-pin',
				'value' => __( 'Sydney & NSW', 'site-blocks' ),
				'label' => __( 'Local delivery', 'site-blocks' ),
			),
		),
	);
}

/**
 * Four pain-point challenge cards for the hub.
 *
 * @return array{title: string, intro: string, cards: array<int, array{title: string, body: string}>}
 */
function site_blocks_enterprise_challenges_config(): array {
	return array(
		'title' => __( 'The problems we\'re built to solve', 'site-blocks' ),
		'intro' => '',
		'cards' => array(
			array(
				'title' => __( 'Too many vendors, no single accountability', 'site-blocks' ),
				'body'  => __( 'A camera installer here, a guard company there, a separate monitoring contract, and when something goes wrong, everyone points elsewhere. Safeguard is one contract and one point of accountability.', 'site-blocks' ),
			),
			array(
				'title' => __( 'No central view across sites', 'site-blocks' ),
				'body'  => __( 'Footage on one system, door logs on another, alarms on a third. Without a single console, incidents take too long to piece together. Safeguard Solutions gives you one login for every site.', 'site-blocks' ),
			),
			array(
				'title' => __( 'Compliance and reporting pressure', 'site-blocks' ),
				'body'  => __( 'Audit trails, incident records and licence-backed providers matter to insurers, boards and regulators. We deliver documented processes and hold Master Licence #000103619.', 'site-blocks' ),
			),
			array(
				'title' => __( 'After-hours gaps', 'site-blocks' ),
				'body'  => __( 'Risk does not keep business hours. Back-to-base monitoring, virtual patrols and mobile response close the overnight and weekend gaps that leave sites exposed.', 'site-blocks' ),
			),
		),
	);
}

/**
 * Hub solutions grid cards for the simple text grid.
 *
 * @return array<int, array{title: string, desc: string, url: string}>
 */
function site_blocks_enterprise_hub_solutions(): array {
	$items = site_blocks_enterprise_hub_photo_solutions();
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
 * Hub solutions cards for the photo-options template.
 *
 * @return array<int, array{title: string, desc: string, url: string, image: string, alt: string, icon: string}>
 */
function site_blocks_enterprise_hub_photo_solutions(): array {
	return array(
		array(
			'title' => __( 'CCTV & Video Surveillance', 'site-blocks' ),
			'desc'  => __( 'Business-grade cameras with secure remote viewing and analytics-ready footage across every entrance, dock and perimeter.', 'site-blocks' ),
			'url'   => home_url( '/cctv-security-cameras/' ),
			'image' => 'images/cctv/commercial.webp',
			'alt'   => __( 'Commercial CCTV covering entrances and perimeters', 'site-blocks' ),
			'icon'  => 'cctv',
		),
		array(
			'title' => __( 'Access Control', 'site-blocks' ),
			'desc'  => __( 'Card, mobile and biometric entry with lift control and full audit trails for offices, factories and multi-tenant sites.', 'site-blocks' ),
			'url'   => home_url( '/access-control/' ),
			'image' => 'images/access-control/hero.webp',
			'alt'   => __( 'Enterprise access control at a multi-tenant site', 'site-blocks' ),
			'icon'  => 'lock',
		),
		array(
			'title' => __( 'Alarm Systems', 'site-blocks' ),
			'desc'  => __( 'Monitored intrusion detection (wired, wireless or hybrid) matched to your site\'s risk profile.', 'site-blocks' ),
			'url'   => home_url( '/alarm-systems/' ),
			'image' => 'images/alarm/alarm-hero.webp',
			'alt'   => __( 'Monitored commercial alarm system installation', 'site-blocks' ),
			'icon'  => 'bell-ring',
		),
		array(
			'title' => __( '24/7 Monitoring', 'site-blocks' ),
			'desc'  => __( 'Back-to-base and virtual-patrol response with video verification and clear escalation, from an Australian centre.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/' ),
			'image' => 'images/monitoring/hub-hero.webp',
			'alt'   => __( 'Australian monitoring centre providing 24/7 response', 'site-blocks' ),
			'icon'  => 'headset',
		),
		array(
			'title' => __( 'Physical Security & Guards', 'site-blocks' ),
			'desc'  => __( 'Licensed static guards and mobile patrols for staffed deterrence and rapid on-site response.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/' ),
			'image' => 'images/physical-security/hub-hero.webp',
			'alt'   => __( 'Licensed security officers protecting a commercial site', 'site-blocks' ),
			'icon'  => 'user',
		),
		array(
			'title' => __( 'Safeguard Solutions Platform', 'site-blocks' ),
			'desc'  => __( 'Cloud video, AI analytics and multi-site management in one console: the software layer that unifies your sites.', 'site-blocks' ),
			'url'   => home_url( '/safeguard-solutions/' ),
			'image' => 'images/enterprise/ss-cloud-video.webp',
			'alt'   => __( 'Safeguard Solutions cloud video and multi-site console', 'site-blocks' ),
			'icon'  => 'monitor',
		),
	);
}

/**
 * Solutions section heading.
 *
 * @return array{title: string, intro: string}
 */
function site_blocks_enterprise_solutions_heading(): array {
	return array(
		'title' => __( 'Everything your operation needs, integrated', 'site-blocks' ),
		'intro' => '',
	);
}

/**
 * Safeguard Solutions promo band config.
 *
 * @return array<string, mixed>
 */
function site_blocks_enterprise_promo_config(): array {
	return array(
		'heading_id'    => 'sg-enterprise-promo-heading',
		'section_class' => 'sg-enterprise-promo',
		'title'         => __( 'Safeguard Solutions: your whole portfolio, one console', 'site-blocks' ),
		'paragraphs'    => array(
			__( 'Hardware secures a site. Safeguard Solutions runs your whole operation. It is our cloud-managed platform for businesses that need to see and control every location from one place: live video, access events, alerts and analytics unified in a single, permission-controlled console you can open from a desk or a phone.', 'site-blocks' ),
			__( 'Built for multi-site and enterprise environments, it turns raw footage into fast answers: search video by describing what you are looking for, get real-time alerts the moment something matters, and give each manager exactly the access they need with full audit logging.', 'site-blocks' ),
		),
		'bullets'       => array(
			__( 'Cloud video with secure offsite storage and one-login, multi-site viewing', 'site-blocks' ),
			__( 'AI-assisted video search and real-time detections and alerts', 'site-blocks' ),
			__( 'Access control events and door status in the same view', 'site-blocks' ),
			__( 'Environmental and occupancy sensors for areas cameras cannot cover', 'site-blocks' ),
			__( 'Role-based user permissions with full audit trails', 'site-blocks' ),
			__( 'Integrations that bring existing systems into one pane of glass', 'site-blocks' ),
		),
		'cta_label'     => __( 'Explore Safeguard Solutions', 'site-blocks' ),
		'cta_url'       => home_url( '/safeguard-solutions/' ),
	);
}

/**
 * Industry scenario grid for the hub.
 *
 * @return array<string, mixed>
 */
function site_blocks_enterprise_industry_scenario_config(): array {
	return array(
		'title_before'    => __( 'Built for the way your sector ', 'site-blocks' ),
		'title_accent'    => __( 'operates', 'site-blocks' ),
		'title_after'     => '',
		'use_brand_icons' => true,
		'use_lucide_icons'=> true,
		'cards'           => array(
			array( 'title' => __( 'Retail & Multi-Store', 'site-blocks' ), 'desc' => __( 'Reduce shrinkage and connect every store to one monitored platform.', 'site-blocks' ), 'icon' => 'store' ),
			array( 'title' => __( 'Logistics & Warehousing', 'site-blocks' ), 'desc' => __( 'Perimeter coverage, dock monitoring and after-hours response for large sites.', 'site-blocks' ), 'icon' => 'warehouse' ),
			array( 'title' => __( 'Strata & Multi-Tenant', 'site-blocks' ), 'desc' => __( 'Access control, intercom and audit trails for shared commercial buildings.', 'site-blocks' ), 'icon' => 'building' ),
			array( 'title' => __( 'Healthcare & Aged Care', 'site-blocks' ), 'desc' => __( 'Controlled access and discreet monitoring for sensitive, high-duty-of-care sites.', 'site-blocks' ), 'icon' => 'heart-pulse' ),
			array( 'title' => __( 'Construction & Sites', 'site-blocks' ), 'desc' => __( 'Rapid-deploy cameras and mobile patrols for changing, high-risk environments.', 'site-blocks' ), 'icon' => 'hard-hat' ),
			array( 'title' => __( 'Corporate & Commercial Offices', 'site-blocks' ), 'desc' => __( 'Integrated access, CCTV and monitoring that scale across floors and offices.', 'site-blocks' ), 'icon' => 'briefcase' ),
		),
	);
}

/**
 * Quote / assessment band for the hub.
 *
 * @return array<string, string>
 */
function site_blocks_enterprise_quote_config(): array {
	return array(
		'title'  => __( 'Ready to bring your security under one team?', 'site-blocks' ),
		'body'   => __( 'Book a site assessment and we\'ll map your risks, scope the solution and give you a clear, itemised quote, with no obligation.', 'site-blocks' ),
		'label'  => __( 'Book a site assessment', 'site-blocks' ),
		'url'    => home_url( '/contact/' ),
		'phone'  => '1300 225 276',
	);
}

/**
 * Safeguard Solutions capability bullets.
 *
 * @return array<int, string>
 */
function site_blocks_safeguard_solutions_capabilities(): array {
	return array(
		__( 'Cloud video: secure offsite storage and one-login viewing across every site', 'site-blocks' ),
		__( 'Access control: door events, lift control and audit trails in one view', 'site-blocks' ),
		__( 'AI analytics: describe what you are looking for and surface the footage fast', 'site-blocks' ),
		__( 'Smart sensors: temperature, air quality, occupancy and motion where cameras cannot see', 'site-blocks' ),
		__( 'Multi-site management: a single dashboard for your whole portfolio', 'site-blocks' ),
		__( 'Integrations: bring compatible existing systems into one pane of glass', 'site-blocks' ),
	);
}

/**
 * Future spoke teasers for Safeguard Solutions child hub.
 *
 * @return array<int, array{title: string, desc: string, slug: string}>
 */
function site_blocks_safeguard_solutions_spoke_teasers(): array {
	return array(
		array(
			'title' => __( 'AI Analytics', 'site-blocks' ),
			'desc'  => __( 'Turn camera footage into security intelligence. Real-time detections and natural-language video search that cut investigation time from hours to seconds.', 'site-blocks' ),
			'slug'  => 'ai-analytics',
			'image' => 'ss-ai-search.webp',
			'alt'   => __( 'AI video search on Safeguard Solutions console', 'site-blocks' ),
		),
		array(
			'title' => __( 'Cloud Video', 'site-blocks' ),
			'desc'  => __( 'Add secure cloud storage to compatible cameras and watch every location from one login, with no on-site recorder to babysit.', 'site-blocks' ),
			'slug'  => 'cloud-video',
			'image' => 'ss-cloud-video.webp',
			'alt'   => __( 'Cloud CCTV multi-camera view on Safeguard Solutions', 'site-blocks' ),
		),
		array(
			'title' => __( 'Multi-Location Management', 'site-blocks' ),
			'desc'  => __( 'Run every site from one console, with role-based permissions and full audit logs for each manager and location.', 'site-blocks' ),
			'slug'  => 'multi-site-management',
			'image' => 'ss-multi-site-map.webp',
			'alt'   => __( 'Safeguard Solutions multi-site map console', 'site-blocks' ),
		),
		array(
			'title' => __( 'Smart Sensors', 'site-blocks' ),
			'desc'  => __( 'Monitor the conditions cameras miss: environmental readings and occupancy insights that trigger instant alerts.', 'site-blocks' ),
			'slug'  => 'smart-sensors',
			'image' => 'ss-access-events.webp',
			'alt'   => __( 'Access and alert events in the Safeguard Solutions console', 'site-blocks' ),
		),
	);
}

/**
 * FAQ items per page key.
 *
 * @return array<int, array{q: string, a: string}>
 */
function site_blocks_enterprise_faq_items( string $page_key ): array {
	$faqs = array(
		'hub' => array(
			array(
				'q' => __( 'Do you cover multiple sites across different locations?', 'site-blocks' ),
				'a' => __( 'Yes. Safeguard is built for multi-site operations: one contract, one platform and one console across every location in Sydney, NSW and beyond, with local delivery from our Ingleburn base.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can you both install the technology and provide guards?', 'site-blocks' ),
				'a' => __( 'Yes. We are one of the few providers that designs and installs electronics, runs 24/7 monitoring, and fields licensed static guards and mobile patrols, all in-house, under Master Licence #000103619.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Will a new system work with our existing cameras or access control?', 'site-blocks' ),
				'a' => __( 'Often, yes. Where equipment is compatible we integrate with existing infrastructure so you can upgrade without ripping everything out. We confirm this during the site assessment.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What is Safeguard Solutions?', 'site-blocks' ),
				'a' => __( 'It is our cloud-managed platform for viewing and controlling video, access and alerts across all your sites from one login: the software layer that unifies the hardware we install.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How does after-hours response work?', 'site-blocks' ),
				'a' => __( 'Back-to-base and virtual-patrol monitoring watches your sites 24/7. Verified events are escalated per an agreed plan, with mobile patrols or guards dispatched as needed.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Are you licensed and compliant?', 'site-blocks' ),
				'a' => __( 'Yes. Master Licence #000103619 and ASIAL membership, with documented processes and audit trails suited to insurer and board requirements.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What does it cost?', 'site-blocks' ),
				'a' => __( 'Pricing depends on site size, risk and the mix of electronics, monitoring and personnel. We provide a clear, itemised quote after a site assessment.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How quickly can you deploy?', 'site-blocks' ),
				'a' => __( 'Smaller installations can be completed quickly; larger multi-site rollouts are staged to a plan agreed at design. Rapid-deploy options are available for construction and temporary sites.', 'site-blocks' ),
			),
		),
		'safeguard-solutions' => array(
			array(
				'q' => __( 'Is Safeguard Solutions a separate product I can buy on its own?', 'site-blocks' ),
				'a' => __( 'It is the platform layer of a Safeguard system. We scope it as part of your solution so the software and the hardware are designed to work together from day one.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can I see all my sites in one place?', 'site-blocks' ),
				'a' => __( 'Yes. That is the point. One login shows every location, with permissions controlling who sees what.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Does it work with cameras I already own?', 'site-blocks' ),
				'a' => __( 'Where equipment is compatible, yes. We confirm compatibility during the assessment.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can my team get different levels of access?', 'site-blocks' ),
				'a' => __( 'Yes. Role-based permissions and audit logs let each manager see and do only what they should, with a record of actions.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Is my footage stored securely?', 'site-blocks' ),
				'a' => __( 'Footage is held in secure cloud storage with controlled access. We will walk you through retention and access options at proposal.', 'site-blocks' ),
			),
		),
	);

	return $faqs[ $page_key ] ?? array();
}

/**
 * End-of-page related services grid config.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_enterprise_related_page_grid_config( string $page_key ): ?array {
	$grids = array(
		'hub' => array(
			'heading_id'    => 'sg-enterprise-related-heading',
			'section_class' => 'sg-enterprise-related',
			'title_before'  => __( 'Related ', 'site-blocks' ),
			'title_accent'  => __( 'services', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'CCTV Security Cameras', 'site-blocks' ), 'desc' => __( 'Business-grade cameras with remote viewing and analytics-ready footage.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Access Control', 'site-blocks' ), 'desc' => __( 'Card, mobile and biometric entry with full audit trails.', 'site-blocks' ), 'url' => home_url( '/access-control/' ), 'icon' => 'access-control.png' ),
				array( 'title' => __( '24/7 Monitoring', 'site-blocks' ), 'desc' => __( 'Back-to-base and virtual-patrol response from an Australian centre.', 'site-blocks' ), 'url' => home_url( '/monitoring/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Physical Security', 'site-blocks' ), 'desc' => __( 'Licensed static guards and mobile patrols for staffed deterrence.', 'site-blocks' ), 'url' => home_url( '/physical-security/' ), 'icon' => 'support.png' ),
			),
		),
	);

	return $grids[ $page_key ] ?? null;
}

/**
 * Final CTA band per page key.
 *
 * @return array{before: string, accent: string, after?: string, sub: string, secondary_label?: string, secondary_url?: string}|null
 */
function site_blocks_enterprise_cta_config( string $page_key ): ?array {
	$ctas = array(
		'hub' => array(
			'before'          => __( 'One team. Every site. ', 'site-blocks' ),
			'accent'          => __( 'Total accountability', 'site-blocks' ),
			'after'           => __( '.', 'site-blocks' ),
			'sub'             => __( 'Talk to Safeguard about bringing your commercial security under one roof.', 'site-blocks' ),
			'secondary_label' => __( 'Call 1300 225 276', 'site-blocks' ),
			'secondary_url'   => 'tel:1300225276',
		),
		'safeguard-solutions' => array(
			'before'          => __( 'See every site from ', 'site-blocks' ),
			'accent'          => __( 'one console', 'site-blocks' ),
			'after'           => __( '.', 'site-blocks' ),
			'sub'             => __( 'Book a platform demo and we will walk you through multi-site video, access and alerts for your business.', 'site-blocks' ),
			'secondary_label' => __( 'Enterprise Solutions', 'site-blocks' ),
			'secondary_url'   => home_url( '/enterprise-solutions/' ),
		),
	);

	return $ctas[ $page_key ] ?? null;
}

/**
 * Enterprise insight category slugs and labels.
 *
 * @return array<string, string>
 */
function site_blocks_enterprise_insight_category_terms(): array {
	return array(
		'video-analytics'      => __( 'Video & Analytics', 'site-blocks' ),
		'access-identity'      => __( 'Access & Identity', 'site-blocks' ),
		'monitoring-response'  => __( 'Monitoring & Response', 'site-blocks' ),
		'multi-site-operations'=> __( 'Multi-Site & Operations', 'site-blocks' ),
	);
}
