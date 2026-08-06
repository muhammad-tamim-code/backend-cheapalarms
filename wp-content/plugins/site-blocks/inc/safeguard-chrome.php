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
 * Primary service nav, flat map (legacy / footer).
 *
 * @return array<string, string> Path => label.
 */
function site_blocks_get_safeguard_nav_services(): array {
	$flat = array();
	foreach ( site_blocks_get_safeguard_nav_items() as $item ) {
		if ( ( $item['type'] ?? '' ) === 'group' ) {
			$flat[ $item['path'] ] = $item['label'];
			foreach ( $item['children'] ?? array() as $child ) {
				$flat[ $child['path'] ] = $child['label'];
			}
		} else {
			$flat[ $item['path'] ] = $item['label'];
		}
	}

	return $flat;
}

/**
 * Structured primary nav — silo hubs with spoke dropdowns.
 *
 * IA (hub → spokes). Product pages under Electronic are top-level URLs
 * but belong to that silo in nav. Monitoring is its own silo.
 *
 * @return array<int, array<string, mixed>>
 */
function site_blocks_get_safeguard_nav_items(): array {
	return array(
		array(
			'type'     => 'group',
			'label'    => __( 'Physical Security', 'site-blocks' ),
			'short'    => __( 'Physical', 'site-blocks' ),
			'path'     => '/physical-security/',
			'children' => array(
				array(
					'path'  => '/physical-security/static-guards/',
					'label' => __( 'Static Security Guards', 'site-blocks' ),
				),
				array(
					'path'  => '/physical-security/mobile-patrols/',
					'label' => __( 'Mobile Patrols', 'site-blocks' ),
				),
			),
		),
		array(
			'path'  => '/manpower/',
			'label' => __( 'ManPower', 'site-blocks' ),
			'short' => __( 'ManPower', 'site-blocks' ),
		),
		array(
			'type'     => 'group',
			'label'    => __( 'Electronic Security', 'site-blocks' ),
			'short'    => __( 'Electronic', 'site-blocks' ),
			'path'     => '/electronic-security/',
			'mega'     => true,
			'children' => array(
				array(
					'path'  => '/alarm-systems/',
					'label' => __( 'Alarm Systems', 'site-blocks' ),
				),
				array(
					'path'  => '/ajax-alarm-systems/',
					'label' => __( 'Ajax Alarm Systems', 'site-blocks' ),
				),
				array(
					'path'  => '/cctv-security-cameras/',
					'label' => __( 'CCTV & Security Cameras', 'site-blocks' ),
				),
				array(
					'path'  => '/access-control/',
					'label' => __( 'Access Control', 'site-blocks' ),
				),
				array(
					'path'  => '/intercom-systems/',
					'label' => __( 'Intercom Systems', 'site-blocks' ),
				),
			),
		),
		array(
			'type'     => 'group',
			'label'    => __( 'Monitoring', 'site-blocks' ),
			'short'    => __( 'Monitoring', 'site-blocks' ),
			'path'     => '/monitoring/',
			'children' => array(
				array(
					'path'  => '/monitoring/back-to-base/',
					'label' => __( 'Back-to-Base Monitoring', 'site-blocks' ),
				),
				array(
					'path'  => '/monitoring/virtual-patrol/',
					'label' => __( 'Virtual Patrol', 'site-blocks' ),
				),
				array(
					'path'  => '/monitoring/solar-cameras-monitoring/',
					'label' => __( 'Solar Cameras with Monitoring', 'site-blocks' ),
				),
			),
		),
		array(
			'type'     => 'group',
			'label'    => __( 'Enterprise', 'site-blocks' ),
			'short'    => __( 'Enterprise', 'site-blocks' ),
			'path'     => '/enterprise-solutions/',
			'children' => array(
				array(
					'path'  => '/safeguard-solutions/',
					'label' => __( 'Safeguard Solutions', 'site-blocks' ),
				),
			),
		),
		array(
			'path'  => '/contact/',
			'label' => __( 'Contact', 'site-blocks' ),
		),
	);
}

/**
 * Whether a nav item or any child matches the current page.
 *
 * @param array<string, mixed> $item Nav item.
 */
function site_blocks_safeguard_item_is_current( array $item ): bool {
	$path = trim( (string) ( $item['path'] ?? '' ), '/' );

	if ( '' !== $path && is_page( $path ) ) {
		return true;
	}

	if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) {
		foreach ( $item['children'] as $child ) {
			if ( is_array( $child ) && site_blocks_safeguard_item_is_current( $child ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Whether a nav path (or its silo children) is the current page.
 */
function site_blocks_safeguard_nav_is_current( string $path, bool $include_children = false ): bool {
	$slug = trim( $path, '/' );

	if ( is_page( $slug ) ) {
		return true;
	}

	if ( ! $include_children ) {
		return false;
	}

	foreach ( site_blocks_get_safeguard_nav_items() as $item ) {
		if ( (string) ( $item['path'] ?? '' ) === $path ) {
			return site_blocks_safeguard_item_is_current( $item );
		}
	}

	return false;
}

/**
 * @param array<string, mixed> $item Nav item.
 */
function site_blocks_render_safeguard_nav_link( array $item, string $class = '' ): void {
	$path       = (string) ( $item['path'] ?? '' );
	$label      = (string) ( $item['label'] ?? '' );
	$short      = isset( $item['short'] ) ? (string) $item['short'] : '';
	$is_current = site_blocks_safeguard_nav_is_current( $path, false );
	$attrs      = $is_current ? ' aria-current="page"' : '';
	$extra      = $class !== '' ? ' class="' . esc_attr( $class ) . '"' : '';
	$title      = $short !== '' && $short !== $label ? ' title="' . esc_attr( $label ) . '"' : '';

	printf(
		'<a href="%s"%s%s%s>',
		esc_url( home_url( $path ) ),
		$extra,
		$attrs,
		$title
	);
	if ( $short !== '' ) {
		printf(
			'<span class="sg-header__nav-label sg-header__nav-label--long">%s</span><span class="sg-header__nav-label sg-header__nav-label--short">%s</span>',
			esc_html( $label ),
			esc_html( $short )
		);
	} else {
		echo esc_html( $label );
	}
	echo '</a>';
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
	$nav_items = site_blocks_get_safeguard_nav_items();
	$contact   = site_blocks_get_safeguard_contact();
	$phone     = $contact['phone'];
	$phone_h   = $contact['phone_href'];
	$quote     = $contact['quote'];
	?>
	<div class="sg-utility" role="complementary" aria-label="<?php esc_attr_e( 'Service information', 'site-blocks' ); ?>">
		<div class="sg-container sg-utility__inner">
			<span><?php esc_html_e( 'Servicing Greater Sydney Metro', 'site-blocks' ); ?></span>
			<span><?php esc_html_e( 'Master Licence No. 000103619', 'site-blocks' ); ?> &nbsp;·&nbsp; <?php esc_html_e( 'ASIAL Member', 'site-blocks' ); ?></span>
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
				<ul class="sg-header__nav-list" role="list">
					<?php foreach ( $nav_items as $item ) : ?>
						<?php if ( ( $item['type'] ?? '' ) === 'group' ) : ?>
							<?php
							$group_current = site_blocks_safeguard_nav_is_current( (string) $item['path'], true );
							$group_id      = 'sg-nav-' . sanitize_title( (string) $item['label'] );
							?>
							<li class="sg-header__nav-item sg-header__nav-item--dropdown<?php echo $group_current ? ' is-active' : ''; ?>">
								<div class="sg-header__nav-dropdown-wrap">
									<a
										class="sg-header__nav-link sg-header__nav-link--parent"
										href="<?php echo esc_url( home_url( (string) $item['path'] ) ); ?>"
										title="<?php echo esc_attr( (string) $item['label'] ); ?>"
										<?php echo $group_current ? 'aria-current="page"' : ''; ?>
									>
										<span class="sg-header__nav-label sg-header__nav-label--long"><?php echo esc_html( (string) $item['label'] ); ?></span>
										<span class="sg-header__nav-label sg-header__nav-label--short"><?php echo esc_html( (string) ( $item['short'] ?? $item['label'] ) ); ?></span>
										<span class="sg-header__nav-caret" aria-hidden="true"></span>
									</a>
									<?php if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) : ?>
										<?php
										$menu_class = 'sg-header__nav-menu';
										if ( ! empty( $item['mega'] ) ) {
											$menu_class .= ' sg-header__nav-menu--mega';
										}
										?>
										<ul class="<?php echo esc_attr( $menu_class ); ?>" id="<?php echo esc_attr( $group_id ); ?>" role="list">
											<?php foreach ( $item['children'] as $child ) : ?>
												<li>
													<?php site_blocks_render_safeguard_nav_link( $child, 'sg-header__nav-menu-link' ); ?>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</li>
						<?php else : ?>
							<li class="sg-header__nav-item">
								<?php site_blocks_render_safeguard_nav_link( $item, 'sg-header__nav-link' ); ?>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</nav>

			<div class="sg-header__actions">
				<a class="sg-header__phone" href="<?php echo esc_attr( $phone_h ); ?>">
					<?php site_blocks_lucide_icon( 'phone', 18 ); ?>
					<?php echo esc_html( $phone ); ?>
				</a>
				<button type="button" class="sg-header__menu-btn" aria-expanded="false" aria-controls="sg-mobile-panel" aria-label="<?php esc_attr_e( 'Open menu', 'site-blocks' ); ?>">
					<span></span><span></span><span></span>
				</button>
				<a class="sg-btn sg-btn--primary sg-header__quote-mobile" href="<?php echo esc_url( $quote ); ?>"><?php esc_html_e( 'Quote', 'site-blocks' ); ?></a>
				<a class="sg-btn sg-btn--primary sg-header__quote" href="<?php echo esc_url( $quote ); ?>">
					<span class="sg-header__quote-long"><?php esc_html_e( 'Get a Quote', 'site-blocks' ); ?></span>
					<span class="sg-header__quote-short" aria-hidden="true"><?php esc_html_e( 'Quote', 'site-blocks' ); ?></span>
				</a>
			</div>
		</div>

		<div class="sg-mobile-panel" id="sg-mobile-panel" hidden>
			<div class="sg-container">
				<nav class="sg-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile', 'site-blocks' ); ?>">
					<?php foreach ( $nav_items as $item ) : ?>
						<?php if ( ( $item['type'] ?? '' ) === 'group' ) : ?>
							<div class="sg-mobile-nav__group">
								<?php
								$group_current = site_blocks_safeguard_nav_is_current( (string) $item['path'], true );
								$attrs         = $group_current ? ' aria-current="page"' : '';
								?>
								<a class="sg-mobile-nav__parent" href="<?php echo esc_url( home_url( (string) $item['path'] ) ); ?>"<?php echo $attrs; ?>>
									<?php echo esc_html( (string) $item['label'] ); ?>
								</a>
								<?php if ( ! empty( $item['children'] ) && is_array( $item['children'] ) ) : ?>
									<?php foreach ( $item['children'] as $child ) : ?>
										<?php
										$child_current = site_blocks_safeguard_nav_is_current( (string) $child['path'], false );
										$child_attrs   = $child_current ? ' aria-current="page"' : '';
										?>
										<a class="sg-mobile-nav__child" href="<?php echo esc_url( home_url( (string) $child['path'] ) ); ?>"<?php echo $child_attrs; ?>>
											<?php echo esc_html( (string) $child['label'] ); ?>
										</a>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<?php
							$is_current = site_blocks_safeguard_nav_is_current( (string) $item['path'], false );
							$attrs      = $is_current ? ' aria-current="page"' : '';
							?>
							<a class="sg-mobile-nav__link" href="<?php echo esc_url( home_url( (string) $item['path'] ) ); ?>"<?php echo $attrs; ?>>
								<?php echo esc_html( (string) $item['label'] ); ?>
							</a>
						<?php endif; ?>
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
		|| is_page( 'ajax-calculator' )
		|| is_page( 'physical-security' )
		|| is_page( 'static-guards' )
		|| is_page( 'mobile-patrols' )
		|| is_page( 'electronic-security' )
		|| is_page( 'manpower' )
		|| is_page( 'monitoring' )
		|| is_page( 'back-to-base' )
		|| is_page( 'virtual-patrol' )
		|| is_page( 'solar-cameras-monitoring' )
		|| is_page( 'enterprise-solutions' )
		|| is_page( 'safeguard-solutions' )
		|| is_singular( 'enterprise_insight' );
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
