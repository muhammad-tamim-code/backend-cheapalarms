<?php
/**
 * CCTV — Safeguard + Ajax promo card.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-ajax-card.php';
?>
<section class="sg-band sg-band--blue sg-cctv-ajax-promo alignfull" aria-label="<?php esc_attr_e( 'Safeguard + Ajax', 'site-blocks' ); ?>">
	<div class="sg-container sg-cctv-ajax-promo__inner">
		<?php site_blocks_render_safeguard_ajax_card(); ?>
	</div>
</section>
