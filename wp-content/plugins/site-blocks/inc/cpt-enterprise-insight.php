<?php
/**
 * Enterprise Insight custom post type and taxonomy.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register enterprise_insight post type and categories.
 */
function site_blocks_register_enterprise_insight_cpt(): void {
	register_post_type(
		'enterprise_insight',
		array(
			'labels'              => array(
				'name'               => __( 'Enterprise Insights', 'site-blocks' ),
				'singular_name'      => __( 'Enterprise Insight', 'site-blocks' ),
				'add_new'            => __( 'Add Insight', 'site-blocks' ),
				'add_new_item'       => __( 'Add New Insight', 'site-blocks' ),
				'edit_item'          => __( 'Edit Insight', 'site-blocks' ),
				'new_item'           => __( 'New Insight', 'site-blocks' ),
				'view_item'          => __( 'View Insight', 'site-blocks' ),
				'search_items'       => __( 'Search Insights', 'site-blocks' ),
				'not_found'          => __( 'No insights found.', 'site-blocks' ),
				'not_found_in_trash' => __( 'No insights found in Trash.', 'site-blocks' ),
				'menu_name'          => __( 'Enterprise Insights', 'site-blocks' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-welcome-write-blog',
			'menu_position'       => 27,
			'has_archive'         => false,
			'rewrite'             => array(
				'slug'       => 'enterprise-solutions',
				'with_front' => false,
			),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'capability_type'     => 'post',
			'exclude_from_search' => false,
		)
	);

	register_taxonomy(
		'enterprise_insight_category',
		'enterprise_insight',
		array(
			'labels'            => array(
				'name'          => __( 'Insight Categories', 'site-blocks' ),
				'singular_name' => __( 'Insight Category', 'site-blocks' ),
				'search_items'  => __( 'Search Categories', 'site-blocks' ),
				'all_items'     => __( 'All Categories', 'site-blocks' ),
				'edit_item'     => __( 'Edit Category', 'site-blocks' ),
				'update_item'   => __( 'Update Category', 'site-blocks' ),
				'add_new_item'  => __( 'Add New Category', 'site-blocks' ),
				'new_item_name' => __( 'New Category Name', 'site-blocks' ),
				'menu_name'     => __( 'Categories', 'site-blocks' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				// Keep off the /enterprise-solutions/{slug}/ post namespace.
				'slug'         => 'enterprise-insight-category',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'show_admin_column' => true,
		)
	);
}
add_action( 'init', 'site_blocks_register_enterprise_insight_cpt' );

/**
 * Seed default insight category terms.
 */
function site_blocks_seed_enterprise_insight_categories(): void {
	if ( ! taxonomy_exists( 'enterprise_insight_category' ) ) {
		return;
	}

	require_once SITE_BLOCKS_DIR . 'inc/enterprise-config.php';

	foreach ( site_blocks_enterprise_insight_category_terms() as $slug => $label ) {
		if ( ! term_exists( $slug, 'enterprise_insight_category' ) ) {
			wp_insert_term( $label, 'enterprise_insight_category', array( 'slug' => $slug ) );
		}
	}
}
