<?php
/**
 * Physical Security hub page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Physical Security Hub Page', 'site-blocks' ),
	'description' => __( 'Physical Security pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'physical security', 'guards', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/physical-security-hero /--><!-- wp:site/physical-security-section {"section":"covers"} /--><!-- wp:site/physical-security-section {"section":"services"} /--><!-- wp:site/physical-security-section {"section":"integration"} /--><!-- wp:site/physical-security-section {"section":"sites"} /--><!-- wp:site/physical-security-section {"section":"why"} /--><!-- wp:site/physical-security-section {"section":"process"} /--><!-- wp:site/physical-security-section {"section":"portal"} /--><!-- wp:site/physical-security-section {"section":"trust"} /--><!-- wp:site/physical-security-section {"section":"related-services"} /--><!-- wp:site/physical-security-section {"section":"faq"} /--><!-- wp:site/physical-security-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
