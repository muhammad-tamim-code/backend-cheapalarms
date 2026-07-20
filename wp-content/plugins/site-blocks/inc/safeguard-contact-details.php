<?php
/**
 * Safeguard business contact details (contact page + footer).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Safeguard contact details.
 *
 * @return array<string, string>
 */
function site_blocks_get_safeguard_contact_details(): array {
	return array(
		'organizationName' => __( 'Safeguard Security Services', 'site-blocks' ),
		'streetAddress'    => '2/2 Stennett Road',
		'city'             => 'Ingleburn',
		'region'           => 'NSW',
		'postalCode'       => '2565',
		'country'          => __( 'Australia', 'site-blocks' ),
		'phone'            => '1300 225 276',
		'email'            => 'sales@safeguardsecurity.com.au',
		'hoursNote'        => __( 'Mon–Fri 8 AM – 6 PM · Sat–Sun closed', 'site-blocks' ),
		'responseNote'     => __( 'We aim to reply within one business day.', 'site-blocks' ),
		'mapsEmbed'        => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3312.619235886837!2d150.85348587031246!3d-33.9994517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b12bea03e504021%3A0x2840053cf7bd776d!2sSafeguard%20Security%20Services!5e0!3m2!1sen!2sau!4v1718025600000!5m2!1sen!2sau',
		'mapsLink'         => 'https://maps.google.com/?q=Safeguard+Security+Services+Ingleburn+NSW',
	);
}

/**
 * Merge block attributes with Safeguard defaults.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return array<string, string>
 */
function site_blocks_merge_contact_info_attributes( array $attributes ): array {
	$defaults = site_blocks_get_safeguard_contact_details();
	$merged   = array();

	foreach ( $defaults as $key => $default ) {
		$value = $attributes[ $key ] ?? '';
		$merged[ $key ] = is_string( $value ) && '' !== trim( $value ) ? trim( $value ) : $default;
	}

	$merged['showMap'] = ! isset( $attributes['showMap'] ) || (bool) $attributes['showMap'];

	return $merged;
}
