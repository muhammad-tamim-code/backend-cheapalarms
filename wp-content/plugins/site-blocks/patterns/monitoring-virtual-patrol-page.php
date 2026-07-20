<?php
/**
 * Virtual Patrol page pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Virtual Patrol Page', 'site-blocks' ),
	'description' => __( 'Virtual patrol and remote guarding page under Monitoring.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'virtual patrol', 'monitoring', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/monitoring-hero /--><!-- wp:site/monitoring-section {"section":"intro"} /--><!-- wp:site/monitoring-section {"section":"how-it-works"} /--><!-- wp:site/monitoring-section {"section":"compare"} /--><!-- wp:site/monitoring-section {"section":"features"} /--><!-- wp:site/monitoring-section {"section":"industries"} /--><!-- wp:site/monitoring-section {"section":"requirements"} /--><!-- wp:site/monitoring-section {"section":"portal"} /--><!-- wp:site/monitoring-section {"section":"trust"} /--><!-- wp:site/monitoring-section {"section":"related-services"} /--><!-- wp:site/monitoring-section {"section":"faq"} /--><!-- wp:site/monitoring-section {"section":"cta"} /--></main><!-- wp:site/safeguard-footer /--></div>',
);
