<?php
/**
 * Package type taxonomy archive.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term  = get_queried_object();
$types = get_terms(
	array(
		'taxonomy'   => 'package_type',
		'hide_empty' => false,
	)
);
?>
<main id="main" class="packages-archive" aria-labelledby="packages-archive-heading">
	<header class="packages-archive__hero">
		<p class="packages-archive__eyebrow"><?php esc_html_e( 'Security Packages', 'site-blocks' ); ?></p>
		<h1 id="packages-archive-heading" class="packages-archive__title">
			<?php echo esc_html( $term instanceof WP_Term ? $term->name : __( 'Packages', 'site-blocks' ) ); ?>
		</h1>
		<?php if ( $term instanceof WP_Term && $term->description ) : ?>
			<p class="packages-archive__intro"><?php echo esc_html( $term->description ); ?></p>
		<?php else : ?>
			<p class="packages-archive__intro"><?php esc_html_e( 'Browse installation packages in this category.', 'site-blocks' ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( ! is_wp_error( $types ) && ! empty( $types ) ) : ?>
		<nav class="packages-archive__filters" aria-label="<?php esc_attr_e( 'Filter by package type', 'site-blocks' ); ?>">
			<a class="packages-archive__filter" href="<?php echo esc_url( get_post_type_archive_link( 'security_package' ) ); ?>">
				<?php esc_html_e( 'All', 'site-blocks' ); ?>
			</a>
			<?php foreach ( $types as $type ) : ?>
				<?php $is_active = ( $term instanceof WP_Term && $type->term_id === $term->term_id ); ?>
				<a class="packages-archive__filter<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $type ) ); ?>">
					<?php echo esc_html( $type->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="packages-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				site_blocks_render_package_card( get_the_ID() );
			endwhile;
			?>
		</div>
	<?php else : ?>
		<section class="packages-empty">
			<h2 class="packages-empty__title"><?php esc_html_e( 'No packages in this category yet.', 'site-blocks' ); ?></h2>
			<p class="packages-empty__text"><?php esc_html_e( 'Add a package and assign it to this type in the editor sidebar.', 'site-blocks' ); ?></p>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
