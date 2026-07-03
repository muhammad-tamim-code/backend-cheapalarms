<?php
/**
 * Package Grid block render.
 *
 * @package Site_Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading      = isset( $attributes['heading'] ) ? $attributes['heading'] : __( 'Security packages', 'site-blocks' );
$intro        = isset( $attributes['intro'] ) ? $attributes['intro'] : '';
$package_type = isset( $attributes['packageType'] ) ? $attributes['packageType'] : '';
$limit        = isset( $attributes['limit'] ) ? (int) $attributes['limit'] : 0;

$query_args = array();

if ( $package_type ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'package_type',
			'field'    => 'slug',
			'terms'    => sanitize_title( $package_type ),
		),
	);
}

if ( $limit > 0 ) {
	$query_args['posts_per_page'] = $limit;
}

$packages = site_blocks_query_packages( $query_args );
?>
<section class="package-grid-block" aria-labelledby="package-grid-heading">
	<header class="package-grid-block__header">
		<h2 id="package-grid-heading" class="package-grid-block__title"><?php echo esc_html( $heading ); ?></h2>
		<?php if ( $intro ) : ?>
			<p class="package-grid-block__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( $packages->have_posts() ) : ?>
		<div class="packages-grid">
			<?php
			while ( $packages->have_posts() ) :
				$packages->the_post();
				site_blocks_render_package_card( get_the_ID() );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<p class="package-grid-block__archive-link">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'security_package' ) ); ?>">
				<?php esc_html_e( 'View all packages →', 'site-blocks' ); ?>
			</a>
		</p>
	<?php else : ?>
		<p class="package-grid-block__empty">
			<?php esc_html_e( 'No packages yet. Add one under Security Packages in the admin.', 'site-blocks' ); ?>
		</p>
	<?php endif; ?>
</section>
