<?php
/**
 * Ajax calculator iframe embed.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/ajax-calculator.php';

echo site_blocks_ajax_calculator_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in shortcode.
