<?php
/**
 * Ajax Alarm Systems landing page block pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Ajax Alarm Systems Page', 'site-blocks' ),
	'description' => __( 'SEO landing page for Ajax alarm systems installed by Safeguard.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'ajax', 'alarm', 'seo', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/ajax-alarm-systems-hero /--><!-- wp:site/ajax-alarm-systems-process /--><!-- wp:site/ajax-alarm-systems-difference /--><!-- wp:site/ajax-alarm-systems-products /--><!-- wp:site/ajax-alarm-systems-monitoring /--><!-- wp:site/ajax-alarm-systems-property-fit /--><!-- wp:site/ajax-alarm-systems-compare /--><!-- wp:site/ajax-alarm-systems-faq /--><!-- wp:site/ajax-alarm-systems-quote-cta /--></main><!-- wp:site/safeguard-footer /--></div>',
);
