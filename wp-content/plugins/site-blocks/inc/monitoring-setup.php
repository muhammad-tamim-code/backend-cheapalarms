<?php
/**
 * Monitoring silo, assets, body class, and SEO meta.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/monitoring-config.php';

/**
 * Page title for Monitoring silo pages.
 */
function site_blocks_monitoring_document_title_filter( string $title ): string {
	$page_key = site_blocks_get_monitoring_page_key();

	if ( null === $page_key ) {
		return $title;
	}

	$custom = site_blocks_monitoring_document_title( $page_key );

	return '' !== $custom ? $custom : $title;
}
add_filter( 'pre_get_document_title', 'site_blocks_monitoring_document_title_filter', 20 );

/**
 * Meta description in head.
 */
function site_blocks_monitoring_output_meta_description(): void {
	$page_key = site_blocks_get_monitoring_page_key();

	if ( null === $page_key ) {
		return;
	}

	$description = site_blocks_monitoring_get_meta_description( $page_key );

	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'site_blocks_monitoring_output_meta_description', 2 );

/**
 * JSON-LD structured data per Monitoring page.
 */
function site_blocks_monitoring_schema(): void {
	$page_key = site_blocks_get_monitoring_page_key();

	if ( null === $page_key ) {
		return;
	}

	$site_url = home_url( '/' );
	$slugs    = site_blocks_monitoring_page_slugs();
	$page_url = home_url( '/' . $slugs[ $page_key ] . '/' );
	$phone    = '1300225276';

	$service_descriptions = array(
		'hub'                      => 'Professional alarm monitoring across Greater Sydney, back-to-base, virtual patrol and solar CCTV monitoring, integrated with install and physical response.',
		'back-to-base'             => 'Back-to-base alarm monitoring in Sydney with IP and 4G paths, clear response plans and professional monitoring centre cover.',
		'virtual-patrol'           => 'Virtual patrol and remote CCTV guarding in Sydney with live operator tours, alarm verification and escalation to physical response.',
		'solar-cameras-monitoring' => 'Solar-powered security cameras with 4G connectivity and professional monitoring for construction, rural and remote NSW sites.',
	);

	$service_types = array(
		'hub'                      => 'Alarm monitoring services',
		'back-to-base'             => 'Back-to-base alarm monitoring',
		'virtual-patrol'           => 'Virtual patrol and remote guarding',
		'solar-cameras-monitoring' => 'Solar camera monitoring',
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
			'name'     => 'Monitoring & Response',
			'item'     => home_url( '/monitoring/' ),
		),
	);

	if ( 'hub' !== $page_key ) {
		$child_names = array(
			'back-to-base'             => 'Back-to-Base Monitoring',
			'virtual-patrol'           => 'Virtual Patrol',
			'solar-cameras-monitoring' => 'Solar Cameras with Monitoring',
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
	foreach ( site_blocks_monitoring_faq_items( $page_key ) as $item ) {
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
add_action( 'wp_head', 'site_blocks_monitoring_schema', 5 );
