<?php
/**
 * CCTV category page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'CCTV & Security Cameras Page', 'site-blocks' ),
	'description' => __( 'CCTV category pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'cctv', 'cameras', 'service', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/cctv-hero /--><!-- wp:site/cctv-intro /--><!-- wp:site/cctv-difference /--><!-- wp:site/cctv-install /--><!-- wp:site/cctv-photo-band /--><!-- wp:site/cctv-segments /--><!-- wp:site/cctv-layered /--><!-- wp:site/cctv-portal /--><!-- wp:site/cctv-trust /--><!-- wp:site/cctv-faq /--><!-- wp:site/cctv-related /--><!-- wp:site/cctv-quote-cta /--></main><!-- wp:site/safeguard-footer /--></div>',
);
