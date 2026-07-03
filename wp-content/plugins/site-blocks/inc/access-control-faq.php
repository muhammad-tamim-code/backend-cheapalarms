<?php
/**
 * Access Control category page FAQ content.
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
function site_blocks_get_access_control_faq_items(): array {
	return array(
		array(
			'q' => __( 'What is an access control system?', 'site-blocks' ),
			'a' => __( 'Access control replaces traditional keys with credentials you can issue, schedule and revoke — cards, fobs, PINs, mobile passes or biometrics — tied to specific doors, gates and times.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can I manage doors and users remotely?', 'site-blocks' ),
			'a' => __( 'Yes. Cloud-managed systems let you add or remove users, change schedules and review entry events from a browser or app — ideal for multi-site and after-hours changes.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Do you install access control for commercial buildings?', 'site-blocks' ),
			'a' => __( 'Yes. We design and install for offices, retail, warehouses, schools, strata and healthcare — from a single door to multi-reader sites with intercom and CCTV integration.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can access control work with our intercom and CCTV?', 'site-blocks' ),
			'a' => __( 'Absolutely. We plan access control as part of a layered system — video verification at the door, alarm events, and audit trails that line up across platforms.', 'site-blocks' ),
		),
		array(
			'q' => __( 'What credentials can we use?', 'site-blocks' ),
			'a' => __( 'Depending on your site: cards and fobs, PIN codes, smartphone credentials, biometric readers, and intercom-based visitor entry — often mixed on the same system.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Is the online estimate the final price?', 'site-blocks' ),
			'a' => __( 'It\'s a tailored estimate. For most jobs it\'s reviewed by our technicians before approval, so you can proceed with confidence and no surprises on install day.', 'site-blocks' ),
		),
	);
}
