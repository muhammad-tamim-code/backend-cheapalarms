<?php
/**
 * Security package custom fields (register_post_meta + admin UI).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register post meta for security packages.
 */
function site_blocks_register_package_meta(): void {
	$fields = array(
		'package_price_from' => array(
			'type'              => 'string',
			'description'       => __( 'Starting price (numbers only, e.g. 899).', 'site-blocks' ),
			'sanitize_callback' => 'sanitize_text_field',
		),
		'package_price_prefix' => array(
			'type'              => 'string',
			'description'       => __( 'Price prefix label.', 'site-blocks' ),
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'From',
		),
		'package_tagline' => array(
			'type'              => 'string',
			'description'       => __( 'Short one-line summary.', 'site-blocks' ),
			'sanitize_callback' => 'sanitize_text_field',
		),
		'package_features' => array(
			'type'              => 'string',
			'description'       => __( 'Feature list, one per line.', 'site-blocks' ),
			'sanitize_callback' => 'site_blocks_sanitize_features',
		),
		'package_cta_label' => array(
			'type'              => 'string',
			'description'       => __( 'CTA button label.', 'site-blocks' ),
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'Get a quote',
		),
		'package_cta_url' => array(
			'type'              => 'string',
			'description'       => __( 'CTA button URL.', 'site-blocks' ),
			'sanitize_callback' => 'esc_url_raw',
		),
	);

	foreach ( $fields as $key => $args ) {
		register_post_meta(
			'security_package',
			$key,
			array_merge(
				array(
					'single'       => true,
					'show_in_rest' => true,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				),
				$args
			)
		);
	}
}
add_action( 'init', 'site_blocks_register_package_meta' );

/**
 * Sanitize newline-separated features.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function site_blocks_sanitize_features( $value ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$lines = array_filter(
		array_map(
			static function ( string $line ): string {
				return sanitize_text_field( $line );
			},
			explode( "\n", str_replace( array( "\r\n", "\r" ), "\n", $value ) )
		)
	);

	return implode( "\n", $lines );
}

/**
 * Add meta box to package edit screen.
 */
function site_blocks_add_package_meta_box(): void {
	add_meta_box(
		'site-blocks-package-details',
		__( 'Package Details', 'site-blocks' ),
		'site_blocks_render_package_meta_box',
		'security_package',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'site_blocks_add_package_meta_box' );

/**
 * Render package meta box fields.
 *
 * @param WP_Post $post Current post.
 */
function site_blocks_render_package_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'site_blocks_save_package_meta', 'site_blocks_package_meta_nonce' );

	$price_from    = get_post_meta( $post->ID, 'package_price_from', true );
	$price_prefix  = get_post_meta( $post->ID, 'package_price_prefix', true ) ?: 'From';
	$tagline       = get_post_meta( $post->ID, 'package_tagline', true );
	$features      = get_post_meta( $post->ID, 'package_features', true );
	$cta_label     = get_post_meta( $post->ID, 'package_cta_label', true ) ?: 'Get a quote';
	$cta_url       = get_post_meta( $post->ID, 'package_cta_url', true );
	$contact_url   = get_permalink( get_page_by_path( 'contact' ) ) ?: home_url( '/contact/' );
	?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><label for="package_tagline"><?php esc_html_e( 'Tagline', 'site-blocks' ); ?></label></th>
				<td>
					<input type="text" id="package_tagline" name="package_tagline" class="large-text" value="<?php echo esc_attr( (string) $tagline ); ?>" placeholder="<?php esc_attr_e( 'e.g. Complete 4-camera coverage for small businesses', 'site-blocks' ); ?>">
					<p class="description"><?php esc_html_e( 'One-line summary shown on the package card.', 'site-blocks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="package_price_prefix"><?php esc_html_e( 'Price label', 'site-blocks' ); ?></label></th>
				<td>
					<input type="text" id="package_price_prefix" name="package_price_prefix" class="regular-text" value="<?php echo esc_attr( (string) $price_prefix ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="package_price_from"><?php esc_html_e( 'Price from ($)', 'site-blocks' ); ?></label></th>
				<td>
					<input type="text" id="package_price_from" name="package_price_from" class="regular-text" value="<?php echo esc_attr( (string) $price_from ); ?>" placeholder="899" inputmode="decimal">
					<p class="description"><?php esc_html_e( 'Numbers only. Leave blank if pricing is quote-only.', 'site-blocks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="package_features"><?php esc_html_e( 'Features', 'site-blocks' ); ?></label></th>
				<td>
					<textarea id="package_features" name="package_features" class="large-text" rows="8" placeholder="<?php esc_attr_e( "4K night-vision cameras\n90-day cloud storage\nProfessional installation", 'site-blocks' ); ?>"><?php echo esc_textarea( (string) $features ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One feature per line. Shown as a bullet list on the package page.', 'site-blocks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="package_cta_label"><?php esc_html_e( 'Button label', 'site-blocks' ); ?></label></th>
				<td>
					<input type="text" id="package_cta_label" name="package_cta_label" class="regular-text" value="<?php echo esc_attr( (string) $cta_label ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="package_cta_url"><?php esc_html_e( 'Button URL', 'site-blocks' ); ?></label></th>
				<td>
					<input type="url" id="package_cta_url" name="package_cta_url" class="large-text" value="<?php echo esc_attr( (string) $cta_url ); ?>" placeholder="<?php echo esc_attr( (string) $contact_url ); ?>">
					<p class="description"><?php esc_html_e( 'Defaults to your Contact page if left empty.', 'site-blocks' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/**
 * Save package meta box values.
 *
 * @param int $post_id Post ID.
 */
function site_blocks_save_package_meta( int $post_id ): void {
	if ( ! isset( $_POST['site_blocks_package_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['site_blocks_package_meta_nonce'] ) ), 'site_blocks_save_package_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'security_package' !== get_post_type( $post_id ) ) {
		return;
	}

	$text_fields = array(
		'package_price_from',
		'package_price_prefix',
		'package_tagline',
		'package_cta_label',
	);

	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	if ( isset( $_POST['package_features'] ) ) {
		update_post_meta( $post_id, 'package_features', site_blocks_sanitize_features( wp_unslash( $_POST['package_features'] ) ) );
	}

	if ( isset( $_POST['package_cta_url'] ) ) {
		update_post_meta( $post_id, 'package_cta_url', esc_url_raw( wp_unslash( $_POST['package_cta_url'] ) ) );
	}
}
add_action( 'save_post_security_package', 'site_blocks_save_package_meta' );
