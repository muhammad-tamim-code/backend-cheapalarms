<?php
/**
 * Single security package template.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$package = site_blocks_get_package_data( get_the_ID() );
	$schema  = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'name'        => $package['title'],
		'description' => $package['tagline'] ?: wp_strip_all_tags( get_the_excerpt() ),
		'url'         => $package['permalink'],
		'provider'    => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);
	?>
	<main id="main" class="package-single" aria-labelledby="package-single-heading">
		<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

		<p class="package-single__back">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'security_package' ) ); ?>">
				<?php esc_html_e( '← All packages', 'site-blocks' ); ?>
			</a>
		</p>

		<div class="package-single__layout">
			<div class="package-single__main">
				<?php if ( $package['type_name'] ) : ?>
					<p class="package-single__type"><?php echo esc_html( $package['type_name'] ); ?></p>
				<?php endif; ?>

				<h1 id="package-single-heading" class="package-single__title"><?php echo esc_html( $package['title'] ); ?></h1>

				<?php if ( $package['tagline'] ) : ?>
					<p class="package-single__tagline"><?php echo esc_html( $package['tagline'] ); ?></p>
				<?php endif; ?>

				<p class="package-single__price"><?php echo esc_html( site_blocks_format_package_price( $package ) ); ?></p>

				<?php if ( ! empty( $package['features'] ) ) : ?>
					<section class="package-single__features" aria-labelledby="package-features-heading">
						<h2 id="package-features-heading" class="package-single__section-title"><?php esc_html_e( 'What\'s included', 'site-blocks' ); ?></h2>
						<ul class="package-single__feature-list">
							<?php foreach ( $package['features'] as $feature ) : ?>
								<li><?php echo esc_html( $feature ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( get_the_content() ) : ?>
					<section class="package-single__content entry-content">
						<h2 class="package-single__section-title"><?php esc_html_e( 'Overview', 'site-blocks' ); ?></h2>
						<?php the_content(); ?>
					</section>
				<?php endif; ?>
			</div>

			<aside class="package-single__aside">
				<?php if ( $package['thumbnail_id'] ) : ?>
					<figure class="package-single__figure">
						<?php echo wp_get_attachment_image( (int) $package['thumbnail_id'], 'large', false, array( 'class' => 'package-single__image' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="package-single__cta-card">
					<h2 class="package-single__cta-title"><?php esc_html_e( 'Ready to secure your property?', 'site-blocks' ); ?></h2>
					<p class="package-single__cta-text"><?php esc_html_e( 'Tell us about your site and we will recommend the right configuration.', 'site-blocks' ); ?></p>
					<a class="package-single__cta-button" href="<?php echo esc_url( $package['cta_url'] ); ?>">
						<?php echo esc_html( $package['cta_label'] ); ?>
					</a>
				</div>
			</aside>
		</div>
	</main>
	<?php
endwhile;

get_footer();
