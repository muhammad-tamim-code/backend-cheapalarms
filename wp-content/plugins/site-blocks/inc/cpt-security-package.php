<?php
/**
 * Security Package custom post type and taxonomy.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register security_package post type.
 */
function site_blocks_register_security_package_cpt(): void {
	register_post_type(
		'security_package',
		array(
			'labels'              => array(
				'name'               => __( 'Security Packages', 'site-blocks' ),
				'singular_name'      => __( 'Security Package', 'site-blocks' ),
				'add_new'            => __( 'Add Package', 'site-blocks' ),
				'add_new_item'       => __( 'Add New Package', 'site-blocks' ),
				'edit_item'          => __( 'Edit Package', 'site-blocks' ),
				'new_item'           => __( 'New Package', 'site-blocks' ),
				'view_item'          => __( 'View Package', 'site-blocks' ),
				'search_items'       => __( 'Search Packages', 'site-blocks' ),
				'not_found'          => __( 'No packages found.', 'site-blocks' ),
				'not_found_in_trash' => __( 'No packages found in Trash.', 'site-blocks' ),
				'menu_name'          => __( 'Security Packages', 'site-blocks' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-shield-alt',
			'menu_position'       => 26,
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'packages',
				'with_front' => false,
			),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'capability_type'     => 'post',
			'exclude_from_search' => false,
		)
	);

	register_taxonomy(
		'package_type',
		'security_package',
		array(
			'labels'            => array(
				'name'          => __( 'Package Types', 'site-blocks' ),
				'singular_name' => __( 'Package Type', 'site-blocks' ),
				'search_items'  => __( 'Search Types', 'site-blocks' ),
				'all_items'     => __( 'All Types', 'site-blocks' ),
				'edit_item'     => __( 'Edit Type', 'site-blocks' ),
				'update_item'   => __( 'Update Type', 'site-blocks' ),
				'add_new_item'  => __( 'Add New Type', 'site-blocks' ),
				'new_item_name' => __( 'New Type Name', 'site-blocks' ),
				'menu_name'     => __( 'Package Types', 'site-blocks' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				'slug'         => 'package-type',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'show_admin_column' => true,
		)
	);
}
add_action( 'init', 'site_blocks_register_security_package_cpt' );

/**
 * Seed default package type terms on activation.
 */
function site_blocks_seed_package_types(): void {
	$types = array(
		'security-cameras' => __( 'Security Cameras', 'site-blocks' ),
		'alarm-systems'      => __( 'Alarm Systems', 'site-blocks' ),
		'intercom-systems'   => __( 'Intercom Systems', 'site-blocks' ),
	);

	foreach ( $types as $slug => $name ) {
		if ( ! term_exists( $slug, 'package_type' ) ) {
			wp_insert_term( $name, 'package_type', array( 'slug' => $slug ) );
		}
	}
}
