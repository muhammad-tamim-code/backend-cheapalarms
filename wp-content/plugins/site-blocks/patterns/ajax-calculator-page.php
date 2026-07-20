<?php
/**
 * Ajax calculator page pattern — calculator only (site chrome + embed).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'title'       => __( 'Ajax Calculator Page', 'site-blocks' ),
	'description' => __( 'Calculator-only Ajax system design page (header, embed, footer).', 'site-blocks' ),
	'categories'  => array( 'site-pages' ),
	'keywords'    => array( 'ajax', 'calculator', 'quote' ),
	'content'     => '<div class="sg-page sg-page--ajax-calculator alignfull"><!-- wp:site/safeguard-header /--><main id="main" class="sg-main sg-main--ajax-calculator"><!-- wp:site/ajax-calculator-embed /--></main><!-- wp:site/safeguard-footer /--></div>',
);
