<?php
/**
 * Solar Cameras with Monitoring page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Solar Cameras with Monitoring Page', 'site-blocks' ),
	'description' => __( 'Solar-powered camera monitoring page under Monitoring.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'solar', 'monitoring', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/monitoring-hero /--><!-- wp:site/monitoring-section {"section":"intro"} /--><!-- wp:site/monitoring-section {"section":"how-it-works"} /--><!-- wp:site/monitoring-section {"section":"use-cases"} /--><!-- wp:site/monitoring-section {"section":"technical"} /--><!-- wp:site/monitoring-section {"section":"industries"} /--><!-- wp:site/monitoring-section {"section":"portal"} /--><!-- wp:site/monitoring-section {"section":"monitoring-integration"} /--><!-- wp:site/monitoring-section {"section":"related-services"} /--><!-- wp:site/monitoring-section {"section":"faq"} /--><!-- wp:site/monitoring-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
