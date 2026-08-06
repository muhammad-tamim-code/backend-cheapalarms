<?php
/**
 * Intercom Systems hero — light product hero (Akuvox composition).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-stage-hero.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

$id = 'sg-intercom-hero-heading';

$breadcrumb = array(
	array(
		'label' => __( 'Home', 'site-blocks' ),
		'url'   => home_url( '/' ),
	),
	array(
		'label' => __( 'Electronic Security', 'site-blocks' ),
		'url'   => home_url( '/electronic-security/' ),
	),
	array(
		'label'   => __( 'Intercom Systems', 'site-blocks' ),
		'current' => true,
	),
);

$features = array(
	array(
		'icon'  => 'video',
		'label' => __( 'Video Verification', 'site-blocks' ),
	),
	array(
		'icon'  => 'mic',
		'label' => __( 'Two-Way Communication', 'site-blocks' ),
	),
	array(
		'icon'  => 'key',
		'label' => __( 'Remote Door Release', 'site-blocks' ),
	),
	array(
		'icon'  => 'shield',
		'label' => __( 'Enhanced Security', 'site-blocks' ),
	),
);

$primary_url = home_url( '/get-an-instant-quote/' );
?>
<section class="sg-intercom-product-hero alignfull" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<div class="sg-intercom-product-hero__inner">
		<div class="sg-intercom-product-hero__copy">
			<nav class="sg-intercom-product-hero__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
				<?php
				$i     = 0;
				$count = count( $breadcrumb );
				foreach ( $breadcrumb as $crumb ) :
					++$i;
					$label   = (string) ( $crumb['label'] ?? '' );
					$url     = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
					$current = ! empty( $crumb['current'] );
					?>
					<?php if ( $current || '' === $url ) : ?>
						<span<?php echo $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></span>
					<?php else : ?>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endif; ?>
					<?php if ( $i < $count ) : ?>
						<span aria-hidden="true">/</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>

			<p class="sg-intercom-product-hero__eyebrow"><?php esc_html_e( 'Intercom Solutions', 'site-blocks' ); ?></p>

			<h1 id="<?php echo esc_attr( $id ); ?>" class="sg-intercom-product-hero__title">
				<?php esc_html_e( 'Smarter Access. Stronger Connections. Better Security.', 'site-blocks' ); ?>
			</h1>

			<p class="sg-intercom-product-hero__lead">
				<?php esc_html_e( 'See who\'s at the door. Communicate instantly. Grant access with confidence.', 'site-blocks' ); ?>
			</p>

			<div class="sg-intercom-product-hero__ctas">
				<a class="sg-btn sg-btn--soft-orange" href="<?php echo esc_url( $primary_url ); ?>">
					<?php esc_html_e( 'Explore Intercom Solutions', 'site-blocks' ); ?>
				</a>
			</div>

			<ul class="sg-intercom-product-hero__features" role="list">
				<?php foreach ( $features as $item ) : ?>
					<li>
						<span class="sg-intercom-product-hero__feature-icon" aria-hidden="true">
							<?php site_blocks_lucide_icon( (string) ( $item['icon'] ?? 'shield' ), 22 ); ?>
						</span>
						<span class="sg-intercom-product-hero__feature-label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="sg-intercom-product-hero__visual">
			<figure class="sg-intercom-product-hero__figure">
				<?php
				site_blocks_stage_hero_media(
					'images/intercom/hero.webp',
					__( 'Akuvox video intercom outdoor station and indoor monitor showing a visitor at the door', 'site-blocks' ),
					'sg-intercom-product-hero__img',
					'eager'
				);
				?>
			</figure>
		</div>
	</div>
</section>
