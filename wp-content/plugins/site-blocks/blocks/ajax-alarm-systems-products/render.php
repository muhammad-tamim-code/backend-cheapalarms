<?php
/**
 * Ajax Alarm Systems — common components grid.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-icons.php';

$products = array(
	array(
		'title'       => __( 'Ajax Hub 2 Plus', 'site-blocks' ),
		'description' => __( 'The central hub that controls Ajax sensors and scene automation across your property.', 'site-blocks' ),
		'image'       => '',
	),
	array(
		'title'       => __( 'KeyPad options', 'site-blocks' ),
		'description' => __( 'Indoor keypad options including KeyPad Plus, KeyPad Plus G3 and KeyPad TouchScreen depending on the install.', 'site-blocks' ),
		'image'       => '',
	),
	array(
		'title'       => __( 'Internal and external sirens', 'site-blocks' ),
		'description' => __( 'Loud sirens inside and outside the property to help deter intruders and alert neighbours.', 'site-blocks' ),
		'image'       => '',
	),
	array(
		'title'       => __( 'Door and window contacts', 'site-blocks' ),
		'description' => __( 'Magnetic contacts for doors and windows to detect unauthorised entry.', 'site-blocks' ),
		'image'       => '',
	),
	array(
		'title'       => __( 'Motion detectors', 'site-blocks' ),
		'description' => __( 'Indoor PIR detectors with pet-friendly options where required.', 'site-blocks' ),
		'image'       => '',
	),
	array(
		'title'       => __( 'Alarm communicator', 'site-blocks' ),
		'description' => __( 'Ethernet, Wi-Fi and cellular options to keep your system connected and monitored.', 'site-blocks' ),
		'image'       => '',
	),
);
?>
<section class="sg-ajax-section sg-ajax-products alignfull" aria-labelledby="sg-ajax-products-heading">
	<div class="sg-container">
		<header class="sg-ajax-section__header">
			<p class="sg-ajax-section__eyebrow"><?php esc_html_e( 'The products', 'site-blocks' ); ?></p>
			<h2 id="sg-ajax-products-heading" class="sg-ajax-section__title">
				<?php esc_html_e( 'Common Ajax components we may include in your design.', 'site-blocks' ); ?>
			</h2>
		</header>

		<div class="sg-ajax-products__grid">
			<?php foreach ( $products as $product ) : ?>
				<article class="sg-ajax-split-card">
					<div class="sg-ajax-split-card__media" aria-hidden="true">
						<?php site_blocks_ajax_card_image( $product['image'], $product['title'] ); ?>
					</div>
					<div class="sg-ajax-split-card__body">
						<h3 class="sg-ajax-split-card__title"><?php echo esc_html( $product['title'] ); ?></h3>
						<p class="sg-ajax-split-card__desc"><?php echo esc_html( $product['description'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
