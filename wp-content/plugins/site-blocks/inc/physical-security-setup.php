<?php
/**
 * Physical Security silo, assets, body class, and SEO meta.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/physical-security-config.php';

/**
 * Page title for Physical Security silo pages.
 */
function site_blocks_physical_security_document_title_filter( string $title ): string {
	$page_key = site_blocks_get_physical_security_page_key();

	if ( null === $page_key ) {
		return $title;
	}

	$custom = site_blocks_physical_security_document_title( $page_key );

	return '' !== $custom ? $custom : $title;
}
add_filter( 'pre_get_document_title', 'site_blocks_physical_security_document_title_filter', 20 );

/**
 * Meta description in head.
 */
function site_blocks_physical_security_output_meta_description(): void {
	$page_key = site_blocks_get_physical_security_page_key();

	if ( null === $page_key ) {
		return;
	}

	$description = site_blocks_physical_security_get_meta_description( $page_key );

	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'site_blocks_physical_security_output_meta_description', 2 );

/**
 * JSON-LD structured data per Physical Security page.
 */
function site_blocks_physical_security_schema(): void {
	$page_key = site_blocks_get_physical_security_page_key();

	if ( null === $page_key ) {
		return;
	}

	$site_url = home_url( '/' );
	$slugs    = site_blocks_physical_security_page_slugs();
	$page_url = home_url( '/' . $slugs[ $page_key ] . '/' );
	$phone    = '1300225276';

	$service_descriptions = array(
		'hub'            => 'Licensed static guards, mobile patrols, alarm response, event and retail security across Sydney, integrated with CCTV, alarms and monitoring.',
		'static-guards'  => 'Licensed static security guards providing a constant on-site presence for retail, offices, warehouses, strata, construction and events across Sydney, integrated with CCTV, alarms and monitoring.',
		'mobile-patrols' => 'GPS-tracked mobile security patrols across Sydney with scheduled and random checks, lock-ups, alarm response and welfare checks, plus digital reporting, integrated with CCTV and alarms.',
	);

	$service_types = array(
		'hub'            => 'Security guard and patrol services',
		'static-guards'  => 'Static security guard services',
		'mobile-patrols' => 'Mobile patrol security services',
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
			'priceRange'         => '$$',
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
			'name'     => 'Physical Security',
			'item'     => home_url( '/physical-security/' ),
		),
	);

	if ( 'hub' !== $page_key ) {
		$child_names = array(
			'static-guards'  => 'Static Security Guards',
			'mobile-patrols' => 'Mobile Patrols',
		);
		$breadcrumb_items[] = array(
			'@type'    => 'ListItem',
			'position' => 3,
			'name'     => $child_names[ $page_key ] ?? '',
			'item'     => $page_url,
		);
	}

	$schema[] = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $breadcrumb_items,
	);

	$faq_entities = array();
	foreach ( site_blocks_physical_security_faq_items( $page_key ) as $item ) {
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
add_action( 'wp_head', 'site_blocks_physical_security_schema', 5 );
