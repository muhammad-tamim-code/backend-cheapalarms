<?php
/**
 * Safeguard Solutions child hub page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Safeguard Solutions Page', 'site-blocks' ),
	'description' => __( 'Safeguard Solutions cloud platform child page under Enterprise Solutions.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'safeguard solutions', 'cloud', 'enterprise' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/enterprise-hero /--><!-- wp:site/enterprise-section {"section":"capabilities"} /--><!-- wp:site/enterprise-section {"section":"spoke-teasers"} /--><!-- wp:site/enterprise-section {"section":"faq"} /--><!-- wp:site/enterprise-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
