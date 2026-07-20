<?php

/**

 * Physical Security page media helpers.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



require_once SITE_BLOCKS_DIR . 'inc/safeguard-media.php';



/**

 * Render a physical security image or placeholder when asset is not uploaded yet.

 *

 * @param string $filename Filename under assets/images/physical-security/.

 * @param string $alt      Alt text.

 * @param string $class    Optional img class.

 * @param string $loading  loading attribute.

 */

function site_blocks_physical_security_image( string $filename, string $alt = '', string $class = 'sg-ac-split__img', string $loading = 'lazy' ): void {

	site_blocks_silo_image( 'physical-security', $filename, $alt, $class, $loading );

}



/**

 * Hero image for the active page key.

 *

 * @param string $filename Hero filename.

 * @param string $alt      Alt text.

 */

function site_blocks_physical_security_hero_image( string $filename, string $alt ): void {

	site_blocks_silo_hero_image( 'physical-security', $filename, $alt );

}


