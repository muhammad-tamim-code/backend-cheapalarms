<?php
/**
 * Access Control, projects gallery placeholder.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/access-control-media.php';

$gallery_items = array(
	array( 'file' => 'gallery-01.webp', 'alt' => __( 'Card reader and maglock on commercial glass entry doors in Sydney', 'site-blocks' ) ),
	array( 'file' => 'gallery-02.webp', 'alt' => __( 'Access keypad and intercom at a warehouse gate', 'site-blocks' ) ),
	array( 'file' => 'gallery-03.webp', 'alt' => __( 'Intercom and card-reader entry panel in an apartment building lobby', 'site-blocks' ) ),
	array( 'file' => 'gallery-04.webp', 'alt' => __( 'Card reader on a restricted server-room door', 'site-blocks' ) ),
	array( 'file' => 'gallery-05.webp', 'alt' => __( 'Electronic lock and reader on a retail staff-only door', 'site-blocks' ) ),
	array( 'file' => 'gallery-06.webp', 'alt' => __( 'Reception desk with an access control dashboard', 'site-blocks' ) ),
	array( 'file' => 'gallery-07.webp', 'alt' => __( 'RFID reader at a car park boom gate', 'site-blocks' ) ),
	array( 'file' => 'gallery-08.webp', 'alt' => __( 'Technician installing an access control card reader', 'site-blocks' ) ),
);
?>
<section class="sg-band sg-band--white sg-access-control-gallery alignfull" aria-labelledby="sg-access-control-gallery-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-access-control-gallery-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php esc_html_e( 'Recent ', 'site-blocks' ); ?>
				<span class="sg-accent"><?php esc_html_e( 'projects', 'site-blocks' ); ?></span>
			</h2>
			<p class="sg-section-intro sg-section-intro--center">
				<?php esc_html_e( 'A selection of access control installations across Sydney.', 'site-blocks' ); ?>
			</p>
		</header>

		<div class="sg-ac-gallery__grid" role="list" aria-label="<?php esc_attr_e( 'Access control project photos', 'site-blocks' ); ?>">
			<?php foreach ( $gallery_items as $item ) : ?>
				<div class="sg-ac-gallery__cell" role="listitem">
					<?php
					site_blocks_access_control_image(
						'images/access-control/' . $item['file'],
						$item['alt'],
						'sg-ac-gallery__img'
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
