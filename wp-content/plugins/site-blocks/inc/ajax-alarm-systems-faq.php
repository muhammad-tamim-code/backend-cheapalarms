<?php
/**
 * Ajax Alarm Systems FAQ content.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ items for the Ajax alarm systems landing page.
 *
 * @return array<int, array{q: string, a: string}>
 */
function site_blocks_get_ajax_alarm_faq_items(): array {
	return array(
		array(
			'q' => __( 'Is Ajax a good alarm system for Australian homes?', 'site-blocks' ),
			'a' => __( 'Yes. Ajax is designed with strong security, encrypted wireless communication and reliable monitoring options, making it well-suited to Australian homes, apartments and small businesses when installed and configured correctly.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Does Ajax need professional installation?', 'site-blocks' ),
			'a' => __( 'Yes. Ajax is a professional-grade system. We design the device layout for your property, install the hardware, configure the app and test alerts before handover.', 'site-blocks' ),
		),
		array(
			'q' => __( 'How much does an Ajax alarm system cost?', 'site-blocks' ),
			'a' => __( 'Cost depends on your property size, device count and monitoring choice. Use our free instant online quote for a tailored estimate reviewed by a real technician.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can Ajax be professionally monitored?', 'site-blocks' ),
			'a' => __( 'Yes. Ajax systems can connect to professional monitoring using IP and cellular paths when configured by an accredited installer.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can Ajax work with an old wired alarm?', 'site-blocks' ),
			'a' => __( 'Often, yes. Ajax supports wired zones and hybrid expansion, so existing cabling can sometimes be reused. We assess this during your quote.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Does Ajax work if the internet goes down?', 'site-blocks' ),
			'a' => __( 'Ajax hubs support Ethernet, Wi-Fi and cellular communication. With cellular backup configured, alerts can still reach you and monitoring if broadband fails.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Is Ajax suitable for pets?', 'site-blocks' ),
			'a' => __( 'Yes. Ajax offers pet-friendly motion detectors. We specify the right models and placement for your layout and pets during system design.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Can Ajax connect with CCTV or intercom systems?', 'site-blocks' ),
			'a' => __( 'Yes. Ajax can integrate with CCTV and intercom ecosystems. We can include add-on pathways when we design your complete security solution.', 'site-blocks' ),
		),
		array(
			'q' => __( 'Is Safeguard the manufacturer of Ajax?', 'site-blocks' ),
			'a' => __( 'No. Ajax Systems manufactures Ajax hardware. Safeguard is an independent Australian installer, we design, supply, install and support Ajax systems for your property.', 'site-blocks' ),
		),
		array(
			'q' => __( 'How do I get an Ajax alarm quote?', 'site-blocks' ),
			'a' => __( 'Start our free Ajax calculator online or call 1300 225 276. You will receive a tailored recommendation and can track progress in your secure customer portal.', 'site-blocks' ),
		),
	);
}
