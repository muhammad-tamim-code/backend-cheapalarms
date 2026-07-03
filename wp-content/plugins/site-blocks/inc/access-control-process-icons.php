<?php

/**

 * Access Control process — hub shield SVG and label placement helpers.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



/**

 * Inline hub shield mark (matches reference radial mockup).

 */

function site_blocks_access_control_process_hub_shield(): void {

	echo '<svg class="sg-ac-process__shield" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">';

	echo '<path d="M12 2 4 5v6c0 4.5 3.4 7.6 8 9 4.6-1.4 8-4.5 8-9V5l-8-3Z" stroke="currentColor" stroke-width="1.5"/>';

	echo '<path d="m8.5 12 2.5 2.5L16 9" stroke="var(--sg-orange)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>';

	echo '</svg>';

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

