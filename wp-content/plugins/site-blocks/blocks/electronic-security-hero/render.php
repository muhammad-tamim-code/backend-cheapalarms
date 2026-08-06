<?php
/**
 * Electronic Security hub hero — light product hero (blue soft CTA).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/electronic-security-config.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-stage-hero.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

$page_key = site_blocks_get_electronic_security_page_key();

if ( null === $page_key ) {
	return;
}

$config = site_blocks_electronic_security_hero_config( $page_key );

if ( null === $config ) {
	return;
}

$id       = (string) $config['id'];
$features = isset( $config['features'] ) && is_array( $config['features'] ) ? $config['features'] : array();

$primary_url   = (string) ( $config['primary_url'] ?? home_url( '/contact/' ) );
$primary_label = (string) ( $config['primary_label'] ?? __( 'Explore Solutions', 'site-blocks' ) );
$secondary_url = (string) ( $config['secondary_url'] ?? 'tel:1300225276' );
$secondary_label = (string) ( $config['secondary_label'] ?? '1300 225 276' );

$hero_image = 'images/electronic-security/' . ltrim( (string) $config['hero_image'], '/' );
$hero_alt   = (string) $config['hero_alt'];
?>
<section class="sg-es-product-hero alignfull" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<div class="sg-es-product-hero__inner">
		<div class="sg-es-product-hero__copy">
			<?php if ( '' !== (string) ( $config['badge'] ?? '' ) ) : ?>
				<p class="sg-es-product-hero__eyebrow"><?php echo esc_html( (string) $config['badge'] ); ?></p>
			<?php endif; ?>

			<h1 id="<?php echo esc_attr( $id ); ?>" class="sg-es-product-hero__title">
				<?php
				$title_before = (string) ( $config['title_before'] ?? '' );
				$title_accent = (string) ( $config['title_accent'] ?? '' );
				$title_after  = (string) ( $config['title_after'] ?? '' );
				echo esc_html( $title_before . $title_accent . $title_after );
				?>
			</h1>

			<?php if ( '' !== (string) ( $config['lead'] ?? '' ) ) : ?>
				<p class="sg-es-product-hero__lead"><?php echo esc_html( (string) $config['lead'] ); ?></p>
			<?php endif; ?>

			<div class="sg-es-product-hero__ctas">
				<a class="sg-btn sg-btn--soft-blue" href="<?php echo esc_url( $primary_url ); ?>">
					<?php echo esc_html( $primary_label ); ?>
				</a>
				<a class="sg-btn sg-btn--secondary sg-es-product-hero__phone" href="<?php echo esc_url( $secondary_url ); ?>">
					<span class="sg-es-product-hero__phone-icon" aria-hidden="true">
						<?php site_blocks_lucide_icon( 'phone', 18 ); ?>
					</span>
					<?php echo esc_html( $secondary_label ); ?>
				</a>
			</div>

			<?php if ( $features !== array() ) : ?>
				<ul class="sg-es-product-hero__features" role="list">
					<?php foreach ( $features as $item ) : ?>
						<li>
							<span class="sg-es-product-hero__feature-icon" aria-hidden="true">
								<?php site_blocks_lucide_icon( (string) ( $item['icon'] ?? 'shield-check' ), 22 ); ?>
							</span>
							<span class="sg-es-product-hero__feature-label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="sg-es-product-hero__visual">
			<figure class="sg-es-product-hero__figure">
				<?php
				site_blocks_stage_hero_media(
					$hero_image,
					$hero_alt,
					'sg-es-product-hero__img',
					'eager'
				);
				?>
			</figure>
		</div>
	</div>
</section>
<?php
$trust_strip = $config['trust_strip'] ?? array();

if ( is_array( $trust_strip ) && $trust_strip !== array() ) {
	site_blocks_render_trust_strip(
		array(
			'items'         => $trust_strip,
			'section_class' => 'sg-es-trust-strip',
		)
	);
}
