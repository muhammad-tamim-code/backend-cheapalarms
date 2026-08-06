<?php
/**
 * ManPower hub page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'ManPower Hub Page', 'site-blocks' ),
	'description' => __( 'ManPower pillar page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'manpower', 'guards', 'staffing', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/manpower-hero /--><!-- wp:site/manpower-section {"section":"covers"} /--><!-- wp:site/manpower-section {"section":"services"} /--><!-- wp:site/manpower-section {"section":"integration"} /--><!-- wp:site/manpower-section {"section":"why"} /--><!-- wp:site/manpower-section {"section":"process"} /--><!-- wp:site/manpower-section {"section":"portal"} /--><!-- wp:site/manpower-section {"section":"trust"} /--><!-- wp:site/manpower-section {"section":"related-services"} /--><!-- wp:site/manpower-section {"section":"faq"} /--><!-- wp:site/manpower-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
