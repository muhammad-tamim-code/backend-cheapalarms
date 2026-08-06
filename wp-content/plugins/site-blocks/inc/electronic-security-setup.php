<?php
/**
 * Electronic Security silo, assets, body class, and SEO meta.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/electronic-security-config.php';

/**
 * Page title for Electronic Security silo pages.
 */
function site_blocks_electronic_security_document_title_filter( string $title ): string {
	$page_key = site_blocks_get_electronic_security_page_key();

	if ( null === $page_key ) {
		return $title;
	}

	$custom = site_blocks_electronic_security_document_title( $page_key );

	return '' !== $custom ? $custom : $title;
}
add_filter( 'pre_get_document_title', 'site_blocks_electronic_security_document_title_filter', 20 );

/**
 * Meta description in head.
 */
function site_blocks_electronic_security_output_meta_description(): void {
	$page_key = site_blocks_get_electronic_security_page_key();

	if ( null === $page_key ) {
		return;
	}

	$description = site_blocks_electronic_security_get_meta_description( $page_key );

	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'site_blocks_electronic_security_output_meta_description', 2 );

/**
 * JSON-LD structured data per Electronic Security page.
 */
function site_blocks_electronic_security_schema(): void {
	$page_key = site_blocks_get_electronic_security_page_key();

	if ( null === $page_key ) {
		return;
	}

	$site_url = home_url( '/' );
	$slugs    = site_blocks_electronic_security_page_slugs();
	$page_url = home_url( '/' . $slugs[ $page_key ] . '/' );
	$phone    = '1300225276';

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
		'serviceType' => 'Electronic security systems',
		'provider'    => array( '@id' => $site_url . '#business' ),
		'areaServed'  => array( '@type' => 'City', 'name' => 'Sydney' ),
		'url'         => $page_url,
		'description' => 'Alarms, CCTV, access control, intercoms and monitoring for Sydney homes and businesses, designed, installed and supported by Safeguard.',
	);

	$schema[] = array(
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
				'item'     => $page_url,
			),
		),
	);

	$faq_entities = array();
	foreach ( site_blocks_electronic_security_faq_items( $page_key ) as $item ) {
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
add_action( 'wp_head', 'site_blocks_electronic_security_schema', 5 );
