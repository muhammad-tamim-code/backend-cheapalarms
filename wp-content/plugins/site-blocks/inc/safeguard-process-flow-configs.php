<?php
/**
 * Per-page skeleton process flow configs.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return process flow config by key.
 *
 * @return array{
 *   heading_id?: string,
 *   section_class?: string,
 *   eyebrow?: string,
 *   title_before: string,
 *   title_accent: string,
 *   title_after?: string,
 *   steps: array<int, array{label: string, title: string, badge: string, skeleton: string}>
 * }|null
 */
function site_blocks_process_flow_config( string $key ): ?array {
	$configs = site_blocks_process_flow_configs();

	return $configs[ $key ] ?? null;
}

/**
 * All registered process flow configs.
 *
 * @return array<string, array<string, mixed>>
 */
function site_blocks_process_flow_configs(): array {
	return array(
		'monitoring-virtual-patrol' => array(
			'heading_id'    => 'sg-monitoring-vp-flow-heading',
			'section_class' => 'sg-monitoring-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Remote patrol coverage in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from cameras to confirmed activity.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Schedule agreed', 'site-blocks' ),
					'badge'    => __( 'Your site profile', 'site-blocks' ),
					'skeleton' => 'schedule',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Live operator tours', 'site-blocks' ),
					'badge'    => __( 'Live camera review', 'site-blocks' ),
					'skeleton' => 'tour',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Incidents flagged', 'site-blocks' ),
					'badge'    => __( 'Keyholders notified', 'site-blocks' ),
					'skeleton' => 'flag',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Report delivered', 'site-blocks' ),
					'badge'    => __( 'Tour logs ready', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'monitoring-back-to-base' => array(
			'heading_id'    => 'sg-monitoring-b2b-flow-heading',
			'section_class' => 'sg-monitoring-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Back-to-base monitoring in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from alarm to agreed response.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Alarm activates', 'site-blocks' ),
					'badge'    => __( 'Sensor triggered', 'site-blocks' ),
					'skeleton' => 'signal',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Signal sent', 'site-blocks' ),
					'badge'    => __( 'IP or 4G path', 'site-blocks' ),
					'skeleton' => 'tour',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Operator assesses', 'site-blocks' ),
					'badge'    => __( 'Site profile checked', 'site-blocks' ),
					'skeleton' => 'flag',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Response executed', 'site-blocks' ),
					'badge'    => __( 'Logged and reported', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'monitoring-solar-cameras-monitoring' => array(
			'heading_id'    => 'sg-monitoring-solar-flow-heading',
			'section_class' => 'sg-monitoring-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Solar site monitoring in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from install to live cover.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Site assessment', 'site-blocks' ),
					'badge'    => __( 'Coverage mapped', 'site-blocks' ),
					'skeleton' => 'quote',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Solar install', 'site-blocks' ),
					'badge'    => __( '4G commissioned', 'site-blocks' ),
					'skeleton' => 'install',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Monitoring linked', 'site-blocks' ),
					'badge'    => __( 'Alerts configured', 'site-blocks' ),
					'skeleton' => 'flag',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Response ready', 'site-blocks' ),
					'badge'    => __( 'Plan agreed', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'monitoring-hub-how-it-works' => array(
			'heading_id'    => 'sg-monitoring-how-flow-heading',
			'section_class' => 'sg-monitoring-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'From alert to ', 'site-blocks' ),
			'title_accent'  => __( 'action', 'site-blocks' ),
			'title_after'   => __( ' in four steps', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Signal', 'site-blocks' ),
					'badge'    => __( 'Event detected', 'site-blocks' ),
					'skeleton' => 'signal',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Receive', 'site-blocks' ),
					'badge'    => __( 'Centre alerted', 'site-blocks' ),
					'skeleton' => 'tour',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Verify', 'site-blocks' ),
					'badge'    => __( 'Cause confirmed', 'site-blocks' ),
					'skeleton' => 'flag',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Respond', 'site-blocks' ),
					'badge'    => __( 'Plan executed', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'monitoring-hub-process' => array(
			'heading_id'    => 'sg-monitoring-process-flow-heading',
			'section_class' => 'sg-monitoring-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'From risk to ', 'site-blocks' ),
			'title_accent'  => __( 'response', 'site-blocks' ),
			'title_after'   => __( ' in four steps', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Site assessment', 'site-blocks' ),
					'badge'    => __( 'Risk reviewed', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Tailored plan', 'site-blocks' ),
					'badge'    => __( 'Cover matched', 'site-blocks' ),
					'skeleton' => 'schedule',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Connection', 'site-blocks' ),
					'badge'    => __( 'Tested and live', 'site-blocks' ),
					'skeleton' => 'install',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Monitoring', 'site-blocks' ),
					'badge'    => __( '24/7 coverage', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'enterprise-hub-process' => array(
			'heading_id'    => 'sg-enterprise-process-flow-heading',
			'section_class' => 'sg-enterprise-process-flow',
			'eyebrow'       => __( 'How we deliver', 'site-blocks' ),
			'title_before'  => __( 'From assessment to ', 'site-blocks' ),
			'title_accent'  => __( 'support', 'site-blocks' ),
			'title_after'   => __( ' in four steps', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Assess', 'site-blocks' ),
					'badge'    => __( 'Risks mapped', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Design', 'site-blocks' ),
					'badge'    => __( 'Scope agreed', 'site-blocks' ),
					'skeleton' => 'schedule',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Deploy', 'site-blocks' ),
					'badge'    => __( 'Systems live', 'site-blocks' ),
					'skeleton' => 'install',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Support', 'site-blocks' ),
					'badge'    => __( 'One accountable team', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'homepage' => array(
			'heading_id'    => 'sg-home-process-flow-heading',
			'section_class' => 'sg-home-process-flow sg-reveal',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'A ', 'site-blocks' ),
			'title_accent'  => __( 'smarter', 'site-blocks' ),
			'title_after'   => __( ' way to plan your security system', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Tell us what you need', 'site-blocks' ),
					'badge'    => __( 'Property and goals', 'site-blocks' ),
					'skeleton' => 'quote',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Share your site details and photos', 'site-blocks' ),
					'badge'    => __( 'Photos and access', 'site-blocks' ),
					'skeleton' => 'photos',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Receive a tailored estimate', 'site-blocks' ),
					'badge'    => __( 'Clear package', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Expert review before you approve', 'site-blocks' ),
					'badge'    => __( 'Technician checked', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'alarm-systems' => array(
			'heading_id'    => 'sg-alarm-steps-heading',
			'section_class' => 'sg-alarm-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Simple steps, done ', 'site-blocks' ),
			'title_accent'  => __( 'properly', 'site-blocks' ),
			'title_after'   => __( ' from quote to install.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Tell us what you need', 'site-blocks' ),
					'badge'    => __( 'Quick start', 'site-blocks' ),
					'skeleton' => 'quote',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Share a few photos', 'site-blocks' ),
					'badge'    => __( 'Site details', 'site-blocks' ),
					'skeleton' => 'photos',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Get a tailored price', 'site-blocks' ),
					'badge'    => __( 'Instant estimate', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Technician review', 'site-blocks' ),
					'badge'    => __( 'Checked first', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'cctv' => array(
			'heading_id'    => 'sg-cctv-difference-heading',
			'section_class' => 'sg-cctv-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'One team from first plan to final ', 'site-blocks' ),
			'title_accent'  => __( 'handover', 'site-blocks' ),
			'title_after'   => __( '.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Tell us what to cover', 'site-blocks' ),
					'badge'    => __( 'Entries and blind spots', 'site-blocks' ),
					'skeleton' => 'quote',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Share site details', 'site-blocks' ),
					'badge'    => __( 'Photos and access', 'site-blocks' ),
					'skeleton' => 'photos',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Tailored estimate', 'site-blocks' ),
					'badge'    => __( 'One clear price', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Technician review', 'site-blocks' ),
					'badge'    => __( 'Installer checked', 'site-blocks' ),
					'skeleton' => 'install',
				),
			),
		),
		'intercom' => array(
			'heading_id'    => 'sg-intercom-difference-heading',
			'section_class' => 'sg-intercom-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'One team from first plan to final ', 'site-blocks' ),
			'title_accent'  => __( 'handover', 'site-blocks' ),
			'title_after'   => __( '.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Tell us your entries', 'site-blocks' ),
					'badge'    => __( 'Doors and gates', 'site-blocks' ),
					'skeleton' => 'quote',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Share site details', 'site-blocks' ),
					'badge'    => __( 'Cabling mapped', 'site-blocks' ),
					'skeleton' => 'photos',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Tailored estimate', 'site-blocks' ),
					'badge'    => __( 'Complete system', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Technician review', 'site-blocks' ),
					'badge'    => __( 'Approved first', 'site-blocks' ),
					'skeleton' => 'install',
				),
			),
		),
		'ajax-alarm-systems' => array(
			'heading_id'    => 'sg-ajax-process-heading',
			'section_class' => 'sg-ajax-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Ajax alarm delivery in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from design to support.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Design', 'site-blocks' ),
					'badge'    => __( 'Right Ajax system', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Install', 'site-blocks' ),
					'badge'    => __( 'Professional fit', 'site-blocks' ),
					'skeleton' => 'install',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Monitor', 'site-blocks' ),
					'badge'    => __( 'Flexible cover', 'site-blocks' ),
					'skeleton' => 'tour',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Support', 'site-blocks' ),
					'badge'    => __( 'Local help', 'site-blocks' ),
					'skeleton' => 'support',
				),
			),
		),
		'access-control' => array(
			'heading_id'    => 'sg-access-control-process-heading',
			'section_class' => 'sg-ac-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Access control in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from site visit to handover.', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Tell us your doors', 'site-blocks' ),
					'badge'    => __( 'Users mapped', 'site-blocks' ),
					'skeleton' => 'quote',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Share site details', 'site-blocks' ),
					'badge'    => __( 'Layout understood', 'site-blocks' ),
					'skeleton' => 'photos',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Tailored estimate', 'site-blocks' ),
					'badge'    => __( 'Scoped to site', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Install and handover', 'site-blocks' ),
					'badge'    => __( 'Trained and supported', 'site-blocks' ),
					'skeleton' => 'install',
				),
			),
		),
		'physical-security' => array(
			'heading_id'    => 'sg-ps-process-heading',
			'section_class' => 'sg-ps-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'From risk to ', 'site-blocks' ),
			'title_accent'  => __( 'response', 'site-blocks' ),
			'title_after'   => __( ' in four steps', 'site-blocks' ),
			'steps'         => array(
				array(
					'label'    => __( 'Step one', 'site-blocks' ),
					'title'    => __( 'Site assessment', 'site-blocks' ),
					'badge'    => __( 'Risk reviewed', 'site-blocks' ),
					'skeleton' => 'assess',
				),
				array(
					'label'    => __( 'Step two', 'site-blocks' ),
					'title'    => __( 'Tailored plan', 'site-blocks' ),
					'badge'    => __( 'Right mix', 'site-blocks' ),
					'skeleton' => 'schedule',
				),
				array(
					'label'    => __( 'Step three', 'site-blocks' ),
					'title'    => __( 'Officers deployed', 'site-blocks' ),
					'badge'    => __( 'Licensed team', 'site-blocks' ),
					'skeleton' => 'install',
				),
				array(
					'label'    => __( 'Step four', 'site-blocks' ),
					'title'    => __( 'Supervision', 'site-blocks' ),
					'badge'    => __( 'GPS and reporting', 'site-blocks' ),
					'skeleton' => 'report',
				),
			),
		),
		'physical-security-static-guards' => array(
			'heading_id'    => 'sg-ps-static-process-heading',
			'section_class' => 'sg-ps-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Static guarding in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from brief to on-site cover.', 'site-blocks' ),
			'steps'         => array(
				array( 'label' => __( 'Step one', 'site-blocks' ), 'title' => __( 'Site brief', 'site-blocks' ), 'badge' => __( 'Risk reviewed', 'site-blocks' ), 'skeleton' => 'assess' ),
				array( 'label' => __( 'Step two', 'site-blocks' ), 'title' => __( 'Roster plan', 'site-blocks' ), 'badge' => __( 'Hours mapped', 'site-blocks' ), 'skeleton' => 'schedule' ),
				array( 'label' => __( 'Step three', 'site-blocks' ), 'title' => __( 'Officers deployed', 'site-blocks' ), 'badge' => __( 'Licensed team', 'site-blocks' ), 'skeleton' => 'install' ),
				array( 'label' => __( 'Step four', 'site-blocks' ), 'title' => __( 'Supervision', 'site-blocks' ), 'badge' => __( 'Reports delivered', 'site-blocks' ), 'skeleton' => 'report' ),
			),
		),
		'physical-security-mobile-patrols' => array(
			'heading_id'    => 'sg-ps-mobile-process-heading',
			'section_class' => 'sg-ps-process-flow',
			'eyebrow'       => __( 'How it works', 'site-blocks' ),
			'title_before'  => __( 'Mobile patrols in ', 'site-blocks' ),
			'title_accent'  => __( 'four steps', 'site-blocks' ),
			'title_after'   => __( ' from route plan to GPS-verified checks.', 'site-blocks' ),
			'steps'         => array(
				array( 'label' => __( 'Step one', 'site-blocks' ), 'title' => __( 'Sites mapped', 'site-blocks' ), 'badge' => __( 'Routes planned', 'site-blocks' ), 'skeleton' => 'assess' ),
				array( 'label' => __( 'Step two', 'site-blocks' ), 'title' => __( 'Patrol schedule', 'site-blocks' ), 'badge' => __( 'Checks agreed', 'site-blocks' ), 'skeleton' => 'schedule' ),
				array( 'label' => __( 'Step three', 'site-blocks' ), 'title' => __( 'Vehicle deployed', 'site-blocks' ), 'badge' => __( 'GPS tracked', 'site-blocks' ), 'skeleton' => 'tour' ),
				array( 'label' => __( 'Step four', 'site-blocks' ), 'title' => __( 'Reporting', 'site-blocks' ), 'badge' => __( 'Incidents logged', 'site-blocks' ), 'skeleton' => 'report' ),
			),
		),
	);
}
