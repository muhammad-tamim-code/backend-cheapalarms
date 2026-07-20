<?php
/**
 * Access Control category page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Access Control Page', 'site-blocks' ),
	'description' => __( 'Access Control category pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'access control', 'service', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/access-control-hero /--><!-- wp:site/access-control-what /--><!-- wp:site/access-control-www /--><!-- wp:site/access-control-remote /--><!-- wp:site/access-control-options /--><!-- wp:site/access-control-keys /--><!-- wp:site/access-control-integration /--><!-- wp:site/access-control-install /--><!-- wp:site/access-control-process /--><!-- wp:site/access-control-portal /--><!-- wp:site/access-control-gallery /--><!-- wp:site/access-control-social-proof /--><!-- wp:site/access-control-faq /--><!-- wp:site/access-control-related /--><!-- wp:site/access-control-quote-cta /--></main><!-- wp:site/safeguard-footer /--></div>',
);
