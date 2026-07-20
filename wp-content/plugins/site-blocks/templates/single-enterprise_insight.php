<?php
/**
 * Single Enterprise Insight template.
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
	$hub_url = home_url( '/enterprise-solutions/' );
	?>
	<div class="sg-page alignfull safeguard-enterprise-page">
		<?php
		if ( function_exists( 'site_blocks_render_safeguard_header' ) ) {
			site_blocks_render_safeguard_header();
		}
		?>
		<main id="main" class="sg-main sg-enterprise-article-page">
			<article class="sg-enterprise-article" aria-labelledby="sg-enterprise-article-heading">
				<div class="sg-container">
					<nav class="sg-enterprise-article__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
						<ol role="list">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'site-blocks' ); ?></a></li>
							<li><a href="<?php echo esc_url( $hub_url ); ?>"><?php esc_html_e( 'Enterprise Solutions', 'site-blocks' ); ?></a></li>
							<li aria-current="page"><?php the_title(); ?></li>
						</ol>
					</nav>

					<header class="sg-enterprise-article__header">
						<?php
						$terms = get_the_terms( get_the_ID(), 'enterprise_insight_category' );
						if ( is_array( $terms ) && ! empty( $terms ) ) :
							?>
							<p class="sg-enterprise-article__category"><?php echo esc_html( $terms[0]->name ); ?></p>
						<?php endif; ?>
						<h1 id="sg-enterprise-article-heading" class="sg-enterprise-article__title"><?php the_title(); ?></h1>
						<?php if ( has_excerpt() ) : ?>
							<p class="sg-enterprise-article__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
						<?php
						$word_count = str_word_count( wp_strip_all_tags( get_the_content() ) );
						$read_min   = max( 1, (int) round( $word_count / 200 ) );
						?>
						<div class="sg-enterprise-article__meta">
							<span class="sg-enterprise-article__byline"><?php esc_html_e( 'Safeguard Security Services', 'site-blocks' ); ?></span>
							<span class="sg-enterprise-article__dot" aria-hidden="true">&middot;</span>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<span class="sg-enterprise-article__dot" aria-hidden="true">&middot;</span>
							<span><?php echo esc_html( sprintf( /* translators: %d: minutes */ __( '%d min read', 'site-blocks' ), $read_min ) ); ?></span>
						</div>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="sg-enterprise-article__figure">
							<?php the_post_thumbnail( 'large', array( 'class' => 'sg-enterprise-article__image', 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
						</figure>
					<?php endif; ?>

					<div class="sg-enterprise-article__content entry-content">
						<?php the_content(); ?>
					</div>

					<footer class="sg-enterprise-article__footer">
						<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
							<?php esc_html_e( 'Book a site assessment', 'site-blocks' ); ?>
						</a>
						<a class="sg-btn sg-btn--ghost-dark" href="<?php echo esc_url( $hub_url ); ?>">
							<?php esc_html_e( 'Back to Enterprise Solutions', 'site-blocks' ); ?>
						</a>
					</footer>
				</div>
			</article>
		</main>
		<?php
		if ( function_exists( 'site_blocks_render_safeguard_footer' ) ) {
			site_blocks_render_safeguard_footer();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
