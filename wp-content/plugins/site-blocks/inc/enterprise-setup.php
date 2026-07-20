<?php
/**
 * Enterprise silo - SEO, schema, insight seeding, and page creation hooks.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/enterprise-config.php';

/**
 * Page title for Enterprise silo pages.
 */
function site_blocks_enterprise_document_title_filter( string $title ): string {
	$page_key = site_blocks_get_enterprise_page_key();

	if ( null === $page_key ) {
		return $title;
	}

	$custom = site_blocks_enterprise_document_title( $page_key );

	return '' !== $custom ? $custom : $title;
}
add_filter( 'pre_get_document_title', 'site_blocks_enterprise_document_title_filter', 20 );

/**
 * Meta description in head for Enterprise pages.
 */
function site_blocks_enterprise_output_meta_description(): void {
	$page_key = site_blocks_get_enterprise_page_key();

	if ( null === $page_key ) {
		return;
	}

	$description = site_blocks_enterprise_get_meta_description( $page_key );

	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'site_blocks_enterprise_output_meta_description', 2 );

/**
 * JSON-LD structured data per Enterprise page.
 */
function site_blocks_enterprise_schema(): void {
	$page_key = site_blocks_get_enterprise_page_key();

	if ( null === $page_key ) {
		return;
	}

	$site_url = home_url( '/' );
	$slugs    = site_blocks_enterprise_page_slugs();
	$page_url = home_url( '/' . $slugs[ $page_key ] . '/' );
	$phone    = '1300225276';

	$service_descriptions = array(
		'hub'                 => 'Integrated commercial and enterprise security across Sydney: CCTV, access control, monitoring and licensed guards from one accountable team.',
		'safeguard-solutions' => 'Safeguard Solutions cloud security platform: video, access, AI analytics and multi-site management in one console for Sydney businesses.',
	);

	$service_types = array(
		'hub'                 => 'Commercial and enterprise security services',
		'safeguard-solutions' => 'Cloud security platform',
	);

	$schema = array();

	if ( 'hub' === $page_key ) {
		$schema[] = array(
			'@context'           => 'https://schema.org',
			'@type'              => 'LocalBusiness',
			'@id'                => $site_url . '#business',
			'name'               => 'Safeguard Security Services',
			'url'                => $page_url,
			'telephone'          => $phone,
			'address'            => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '2/2 Stennett Road',
				'addressLocality' => 'Ingleburn',
				'addressRegion'   => 'NSW',
				'postalCode'      => '2565',
				'addressCountry'  => 'AU',
			),
			'areaServed'         => array( '@type' => 'City', 'name' => 'Sydney' ),
			'additionalProperty' => array(
				'@type' => 'PropertyValue',
				'name'  => 'Security Master Licence',
				'value' => '000103619',
			),
		);
	}

	$schema[] = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'serviceType' => $service_types[ $page_key ],
		'provider'    => array( '@id' => $site_url . '#business' ),
		'areaServed'  => array( '@type' => 'City', 'name' => 'Sydney' ),
		'url'         => $page_url,
		'description' => $service_descriptions[ $page_key ],
	);

	$breadcrumb_items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => 'Home',
			'item'     => $site_url,
		),
		array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => 'Enterprise Solutions',
			'item'     => home_url( '/enterprise-solutions/' ),
		),
	);

	if ( 'safeguard-solutions' === $page_key ) {
		$breadcrumb_items[] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => 'Safeguard Solutions',
			'item'     => $page_url,
		);
	}

	$schema[] = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $breadcrumb_items,
	);

	$faq_entities = array();
	foreach ( site_blocks_enterprise_faq_items( $page_key ) as $item ) {
		$faq_entities[] = array(
			'@type'          => 'Question',
			'name'           => $item['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $item['a'],
			),
		);
	}

	if ( $faq_entities !== array() ) {
		$schema[] = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $faq_entities,
		);
	}

	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	echo '</script>' . "\n";
}
add_action( 'wp_head', 'site_blocks_enterprise_schema', 5 );

/**
 * Seed article definitions from the research pack.
 *
 * @return array<int, array{slug: string, title: string, excerpt: string, category: string, content: string}>
 */
function site_blocks_enterprise_insight_seed_articles(): array {
	$hub = home_url( '/enterprise-solutions/' );
	$cta = '<p><strong><a href="' . esc_url( home_url( '/contact/' ) ) . '">Book a site assessment with our team</a></strong> or call 1300 225 276.</p>';

	return array(
		array(
			'slug'     => 'ai-video-analytics-commercial-security',
			'title'    => 'AI Video Analytics for Commercial Security: A Practical Guide',
			'excerpt'  => 'How AI video analytics helps Sydney businesses cut investigation time, catch threats in real time and get more from existing cameras. Practical, no hype.',
			'category' => 'video-analytics',
			'content'  => '<p>Most commercial cameras are still used the old way: something happens, and someone scrubs through hours of footage hoping to find it. AI video analytics changes that. As part of our <a href="' . esc_url( $hub ) . '">Enterprise Solutions</a> approach, Safeguard uses analytics to turn passive recording into an active security tool: one that flags what matters as it happens and finds what you need in seconds.</p>
<h2>What &ldquo;AI video analytics&rdquo; really means</h2>
<p>Strip away the marketing and analytics is software that understands what is in your video. Instead of a camera that only records, you get a system that can recognise a person, a vehicle, a licence plate or an unusual movement, and act on it.</p>
<h2>Faster investigations with AI video search</h2>
<p>The biggest day-to-day win is search. Investigations that used to take an afternoon take minutes. For businesses running <a href="' . esc_url( home_url( '/cctv-security-cameras/' ) ) . '">CCTV across several sites</a>, that time saving compounds every week.</p>
<h2>Real-time detections and alerts</h2>
<p>Analytics also watches for you. Paired with our <a href="' . esc_url( home_url( '/monitoring/' ) ) . '">24/7 monitoring</a>, a verified detection becomes a dispatch decision in real time, not a next-morning discovery.</p>
<h2>The Safeguard approach</h2>
<p>Because we design, install and monitor systems in-house, analytics is built into how the whole solution is scoped, all visible through the <a href="' . esc_url( home_url( '/safeguard-solutions/' ) ) . '">Safeguard Solutions</a> platform.</p>' . $cta,
		),
		array(
			'slug'     => 'cloud-cctv-multi-site-business',
			'title'    => 'Cloud CCTV for Multi-Site Businesses: Why One Login Changes Everything',
			'excerpt'  => 'Managing CCTV across several sites? Cloud CCTV gives Sydney businesses one login, offsite storage and less hardware to babysit.',
			'category' => 'multi-site-operations',
			'content'  => '<p>If your business runs more than one location, traditional CCTV has a hidden tax: every site is its own island. Cloud CCTV removes that friction, and it has become the default we recommend for multi-site commercial clients through our <a href="' . esc_url( $hub ) . '">Enterprise Solutions</a> silo.</p>
<h2>One login for every site</h2>
<p>Instead of logging into five recorders for five branches, you open one console and see them all. It is the same principle behind our broader <a href="' . esc_url( home_url( '/safeguard-solutions/' ) ) . '">Safeguard Solutions</a> platform.</p>
<h2>Better together: monitoring and response</h2>
<p>Cloud CCTV is strongest when it feeds a response. Connected to our <a href="' . esc_url( home_url( '/monitoring/' ) ) . '">24/7 monitoring</a>, verified events can be actioned in real time.</p>
<h2>What to check before you switch</h2>
<p>Compatibility, bandwidth and access control should be assessed up front. Often much of your current <a href="' . esc_url( home_url( '/cctv-security-cameras/' ) ) . '">camera hardware</a> can be retained.</p>' . $cta,
		),
		array(
			'slug'     => 'smart-sensors-real-time-alerts-facilities',
			'title'    => 'Smart Sensors and Real-Time Alerts: Seeing What Cameras Cannot',
			'excerpt'  => 'Cameras cannot see everything. Smart sensors give Sydney facilities real-time alerts on access, environment and occupancy.',
			'category' => 'monitoring-response',
			'content'  => '<p>Cameras are the backbone of commercial security, but they have blind spots. Smart sensors fill those gaps, and paired with real-time alerts they turn a facility from reactive to proactive. As part of our <a href="' . esc_url( $hub ) . '">Enterprise Solutions</a> approach, Safeguard uses sensors to extend protection into the places a lens cannot reach.</p>
<h2>Environmental monitoring that protects assets</h2>
<p>Not every threat is a person. Environmental sensors catch temperature, humidity and air-quality issues early, before a nuisance becomes a loss.</p>
<h2>Access and safety alerts in real time</h2>
<p>Tied into <a href="' . esc_url( home_url( '/access-control/' ) ) . '">access control</a>, sensor events become part of a single, auditable security picture.</p>
<h2>Turning alerts into response</h2>
<p>We connect sensor events to our <a href="' . esc_url( home_url( '/monitoring/' ) ) . '">24/7 monitoring</a> so verified alerts are assessed and escalated without delay.</p>
<h2>One platform, not another silo</h2>
<p>Sensor data surfaces in the same Safeguard Solutions console as your video and access events: one view, one login, across every site.</p>' . $cta,
		),
	);
}

/**
 * Seed Enterprise Insight posts once. Never overwrite editor changes on upgrade.
 */
function site_blocks_seed_enterprise_insight_posts(): void {
	if ( ! post_type_exists( 'enterprise_insight' ) ) {
		return;
	}

	site_blocks_seed_enterprise_insight_categories();

	foreach ( site_blocks_enterprise_insight_seed_articles() as $article ) {
		$existing_posts = get_posts(
			array(
				'name'           => $article['slug'],
				'post_type'      => 'enterprise_insight',
				'post_status'    => 'any',
				'posts_per_page' => 1,
			)
		);

		// Skip existing slugs - bosses must be able to edit without deploy overwriting.
		if ( ! empty( $existing_posts ) ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $article['title'],
				'post_name'    => $article['slug'],
				'post_excerpt' => $article['excerpt'],
				'post_content' => $article['content'],
				'post_status'  => 'publish',
				'post_type'    => 'enterprise_insight',
				'post_author'  => 1,
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		$post_id = (int) $post_id;
		update_post_meta( $post_id, '_kad_post_layout', 'fullwidth' );
		update_post_meta( $post_id, '_kad_post_content_style', 'unboxed' );
		update_post_meta( $post_id, '_kad_post_title', 'hide' );
		update_post_meta( $post_id, '_kad_post_header', 'disable' );
		update_post_meta( $post_id, '_kad_post_footer', 'disable' );

		if ( taxonomy_exists( 'enterprise_insight_category' ) && ! empty( $article['category'] ) ) {
			wp_set_object_terms( $post_id, array( $article['category'] ), 'enterprise_insight_category', false );
		}
	}
}

/**
 * Return pattern markup for the Enterprise hub page.
 */
function site_blocks_get_enterprise_hub_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/enterprise-hub-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Return pattern markup for the Safeguard Solutions child page.
 */
function site_blocks_get_safeguard_solutions_page_content(): string {
	$pattern_file = SITE_BLOCKS_DIR . 'patterns/safeguard-solutions-page.php';

	if ( ! file_exists( $pattern_file ) ) {
		return '';
	}

	$pattern = include $pattern_file;

	return is_array( $pattern ) && isset( $pattern['content'] ) ? $pattern['content'] : '';
}

/**
 * Create or update Enterprise hub + Safeguard Solutions child pages.
 */
function site_blocks_create_enterprise_pages(): void {
	$hub_content = site_blocks_get_enterprise_hub_page_content();

	if ( '' === $hub_content ) {
		return;
	}

	$existing_hub = get_page_by_path( 'enterprise-solutions' );

	$hub_data = array(
		'post_title'   => __( 'Enterprise Solutions', 'site-blocks' ),
		'post_name'    => 'enterprise-solutions',
		'post_content' => $hub_content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
		'post_parent'  => 0,
	);

	if ( $existing_hub instanceof WP_Post ) {
		$hub_data['ID'] = $existing_hub->ID;
		$hub_id         = wp_update_post( $hub_data, true );
	} else {
		$hub_id = wp_insert_post( $hub_data, true );
	}

	if ( is_wp_error( $hub_id ) || ! $hub_id ) {
		return;
	}

	$hub_id = (int) $hub_id;
	site_blocks_apply_safeguard_page_meta( $hub_id );

	$child_content = site_blocks_get_safeguard_solutions_page_content();
	if ( '' === $child_content ) {
		return;
	}

	$path     = 'safeguard-solutions';
	$existing = get_page_by_path( $path );

	$child_data = array(
		'post_title'   => __( 'Safeguard Solutions', 'site-blocks' ),
		'post_name'    => 'safeguard-solutions',
		'post_content' => $child_content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
		'post_parent'  => 0,
	);

	if ( $existing instanceof WP_Post ) {
		$child_data['ID'] = $existing->ID;
		$child_id         = wp_update_post( $child_data, true );
	} else {
		$child_id = wp_insert_post( $child_data, true );
	}

	if ( is_wp_error( $child_id ) || ! $child_id ) {
		return;
	}

	site_blocks_apply_safeguard_page_meta( (int) $child_id );
}
