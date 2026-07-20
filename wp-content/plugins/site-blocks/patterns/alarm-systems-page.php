<?php
/**
 * Alarm Systems page block pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Alarm Systems Page', 'site-blocks' ),
	'description' => __( 'Alarm Systems service page for Safeguard Security Services.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'alarm', 'service', 'safeguard' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main"><!-- wp:site/alarm-systems-hero /--><!-- wp:site/alarm-systems-services /--><!-- wp:site/alarm-systems-why /--><!-- wp:site/alarm-systems-ajax /--><!-- wp:site/alarm-systems-steps /--><!-- wp:site/alarm-systems-portal /--><!-- wp:site/alarm-systems-trust /--><!-- wp:site/alarm-systems-faq /--><!-- wp:site/alarm-systems-related /--><!-- wp:site/alarm-systems-quote-cta /--></main><!-- wp:site/safeguard-footer /--></div>',
);
