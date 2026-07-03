<?php
/**
 * Ajax Alarm Systems — property fit row.
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

$properties = array(
	array(
		'title'       => __( 'Homes', 'site-blocks' ),
		'description' => __( 'Detached houses, townhouses and villas where wireless devices suit typical room layouts.', 'site-blocks' ),
		'image'       => 'images/ajax/property/home.webp',
	),
	array(
		'title'       => __( 'Apartments', 'site-blocks' ),
		'description' => __( 'Units and apartments where discreet sensors and keypads fit smaller living spaces.', 'site-blocks' ),
		'image'       => 'images/ajax/property/apartments.webp',
	),
	array(
		'title'       => __( 'Small commercial', 'site-blocks' ),
		'description' => __( 'Offices, retail and light commercial spaces needing reliable intrusion detection.', 'site-blocks' ),
		'image'       => 'images/ajax/property/small-business.webp',
	),
);
?>
<section class="sg-ajax-section sg-ajax-property alignfull" aria-labelledby="sg-ajax-property-heading">
	<div class="sg-container">
		<header class="sg-ajax-section__header">
			<p class="sg-ajax-section__eyebrow"><?php esc_html_e( 'Property fit', 'site-blocks' ); ?></p>
			<h2 id="sg-ajax-property-heading" class="sg-ajax-section__title">
				<?php esc_html_e( 'Ajax works well for many residential and light commercial sites.', 'site-blocks' ); ?>
			</h2>
		</header>

		<div class="sg-ajax-property__grid">
			<?php foreach ( $properties as $property ) : ?>
				<article class="sg-ajax-split-card sg-ajax-split-card--photo">
					<div class="sg-ajax-split-card__media" aria-hidden="true">
						<?php site_blocks_ajax_card_image( $property['image'], $property['title'] ); ?>
					</div>
					<div class="sg-ajax-split-card__body">
						<h3 class="sg-ajax-split-card__title"><?php echo esc_html( $property['title'] ); ?></h3>
						<p class="sg-ajax-split-card__desc"><?php echo esc_html( $property['description'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
