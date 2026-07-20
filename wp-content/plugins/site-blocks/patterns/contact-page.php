<?php
/**
 * Contact page block pattern.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Contact Page', 'site-blocks' ),
	'description' => __( 'Safeguard contact page with details, form and quote CTA.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'contact', 'form', 'inquiry' ),
	'content'     => '<div class="sg-page alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main sg-contact-main"><!-- wp:site/contact-hero /--><!-- wp:group {"className":"sg-contact-body","layout":{"type":"default"}} --><div class="wp-block-group sg-contact-body"><!-- wp:site/contact-info /--><!-- wp:site/contact-form /--></div><!-- /wp:group --><!-- wp:site/contact-quote-cta /--></main><!-- wp:site/safeguard-footer /--></div>',
);
