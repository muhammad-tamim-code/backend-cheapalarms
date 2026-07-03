<?php
/**
 * Shared Safeguard site chrome (utility bar + header).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Primary service nav links.
 *
 * @return array<string, string> Path => label.
 */
function site_blocks_get_safeguard_nav_services(): array {
	return array(
		'/alarm-systems/'         => __( 'Alarm Systems', 'site-blocks' ),
		'/cctv-security-cameras/' => __( 'CCTV & Cameras', 'site-blocks' ),
		'/access-control/'        => __( 'Access Control', 'site-blocks' ),
		'/intercom-systems/'      => __( 'Intercom Systems', 'site-blocks' ),
	);
}

/**
 * Shared contact and CTA URLs.
 *
 * @return array<string, string>
 */
function site_blocks_get_safeguard_contact(): array {
	return array(
		'phone'     => '1300 225 276',
		'phone_href'=> 'tel:1300225276',
		'quote'     => esc_url( home_url( '/get-an-instant-quote/' ) ),
	);
}

/**
 * Render utility bar and site header.
 */
function site_blocks_render_safeguard_header(): void {
	$logo      = site_blocks_asset_url( 'images/brand/safeguard-logo.png' );
	$logo_mark = site_blocks_asset_url( 'images/brand/safeguard-logo-mark.png' );
	$nav       = site_blocks_get_safeguard_nav_services();
	$contact   = site_blocks_get_safeguard_contact();
	$phone     = $contact['phone'];
	$phone_h   = $contact['phone_href'];
	$quote     = $contact['quote'];
	?>
	<div class="sg-utility" role="complementary" aria-label="<?php esc_attr_e( 'Service information', 'site-blocks' ); ?>">
		<div class="sg-container sg-utility__inner">
			<span><?php esc_html_e( 'Servicing Greater Sydney Metro', 'site-blocks' ); ?></span>
			<span><?php esc_html_e( 'Master Licence No. 000000000', 'site-blocks' ); ?> &nbsp;·&nbsp; <?php esc_html_e( 'ASIAL Member', 'site-blocks' ); ?></span>
		</div>
	</div>

	<header class="sg-header" id="sg-header">
		<div class="sg-container sg-header__inner">
			<a class="sg-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img
					class="sg-header__logo-img sg-header__logo-img--full"
					src="<?php echo esc_url( $logo ); ?>"
					alt="<?php esc_attr_e( 'Safeguard Security Services', 'site-blocks' ); ?>"
					width="766"
					height="210"
					decoding="async"
				/>
				<img
					class="sg-header__logo-img sg-header__logo-img--mark"
					src="<?php echo esc_url( $logo_mark ); ?>"
					alt="<?php esc_attr_e( 'Safeguard Security Services', 'site-blocks' ); ?>"
					width="450"
					height="512"
					decoding="async"
				/>
			</a>

			<nav class="sg-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'site-blocks' ); ?>">
				<?php foreach ( $nav as $path => $label ) : ?>
					<?php
					$is_current = is_page( trim( $path, '/' ) );
					$attrs      = $is_current ? ' aria-current="page"' : '';
					?>
					<a href="<?php echo esc_url( home_url( $path ) ); ?>"<?php echo $attrs; ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<div class="sg-header__actions">
				<a class="sg-header__phone" href="<?php echo esc_attr( $phone_h ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					<?php echo esc_html( $phone ); ?>
				</a>
				<button type="button" class="sg-header__menu-btn" aria-expanded="false" aria-controls="sg-mobile-panel" aria-label="<?php esc_attr_e( 'Open menu', 'site-blocks' ); ?>">
					<span></span><span></span><span></span>
				</button>
				<a class="sg-btn sg-btn--primary sg-header__quote-mobile" href="<?php echo esc_url( $quote ); ?>"><?php esc_html_e( 'Quote', 'site-blocks' ); ?></a>
				<a class="sg-btn sg-btn--primary sg-header__quote" href="<?php echo esc_url( $quote ); ?>"><?php esc_html_e( 'Get an Instant Quote', 'site-blocks' ); ?></a>
			</div>
		</div>

		<div class="sg-mobile-panel" id="sg-mobile-panel" hidden>
			<div class="sg-container">
				<nav aria-label="<?php esc_attr_e( 'Mobile', 'site-blocks' ); ?>">
					<?php foreach ( $nav as $path => $label ) : ?>
						<?php
						$is_current = is_page( trim( $path, '/' ) );
						$attrs      = $is_current ? ' aria-current="page"' : '';
						?>
						<a href="<?php echo esc_url( home_url( $path ) ); ?>"<?php echo $attrs; ?>><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
		</div>
	</header>
	<?php
}

/**
 * Whether the current page uses Safeguard custom chrome.
 */
function site_blocks_uses_safeguard_chrome(): bool {
	return is_front_page()
		|| is_page( 'alarm-systems' )
		|| is_page( 'ajax-alarm-systems' )
		|| is_page( 'cctv-security-cameras' )
		|| is_page( 'access-control' )
		|| is_page( 'intercom-systems' )
		|| is_page( 'ajax-calculator' );
}

/**
 * Suppress Kadence footer markup at render time (after query is ready).
 */
function site_blocks_suppress_kadence_footer(): void {
	if ( ! site_blocks_uses_safeguard_chrome() ) {
		return;
	}

	remove_action( 'kadence_footer', 'Kadence\footer_markup', 10 );
}
add_action( 'kadence_footer', 'site_blocks_suppress_kadence_footer', 1 );

/**
 * Suppress Kadence header markup at render time.
 */
function site_blocks_suppress_kadence_header(): void {
	if ( ! site_blocks_uses_safeguard_chrome() ) {
		return;
	}

	remove_action( 'kadence_header', 'Kadence\header_markup', 10 );
}
add_action( 'kadence_header', 'site_blocks_suppress_kadence_header', 1 );

/**
 * Replace Kadence header/footer with Safeguard chrome on service pages.
 */
function site_blocks_remove_kadence_theme_chrome(): void {
	if ( ! site_blocks_uses_safeguard_chrome() ) {
		return;
	}

	remove_action( 'kadence_header', 'Kadence\header_markup' );
	remove_action( 'kadence_footer', 'Kadence\footer_markup' );
}
add_action( 'wp', 'site_blocks_remove_kadence_theme_chrome' );

/**
 * Kadence layout: fullwidth shell, theme header/footer off (Safeguard chrome in content).
 *
 * @param array<string, string> $layout Layout settings.
 * @return array<string, string>
 */
function site_blocks_safeguard_kadence_layout( array $layout ): array {
	if ( ! site_blocks_uses_safeguard_chrome() ) {
		return $layout;
	}

	$layout['layout']  = 'fullwidth';
	$layout['boxed']   = 'unboxed';
	$layout['title']   = 'hide';
	$layout['sidebar'] = 'disable';
	$layout['header']  = 'disable';
	$layout['footer']  = 'disable';

	return $layout;
}
add_filter( 'kadence_post_layout', 'site_blocks_safeguard_kadence_layout' );
