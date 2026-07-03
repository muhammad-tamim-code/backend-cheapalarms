<?php
/**
 * Access Control category page — assets, body class, and SEO meta.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';

/**
 * Whether the Access Control category page is being viewed.
 */
function site_blocks_is_access_control_page(): bool {
	return is_page( 'access-control' );
}

/**
 * Enqueue Access Control page styles (reuses alarm + CCTV design system).
 */
function site_blocks_enqueue_access_control_assets(): void {
	if ( ! site_blocks_is_access_control_page() ) {
		return;
	}

	wp_enqueue_style(
		'safeguard-access-control-fonts',
		'https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'safeguard-home',
		SITE_BLOCKS_URL . 'assets/css/safeguard-home.css',
		array( 'safeguard-access-control-fonts' ),
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
		'safeguard-access-control',
		SITE_BLOCKS_URL . 'assets/css/access-control.css',
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
add_action( 'wp_enqueue_scripts', 'site_blocks_enqueue_access_control_assets', 30 );

/**
 * @param string[] $classes Body classes.
 * @return string[]
 */
function site_blocks_access_control_body_class( array $classes ): array {
	if ( site_blocks_is_access_control_page() ) {
		$classes[] = 'safeguard-access-control-page';
		$classes[] = 'safeguard-cctv-page';
		$classes[] = 'safeguard-alarm-page';
		$classes[] = 'safeguard-homepage';
	}
	return $classes;
}
add_filter( 'body_class', 'site_blocks_access_control_body_class' );

/**
 * Page title for Access Control.
 */
function site_blocks_access_control_document_title( string $title ): string {
	if ( ! site_blocks_is_access_control_page() ) {
		return $title;
	}

	return __( 'Access Control Systems Sydney | Installation | Safeguard', 'site-blocks' );
}
add_filter( 'pre_get_document_title', 'site_blocks_access_control_document_title', 20 );

/**
 * Meta description in head.
 */
function site_blocks_access_control_meta_description(): void {
	if ( ! site_blocks_is_access_control_page() ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr__(
			'Replace keys with credentials you control. Safeguard designs and installs access control systems across Sydney — cards, mobile, PIN and biometric entry.',
			'site-blocks'
		)
	);
}
add_action( 'wp_head', 'site_blocks_access_control_meta_description', 2 );

/**
 * JSON-LD structured data for the access control category page.
 */
function site_blocks_access_control_schema(): void {
	if ( ! site_blocks_is_access_control_page() ) {
		return;
	}

	$site_url = home_url( '/' );
	$page_url = home_url( '/access-control/' );
	$hub_url  = home_url( '/electronic-security/' );
	$phone    = '1300225276';

	$schema = array(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'LocalBusiness',
			'@id'        => $site_url . '#business',
			'name'       => 'Safeguard Security Services',
			'url'        => $page_url,
			'telephone'  => $phone,
			'priceRange' => '$$',
			'address'    => array(
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
			'serviceType' => 'Access control system installation',
			'provider'    => array( '@id' => $site_url . '#business' ),
			'areaServed'  => array( '@type' => 'City', 'name' => 'Sydney' ),
			'url'         => $page_url,
			'description' => 'Design and installation of access control systems for commercial and residential properties across Sydney.',
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
					'name'     => 'Electronic Security',
					'item'     => $hub_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => 'Access Control',
					'item'     => $page_url,
				),
			),
		),
	);

	require_once SITE_BLOCKS_DIR . 'inc/access-control-faq.php';
	$faq_entities = array();
	foreach ( site_blocks_get_access_control_faq_items() as $item ) {
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
add_action( 'wp_head', 'site_blocks_access_control_schema', 5 );
