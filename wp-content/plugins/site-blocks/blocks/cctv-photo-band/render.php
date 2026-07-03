<?php
/**
 * CCTV full-bleed photo band.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/cctv-media.php';
?>
<section class="sg-cctv-photo-band alignfull" aria-label="<?php esc_attr_e( 'CCTV installation photography', 'site-blocks' ); ?>">
	<?php
	site_blocks_cctv_image(
		'images/cctv/install-band.webp',
		__( 'Safeguard technician installing a CCTV camera on a Sydney home', 'site-blocks' ),
		'sg-cctv-photo-band__img'
	);
	?>
</section>
