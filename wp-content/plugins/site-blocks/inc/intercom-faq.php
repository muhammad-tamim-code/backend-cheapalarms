<?php
/**
 * Intercom category page FAQ content.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int, array{q: string, a: string}>
 */
function site_blocks_get_intercom_faq_items(): array {
	return array(
		array(
			'q' => __( 'Can I answer the door from my phone when I\'m out?', 'site-blocks' ),
			'a' => __( 'On supported systems, yes. A call at the door or gate reaches your smartphone, so you can see the visitor, speak to them and release the entry from anywhere with an internet connection.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Will it still work if the internet goes down?', 'site-blocks' ),
			'a' => __( 'Yes. Your door station and indoor monitor keep talking to each other locally without internet. Only remote answering on your phone needs a connection, the indoor monitor takes over in the meantime.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can I reuse my existing intercom wiring?', 'site-blocks' ),
			'a' => __( 'Often, especially in apartments and older homes with 2-wire cabling. We assess your existing wiring at the site visit and advise whether it can be reused or should be replaced.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Do you install intercoms for apartments and strata buildings?', 'site-blocks' ),
			'a' => __( 'Yes. We design directory panels, per-unit call routing, building-manager access and integration with access control and lifts, planned around fire-egress and strata requirements.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can I let a tradesperson in without being home?', 'site-blocks' ),
			'a' => __( 'Yes. App-based systems let you release the door remotely, and many support temporary access codes you can issue and revoke, ideal for cleaners, couriers and contractors.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Is the online estimate the final price?', 'site-blocks' ),
			'a' => __( 'It\'s a tailored estimate. For most jobs it\'s reviewed by our technicians before approval, so you can proceed with confidence and no surprises on the day.', 'site-blocks' ),
		),
	);
}
