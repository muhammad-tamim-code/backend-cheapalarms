<?php
/**
 * Enterprise silo page media helpers.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-media.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

/**
 * Render an enterprise image or placeholder.
 *
 * @param string $filename Filename under assets/images/enterprise/.
 * @param string $alt      Alt text.
 * @param string $class    Optional img class.
 * @param string $loading  loading attribute.
 */
function site_blocks_enterprise_image( string $filename, string $alt = '', string $class = 'sg-ac-split__img', string $loading = 'lazy' ): void {
	site_blocks_silo_image( 'enterprise', $filename, $alt, $class, $loading );
}

/**
 * Hero image for the active enterprise page key.
 *
 * @param string $filename Hero filename.
 * @param string $alt      Alt text.
 */
function site_blocks_enterprise_hero_image( string $filename, string $alt ): void {
	site_blocks_silo_hero_image( 'enterprise', $filename, $alt );
}

/**
 * Render a Lucide icon for enterprise heroes.
 */
function site_blocks_enterprise_hero_icon( string $icon ): void {
	site_blocks_lucide_icon( $icon );
}
