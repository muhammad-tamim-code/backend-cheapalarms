<?php

/**

 * Monitoring hub page pattern.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



return array(

	'title'       => __( 'Monitoring Hub Page', 'site-blocks' ),

	'description' => __( 'Monitoring & Response pillar page for Safeguard Security Services.', 'site-blocks' ),

	'categories'  => array( 'site-pages' ),

	'keywords'    => array( 'monitoring', 'alarm', 'safeguard' ),

	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/monitoring-hero /--><!-- wp:site/monitoring-section {"section":"services"} /--><!-- wp:site/monitoring-section {"section":"how-it-works"} /--><!-- wp:site/monitoring-section {"section":"paths"} /--><!-- wp:site/monitoring-section {"section":"integration"} /--><!-- wp:site/monitoring-section {"section":"industries"} /--><!-- wp:site/monitoring-section {"section":"portal"} /--><!-- wp:site/monitoring-section {"section":"trust"} /--><!-- wp:site/monitoring-section {"section":"related-services"} /--><!-- wp:site/monitoring-section {"section":"faq"} /--><!-- wp:site/monitoring-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',

);

