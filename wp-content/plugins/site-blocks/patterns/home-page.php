<?php
/**
 * Homepage block pattern — Safeguard Security Services.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Safeguard Security Homepage', 'site-blocks' ),
	'description' => __( 'Complete light-theme homepage for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'home', 'safeguard', 'security', 'sydney' ),
	'content'     => '<!-- wp:site/safeguard-homepage /-->',
);
