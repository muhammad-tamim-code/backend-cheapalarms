<?php
/**
 * Static Security Guards page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Static Security Guards Page', 'site-blocks' ),
	'description' => __( 'Static guarding service page under Physical Security.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'static guards', 'physical security', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/physical-security-hero /--><!-- wp:site/physical-security-section {"section":"intro"} /--><!-- wp:site/physical-security-section {"section":"duties"} /--><!-- wp:site/physical-security-section {"section":"industries"} /--><!-- wp:site/physical-security-section {"section":"integration"} /--><!-- wp:site/physical-security-section {"section":"why"} /--><!-- wp:site/physical-security-section {"section":"compare"} /--><!-- wp:site/physical-security-section {"section":"process"} /--><!-- wp:site/physical-security-section {"section":"portal"} /--><!-- wp:site/physical-security-section {"section":"trust"} /--><!-- wp:site/physical-security-section {"section":"related-services"} /--><!-- wp:site/physical-security-section {"section":"faq"} /--><!-- wp:site/physical-security-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
