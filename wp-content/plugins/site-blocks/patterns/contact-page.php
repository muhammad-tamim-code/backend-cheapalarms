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
	'description' => __( 'Editorial split-layout contact page with info panel and inquiry form.', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'contact', 'form', 'inquiry' ),
	'content'     => '<!-- wp:group {"className":"contact-page-layout","layout":{"type":"default"}} -->
<div class="wp-block-group contact-page-layout"><!-- wp:site/contact-hero /-->

<!-- wp:site/contact-info /-->

<!-- wp:site/contact-form /--></div>
<!-- /wp:group -->',
);
