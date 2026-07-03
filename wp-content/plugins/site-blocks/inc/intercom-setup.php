<?php
/**
 * Intercom category page — assets, body class, and SEO meta.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';

/**
 * Whether the Intercom Systems category page is being viewed.
 */
function site_blocks_is_intercom_page(): bool {
	return is_page( 'intercom-systems' );
}

/**
 * Enqueue Intercom page styles (reuses alarm + CCTV design system).
 */
function site_blocks_enqueue_intercom_assets(): void {
	if ( ! site_blocks_is_intercom_page() ) {
		return;
	}

	wp_enqueue_style(
		'safeguard-intercom-fonts',
		'https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/css/safeguard-home.css',
		array( 'safeguard-intercom-fonts' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_style(
		'safeguard-alarm-systems',
		SITE_BLOCKS_URL . 'assets/css/alarm-systems.css',
		array( 'safeguard-home' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_style(
		'safeguard-cctv',
		SITE_BLOCKS_URL . 'assets/css/cctv.css',
		array( 'safeguard-alarm-systems' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_style(
		'safeguard-intercom',
		SITE_BLOCKS_URL . 'assets/css/intercom.css',
		array( 'safeguard-cctv' ),
		SITE_BLOCKS_VERSION
	);

	wp_enqueue_script(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/js/safeguard-home.js',
		array(),
		SITE_BLOCKS_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_intercom_assets', 30 );

/**
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_intercom_body_class( array $classes ): array {
	if ( site_blocks_is_intercom_page() ) {
		$classes[] = 'safeguard-intercom-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}
	return $classes;
}
add_filter( 'body_class', 'site_blocks_intercom_body_class' );

/**
 * Page title and meta description for Intercom Systems.
 */
function site_blocks_intercom_document_title( string $title ): string {
	if ( ! site_blocks_is_intercom_page() ) {
		return $title;
	}

	return __( 'Intercom Installation Sydney | Video Intercom Systems | Safeguard', 'site-blocks' );
}
add_filter( 'pre_get_document_title', 'site_blocks_intercom_document_title', 20 );

/**
 * Meta description in head.
 */
function site_blocks_intercom_meta_description(): void {
	if ( ! site_blocks_is_intercom_page() ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr__(
			'See and speak to visitors before you open the door. Safeguard designs and installs video and audio intercom systems across Sydney. Start your quote online.',
			'site-blocks'
		)
	);
}
add_action( 'wp_head', 'site_blocks_intercom_meta_description', 2 );

/**
 * JSON-LD structured data for the intercom category page.
 */
function site_blocks_intercom_schema(): void {
	if ( ! site_blocks_is_intercom_page() ) {
		return;
	}

	$site_url = home_url( '/' );
	$page_url = home_url( '/intercom-systems/' );
	$phone    = '1300225276';

	$schema = array(
		array(
			'@context' => 'https://schema.org',
			'@type'    => 'LocalBusiness',
			'@id'      => $site_url . '#business',
			'name'     => 'Safeguard Security Services',
			'url'      => $page_url,
			'telephone'=> $phone,
			'priceRange' => '$$',
			'address'  => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '2/2 Stennett Road',
				'addressLocality' => 'Ingleburn',
				'addressRegion'   => 'NSW',
				'postalCode'      => '2565',
				'addressCountry'  => 'AU',
			),
			'areaServed' => array(
				'@type' => 'City',
				'name'  => 'Sydney',
			),
		),
		array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Service',
			'serviceType' => 'Intercom system installation',
			'provider'    => array( '@id' => $site_url . '#business' ),
			'areaServed'  => array( '@type' => 'City', 'name' => 'Sydney' ),
			'url'         => $page_url,
			'description' => 'Design and installation of video and audio intercom systems for homes, apartments and businesses across Sydney.',
		),
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Home',
					'item'     => $site_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => 'Intercom Systems',
					'item'     => $page_url,
				),
			),
		),
	);

	require_once SITE_BLOCKS_DIR . 'inc/intercom-faq.php';
	$faq_entities = array();
	foreach ( site_blocks_get_intercom_faq_items() as $item ) {
		$faq_entities[] = array(
			'@type'          => 'Question',
			'name'           => $item['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $item['a'],
			),
		);
	}

	$schema[] = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $faq_entities,
	);

	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	echo '</script>' . "\n";
}
add_action( 'wp_head', 'site_blocks_intercom_schema', 5 );
