<?php
/**
 * Shared dark stage hero (arched frame + optional phone).
 *
 * Used by Electronic Security product siblings (CCTV, Alarms, Ajax, Access, Intercom).
 * Hub pages keep a different layout.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render an image path under assets/, or a media placeholder when missing.
 *
 * @param string $relative_path Path under assets/, e.g. images/cctv/hero.webp. Empty = placeholder.
 * @param string $alt           Alt text.
 * @param string $class         Img / placeholder class.
 * @param string $loading       loading attribute.
 */
function site_blocks_stage_hero_media( string $relative_path, string $alt, string $class, string $loading = 'eager' ): void {
	$relative_path = ltrim( $relative_path, '/' );

	if ( '' === $relative_path ) {
		printf( '<span class="sg-cctv-media-placeholder %s" aria-hidden="true"></span>', esc_attr( $class ) );
		return;
	}

	if ( function_exists( 'site_blocks_print_managed_image' ) && site_blocks_print_managed_image( $relative_path, $alt, $class, $loading ) ) {
		return;
	}

	$disk = SITE_BLOCKS_DIR . 'assets/' . $relative_path;
	if ( is_readable( $disk ) ) {
		$dims = array( 'width' => 0, 'height' => 0 );
		if ( function_exists( 'getimagesize' ) ) {
			$size = getimagesize( $disk );
			if ( is_array( $size ) && isset( $size[0], $size[1] ) ) {
				$dims = array( 'width' => (int) $size[0], 'height' => (int) $size[1] );
			}
		}

		printf(
			'<img class="%s" src="%s" alt="%s" loading="%s" decoding="async"%s%s />',
			esc_attr( $class ),
			esc_url( SITE_BLOCKS_URL . 'assets/' . $relative_path ),
			esc_attr( $alt ),
			esc_attr( $loading ),
			$dims['width'] > 0 ? ' width="' . (int) $dims['width'] . '"' : '',
			$dims['height'] > 0 ? ' height="' . (int) $dims['height'] . '"' : ''
		);
		return;
	}

	printf( '<span class="sg-cctv-media-placeholder %s" aria-hidden="true"></span>', esc_attr( $class ) );
}

/**
 * Render the shared Electronic Security product stage hero.
 *
 * @param array{
 *   id: string,
 *   class?: string,
 *   breadcrumb?: array<int, array{label: string, url?: string, current?: bool}>,
 *   eyebrow: string,
 *   title: string,
 *   lead: string,
 *   primary_label?: string,
 *   primary_url?: string,
 *   secondary_label?: string,
 *   secondary_url?: string,
 *   trust?: array<int, array{icon: string, label: string}>,
 *   frame_image?: string,
 *   frame_alt?: string,
 *   phone_image?: string,
 *   phone_alt?: string,
 * } $args Hero content.
 */
function site_blocks_render_stage_hero( array $args ): void {
	require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

	$id         = (string) ( $args['id'] ?? 'sg-stage-hero-heading' );
	$class      = trim( 'sg-cctv-stage-hero sg-stage-hero alignfull ' . (string) ( $args['class'] ?? '' ) );
	$eyebrow    = (string) ( $args['eyebrow'] ?? '' );
	$title      = (string) ( $args['title'] ?? '' );
	$lead       = (string) ( $args['lead'] ?? '' );
	$primary_l  = (string) ( $args['primary_label'] ?? __( 'Start My Quote', 'site-blocks' ) );
	$primary_u  = (string) ( $args['primary_url'] ?? home_url( '/get-an-instant-quote/' ) );
	$secondary_l = (string) ( $args['secondary_label'] ?? __( 'Help Me Choose', 'site-blocks' ) );
	$secondary_u = (string) ( $args['secondary_url'] ?? home_url( '/design-my-solution/' ) );
	$frame_image = (string) ( $args['frame_image'] ?? '' );
	$frame_alt   = (string) ( $args['frame_alt'] ?? '' );
	$phone_image = (string) ( $args['phone_image'] ?? '' );
	$phone_alt   = (string) ( $args['phone_alt'] ?? '' );

	$breadcrumb = isset( $args['breadcrumb'] ) && is_array( $args['breadcrumb'] ) ? $args['breadcrumb'] : array();
	$trust      = isset( $args['trust'] ) && is_array( $args['trust'] ) ? $args['trust'] : array(
		array(
			'icon'  => 'award',
			'label' => __( 'Licensed Installers', 'site-blocks' ),
		),
		array(
			'icon'  => 'house',
			'label' => __( 'Residential & Commercial', 'site-blocks' ),
		),
		array(
			'icon'  => 'shield',
			'label' => __( 'ASIAL Member', 'site-blocks' ),
		),
	);
	?>
<section class="<?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<div class="sg-cctv-stage-hero__bg" aria-hidden="true">
		<span class="sg-cctv-stage-hero__base"></span>
		<span class="sg-cctv-stage-hero__gradient"></span>
		<span class="sg-cctv-stage-hero__blueprint"></span>
		<span class="sg-cctv-stage-hero__noise"></span>
		<span class="sg-cctv-stage-hero__vignette"></span>
	</div>

	<div class="sg-cctv-stage-hero__inner">
		<div class="sg-cctv-stage-hero__copy">
			<?php if ( $breadcrumb !== array() ) : ?>
				<nav class="sg-cctv-stage-hero__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
					<?php
					$i     = 0;
					$count = count( $breadcrumb );
					foreach ( $breadcrumb as $crumb ) :
						++$i;
						$label   = (string) ( $crumb['label'] ?? '' );
						$url     = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
						$current = ! empty( $crumb['current'] );
						?>
						<?php if ( $current || '' === $url ) : ?>
							<span<?php echo $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></span>
						<?php else : ?>
							<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
						<?php endif; ?>
						<?php if ( $i < $count ) : ?>
							<span aria-hidden="true">/</span>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php if ( '' !== $eyebrow ) : ?>
				<p class="sg-cctv-stage-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h1 id="<?php echo esc_attr( $id ); ?>" class="sg-cctv-stage-hero__title">
				<?php echo esc_html( $title ); ?>
			</h1>

			<?php if ( '' !== $lead ) : ?>
				<p class="sg-cctv-stage-hero__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>

			<div class="sg-cctv-stage-hero__ctas">
				<a class="sg-btn sg-btn--soft-orange" href="<?php echo esc_url( $primary_u ); ?>">
					<?php echo esc_html( $primary_l ); ?>
				</a>
				<a class="sg-btn sg-btn--ghost" href="<?php echo esc_url( $secondary_u ); ?>">
					<?php echo esc_html( $secondary_l ); ?>
				</a>
			</div>

			<?php if ( $trust !== array() ) : ?>
				<ul class="sg-cctv-stage-hero__trust" role="list">
					<?php foreach ( $trust as $item ) : ?>
						<li>
							<span class="sg-cctv-stage-hero__trust-icon" aria-hidden="true">
								<?php site_blocks_lucide_icon( (string) ( $item['icon'] ?? 'shield' ), 16 ); ?>
							</span>
							<span><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="sg-cctv-stage-hero__visual">
			<figure class="sg-cctv-stage-hero__frame">
				<div class="sg-cctv-stage-hero__frame-inner">
					<?php
					site_blocks_stage_hero_media(
						$frame_image,
						$frame_alt,
						'sg-cctv-stage-hero__house',
						'eager'
					);
					?>
				</div>
			</figure>

			<?php if ( '' !== $phone_image ) : ?>
				<div class="sg-cctv-stage-hero__phone">
					<?php
					site_blocks_stage_hero_media(
						$phone_image,
						$phone_alt,
						'sg-cctv-stage-hero__phone-img',
						'eager'
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
	<?php
}
