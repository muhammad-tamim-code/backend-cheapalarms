<?php
/**
 * Electronic Security hub page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Electronic Security Hub Page', 'site-blocks' ),
	'description' => __( 'Electronic Security pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'electronic security', 'alarms', 'cctv', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/electronic-security-hero /--><!-- wp:site/electronic-security-section {"section":"covers"} /--><!-- wp:site/electronic-security-section {"section":"services"} /--><!-- wp:site/electronic-security-section {"section":"integration"} /--><!-- wp:site/electronic-security-section {"section":"why"} /--><!-- wp:site/electronic-security-section {"section":"process"} /--><!-- wp:site/electronic-security-section {"section":"portal"} /--><!-- wp:site/electronic-security-section {"section":"trust"} /--><!-- wp:site/electronic-security-section {"section":"related-services"} /--><!-- wp:site/electronic-security-section {"section":"faq"} /--><!-- wp:site/electronic-security-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
