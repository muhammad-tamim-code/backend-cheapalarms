<?php

/**
 * Access Control process hub shield and label placement helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hub shield mark for radial process mockup.
 */
function site_blocks_access_control_process_hub_shield(): void {
	site_blocks_lucide_icon( 'shield-check', 24, 'sg-ac-process__shield' );
}

/**
 * Label position for orbit steps (top / right / bottom / left).
 *
 * @param int $index Zero-based step index.
 */
function site_blocks_access_control_process_pos( int $index ): string {
	if ( 0 === $index ) {
		return 'top';
	}

	if ( 3 === $index ) {
		return 'bottom';
	}

	if ( $index >= 4 ) {
		return 'left';
	}

	return 'right';
}
