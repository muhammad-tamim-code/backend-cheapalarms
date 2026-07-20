<?php

/**

 * Back-to-Base Monitoring page pattern.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



return array(

	'title'       => __( 'Back-to-Base Monitoring Page', 'site-blocks' ),

	'description' => __( 'Back-to-base alarm monitoring service page under Monitoring.', 'site-blocks' ),

	'categories'  => array( 'site-pages' ),

	'keywords'    => array( 'back-to-base', 'monitoring', 'safeguard' ),

	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/monitoring-hero /--><!-- wp:site/monitoring-section {"section":"intro"} /--><!-- wp:site/monitoring-section {"section":"how-it-works"} /--><!-- wp:site/monitoring-section {"section":"communicators"} /--><!-- wp:site/monitoring-section {"section":"response-plans"} /--><!-- wp:site/monitoring-section {"section":"quote"} /--><!-- wp:site/monitoring-section {"section":"portal"} /--><!-- wp:site/monitoring-section {"section":"trust"} /--><!-- wp:site/monitoring-section {"section":"related-services"} /--><!-- wp:site/monitoring-section {"section":"faq"} /--><!-- wp:site/monitoring-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',

);

