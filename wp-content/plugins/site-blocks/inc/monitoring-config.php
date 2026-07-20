<?php
/**
 * Monitoring silo, copy, SEO, and section data per page key.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared CTA URLs for Monitoring pages.
 *
 * @return array{primary_label: string, primary_url: string, secondary_label: string, secondary_url: string}
 */
function site_blocks_monitoring_ctas(): array {
	return array(
		'primary_label'   => __( 'Start My Quote', 'site-blocks' ),
		'primary_url'     => home_url( '/get-an-instant-quote/' ),
		'secondary_label' => __( 'Help Me Choose', 'site-blocks' ),
		'secondary_url'   => home_url( '/design-my-solution/' ),
	);
}

/**
 * Page keys in this silo.
 *
 * @return array<string, string> key => hierarchical slug path.
 */
function site_blocks_monitoring_page_slugs(): array {
	return array(
		'hub'                      => 'monitoring',
		'back-to-base'             => 'monitoring/back-to-base',
		'virtual-patrol'           => 'monitoring/virtual-patrol',
		'solar-cameras-monitoring' => 'monitoring/solar-cameras-monitoring',
	);
}

/**
 * Whether the current request is a Monitoring silo page.
 */
function site_blocks_is_monitoring_page(): bool {
	return null !== site_blocks_get_monitoring_page_key();
}

/**
 * Detect the active Monitoring page key.
 */
function site_blocks_get_monitoring_page_key(): ?string {
	foreach ( site_blocks_monitoring_page_slugs() as $key => $path ) {
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
function site_blocks_monitoring_page_sections(): array {
	return array(
		'hub'                      => array( 'services', 'how-it-works', 'paths', 'integration', 'industries', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
		'back-to-base'             => array( 'intro', 'how-it-works', 'communicators', 'response-plans', 'quote', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
		'virtual-patrol'           => array( 'intro', 'how-it-works', 'compare', 'features', 'industries', 'requirements', 'portal', 'trust', 'related-services', 'faq', 'cta' ),
		'solar-cameras-monitoring' => array( 'intro', 'how-it-works', 'use-cases', 'technical', 'industries', 'portal', 'monitoring-integration', 'related-services', 'faq', 'cta' ),
	);
}

/**
 * Whether a section applies to the given page key.
 */
function site_blocks_monitoring_section_applies( string $page_key, string $section ): bool {
	$sections = site_blocks_monitoring_page_sections();

	return isset( $sections[ $page_key ] ) && in_array( $section, $sections[ $page_key ], true );
}

/**
 * Document title per page key.
 */
function site_blocks_monitoring_document_title( string $page_key ): string {
	$titles = array(
		'hub'                      => __( 'Alarm Monitoring Sydney | 24/7 Back-to-Base | Safeguard', 'site-blocks' ),
		'back-to-base'             => __( 'Back-to-Base Alarm Monitoring Sydney | Safeguard', 'site-blocks' ),
		'virtual-patrol'           => __( 'Virtual Patrol Sydney | Remote CCTV Guarding | Safeguard', 'site-blocks' ),
		'solar-cameras-monitoring' => __( 'Solar CCTV Monitoring Sydney | 4G Site Cameras | Safeguard', 'site-blocks' ),
	);

	return $titles[ $page_key ] ?? '';
}

/**
 * Meta description per page key.
 */
function site_blocks_monitoring_get_meta_description( string $page_key ): string {
	$descriptions = array(
		'hub'                      => __( '24/7 alarm monitoring across Greater Sydney, back-to-base, virtual patrol and solar CCTV monitoring. One team for install and response. Request a quote today.', 'site-blocks' ),
		'back-to-base'             => __( 'Back-to-base alarm monitoring in Sydney, professional 24/7 monitoring centre, IP and 4G paths, clear response plans. Request a quote for your property.', 'site-blocks' ),
		'virtual-patrol'           => __( 'Virtual patrol and remote guarding in Sydney, live CCTV tours, alarm verification and professional monitoring. Cut false callouts. Request an assessment.', 'site-blocks' ),
		'solar-cameras-monitoring' => __( 'Solar-powered security cameras with professional monitoring for construction, farms and remote NSW sites. 4G back-to-base. Request a site quote.', 'site-blocks' ),
	);

	return $descriptions[ $page_key ] ?? '';
}

/**
 * Hero config per page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_monitoring_hero_config( string $page_key ): ?array {
	$ctas = site_blocks_monitoring_ctas();
	$hub  = home_url( '/monitoring/' );

	$configs = array(
		'hub' => array_merge(
			$ctas,
			array(
				'id'            => 'sg-monitoring-hero-heading',
				'class'         => 'sg-monitoring-hero',
				'breadcrumb'    => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Monitoring & Response', 'site-blocks' ), 'current' => true ),
				),
				'badge'         => __( 'Monitoring & Response · Sydney', 'site-blocks' ),
				'title_before'  => __( 'When your alarm trips, someone should be ', 'site-blocks' ),
				'title_accent'  => __( 'watching', 'site-blocks' ),
				'title_after'   => __( '.', 'site-blocks' ),
				'lead'            => __( 'Safeguard provides professional alarm monitoring across Greater Sydney, back-to-base alarm response, virtual patrol over your CCTV, and monitored solar cameras for remote sites. Because we also install your alarms, cameras and access control, monitoring connects to real response plans, not three vendors pointing at each other.', 'site-blocks' ),
				'trust_aria_label'=> __( 'Why Safeguard monitoring', 'site-blocks' ),
				'trust_chips'     => array(
					array(
						'icon'  => 'id-card',
						'line1' => __( 'Master Licence', 'site-blocks' ),
						'line2' => '#000103619',
					),
					array(
						'icon'  => 'award',
						'line1' => __( 'ASIAL', 'site-blocks' ),
						'line2' => __( 'member', 'site-blocks' ),
					),
					array(
						'icon'  => 'clock',
						'line1' => __( '24/7', 'site-blocks' ),
						'line2' => __( 'monitoring centre', 'site-blocks' ),
					),
					array(
						'icon'  => 'wrench',
						'line1' => __( 'Install +', 'site-blocks' ),
						'line2' => __( 'monitor', 'site-blocks' ),
					),
				),
				'caption_title'   => __( 'One coordinated response, not three vendors', 'site-blocks' ),
				'caption_items'   => array(
					array(
						'icon'  => 'radio-tower',
						'title' => __( 'Back-to-base', 'site-blocks' ),
						'desc'  => __( 'Alarm signals', 'site-blocks' ),
					),
					array(
						'icon'  => 'cctv',
						'title' => __( 'Virtual patrol', 'site-blocks' ),
						'desc'  => __( 'CCTV tours', 'site-blocks' ),
					),
					array(
						'icon'  => 'sun',
						'title' => __( 'Solar sites', 'site-blocks' ),
						'desc'  => __( 'Remote cameras', 'site-blocks' ),
					),
				),
				'hero_image'      => 'hub-hero.webp',
				'hero_alt'      => __( 'Monitoring operator reviewing alarm alerts at a professional security desk in Sydney', 'site-blocks' ),
			)
		),
		'back-to-base' => array_merge(
			$ctas,
			array(
				'id'            => 'sg-back-to-base-hero-heading',
				'class'         => 'sg-monitoring-hero sg-back-to-base-hero',
				'breadcrumb'    => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Monitoring & Response', 'site-blocks' ), 'url' => $hub ),
					array( 'label' => __( 'Back-to-Base Monitoring', 'site-blocks' ), 'current' => true ),
				),
				'badge'         => __( 'Back-to-Base Monitoring · Sydney', 'site-blocks' ),
				'title_before'  => __( 'Someone watching when you ', 'site-blocks' ),
				'title_accent'  => __( 'cannot', 'site-blocks' ),
				'title_after'   => __( '.', 'site-blocks' ),
				'lead'            => __( 'Safeguard connects your alarm to professional back-to-base monitoring across Greater Sydney. When sensors trigger, our monitoring centre receives the signal and follows your response plan, contacting keyholders, arranging patrol attendance or escalation as agreed. Because we install and maintain your system too, monitoring is not an afterthought from a third party.', 'site-blocks' ),
				'trust_aria_label'=> __( 'Back-to-base monitoring credentials', 'site-blocks' ),
				'trust_chips'     => array(
					array(
						'icon'  => 'id-card',
						'line1' => __( 'Master Licence', 'site-blocks' ),
						'line2' => '#000103619',
					),
					array(
						'icon'  => 'award',
						'line1' => __( 'ASIAL', 'site-blocks' ),
						'line2' => __( 'member', 'site-blocks' ),
					),
					array(
						'icon'  => 'radio-tower',
						'line1' => __( 'IP & 4G', 'site-blocks' ),
						'line2' => __( 'monitoring paths', 'site-blocks' ),
					),
					array(
						'icon'  => 'phone',
						'line1' => __( 'Call us', 'site-blocks' ),
						'line2' => '1300 225 276',
					),
				),
				'caption_title'   => __( 'Dual paths keep your alarm online', 'site-blocks' ),
				'caption_items'   => array(
					array(
						'icon'  => 'router',
						'title' => __( 'IP', 'site-blocks' ),
						'desc'  => __( 'Fixed internet', 'site-blocks' ),
					),
					array(
						'icon'  => 'smartphone',
						'title' => __( '4G', 'site-blocks' ),
						'desc'  => __( 'Cellular backup', 'site-blocks' ),
					),
					array(
						'icon'  => 'shield-check',
						'title' => __( 'Response', 'site-blocks' ),
						'desc'  => __( 'Your plan', 'site-blocks' ),
					),
				),
				'hero_image'      => 'back-to-base-hero-centre.webp',
				'hero_alt'        => __( 'Monitoring centre operator supervising back-to-base alarm events', 'site-blocks' ),
			)
		),
		'virtual-patrol' => array_merge(
			$ctas,
			array(
				'id'            => 'sg-virtual-patrol-hero-heading',
				'class'         => 'sg-monitoring-hero sg-virtual-patrol-hero',
				'breadcrumb'    => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Monitoring & Response', 'site-blocks' ), 'url' => $hub ),
					array( 'label' => __( 'Virtual Patrol', 'site-blocks' ), 'current' => true ),
				),
				'badge'         => __( 'Virtual Patrol · Sydney', 'site-blocks' ),
				'title_before'  => __( 'Professional eyes on your cameras, ', 'site-blocks' ),
				'title_accent'  => __( 'remotely', 'site-blocks' ),
				'title_after'   => __( '.', 'site-blocks' ),
				'lead'            => __( 'Safeguard\'s virtual patrol service puts live operators on your CCTV feeds, scheduled tours, alarm-triggered checks and documented reporting, without keeping a static guard on site every hour. Integrated with our monitoring and physical response options when attendance is required.', 'site-blocks' ),
				'trust_aria_label'=> __( 'Virtual patrol credentials', 'site-blocks' ),
				'trust_chips'     => array(
					array(
						'icon'  => 'id-card',
						'line1' => __( 'Master Licence', 'site-blocks' ),
						'line2' => '#000103619',
					),
					array(
						'icon'  => 'award',
						'line1' => __( 'ASIAL', 'site-blocks' ),
						'line2' => __( 'member', 'site-blocks' ),
					),
					array(
						'icon'  => 'cctv',
						'line1' => __( 'Live operator', 'site-blocks' ),
						'line2' => __( 'CCTV tours', 'site-blocks' ),
					),
					array(
						'icon'  => 'phone',
						'line1' => __( 'Call us', 'site-blocks' ),
						'line2' => '1300 225 276',
					),
				),
				'caption_title'   => __( 'Remote guarding over your existing cameras', 'site-blocks' ),
				'caption_items'   => array(
					array(
						'icon'  => 'calendar-clock',
						'title' => __( 'Scheduled', 'site-blocks' ),
						'desc'  => __( 'Patrol tours', 'site-blocks' ),
					),
					array(
						'icon'  => 'bell-ring',
						'title' => __( 'Alarm', 'site-blocks' ),
						'desc'  => __( 'Triggered checks', 'site-blocks' ),
					),
					array(
						'icon'  => 'clipboard-list',
						'title' => __( 'Reports', 'site-blocks' ),
						'desc'  => __( 'Documented activity', 'site-blocks' ),
					),
				),
				'hero_image'      => 'virtual-patrol-hero.webp',
				'hero_alt'        => __( 'Virtual patrol operator reviewing CCTV feeds in a bright monitoring room', 'site-blocks' ),
			)
		),
		'solar-cameras-monitoring' => array_merge(
			$ctas,
			array(
				'id'              => 'sg-solar-monitoring-hero-heading',
				'class'           => 'sg-monitoring-hero sg-solar-monitoring-hero',
				'breadcrumb'      => array(
					array( 'label' => __( 'Home', 'site-blocks' ), 'url' => home_url( '/' ) ),
					array( 'label' => __( 'Monitoring & Response', 'site-blocks' ), 'url' => $hub ),
					array( 'label' => __( 'Solar Cameras with Monitoring', 'site-blocks' ), 'current' => true ),
				),
				'badge'           => __( 'Solar Monitoring · Sydney & NSW', 'site-blocks' ),
				'title_before'    => __( 'Security where there is no power, ', 'site-blocks' ),
				'title_accent'    => __( 'monitored', 'site-blocks' ),
				'title_after'     => __( '.', 'site-blocks' ),
				'lead'            => __( 'Safeguard supplies solar-powered security cameras with 4G connectivity and professional monitoring for construction sites, farms, vacant land and remote properties across Greater Sydney and NSW. Deploy fast, monitor remotely, and escalate to patrol response when your plan requires it, without waiting for mains power or fixed internet.', 'site-blocks' ),
				'trust_aria_label'=> __( 'Solar monitoring credentials', 'site-blocks' ),
				'trust_chips'     => array(
					array(
						'icon'  => 'id-card',
						'line1' => __( 'Master Licence', 'site-blocks' ),
						'line2' => '#000103619',
					),
					array(
						'icon'  => 'award',
						'line1' => __( 'ASIAL', 'site-blocks' ),
						'line2' => __( 'member', 'site-blocks' ),
					),
					array(
						'icon'  => 'sun',
						'line1' => __( 'Solar +', 'site-blocks' ),
						'line2' => __( '4G', 'site-blocks' ),
					),
					array(
						'icon'  => 'phone',
						'line1' => __( 'Call us', 'site-blocks' ),
						'line2' => '1300 225 276',
					),
				),
				'caption_title'   => __( 'Security where there is no power or fixed internet', 'site-blocks' ),
				'caption_items'   => array(
					array(
						'icon'  => 'sun',
						'title' => __( 'Solar', 'site-blocks' ),
						'desc'  => __( 'Off-grid power', 'site-blocks' ),
					),
					array(
						'icon'  => 'signal',
						'title' => __( '4G', 'site-blocks' ),
						'desc'  => __( 'Remote connectivity', 'site-blocks' ),
					),
					array(
						'icon'  => 'shield-check',
						'title' => __( 'Monitor', 'site-blocks' ),
						'desc'  => __( 'Professional response', 'site-blocks' ),
					),
				),
				'secondary_label' => __( 'Talk to our team', 'site-blocks' ),
				'secondary_url'   => home_url( '/contact/' ),
				'hero_image'      => 'solar-monitoring-hero.webp',
				'hero_alt'        => __( 'Solar-powered security camera on a construction site mast with Sydney skyline in distance', 'site-blocks' ),
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
function site_blocks_monitoring_faq_items( string $page_key ): array {
	$faqs = array(
		'hub' => array(
			array(
				'q' => __( 'What is back-to-base alarm monitoring?', 'site-blocks' ),
				'a' => __( 'Your alarm system sends signals to a professional monitoring centre. When an alert activates, trained operators follow your response plan, contacting you, keyholders or arranging attendance, 24 hours a day.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do I need monitoring if I have the Ajax app?', 'site-blocks' ),
				'a' => __( 'Self-monitoring via the app suits many homes, you receive alerts on your phone. Professional monitoring adds a centre that acts when you cannot, asleep, overseas or unreachable.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What happens when my alarm activates at 2am?', 'site-blocks' ),
				'a' => __( 'Our monitoring centre receives the signal, assesses it against your plan and executes the agreed response, typically keyholder notification first, then patrol or alarm response attendance if required.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Does monitoring work if my internet drops?', 'site-blocks' ),
				'a' => __( 'Dual-path communication (IP plus cellular backup) keeps many systems online. We recommend the right path for your property during assessment, residential and commercial requirements differ.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What is virtual patrol?', 'site-blocks' ),
				'a' => __( 'Live operators review your CCTV on scheduled or alarm-triggered tours, remote guarding without a physical guard on site every hour. See our Virtual Patrol page for detail.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can Safeguard monitor a site with no mains power?', 'site-blocks' ),
				'a' => __( 'Yes, solar-powered cameras with 4G connectivity and professional monitoring suit construction sites, farms and remote properties. See Solar Cameras with Monitoring.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you publish monitoring prices on the website?', 'site-blocks' ),
				'a' => __( 'No. Monitoring is quoted per property based on equipment, communication path and response plan. Request a quote online or speak to our team for a tailored price.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Are you licensed to provide monitoring in NSW?', 'site-blocks' ),
				'a' => __( 'Safeguard operates under Master Licence #000103619 and is an ASIAL member.', 'site-blocks' ),
			),
		),
		'back-to-base' => array(
			array(
				'q' => __( 'What is back-to-base monitoring?', 'site-blocks' ),
				'a' => __( 'Your alarm sends signals to a 24/7 monitoring centre. Operators act on alerts using your agreed response plan, not relying on you to see a phone notification.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What\'s the difference between IP and 4G monitoring?', 'site-blocks' ),
				'a' => __( 'IP uses your internet connection; 4G uses the mobile network. 4G suits commercial sites and anywhere you need backup if broadband fails.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can you monitor my existing alarm?', 'site-blocks' ),
				'a' => __( 'Often yes, we assess your panel and communicator during a site review.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What is a daily test or poll signal?', 'site-blocks' ),
				'a' => __( 'Regular check-ins confirm your system is communicating with the monitoring centre. If communication fails, you are notified to arrange service.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Who gets called first when my alarm trips?', 'site-blocks' ),
				'a' => __( 'Your documented keyholder list, in the order you specify. Patrol or alarm response attendance follows your plan if required.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you publish monitoring prices online?', 'site-blocks' ),
				'a' => __( 'No. We quote monitoring per property. Request a quote or use our Ajax calculator for a system quote that includes monitoring options.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can I switch from self-monitoring to back-to-base later?', 'site-blocks' ),
				'a' => __( 'Yes, many customers start with the app and add professional monitoring when their risk or lifestyle changes.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Is monitoring prepaid?', 'site-blocks' ),
				'a' => __( 'Professional monitoring is typically billed in advance on a quarterly or annual cycle. Your quote confirms billing terms.', 'site-blocks' ),
			),
		),
		'virtual-patrol' => array(
			array(
				'q' => __( 'What is virtual patrol?', 'site-blocks' ),
				'a' => __( 'Live operators review your CCTV on scheduled or alarm-triggered tours, remote guarding without a physical guard on site every hour.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How is virtual patrol different from mobile patrol?', 'site-blocks' ),
				'a' => __( 'Virtual patrol is remote via cameras. Mobile patrol sends a licensed officer to your site in person. Many sites use both.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do I need speakers for virtual patrol?', 'site-blocks' ),
				'a' => __( 'Not always, tours and verification work without audio. Speakers allow operator challenge where installed.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can virtual patrol replace a static guard?', 'site-blocks' ),
				'a' => __( 'It can reduce cost for some after-hours sites, but high-risk locations may still need physical presence. We assess at quote stage.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How often are tours scheduled?', 'site-blocks' ),
				'a' => __( 'Agreed in your service plan based on risk, from several checks per night to broader schedules for lower-risk sites.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you publish virtual patrol prices online?', 'site-blocks' ),
				'a' => __( 'No. Virtual patrol is quoted per site based on camera count, tour frequency and response plan.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can virtual patrol work with my existing cameras?', 'site-blocks' ),
				'a' => __( 'Often yes, we confirm compatibility and coverage during assessment.', 'site-blocks' ),
			),
		),
		'solar-cameras-monitoring' => array(
			array(
				'q' => __( 'Do solar cameras work in winter or overcast weather?', 'site-blocks' ),
				'a' => __( 'Systems are sized for local conditions with battery backup, we specify equipment for your site during quoting.', 'site-blocks' ),
			),
			array(
				'q' => __( 'What happens if the battery runs low?', 'site-blocks' ),
				'a' => __( 'Monitoring paths can alert you to power or communication issues so service is arranged before coverage gaps.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Is 4G monitoring included?', 'site-blocks' ),
				'a' => __( '4G connectivity and monitoring are part of the quoted package, details confirmed in your proposal, not published as standard rates online.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can you monitor a site with no mains power at all?', 'site-blocks' ),
				'a' => __( 'Yes, that is the primary use case for solar monitoring deployments.', 'site-blocks' ),
			),
			array(
				'q' => __( 'How fast can a unit be deployed?', 'site-blocks' ),
				'a' => __( 'Many sites can be live within days of approval, subject to site access and equipment availability.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Do you publish solar monitoring prices online?', 'site-blocks' ),
				'a' => __( 'No. Every site differs, request a quote for hardware, install and monitoring combined.', 'site-blocks' ),
			),
			array(
				'q' => __( 'Can cameras move as my construction site progresses?', 'site-blocks' ),
				'a' => __( 'Often yes, relocation can be arranged as part of an ongoing construction security plan.', 'site-blocks' ),
			),
		),
	);

	return $faqs[ $page_key ] ?? array();
}

/**
 * Hub service cards (3 spokes).
 *
 * @return array<int, array{title: string, desc: string, url: string}>
 */
function site_blocks_monitoring_hub_services(): array {
	return array(
		array(
			'title' => __( 'Back-to-Base Alarm Monitoring', 'site-blocks' ),
			'desc'  => __( 'Your alarm connects to our monitoring centre. When sensors trigger, trained operators follow your response plan, day or night.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/back-to-base/' ),
		),
		array(
			'title' => __( 'Virtual Patrol & Remote Guarding', 'site-blocks' ),
			'desc'  => __( 'Scheduled and alarm-triggered CCTV tours by live operators, professional cover without a guard on site every hour.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/virtual-patrol/' ),
		),
		array(
			'title' => __( 'Solar Cameras with Monitoring', 'site-blocks' ),
			'desc'  => __( 'Solar-powered cameras with 4G connectivity and professional monitoring for construction, rural and off-grid sites.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/solar-cameras-monitoring/' ),
		),
	);
}

/**
 * Hub services section heading.
 *
 * @return array{title: string, intro: string}
 */
function site_blocks_monitoring_services_heading(): array {
	return array(
		'title' => __( 'Choose the monitoring that fits your site', 'site-blocks' ),
		'intro' => __( 'Three ways Safeguard keeps eyes on your property, whether your alarm trips, your cameras need scheduled checks, or your site has no fixed power.', 'site-blocks' ),
	);
}

/**
 * Cross-link integration services.
 *
 * @return array<int, array{title: string, url: string}>
 */
function site_blocks_monitoring_integration_links(): array {
	return array(
		array(
			'title' => __( 'Alarm Systems', 'site-blocks' ),
			'url'   => home_url( '/alarm-systems/' ),
		),
		array(
			'title' => __( 'Ajax Alarm Systems', 'site-blocks' ),
			'url'   => home_url( '/ajax-alarm-systems/' ),
		),
		array(
			'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
			'url'   => home_url( '/cctv-security-cameras/' ),
		),
		array(
			'title' => __( 'Access Control', 'site-blocks' ),
			'url'   => home_url( '/access-control/' ),
		),
		array(
			'title' => __( 'Physical Security', 'site-blocks' ),
			'url'   => home_url( '/physical-security/' ),
		),
		array(
			'title' => __( 'Mobile Patrols', 'site-blocks' ),
			'url'   => home_url( '/physical-security/mobile-patrols/' ),
		),
	);
}

/**
 * Related services card strip config per cross-link variant.
 *
 * @param bool|string $variant Config key.
 * @return array<string, mixed>|null
 */
function site_blocks_monitoring_related_services_config( $variant ): ?array {
	$monitoring_hub = home_url( '/monitoring/' );

	$base = array(
		'eyebrow'       => __( 'Related services', 'site-blocks' ),
		'title_before'  => __( 'Explore the ', 'site-blocks' ),
		'title_accent'  => __( 'connected', 'site-blocks' ),
		'title_after'   => __( ' services', 'site-blocks' ),
		'intro'         => __( 'If you need another part of the system, these are the most common next steps.', 'site-blocks' ),
		'use_brand_icons' => false,
		'hub_link'      => array(
			'label' => __( 'View all related services', 'site-blocks' ),
			'url'   => $monitoring_hub,
		),
	);

	$solar_cards = array(
		array(
			'title' => __( 'Back-to-Base Monitoring', 'site-blocks' ),
			'desc'  => __( '24/7 alarm monitoring for connected systems.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/back-to-base/' ),
			'icon'  => 'back-to-base',
		),
		array(
			'title' => __( 'Virtual Patrol', 'site-blocks' ),
			'desc'  => __( 'Live CCTV monitoring with operator response.', 'site-blocks' ),
			'url'   => home_url( '/monitoring/virtual-patrol/' ),
			'icon'  => 'virtual-patrol',
		),
		array(
			'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
			'desc'  => __( 'Camera systems that support verification and review.', 'site-blocks' ),
			'url'   => home_url( '/cctv-security-cameras/' ),
			'icon'  => 'cctv',
		),
		array(
			'title' => __( 'Mobile Patrols', 'site-blocks' ),
			'desc'  => __( 'On-site attendance, checks and physical response.', 'site-blocks' ),
			'url'   => home_url( '/physical-security/mobile-patrols/' ),
			'icon'  => 'mobile-patrols',
		),
	);

	if ( 'solar' === $variant ) {
		// Solar integration, brand icons from Safeguard asset pack.
		return array_merge(
			$base,
			array(
				'use_brand_icons' => true,
				'cards'           => $solar_cards,
			)
		);
	}

	if ( 'hub' === $variant || true === $variant ) {
		return array_merge(
			$base,
			array(
				'cards' => $solar_cards,
			)
		);
	}

	if ( 'compatible' === $variant ) {
		return array_merge(
			$base,
			array(
				'cards' => array(
					array(
						'title' => __( 'Ajax Alarm Systems', 'site-blocks' ),
						'desc'  => __( 'Wireless systems with app and professional monitoring paths.', 'site-blocks' ),
						'url'   => home_url( '/ajax-alarm-systems/' ),
						'icon'  => 'ajax',
					),
					array(
						'title' => __( 'Alarm Systems', 'site-blocks' ),
						'desc'  => __( 'Wireless, wired and smart alarm solutions for your property.', 'site-blocks' ),
						'url'   => home_url( '/alarm-systems/' ),
						'icon'  => 'alarm-systems',
					),
					array(
						'title' => __( 'Virtual Patrol', 'site-blocks' ),
						'desc'  => __( 'Live CCTV monitoring with operator response.', 'site-blocks' ),
						'url'   => home_url( '/monitoring/virtual-patrol/' ),
						'icon'  => 'virtual-patrol',
					),
				),
			)
		);
	}

	if ( 'virtual-patrol-req' === $variant ) {
		return array_merge(
			$base,
			array(
				'hub_link' => null,
				'cards'    => array(
					array(
						'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),
						'desc'  => __( 'Camera systems that support verification and review.', 'site-blocks' ),
						'url'   => home_url( '/cctv-security-cameras/' ),
						'icon'  => 'cctv',
					),
					array(
						'title' => __( 'Solar Cameras with Monitoring', 'site-blocks' ),
						'desc'  => __( 'Off-grid solar and 4G cameras with professional monitoring.', 'site-blocks' ),
						'url'   => home_url( '/monitoring/solar-cameras-monitoring/' ),
						'icon'  => 'solar',
					),
					array(
						'title' => __( 'Mobile Patrols', 'site-blocks' ),
						'desc'  => __( 'On-site attendance, checks and physical response.', 'site-blocks' ),
						'url'   => home_url( '/physical-security/mobile-patrols/' ),
						'icon'  => 'mobile-patrols',
					),
				),
			)
		);
	}

	return null;
}

/**
 * End-of-page related services grid config per monitoring page key.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_monitoring_related_page_grid_config( string $page_key ): ?array {
	$grids = array(
		'hub' => array(
			'heading_id'    => 'sg-monitoring-related-heading',
			'section_class' => 'sg-monitoring-related',
			'title_before'  => __( 'Related ', 'site-blocks' ),
			'title_accent'  => __( 'services', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Alarm Systems', 'site-blocks' ), 'desc' => __( 'Wireless and smart alarms we connect to monitoring.', 'site-blocks' ), 'url' => home_url( '/alarm-systems/' ), 'icon' => 'alarm-systems.png' ),
				array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'Cameras for verification and virtual patrol.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Mobile Patrols', 'site-blocks' ), 'desc' => __( 'On-site attendance when response plans require it.', 'site-blocks' ), 'url' => home_url( '/physical-security/mobile-patrols/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Access Control', 'site-blocks' ), 'desc' => __( 'Manage entry alongside alarm and camera cover.', 'site-blocks' ), 'url' => home_url( '/access-control/' ), 'icon' => 'access-control.png' ),
			),
		),
		'back-to-base' => array(
			'heading_id'    => 'sg-monitoring-b2b-related-heading',
			'section_class' => 'sg-monitoring-b2b-related',
			'title_before'  => __( 'Often installed ', 'site-blocks' ),
			'title_accent'  => __( 'with', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Ajax Alarm Systems', 'site-blocks' ), 'desc' => __( 'Wireless systems with IP and 4G reporting paths.', 'site-blocks' ), 'url' => home_url( '/ajax-alarm-systems/' ), 'icon' => 'alarm-systems.png' ),
				array( 'title' => __( 'Virtual Patrol', 'site-blocks' ), 'desc' => __( 'Add live CCTV verification to alarm events.', 'site-blocks' ), 'url' => home_url( '/monitoring/virtual-patrol/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Solar Camera Monitoring', 'site-blocks' ), 'desc' => __( 'Remote sites without fixed internet.', 'site-blocks' ), 'url' => home_url( '/monitoring/solar-cameras-monitoring/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Mobile Patrols', 'site-blocks' ), 'desc' => __( 'Physical attendance when your plan requires it.', 'site-blocks' ), 'url' => home_url( '/physical-security/mobile-patrols/' ), 'icon' => 'support.png' ),
			),
		),
		'virtual-patrol' => array(
			'heading_id'    => 'sg-monitoring-vp-related-heading',
			'section_class' => 'sg-monitoring-vp-related',
			'title_before'  => __( 'Works best ', 'site-blocks' ),
			'title_accent'  => __( 'with', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'Camera systems operators tour and verify.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Back-to-Base Monitoring', 'site-blocks' ), 'desc' => __( 'Alarm signals with agreed response plans.', 'site-blocks' ), 'url' => home_url( '/monitoring/back-to-base/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Solar Camera Monitoring', 'site-blocks' ), 'desc' => __( 'Temporary and remote sites on 4G.', 'site-blocks' ), 'url' => home_url( '/monitoring/solar-cameras-monitoring/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Mobile Patrols', 'site-blocks' ), 'desc' => __( 'Escalate to on-site attendance when required.', 'site-blocks' ), 'url' => home_url( '/physical-security/mobile-patrols/' ), 'icon' => 'support.png' ),
			),
		),
		'solar-cameras-monitoring' => array(
			'heading_id'    => 'sg-monitoring-solar-related-heading',
			'section_class' => 'sg-monitoring-solar-related',
			'title_before'  => __( 'Related ', 'site-blocks' ),
			'title_accent'  => __( 'services', 'site-blocks' ),
			'cards'         => array(
				array( 'title' => __( 'Back-to-Base Monitoring', 'site-blocks' ), 'desc' => __( '24/7 centre cover for connected alarms.', 'site-blocks' ), 'url' => home_url( '/monitoring/back-to-base/' ), 'icon' => 'support.png' ),
				array( 'title' => __( 'Virtual Patrol', 'site-blocks' ), 'desc' => __( 'Operator tours over live camera feeds.', 'site-blocks' ), 'url' => home_url( '/monitoring/virtual-patrol/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'CCTV & Security Cameras', 'site-blocks' ), 'desc' => __( 'Permanent camera systems for powered sites.', 'site-blocks' ), 'url' => home_url( '/cctv-security-cameras/' ), 'icon' => 'ip-camera.png' ),
				array( 'title' => __( 'Mobile Patrols', 'site-blocks' ), 'desc' => __( 'Physical checks and alarm response.', 'site-blocks' ), 'url' => home_url( '/physical-security/mobile-patrols/' ), 'icon' => 'support.png' ),
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
function site_blocks_monitoring_process_steps(): array {
	return array(
		array(
			'title'       => __( 'Site assessment', 'site-blocks' ),
			'description' => __( 'We review your risk, hours, access and existing equipment.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Tailored plan', 'site-blocks' ),
			'description' => __( 'The right mix of back-to-base, virtual patrol, solar monitoring and physical response.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Connection & commissioning', 'site-blocks' ),
			'description' => __( 'Your alarm or cameras are connected, tested and documented.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Monitoring & reporting', 'site-blocks' ),
			'description' => __( '24/7 centre cover with clear incident logs and agreed notifications.', 'site-blocks' ),
		),
		array(
			'title'       => __( 'Review', 'site-blocks' ),
			'description' => __( 'We refine coverage as your site, staff or build phase changes.', 'site-blocks' ),
		),
	);
}

/**
 * Hub three levels of cover (paths section).
 *
 * @return array{title: string, intro: string, columns: array<int, array{title: string, body: string}>, note: string}
 */
function site_blocks_monitoring_paths_config(): array {
	return array(
		'title'   => __( 'Three levels of cover', 'site-blocks' ),
		'intro'   => __( 'Not every property needs the same response. Safeguard helps you choose, and we can combine options as your risk changes.', 'site-blocks' ),
		'columns' => array(
			array(
				'title' => __( 'Self-monitoring via app', 'site-blocks' ),
				'body'  => __( 'Monitor compatible systems from your phone with instant alerts when sensors trigger. You stay in control, but you need to be available to act. Best for low-risk sites or as a starting point before professional monitoring.', 'site-blocks' ),
			),
			array(
				'title' => __( 'Professional back-to-base', 'site-blocks' ),
				'body'  => __( 'Your system reports to our monitoring centre 24/7. When an alarm activates, operators follow your response plan, contacting keyholders, arranging patrol attendance or escalation as agreed. Ideal when you cannot rely on being reachable at 2am.', 'site-blocks' ),
			),
			array(
				'title' => __( 'Virtual patrol', 'site-blocks' ),
				'body'  => __( 'Live operators tour your CCTV feeds on a schedule or when alarms trigger, a remote guarding layer for warehouses, yards and after-hours sites. Different from mobile patrols: no vehicle on site unless your plan requires it.', 'site-blocks' ),
			),
		),
		'note'    => __( 'Monitoring is quoted per property based on communication path, response plan and equipment. We do not publish standard rates on this site, request a quote for your site.', 'site-blocks' ),
	);
}

/**
 * Split-section data for a page key and section id.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_monitoring_split_config( string $page_key, string $section ): ?array {
	$hub = home_url( '/monitoring/' );

	$splits = array(
		'hub' => array(
			'how-it-works' => array(
				'id'           => 'sg-monitoring-how-heading',
				'class'        => 'sg-monitoring-how-it-works',
				'title_before' => __( 'From alert to ', 'site-blocks' ),
				'title_accent' => __( 'action', 'site-blocks' ),
				'title_after'  => __( ' in four steps', 'site-blocks' ),
				'list'         => array(
					array( 'title' => __( 'Signal', 'site-blocks' ), 'desc' => __( 'Your alarm, camera or sensor detects an event and sends a signal to Safeguard\'s monitoring path.', 'site-blocks' ) ),
					array( 'title' => __( 'Receive', 'site-blocks' ), 'desc' => __( 'Our monitoring centre receives the alert and assesses it against your site profile and response plan.', 'site-blocks' ) ),
					array( 'title' => __( 'Verify', 'site-blocks' ), 'desc' => __( 'Where cameras are integrated, operators can verify the cause before escalating, reducing false callouts.', 'site-blocks' ) ),
					array( 'title' => __( 'Respond', 'site-blocks' ), 'desc' => __( 'Keyholders are notified, virtual patrol checks run, mobile patrols or alarm response officers are dispatched, per your agreed plan.', 'site-blocks' ) ),
				),
				'image'        => 'hub-how-it-works.webp',
				'alt'          => __( 'Back-to-base alarm signal received at monitoring centre', 'site-blocks' ),
				'reverse'      => false,
			),
			'integration' => array(
				'id'           => 'sg-monitoring-integration-heading',
				'class'        => 'sg-monitoring-integration',
				'band'         => 'white',
				'title_before' => __( 'Installed, monitored and ', 'site-blocks' ),
				'title_accent' => __( 'responded to', 'site-blocks' ),
				'title_after'  => __( ' by one team', 'site-blocks' ),
				'paragraphs'   => array(
					__( 'Most monitoring providers only watch signals. Most installers only fit hardware. Safeguard does both, so your alarms, CCTV, access control and guarding work together when something happens.', 'site-blocks' ),
					__( 'A camera flags movement, monitoring verifies it, and a guard responds. It\'s one coordinated response, not three vendors pointing at each other.', 'site-blocks' ),
				),
				'image'        => 'hub-integration.webp',
				'alt'          => __( 'Safeguard technician linking alarm and CCTV systems', 'site-blocks' ),
				'reverse'      => false,
			),
		),
		'back-to-base' => array(
			'intro' => array(
				'id'           => 'sg-btb-intro-heading',
				'class'        => 'sg-back-to-base-intro',
				'title_before' => __( 'Self-monitoring fails when the phone is ', 'site-blocks' ),
				'title_accent' => __( 'off', 'site-blocks' ),
				'title_after'  => __( '.', 'site-blocks' ),
				'paragraphs'   => array(
					__( 'App alerts work until you are asleep, in a meeting, on a flight or simply miss the notification. Back-to-base monitoring puts a professional centre between your alarm and your response plan, 24 hours a day.', 'site-blocks' ),
					sprintf(
						/* translators: %s: link to monitoring hub */
						__( 'Part of Safeguard\'s %s across Greater Sydney.', 'site-blocks' ),
						'<a href="' . esc_url( $hub ) . '">' . esc_html__( 'alarm monitoring services', 'site-blocks' ) . '</a>'
					),
				),
				'paragraphs_html' => true,
				'image'           => 'back-to-base-monitoring-centre.webp',
				'alt'             => __( 'Monitoring centre operator handling back-to-base alarm events', 'site-blocks' ),
				'reverse'         => false,
			),
			'response-plans' => array(
				'id'           => 'sg-btb-response-heading',
				'class'        => 'sg-back-to-base-response',
				'title_before' => __( 'Your response plan, agreed before monitoring ', 'site-blocks' ),
				'title_accent' => __( 'starts', 'site-blocks' ),
				'title_after'  => '',
				'intro'        => __( 'Monitoring only works with a clear plan. Safeguard documents who is called, in what order, and when physical attendance is required.', 'site-blocks' ),
				'list'         => array(
					array(
						'title' => __( 'Keyholder notification', 'site-blocks' ),
						'desc'  => __( 'Primary and secondary contacts called when alarms activate.', 'site-blocks' ),
					),
					array(
						'title' => __( 'Alarm response / patrol attendance', 'site-blocks' ),
						'desc'  => sprintf(
							/* translators: 1: mobile patrols link */
							__( 'Licensed officers attend to verify and secure the site when your plan requires it. Links to %1$s and future Alarm Response services.', 'site-blocks' ),
							'<a href="' . esc_url( home_url( '/physical-security/mobile-patrols/' ) ) . '">' . esc_html__( 'Mobile Patrols', 'site-blocks' ) . '</a>'
						),
						'html'  => true,
					),
					array(
						'title' => __( 'Video verification', 'site-blocks' ),
						'desc'  => __( 'Where CCTV is integrated, operators can review footage before escalating, reducing false callouts.', 'site-blocks' ),
					),
					array(
						'title' => __( 'Emergency services', 'site-blocks' ),
						'desc'  => __( 'Escalation per your documented plan and applicable regulations.', 'site-blocks' ),
					),
				),
				'image'        => 'back-to-base-response-plan.webp',
				'alt'          => __( 'Monitoring centre operator managing an alarm response workflow', 'site-blocks' ),
				'reverse'      => true,
			),
			'compatible-systems' => array(
				'id'           => 'sg-btb-compatible-heading',
				'class'        => 'sg-back-to-base-compatible',
				'band'         => 'blue',
				'title_before' => __( 'Systems we ', 'site-blocks' ),
				'title_accent' => __( 'monitor', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array(
						'title' => __( 'Ajax wireless systems', 'site-blocks' ),
						'desc'  => __( 'Installed and supported by Safeguard; self-monitoring via app or professional back-to-base.', 'site-blocks' ),
					),
					array(
						'title' => __( 'Upgraded wired systems', 'site-blocks' ),
						'desc'  => __( 'Legacy panels upgraded to report to a monitoring centre.', 'site-blocks' ),
					),
					array(
						'title' => __( 'Existing panels', 'site-blocks' ),
						'desc'  => __( 'Many systems can be connected, we assess compatibility during site review.', 'site-blocks' ),
					),
				),
				'cross_links'  => 'compatible',
			),
		),
		'virtual-patrol' => array(
			'intro' => array(
				'id'           => 'sg-vp-intro-heading',
				'class'        => 'sg-virtual-patrol-intro',
				'title_before' => __( 'Unmonitored CCTV only helps ', 'site-blocks' ),
				'title_accent' => __( 'after', 'site-blocks' ),
				'title_after'  => __( ' the fact.', 'site-blocks' ),
				'paragraphs'   => array(
					__( 'Cameras record incidents, but without someone watching, you often learn about problems too late. Virtual patrol adds live operator tours of your feeds on a schedule or when alarms trigger.', 'site-blocks' ),
					sprintf(
						/* translators: 1: monitoring hub link, 2: mobile patrols link */
						__( 'Part of Safeguard\'s %1$s, distinct from %2$s, where licensed officers attend in person.', 'site-blocks' ),
						'<a href="' . esc_url( $hub ) . '">' . esc_html__( 'monitoring services', 'site-blocks' ) . '</a>',
						'<a href="' . esc_url( home_url( '/physical-security/mobile-patrols/' ) ) . '">' . esc_html__( 'mobile patrols', 'site-blocks' ) . '</a>'
					),
				),
				'paragraphs_html' => true,
				'image'           => 'virtual-patrol-hero.webp',
				'alt'             => __( 'Virtual patrol operator reviewing CCTV feeds in a bright monitoring room', 'site-blocks' ),
				'reverse'         => false,
			),
		),
		'solar-cameras-monitoring' => array(
			'intro' => array(
				'id'           => 'sg-solar-intro-heading',
				'class'        => 'sg-solar-monitoring-intro',
				'title_before' => __( 'Temporary sites still need ', 'site-blocks' ),
				'title_accent' => __( 'permanent', 'site-blocks' ),
				'title_after'  => __( ' cover.', 'site-blocks' ),
				'paragraphs'   => array(
					__( 'Construction phases, rural blocks and off-grid properties cannot always wait for cabling and NBN. Solar cameras with 4G reporting and professional monitoring close that gap, with incident verification and response linked to your plan.', 'site-blocks' ),
				),
				'image'        => 'solar-monitoring-hero.webp',
				'alt'          => __( 'Solar security camera on a Sydney construction site', 'site-blocks' ),
				'reverse'      => false,
			),
			'whats-included' => array(
				'id'           => 'sg-solar-included-heading',
				'class'        => 'sg-solar-monitoring-included',
				'title_before' => __( 'What a solar monitoring package typically ', 'site-blocks' ),
				'title_accent' => __( 'includes', 'site-blocks' ),
				'title_after'  => '',
				'intro'        => __( 'Every site differs, your quote lists exact hardware, install scope and monitoring plan. We do not publish package prices on the website.', 'site-blocks' ),
				'list'         => array(
					array( 'title' => __( 'Solar camera unit(s) with battery storage', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( '4G connectivity for live view and alarm reporting', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Professional installation and commissioning', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Back-to-base monitoring integration', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Optional virtual patrol tours over camera feeds', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Optional mobile patrol or alarm response escalation', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Relocation or expansion support for construction phases', 'site-blocks' ), 'desc' => '' ),
				),
				'image'        => 'solar-monitoring-install.webp',
				'alt'          => __( 'Safeguard installing solar monitoring camera', 'site-blocks' ),
				'reverse'      => true,
			),
			'technical' => array(
				'id'           => 'sg-solar-technical-heading',
				'class'        => 'sg-solar-monitoring-technical',
				'band'         => 'blue',
				'title_before' => __( 'Built for Australian ', 'site-blocks' ),
				'title_accent' => __( 'conditions', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Solar panel sized for local sun hours and camera load', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Battery backup for overnight and cloudy periods', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Human/vehicle detection to reduce false alerts', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'PTZ or fixed cameras depending on coverage needs', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( '4G SIM data plan managed as part of service', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Weather-rated housings for outdoor deployment', 'site-blocks' ), 'desc' => '' ),
				),
			),
			'monitoring-integration' => array(
				'id'           => 'sg-solar-integration-heading',
				'class'        => 'sg-solar-monitoring-integration',
				'title_before' => __( 'Monitored, not just ', 'site-blocks' ),
				'title_accent' => __( 'recorded', 'site-blocks' ),
				'title_after'  => '',
				'list'         => array(
					array( 'title' => __( 'Alarm events report to Safeguard\'s monitoring centre', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Operators verify via live view where cameras allow', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Keyholders notified; patrol dispatched per response plan', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'Tour logs for construction managers and asset owners', 'site-blocks' ), 'desc' => '' ),
					array( 'title' => __( 'One vendor for install, monitoring and physical response, not three vendors pointing at each other.', 'site-blocks' ), 'desc' => '' ),
				),
				'image'        => 'solar-monitoring-night.webp',
				'alt'          => __( 'Night-time monitored perimeter on construction site', 'site-blocks' ),
				'reverse'      => false,
			),
		),
	);

	return $splits[ $page_key ][ $section ] ?? null;
}

/**
 * Numbered steps config for spoke how-it-works sections.
 *
 * @return array{eyebrow?: string, title_before: string, title_accent: string, title_after?: string, intro?: string, steps: array<int, array{title: string, desc: string}>}|null
 */
function site_blocks_monitoring_steps_config( string $page_key ): ?array {
	$configs = array(
		'back-to-base' => array(
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'How back-to-base monitoring ', 'site-blocks' ),
			'title_accent'  => __( 'works', 'site-blocks' ),
			'steps'         => array(
				array( 'title' => __( 'Alarm activates', 'site-blocks' ), 'desc' => __( 'A sensor, door contact or panic button triggers your system.', 'site-blocks' ) ),
				array( 'title' => __( 'Signal sent', 'site-blocks' ), 'desc' => __( 'Your communicator sends the alert to the monitoring centre over IP, 4G or dual path.', 'site-blocks' ) ),
				array( 'title' => __( 'Operator assesses', 'site-blocks' ), 'desc' => __( 'Trained staff review the signal against your site profile and event type.', 'site-blocks' ) ),
				array( 'title' => __( 'Response executed', 'site-blocks' ), 'desc' => __( 'Keyholders are called in order; patrol or alarm response attendance is arranged per your plan.', 'site-blocks' ) ),
				array( 'title' => __( 'Logged and reported', 'site-blocks' ), 'desc' => __( 'Incidents are recorded for your records and future review.', 'site-blocks' ) ),
			),
		),
		'virtual-patrol' => array(
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'How virtual patrol ', 'site-blocks' ),
			'title_accent'  => __( 'works', 'site-blocks' ),
			'steps'         => array(
				array( 'title' => __( 'Schedule agreed', 'site-blocks' ), 'desc' => __( 'Random and scheduled tour times based on your risk profile.', 'site-blocks' ) ),
				array( 'title' => __( 'Operator tours feeds', 'site-blocks' ), 'desc' => __( 'Live review of nominated cameras, perimeters, gates, yards, loading docks.', 'site-blocks' ) ),
				array( 'title' => __( 'Incidents flagged', 'site-blocks' ), 'desc' => __( 'Suspicious activity logged with timestamps; keyholders notified per plan.', 'site-blocks' ) ),
				array( 'title' => __( 'Escalation', 'site-blocks' ), 'desc' => __( 'Alarm response or mobile patrol dispatched when your plan requires physical attendance.', 'site-blocks' ) ),
				array( 'title' => __( 'Report delivered', 'site-blocks' ), 'desc' => __( 'Tour logs and incident notes for your records.', 'site-blocks' ) ),
			),
		),
	);

	return $configs[ $page_key ] ?? null;
}

/**
 * Communicators table (back-to-base).
 *
 * @return array{title: string, rows: array<int, array{path: string, best_for: string, summary: string, card_summary: string, icon: string}>, note: string}
 */
function site_blocks_monitoring_communicators_config(): array {
	return array(
		'title' => __( 'IP and 4G monitoring paths', 'site-blocks' ),
		'rows' => array(
			array(
				'path'         => __( 'IP monitoring', 'site-blocks' ),
				'best_for'     => __( 'Residential with stable internet', 'site-blocks' ),
				'summary'      => __( 'Alarm reports over your broadband connection. Cost-effective where connectivity is reliable.', 'site-blocks' ),
				'card_summary' => __( 'Best for residential with stable internet.', 'site-blocks' ),
				'icon'         => 'router',
			),
			array(
				'path'         => __( '4G monitoring', 'site-blocks' ),
				'best_for'     => __( 'Commercial sites, or where backup is required', 'site-blocks' ),
				'summary'      => __( 'Reports over the mobile network, keeps monitoring online if internet drops. Often required for commercial premises.', 'site-blocks' ),
				'card_summary' => __( 'Ideal for commercial sites, or where backup is required.', 'site-blocks' ),
				'icon'         => 'mobile',
			),
			array(
				'path'         => __( 'Dual path', 'site-blocks' ),
				'best_for'     => __( 'Higher reliability needs', 'site-blocks' ),
				'summary'      => __( 'Ethernet or Wi‑Fi plus cellular backup, common on modern smart alarm systems.', 'site-blocks' ),
				'card_summary' => __( 'For sites with higher reliability needs.', 'site-blocks' ),
				'icon'         => 'shield',
			),
		),
		'note' => __( 'Commercial and shop premises typically require a cellular monitoring path. We confirm requirements during your assessment.', 'site-blocks' ),
	);
}

/**
 * Virtual patrol vs mobile patrol comparison table.
 *
 * @return array{title: string, headers: array<int, string>, rows: array<int, array<string, string>>, clarifier_html: string}
 */
function site_blocks_monitoring_compare_config(): array {
	return array(
		'title'   => __( 'Virtual patrol vs mobile patrol: which do you need?', 'site-blocks' ),
		'headers' => array(
			'',
			__( 'Virtual Patrol', 'site-blocks' ),
			__( 'Mobile Patrols', 'site-blocks' ),
		),
		'rows'    => array(
			array(
				'label' => __( 'Cover type', 'site-blocks' ),
				'virtual' => __( 'Remote, operators on CCTV', 'site-blocks' ),
				'mobile'  => __( 'Physical, licensed officer on site', 'site-blocks' ),
			),
			array(
				'label' => __( 'Best for', 'site-blocks' ),
				'virtual' => __( 'Warehouses, yards, after-hours commercial', 'site-blocks' ),
				'mobile'  => __( 'Lock-ups, alarm response, visible deterrence', 'site-blocks' ),
			),
			array(
				'label' => __( 'Cost profile', 'site-blocks' ),
				'virtual' => __( 'Lower than 24/7 static guard; quoted per site', 'site-blocks' ),
				'mobile'  => __( 'Scheduled visits; quoted per site', 'site-blocks' ),
			),
			array(
				'label' => __( 'Response', 'site-blocks' ),
				'virtual' => __( 'Remote verification first; physical dispatch per plan', 'site-blocks' ),
				'mobile'  => __( 'Officer attends in person', 'site-blocks' ),
			),
			array(
				'label' => __( 'Requires', 'site-blocks' ),
				'virtual' => __( 'Compatible CCTV/NVR and bandwidth', 'site-blocks' ),
				'mobile'  => __( 'Site access and patrol route', 'site-blocks' ),
			),
			array(
				'label' => __( 'Safeguard page', 'site-blocks' ),
				'virtual' => home_url( '/monitoring/virtual-patrol/' ),
				'mobile'  => home_url( '/physical-security/mobile-patrols/' ),
				'links'   => true,
			),
		),
		'clarifier_html' => sprintf(
			/* translators: %s: mobile patrols link */
			__( 'Need a guard on site? See %s. Need 24/7 eyes on cameras? Virtual patrol is the answer.', 'site-blocks' ),
			'<a href="' . esc_url( home_url( '/physical-security/mobile-patrols/' ) ) . '">' . esc_html__( 'Mobile Patrols', 'site-blocks' ) . '</a>'
		),
	);
}

/**
 * Virtual patrol features list.
 *
 * @return array<int, string>
 */
function site_blocks_monitoring_features_list(): array {
	return array(
		__( 'Scheduled and random virtual tours', 'site-blocks' ),
		__( 'Alarm-triggered camera verification', 'site-blocks' ),
		__( 'Timestamped tour and incident logs', 'site-blocks' ),
		__( 'Integration with back-to-base monitoring', 'site-blocks' ),
		__( 'Optional audio challenge where speakers are installed', 'site-blocks' ),
		__( 'Escalation to physical patrol or alarm response per plan', 'site-blocks' ),
	);
}

/**
 * Industry grid items per page key.
 *
 * @return array<int, array{title: string, desc: string}>
 */
function site_blocks_monitoring_industry_items( string $page_key ): array {
	$items = array(
		'hub' => array(
			array( 'title' => __( 'Residential', 'site-blocks' ), 'desc' => __( 'Home alarms monitored when you travel, sleep or cannot reach your phone in time.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
			array( 'title' => __( 'Retail & commercial', 'site-blocks' ), 'desc' => __( 'After-hours alarm and camera cover for shops, offices and small warehouses.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Warehouses & logistics', 'site-blocks' ), 'desc' => __( 'Virtual patrol and back-to-base for large perimeters and limited staff overnight.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Construction', 'site-blocks' ), 'desc' => __( 'Solar-monitored cameras for sites without power or fixed internet.', 'site-blocks' ), 'icon' => 'construction' ),
			array( 'title' => __( 'Strata', 'site-blocks' ), 'desc' => __( 'Common-area alarms and cameras with clear reporting for committees.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
			array( 'title' => __( 'Rural & remote', 'site-blocks' ), 'desc' => __( '4G solar monitoring where traditional cabling is not practical.', 'site-blocks' ), 'icon' => 'farm-rural' ),
		),
		'virtual-patrol' => array(
			array( 'title' => __( 'Warehouses & logistics', 'site-blocks' ), 'desc' => __( 'Perimeter and loading bay checks overnight.', 'site-blocks' ), 'icon' => 'warehouse' ),
			array( 'title' => __( 'Construction', 'site-blocks' ), 'desc' => __( 'After-hours monitoring before permanent power and staff.', 'site-blocks' ), 'icon' => 'hard-hat' ),
			array( 'title' => __( 'Car yards & equipment storage', 'site-blocks' ), 'desc' => __( 'Wide open sites with multiple entry points.', 'site-blocks' ), 'icon' => 'car' ),
			array( 'title' => __( 'Strata common areas', 'site-blocks' ), 'desc' => __( 'Car parks, lobbies and plant rooms.', 'site-blocks' ), 'icon' => 'building-2' ),
			array( 'title' => __( 'Retail back-of-house', 'site-blocks' ), 'desc' => __( 'Stock areas and delivery zones.', 'site-blocks' ), 'icon' => 'store' ),
		),
		'solar-cameras-monitoring' => array(
			array( 'title' => __( 'Construction sites', 'site-blocks' ), 'desc' => __( 'Temporary cover before power and fixed internet are live.', 'site-blocks' ), 'icon' => 'construction' ),
			array( 'title' => __( 'Rural properties', 'site-blocks' ), 'desc' => __( 'Remote blocks and sheds without trenching for cable.', 'site-blocks' ), 'icon' => 'farm-rural' ),
			array( 'title' => __( 'Warehouses & yards', 'site-blocks' ), 'desc' => __( 'Perimeter cameras where mains power is limited.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
			array( 'title' => __( 'Vacant land', 'site-blocks' ), 'desc' => __( 'Deterrence and verified alerts on undeveloped sites.', 'site-blocks' ), 'icon' => 'fence-perimeter' ),
			array( 'title' => __( 'Equipment storage', 'site-blocks' ), 'desc' => __( 'Plant, tools and materials left on site overnight.', 'site-blocks' ), 'icon' => 'warehouse-yard' ),
		),
	);

	return $items[ $page_key ] ?? array();
}

/**
 * Industry grid heading per page key.
 *
 * @return array{before: string, accent: string}|null
 */
function site_blocks_monitoring_industry_heading( string $page_key ): ?array {
	$headings = array(
		'hub'                      => array(
			'before' => __( 'Monitoring for every kind of ', 'site-blocks' ),
			'accent' => __( 'site', 'site-blocks' ),
		),
		'virtual-patrol'           => array(
			'before' => __( 'Built for after-hours ', 'site-blocks' ),
			'accent' => __( 'sites', 'site-blocks' ),
		),
		'solar-cameras-monitoring' => array(
			'before' => __( 'Solar monitoring for remote ', 'site-blocks' ),
			'accent' => __( 'sites', 'site-blocks' ),
		),
	);

	return $headings[ $page_key ] ?? null;
}

/**
 * Build scenario grid config for monitoring industry sections.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_monitoring_industry_scenario_config( string $page_key ): ?array {
	$heading = site_blocks_monitoring_industry_heading( $page_key );
	$items   = site_blocks_monitoring_industry_items( $page_key );

	if ( null === $heading || $items === array() ) {
		return null;
	}

	return array(
		'layout'          => 'default',
		'eyebrow'         => __( 'Industries', 'site-blocks' ),
		'title_before'    => $heading['before'],
		'title_accent'    => $heading['accent'],
		'use_brand_icons' => 'virtual-patrol' !== $page_key,
		'use_lucide_icons'=> 'virtual-patrol' === $page_key,
		'cards'           => $items,
	);
}

/**
 * Solar package inclusions section.
 *
 * @return array<string, mixed>|null
 */
function site_blocks_monitoring_package_inclusions_config( string $page_key ): ?array {
	if ( 'solar-cameras-monitoring' !== $page_key ) {
		return null;
	}

	return array(
		'eyebrow'      => __( 'Package inclusions', 'site-blocks' ),
		'title_before' => __( 'What makes it work in ', 'site-blocks' ),
		'title_accent' => __( 'Australian conditions', 'site-blocks' ),
		'intro'        => __( 'Key package elements are selected to keep remote monitoring reliable across sun, weather and changing site conditions.', 'site-blocks' ),
		'image'        => 'solar-package-banner.png',
		'alt'          => __( 'Solar-powered CCTV camera installed in remote Australian conditions', 'site-blocks' ),
		'cards'        => array(
			array(
				'eyebrow' => __( 'Power', 'site-blocks' ),
				'icon'    => 'power',
				'title'   => __( 'Solar panel sized for local sun hours and camera load', 'site-blocks' ),
				'desc'    => __( 'Right-sized solar for consistent power from daylight through to peak usage.', 'site-blocks' ),
			),
			array(
				'eyebrow' => __( 'Backup', 'site-blocks' ),
				'icon'    => 'backup',
				'title'   => __( 'Battery backup for overnight and cloudy periods', 'site-blocks' ),
				'desc'    => __( 'Integrated battery storage keeps your system online when the sun is not.', 'site-blocks' ),
			),
			array(
				'eyebrow' => __( 'Detection', 'site-blocks' ),
				'icon'    => 'detection',
				'title'   => __( 'Human/vehicle detection to reduce false alerts', 'site-blocks' ),
				'desc'    => __( 'Smart detection focuses on people and vehicles, not trees or shadows.', 'site-blocks' ),
			),
			array(
				'eyebrow' => __( 'Camera type', 'site-blocks' ),
				'icon'    => 'camera',
				'title'   => __( 'PTZ or fixed cameras depending on coverage needs', 'site-blocks' ),
				'desc'    => __( 'Choose the right camera type and placement for your site and risks.', 'site-blocks' ),
			),
			array(
				'eyebrow' => __( 'Connectivity', 'site-blocks' ),
				'icon'    => 'connectivity',
				'title'   => __( '4G SIM data plan managed as part of service', 'site-blocks' ),
				'desc'    => __( 'Secure 4G connectivity with data managed and monitored by us.', 'site-blocks' ),
			),
			array(
				'eyebrow' => __( 'Outdoor hardware', 'site-blocks' ),
				'icon'    => 'weather',
				'title'   => __( 'Weather-rated housings for outdoor deployment', 'site-blocks' ),
				'desc'    => __( 'Rugged enclosures and mounts built to withstand harsh Australian conditions.', 'site-blocks' ),
			),
		),
		'checks'       => array(
			__( 'Installed to site requirements', 'site-blocks' ),
			__( 'Commissioned and tested', 'site-blocks' ),
			__( 'Monitored and supported 24/7', 'site-blocks' ),
		),
	);
}

/**
 * Solar use-cases scenario grid (40/60 split with photo).
 *
 * @return array<string, mixed>
 */
function site_blocks_monitoring_use_cases_config(): array {
	return array(
		'layout'          => 'split',
		'eyebrow'         => __( 'Use cases', 'site-blocks' ),
		'title_before'    => __( 'Where solar monitoring ', 'site-blocks' ),
		'title_accent'    => __( 'fits', 'site-blocks' ),
		'use_brand_icons' => true,
		'photo'           => array(
			'file' => 'solar-monitoring-rural.webp',
			'alt'  => __( 'Solar CCTV monitoring on a rural NSW property', 'site-blocks' ),
			'dir'  => 'monitoring',
		),
		'cards'           => array(
			array(
				'title' => __( 'Construction', 'site-blocks' ),
				'desc'  => __( 'Monitor plant, materials and access points from slab to handover. Move or expand as the site evolves.', 'site-blocks' ),
				'icon'  => 'construction',
			),
			array(
				'title' => __( 'Farms & rural', 'site-blocks' ),
				'desc'  => __( 'Perimeter and shed cover without trenching cable across paddocks.', 'site-blocks' ),
				'icon'  => 'farm-rural',
			),
			array(
				'title' => __( 'Vacant land', 'site-blocks' ),
				'desc'  => __( 'Deter trespass and dumping on undeveloped blocks.', 'site-blocks' ),
				'icon'  => 'fence-perimeter',
			),
			array(
				'title' => __( 'Remote commercial', 'site-blocks' ),
				'desc'  => __( 'Yards and depots where mains power exists but camera cabling does not.', 'site-blocks' ),
				'icon'  => 'warehouse-yard',
			),
		),
	);
}

/**
 * Virtual patrol requirements list.
 *
 * @return array{title: string, items: array<int, string>}
 */
function site_blocks_monitoring_requirements_config(): array {
	return array(
		'title' => __( 'What you need for virtual patrol', 'site-blocks' ),
		'items' => array(
			__( 'Compatible CCTV system or NVR with remote access', 'site-blocks' ),
			__( 'Adequate internet or 4G connectivity at the site', 'site-blocks' ),
			__( 'Camera coverage of priority areas (perimeter, entries, high-value zones)', 'site-blocks' ),
			__( 'Documented response plan linked to monitoring', 'site-blocks' ),
			__( 'Optional: IP speakers for operator audio challenge', 'site-blocks' ),
		),
	);
}

/**
 * Back-to-base quote section (no prices).
 *
 * @return array{title: string, body: string, links: array<int, array{label: string, url: string}>}
 */
function site_blocks_monitoring_quote_config(): array {
	return array(
		'title' => __( 'Request a quote for your property', 'site-blocks' ),
		'body'  => __( 'Monitoring cost depends on your communicator path, response plan and property type. Safeguard does not publish monitoring prices on this website, every site is different. Request a quote online and our team will recommend the right back-to-base setup with a tailored price.', 'site-blocks' ),
		'links' => array(
			array(
				'label' => __( 'Start My Quote', 'site-blocks' ),
				'url'   => home_url( '/get-an-instant-quote/' ),
				'primary' => true,
			),
			array(
				'label' => __( 'Ajax calculator', 'site-blocks' ),
				'url'   => home_url( '/ajax-calculator/' ),
			),
			array(
				'label' => __( 'Talk to our team', 'site-blocks' ),
				'url'   => home_url( '/contact/' ),
			),
		),
	);
}

/**
 * Final CTA band per page key.
 *
 * @return array{before: string, accent: string, after?: string, sub: string, secondary_label?: string, secondary_url?: string}|null
 */
function site_blocks_monitoring_cta_config( string $page_key ): ?array {
	$ctas = array(
		'hub' => array(
			'before'          => __( 'Get monitoring that connects to ', 'site-blocks' ),
			'accent'          => __( 'real response', 'site-blocks' ),
			'after'           => __( '.', 'site-blocks' ),
			'sub'             => __( 'Tell us about your property and we\'ll recommend back-to-base, virtual patrol or solar monitoring, quoted for your site, with no obligation.', 'site-blocks' ),
			'secondary_label' => __( 'Talk to our team', 'site-blocks' ),
			'secondary_url'   => home_url( '/contact/' ),
		),
		'back-to-base' => array(
			'before' => __( 'Connect your alarm to professional ', 'site-blocks' ),
			'accent' => __( 'monitoring', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Request a quote for back-to-base monitoring, tailored to your property, communicator path and response plan.', 'site-blocks' ),
		),
		'virtual-patrol' => array(
			'before' => __( 'Add live eyes to your ', 'site-blocks' ),
			'accent' => __( 'cameras', 'site-blocks' ),
			'after'  => __( '.', 'site-blocks' ),
			'sub'    => __( 'Tell us about your site and camera coverage, we\'ll recommend virtual patrol with a tailored quote.', 'site-blocks' ),
		),
		'solar-cameras-monitoring' => array(
			'before'          => __( 'Monitor your site before power and cabling are ', 'site-blocks' ),
			'accent'          => __( 'ready', 'site-blocks' ),
			'after'           => __( '.', 'site-blocks' ),
			'sub'             => __( 'Request a site quote for solar cameras with professional monitoring, hardware, install and response plan combined.', 'site-blocks' ),
			'secondary_label' => __( 'Talk to our team', 'site-blocks' ),
			'secondary_url'   => home_url( '/contact/' ),
		),
	);

	return $ctas[ $page_key ] ?? null;
}
