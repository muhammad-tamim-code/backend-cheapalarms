<?php
/**
 * Security package data helpers and card rendering.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get structured package data for a post.
 *
 * @param int $post_id Package post ID.
 * @return array<string, mixed>
 */
function site_blocks_get_package_data( int $post_id ): array {
	$contact_page = get_page_by_path( 'contact' );
	$contact_url  = $contact_page instanceof WP_Post ? get_permalink( $contact_page ) : home_url( '/contact/' );
	$cta_url      = get_post_meta( $post_id, 'package_cta_url', true );
	$features_raw = get_post_meta( $post_id, 'package_features', true );
	$types        = get_the_terms( $post_id, 'package_type' );
	$type         = ( is_array( $types ) && ! empty( $types ) ) ? $types[0] : null;

	return array(
		'id'            => $post_id,
		'title'         => get_the_title( $post_id ),
		'permalink'     => get_permalink( $post_id ),
		'excerpt'       => get_the_excerpt( $post_id ),
		'thumbnail_id'  => get_post_thumbnail_id( $post_id ),
		'tagline'       => (string) get_post_meta( $post_id, 'package_tagline', true ),
		'price_from'    => (string) get_post_meta( $post_id, 'package_price_from', true ),
		'price_prefix'  => (string) ( get_post_meta( $post_id, 'package_price_prefix', true ) ?: 'From' ),
		'features'      => site_blocks_parse_features( (string) $features_raw ),
		'cta_label'     => (string) ( get_post_meta( $post_id, 'package_cta_label', true ) ?: __( 'Get a quote', 'site-blocks' ) ),
		'cta_url'       => $cta_url ? (string) $cta_url : (string) $contact_url,
		'type_name'     => $type ? $type->name : '',
		'type_slug'     => $type ? $type->slug : '',
		'type_link'     => $type ? get_term_link( $type ) : '',
	);
}

/**
 * Parse newline-separated features into an array.
 *
 * @param string $raw Raw features string.
 * @return string[]
 */
function site_blocks_parse_features( string $raw ): array {
	if ( '' === trim( $raw ) ) {
		return array();
	}

	return array_values(
		array_filter(
			array_map( 'trim', explode( "\n", $raw ) )
		)
	);
}

/**
 * Format package price for display.
 *
 * @param array<string, mixed> $package Package data.
 * @return string
 */
function site_blocks_format_package_price( array $package ): string {
	$amount = isset( $package['price_from'] ) ? trim( (string) $package['price_from'] ) : '';

	if ( '' === $amount ) {
		return __( 'Quote on request', 'site-blocks' );
	}

	$prefix = isset( $package['price_prefix'] ) ? trim( (string) $package['price_prefix'] ) : 'From';
	$amount = preg_replace( '/[^0-9.]/', '', $amount );

	if ( '' === $amount ) {
		return __( 'Quote on request', 'site-blocks' );
	}

	return trim( $prefix . ' $' . number_format( (float) $amount, 0 ) );
}

/**
 * Render a package card.
 *
 * @param int  $post_id  Package post ID.
 * @param bool $is_featured Whether card is featured size.
 */
function site_blocks_render_package_card( int $post_id, bool $is_featured = false ): void {
	$package = site_blocks_get_package_data( $post_id );
	$classes = 'package-card' . ( $is_featured ? ' package-card--featured' : '' );
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<?php if ( $package['thumbnail_id'] ) : ?>
			<a class="package-card__media" href="<?php echo esc_url( $package['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo wp_get_attachment_image( (int) $package['thumbnail_id'], 'large', false, array( 'class' => 'package-card__image' ) ); ?>
			</a>
		<?php else : ?>
			<a class="package-card__media package-card__media--placeholder" href="<?php echo esc_url( $package['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
				<span class="package-card__placeholder-icon" aria-hidden="true">◈</span>
			</a>
		<?php endif; ?>

		<div class="package-card__body">
			<?php if ( $package['type_name'] ) : ?>
				<p class="package-card__type"><?php echo esc_html( $package['type_name'] ); ?></p>
			<?php endif; ?>

			<h2 class="package-card__title">
				<a href="<?php echo esc_url( $package['permalink'] ); ?>"><?php echo esc_html( $package['title'] ); ?></a>
			</h2>

			<?php if ( $package['tagline'] ) : ?>
				<p class="package-card__tagline"><?php echo esc_html( $package['tagline'] ); ?></p>
			<?php elseif ( $package['excerpt'] ) : ?>
				<p class="package-card__tagline"><?php echo esc_html( wp_strip_all_tags( $package['excerpt'] ) ); ?></p>
			<?php endif; ?>

			<p class="package-card__price"><?php echo esc_html( site_blocks_format_package_price( $package ) ); ?></p>

			<?php if ( ! empty( $package['features'] ) ) : ?>
				<ul class="package-card__features">
					<?php foreach ( array_slice( $package['features'], 0, 3 ) as $feature ) : ?>
						<li><?php echo esc_html( $feature ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="package-card__actions">
				<a class="package-card__link" href="<?php echo esc_url( $package['permalink'] ); ?>">
					<?php esc_html_e( 'View details', 'site-blocks' ); ?>
				</a>
				<a class="package-card__cta" href="<?php echo esc_url( $package['cta_url'] ); ?>">
					<?php echo esc_html( $package['cta_label'] ); ?>
				</a>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Query published security packages.
 *
 * @param array<string, mixed> $args Optional query overrides.
 * @return WP_Query
 */
function site_blocks_query_packages( array $args = array() ): WP_Query {
	$defaults = array(
		'post_type'      => 'security_package',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	return new WP_Query( array_merge( $defaults, $args ) );
}
