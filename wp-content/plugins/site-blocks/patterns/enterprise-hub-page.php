<?php
/**
 * Enterprise Solutions hub page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Enterprise Solutions Hub Page', 'site-blocks' ),
	'description' => __( 'Enterprise Solutions pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'enterprise', 'commercial', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/enterprise-hero /--><!-- wp:site/enterprise-section {"section":"intro"} /--><!-- wp:site/enterprise-section {"section":"challenges"} /--><!-- wp:site/enterprise-section {"section":"approach"} /--><!-- wp:site/enterprise-section {"section":"solutions"} /--><!-- wp:site/enterprise-section {"section":"promo"} /--><!-- wp:site/enterprise-section {"section":"industries"} /--><!-- wp:site/enterprise-section {"section":"process"} /--><!-- wp:site/enterprise-section {"section":"integration"} /--><!-- wp:site/enterprise-section {"section":"trust"} /--><!-- wp:site/enterprise-section {"section":"insights"} /--><!-- wp:site/enterprise-section {"section":"quote"} /--><!-- wp:site/enterprise-section {"section":"related-services"} /--><!-- wp:site/enterprise-section {"section":"faq"} /--><!-- wp:site/enterprise-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
