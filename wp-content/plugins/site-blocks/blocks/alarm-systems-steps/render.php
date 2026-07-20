<?php
/**
 * Alarm Systems, How it works block render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow-configs.php';

$config = site_blocks_process_flow_config( 'alarm-systems' );

if ( null === $config ) {
	return;
}

if ( isset( $attributes['eyebrow'] ) && '' !== (string) $attributes['eyebrow'] ) {
	$config['eyebrow'] = (string) $attributes['eyebrow'];
}
if ( isset( $attributes['headlineBefore'] ) && '' !== (string) $attributes['headlineBefore'] ) {
	$config['title_before'] = (string) $attributes['headlineBefore'];
}
if ( isset( $attributes['headlineAccent'] ) && '' !== (string) $attributes['headlineAccent'] ) {
	$config['title_accent'] = (string) $attributes['headlineAccent'];
}

site_blocks_render_process_flow( $config );
