<?php
/**
 * Safeguard marketing media → WordPress Media Library.
 *
 * Config still references paths under assets/images/…. After import, those
 * paths resolve to attachment IDs. Plugin files remain as fallback until import.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SITE_BLOCKS_MEDIA_MAP_OPTION = 'site_blocks_media_map';

/**
 * @return array<string, int> Relative assets path => attachment ID.
 */
function site_blocks_media_map(): array {
	$map = get_option( SITE_BLOCKS_MEDIA_MAP_OPTION, array() );

	if ( ! is_array( $map ) ) {
		return array();
	}

	$out = array();
	foreach ( $map as $path => $id ) {
		$path = ltrim( str_replace( '\\', '/', (string) $path ), '/' );
		$id   = (int) $id;
		if ( '' !== $path && $id > 0 ) {
			$out[ $path ] = $id;
		}
	}

	return $out;
}

/**
 * Persist media map.
 *
 * @param array<string, int> $map Map.
 */
function site_blocks_media_map_save( array $map ): void {
	update_option( SITE_BLOCKS_MEDIA_MAP_OPTION, $map, false );
}

/**
 * Attachment ID for an assets-relative image path, if imported.
 */
function site_blocks_media_attachment_id( string $relative_path ): int {
	$relative_path = site_blocks_resolve_asset_path( ltrim( str_replace( '\\', '/', $relative_path ), '/' ) );
	$map           = site_blocks_media_map();

	if ( isset( $map[ $relative_path ] ) ) {
		$id = (int) $map[ $relative_path ];
		if ( $id > 0 && get_post_type( $id ) === 'attachment' ) {
			return $id;
		}
	}

	return 0;
}

/**
 * Public URL from Media Library, or empty string if not mapped.
 */
function site_blocks_media_library_url( string $relative_path ): string {
	$id = site_blocks_media_attachment_id( $relative_path );
	if ( $id <= 0 ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $id, 'full' );

	return is_string( $url ) ? $url : '';
}

/**
 * Whether an assets image exists in Media Library or on disk in the plugin.
 */
function site_blocks_media_source_exists( string $relative_path ): bool {
	$relative_path = site_blocks_resolve_asset_path( ltrim( str_replace( '\\', '/', $relative_path ), '/' ) );

	if ( site_blocks_media_attachment_id( $relative_path ) > 0 ) {
		return true;
	}

	return is_readable( SITE_BLOCKS_DIR . 'assets/' . $relative_path );
}

/**
 * Print an img tag: Media Library (with srcset) preferred, else plugin asset URL.
 *
 * @return bool True when an image was printed.
 */
function site_blocks_print_managed_image( string $relative_path, string $alt, string $class, string $loading = 'lazy' ): bool {
	$relative_path = site_blocks_resolve_asset_path( ltrim( str_replace( '\\', '/', $relative_path ), '/' ) );
	$id            = site_blocks_media_attachment_id( $relative_path );

	if ( $id > 0 ) {
		$attr = array(
			'class'    => $class,
			'loading'  => $loading,
			'decoding' => 'async',
			'alt'      => $alt,
		);
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image escapes.
		echo wp_get_attachment_image( $id, 'full', false, $attr );
		return true;
	}

	$disk = SITE_BLOCKS_DIR . 'assets/' . $relative_path;
	if ( ! is_readable( $disk ) ) {
		return false;
	}

	printf(
		'<img class="%s" src="%s" alt="%s" loading="%s" decoding="async" />',
		esc_attr( $class ),
		esc_url( SITE_BLOCKS_URL . 'assets/' . implode( '/', array_map( 'rawurlencode', explode( '/', $relative_path ) ) ) ),
		esc_attr( $alt ),
		esc_attr( $loading )
	);

	return true;
}

/**
 * Collect image files under assets/images for import.
 *
 * @return array<int, string> Relative paths under assets/.
 */
function site_blocks_media_discover_files(): array {
	$root = SITE_BLOCKS_DIR . 'assets/images';
	if ( ! is_dir( $root ) ) {
		return array();
	}

	$out  = array();
	$iter = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
	);

	foreach ( $iter as $file ) {
		if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
			continue;
		}

		$ext = strtolower( $file->getExtension() );
		if ( ! in_array( $ext, array( 'webp', 'png', 'jpg', 'jpeg', 'gif', 'svg' ), true ) ) {
			continue;
		}

		$full = str_replace( '\\', '/', $file->getPathname() );
		$base = str_replace( '\\', '/', SITE_BLOCKS_DIR . 'assets/' );
		if ( 0 !== strpos( $full, $base ) ) {
			continue;
		}

		$out[] = substr( $full, strlen( $base ) );
	}

	sort( $out );

	return $out;
}

/**
 * Import one plugin image into the Media Library.
 *
 * @return array{ok: bool, id?: int, path: string, skipped?: bool, error?: string}
 */
function site_blocks_media_import_one( string $relative_path, array &$map ): array {
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );
	$disk          = SITE_BLOCKS_DIR . 'assets/' . $relative_path;

	if ( ! is_readable( $disk ) ) {
		return array(
			'ok'    => false,
			'path'  => $relative_path,
			'error' => 'missing_file',
		);
	}

	if ( isset( $map[ $relative_path ] ) ) {
		$existing = (int) $map[ $relative_path ];
		if ( $existing > 0 && get_post_type( $existing ) === 'attachment' ) {
			return array(
				'ok'      => true,
				'id'      => $existing,
				'path'    => $relative_path,
				'skipped' => true,
			);
		}
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = wp_basename( $relative_path );
	$tmp      = wp_tempnam( $filename );
	if ( ! $tmp || ! copy( $disk, $tmp ) ) {
		return array(
			'ok'    => false,
			'path'  => $relative_path,
			'error' => 'temp_copy_failed',
		);
	}

	$file_array = array(
		'name'     => $filename,
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload(
		$file_array,
		0,
		null,
		array(
			'post_title' => 'Safeguard: ' . $relative_path,
			'post_content' => '',
			'post_excerpt' => '',
		)
	);

	if ( is_wp_error( $attachment_id ) ) {
		if ( is_string( $tmp ) && file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}

		return array(
			'ok'    => false,
			'path'  => $relative_path,
			'error' => $attachment_id->get_error_message(),
		);
	}

	$id = (int) $attachment_id;
	update_post_meta( $id, '_site_blocks_asset_path', $relative_path );
	$map[ $relative_path ] = $id;

	// Also map resolved webp key when importing a png that has a webp sibling already mapped separately.
	$resolved = site_blocks_resolve_asset_path( $relative_path );
	if ( $resolved !== $relative_path && ! isset( $map[ $resolved ] ) ) {
		$map[ $resolved ] = $id;
	}

	return array(
		'ok'   => true,
		'id'   => $id,
		'path' => $relative_path,
	);
}

/**
 * Import all discovered marketing images.
 *
 * @return array{imported: int, skipped: int, failed: int, total: int, errors: array<int, string>}
 */
function site_blocks_media_import_all(): array {
	if ( ! current_user_can( 'upload_files' ) ) {
		return array(
			'imported' => 0,
			'skipped'  => 0,
			'failed'   => 1,
			'total'    => 0,
			'errors'   => array( 'capability' ),
		);
	}

	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$files    = site_blocks_media_discover_files();
	$map      = site_blocks_media_map();
	$imported = 0;
	$skipped  = 0;
	$failed   = 0;
	$errors   = array();

	foreach ( $files as $path ) {
		$result = site_blocks_media_import_one( $path, $map );
		if ( ! empty( $result['skipped'] ) ) {
			++$skipped;
			continue;
		}
		if ( ! empty( $result['ok'] ) ) {
			++$imported;
			continue;
		}
		++$failed;
		$errors[] = $path . ': ' . (string) ( $result['error'] ?? 'unknown' );
	}

	site_blocks_media_map_save( $map );

	return array(
		'imported' => $imported,
		'skipped'  => $skipped,
		'failed'   => $failed,
		'total'    => count( $files ),
		'errors'   => array_slice( $errors, 0, 20 ),
	);
}

/**
 * Register Tools → Safeguard Media admin page.
 */
function site_blocks_media_admin_menu(): void {
	add_management_page(
		__( 'Safeguard Media', 'site-blocks' ),
		__( 'Safeguard Media', 'site-blocks' ),
		'manage_options',
		'site-blocks-media',
		'site_blocks_media_admin_page'
	);
}
add_action( 'admin_menu', 'site_blocks_media_admin_menu' );

/**
 * Handle import form POST.
 */
function site_blocks_media_admin_handle_post(): void {
	if ( ! isset( $_POST['site_blocks_media_import'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'site_blocks_media_import' );

	$result = site_blocks_media_import_all();
	set_transient(
		'site_blocks_media_import_result',
		$result,
		MINUTE_IN_SECONDS * 5
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'site-blocks-media',
				'imported' => '1',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'site_blocks_media_admin_handle_post' );

/**
 * Admin UI.
 */
function site_blocks_media_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$files   = site_blocks_media_discover_files();
	$map     = site_blocks_media_map();
	$mapped  = 0;
	foreach ( $files as $path ) {
		if ( isset( $map[ $path ] ) && get_post_type( (int) $map[ $path ] ) === 'attachment' ) {
			++$mapped;
		}
	}

	$result = get_transient( 'site_blocks_media_import_result' );
	if ( false !== $result ) {
		delete_transient( 'site_blocks_media_import_result' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Safeguard Media Import', 'site-blocks' ); ?></h1>
		<p>
			<?php esc_html_e( 'Copies marketing images from the site-blocks plugin into the WordPress Media Library and links each page slot to the right attachment. Safe to run more than once (already-imported files are skipped).', 'site-blocks' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'On disk in plugin:', 'site-blocks' ); ?></strong>
			<?php echo esc_html( (string) count( $files ) ); ?>
			&nbsp;·&nbsp;
			<strong><?php esc_html_e( 'Mapped to Media Library:', 'site-blocks' ); ?></strong>
			<?php echo esc_html( (string) $mapped ); ?>
		</p>

		<?php if ( is_array( $result ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: imported count, 2: skipped, 3: failed, 4: total */
						esc_html__( 'Import finished. Imported %1$d, skipped %2$d, failed %3$d (of %4$d files).', 'site-blocks' ),
						(int) ( $result['imported'] ?? 0 ),
						(int) ( $result['skipped'] ?? 0 ),
						(int) ( $result['failed'] ?? 0 ),
						(int) ( $result['total'] ?? 0 )
					);
					?>
				</p>
				<?php if ( ! empty( $result['errors'] ) && is_array( $result['errors'] ) ) : ?>
					<ul>
						<?php foreach ( $result['errors'] as $err ) : ?>
							<li><?php echo esc_html( (string) $err ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'site_blocks_media_import' ); ?>
			<?php submit_button( __( 'Import plugin images into Media Library', 'site-blocks' ), 'primary', 'site_blocks_media_import' ); ?>
		</form>

		<p class="description">
			<?php esc_html_e( 'Until you run this, pages still load images from the plugin folder (fallback). After a successful import, pages prefer Media Library URLs. Do not delete plugin images until you have verified staging.', 'site-blocks' ); ?>
		</p>
	</div>
	<?php
}

/**
 * WP-CLI: wp site-blocks import-media
 */
function site_blocks_media_register_cli(): void {
	if ( ! class_exists( 'WP_CLI' ) ) {
		return;
	}

	WP_CLI::add_command(
		'site-blocks-import-media',
		static function (): void {
			$result = site_blocks_media_import_all();
			WP_CLI::success(
				sprintf(
					'Imported %d, skipped %d, failed %d (of %d).',
					(int) $result['imported'],
					(int) $result['skipped'],
					(int) $result['failed'],
					(int) $result['total']
				)
			);
			foreach ( $result['errors'] as $err ) {
				WP_CLI::warning( (string) $err );
			}
		}
	);
}
add_action( 'cli_init', 'site_blocks_media_register_cli' );
