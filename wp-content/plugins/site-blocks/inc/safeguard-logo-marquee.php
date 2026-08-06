<?php
/**
 * Reusable sliding logo strip (portfolio / partner brands).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-portfolio-brands.php';

/**
 * Enqueue logo marquee styles once per request.
 */
function site_blocks_enqueue_logo_marquee_assets(): void {
	static $enqueued = false;

	if ( $enqueued ) {
		return;
	}

	$enqueued = true;

	wp_enqueue_style(
		'safeguard-logo-marquee',
		SITE_BLOCKS_URL . 'assets/css/safeguard-logo-marquee.css',
		array(),
		SITE_BLOCKS_VERSION
	);
}

/**
 * Brands for a marquee strip.
 *
 * @param string $source portfolio|partners
 * @return array<int, array<string, mixed>>
 */
function site_blocks_get_logo_marquee_brands( string $source = 'portfolio' ): array {
	if ( $source === 'partners' ) {
		/**
		 * Official partnership / supplier logos (e.g. Ajax). Add via filter or extend here.
		 *
		 * @param array<int, array<string, mixed>> $brands
		 */
		$brands = apply_filters( 'site_blocks_partner_marquee_brands', array() );

		return is_array( $brands ) ? $brands : array();
	}

	$brands = site_blocks_get_portfolio_brands( true );

	/**
	 * Portfolio / client brands Safeguard has worked with.
	 *
	 * @param array<int, array<string, mixed>> $brands
	 */
	return apply_filters( 'site_blocks_portfolio_marquee_brands', $brands );
}

/**
 * Render infinite-scrolling logo marquee.
 *
 * @param array<string, mixed> $args {
 *     @type string               $title         Section heading.
 *     @type string               $subtitle      Optional line under heading.
 *     @type string               $source        portfolio|partners
 *     @type array<int, mixed>|null $brands      Override brand list.
 *     @type string               $variant       light|wash
 *     @type bool                 $show_heading  Show title block.
 *     @type string               $class         Extra section classes.
 *     @type string               $heading_id    Optional id for aria-labelledby.
 * }
 */
function site_blocks_render_logo_marquee( array $args = array() ): void {
	$defaults = array(
		'title'        => __( 'Clients Portfolio', 'site-blocks' ),
		'subtitle'     => '',
		'source'       => 'portfolio',
		'brands'       => null,
		'variant'      => 'wash',
		'show_heading' => true,
		'class'        => '',
		'heading_id'   => 'sg-logo-marquee-heading',
	);

	$args = wp_parse_args( $args, $defaults );

	site_blocks_enqueue_logo_marquee_assets();

	$brands = is_array( $args['brands'] ) ? $args['brands'] : site_blocks_get_logo_marquee_brands( (string) $args['source'] );

	$visible = array_values(
		array_filter(
			$brands,
			static function ( $brand ): bool {
				return is_array( $brand )
					&& ! empty( $brand['url'] )
					&& ! empty( $brand['name'] )
					&& ( ! isset( $brand['verified'] ) || ! empty( $brand['verified'] ) );
			}
		)
	);

	if ( $visible === array() ) {
		return;
	}

	$variant_class = $args['variant'] === 'light' ? 'sg-logo-marquee--light' : 'sg-logo-marquee--wash';
	$extra_class   = is_string( $args['class'] ) && $args['class'] !== '' ? ' ' . $args['class'] : '';
	$heading_id    = sanitize_html_class( (string) $args['heading_id'] );

	$render_items = static function ( array $items, bool $aria_hidden ) use ( $heading_id ): void {
		foreach ( $items as $brand ) {
			$name = (string) ( $brand['name'] ?? '' );
			$url  = (string) ( $brand['url'] ?? '' );
			if ( $name === '' || $url === '' ) {
				continue;
			}
			?>
			<li class="sg-logo-marquee__item"<?php echo $aria_hidden ? ' aria-hidden="true"' : ''; ?>>
				<img
					class="sg-logo-marquee__logo"
					src="<?php echo esc_url( $url ); ?>"
					alt="<?php echo esc_attr( $name ); ?>"
					width="160"
					height="64"
					loading="lazy"
					decoding="async"
				/>
			</li>
			<?php
		}
	};
	?>
	<section
		class="sg-logo-marquee sg-reveal<?php echo esc_attr( $variant_class . $extra_class ); ?>"
		aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
	>
		<?php if ( ! empty( $args['show_heading'] ) ) : ?>
			<div class="sg-container sg-logo-marquee__head">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-logo-marquee__title">
					<?php echo esc_html( (string) $args['title'] ); ?>
				</h2>
				<?php if ( is_string( $args['subtitle'] ) && $args['subtitle'] !== '' ) : ?>
					<p class="sg-logo-marquee__subtitle"><?php echo esc_html( $args['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="sg-logo-marquee__viewport" tabindex="0" role="region" aria-label="<?php echo esc_attr( (string) $args['title'] ); ?>">
			<ul class="sg-logo-marquee__track" role="list">
				<?php $render_items( $visible, false ); ?>
				<?php $render_items( $visible, true ); ?>
			</ul>
		</div>
	</section>
	<?php
}
