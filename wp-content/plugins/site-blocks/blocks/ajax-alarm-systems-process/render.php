<?php
/**
 * Ajax Alarm Systems — design / install / monitor / support strip.
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

$items = array(
	array(
		'title'       => __( 'Design', 'site-blocks' ),
		'description' => __( 'We design the right Ajax system for your property.', 'site-blocks' ),
		'icon'        => 'design.png',
	),
	array(
		'title'       => __( 'Install', 'site-blocks' ),
		'description' => __( 'Clean, professional installation by trained technicians.', 'site-blocks' ),
		'icon'        => 'install.png',
	),
	array(
		'title'       => __( 'Monitor', 'site-blocks' ),
		'description' => __( 'Flexible monitoring to suit how you manage security.', 'site-blocks' ),
		'icon'        => 'shield.png',
	),
	array(
		'title'       => __( 'Support', 'site-blocks' ),
		'description' => __( 'Local support when you need it most.', 'site-blocks' ),
		'icon'        => 'support.png',
	),
);
?>
<section class="sg-ajax-process alignfull" aria-label="<?php esc_attr_e( 'How Safeguard delivers Ajax alarm systems', 'site-blocks' ); ?>">
	<div class="sg-container">
		<ul class="sg-ajax-process__list" role="list">
			<?php foreach ( $items as $item ) : ?>
				<li class="sg-ajax-process__item">
					<span class="sg-ajax-process__icon" aria-hidden="true">
						<?php site_blocks_ajax_hero_icon( $item['icon'] ); ?>
					</span>
					<div class="sg-ajax-process__copy">
						<h3 class="sg-ajax-process__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="sg-ajax-process__desc"><?php echo esc_html( $item['description'] ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
