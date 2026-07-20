<?php
/**
 * Intercom Systems category page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Intercom Systems Page', 'site-blocks' ),
	'description' => __( 'Intercom category pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'intercom', 'service', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/intercom-hero /--><!-- wp:site/intercom-intro /--><!-- wp:site/intercom-difference /--><!-- wp:site/intercom-install /--><!-- wp:site/intercom-segments /--><!-- wp:site/intercom-layered /--><!-- wp:site/intercom-portal /--><!-- wp:site/intercom-trust /--><!-- wp:site/intercom-faq /--><!-- wp:site/intercom-related /--><!-- wp:site/intercom-quote-cta /--></main><!-- wp:site/safeguard-footer /--></div>',
);
