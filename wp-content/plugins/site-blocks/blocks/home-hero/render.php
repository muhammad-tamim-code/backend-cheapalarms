<?php
/**
 * Home Hero block render, Safeguard Security Sydney.
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

require_once SITE_BLOCKS_DIR . 'inc/home-hero-icons.php';

$eyebrow     = isset( $attributes['eyebrow'] ) ? $attributes['eyebrow'] : __( 'Safeguard Security · Sydney', 'site-blocks' );
$headline    = isset( $attributes['headline'] ) ? $attributes['headline'] : __( 'Protection you can see.', 'site-blocks' );
$subhead     = isset( $attributes['subhead'] ) ? $attributes['subhead'] : __( 'Professional camera, alarm, and intercom installation for homes and businesses across Sydney. Designed, installed, and supported by one local team.', 'site-blocks' );
$cta_label   = isset( $attributes['ctaLabel'] ) ? $attributes['ctaLabel'] : __( 'Get a free quote', 'site-blocks' );
$cta_url     = isset( $attributes['ctaUrl'] ) ? $attributes['ctaUrl'] : '/contact/';
$trust_line  = isset( $attributes['trustLine'] ) ? $attributes['trustLine'] : __( 'Licensed & insured installers serving Greater Sydney', 'site-blocks' );
$badge_line  = isset( $attributes['badgeLine'] ) ? $attributes['badgeLine'] : __( 'Cameras · Alarms · Intercoms · Monitoring', 'site-blocks' );
$orbit_label = isset( $attributes['orbitCtaLabel'] ) ? $attributes['orbitCtaLabel'] : __( 'View packages', 'site-blocks' );
$orbit_url   = isset( $attributes['orbitCtaUrl'] ) ? $attributes['orbitCtaUrl'] : '/packages/';

$contact_page = get_page_by_path( 'contact' );
if ( '/contact/' === $cta_url && $contact_page instanceof WP_Post ) {
	$cta_url = get_permalink( $contact_page );
}

$packages_url = get_post_type_archive_link( 'security_package' );
if ( '/packages/' === $orbit_url && $packages_url ) {
	$orbit_url = $packages_url;
}

$schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'LocalBusiness',
	'name'        => 'Safeguard Security',
	'description' => wp_strip_all_tags( $subhead ),
	'url'         => home_url( '/' ),
	'areaServed'  => array(
		'@type' => 'City',
		'name'  => 'Sydney',
	),
	'address'     => array(
		'@type'           => 'PostalAddress',
		'addressLocality' => 'Sydney',
		'addressCountry'  => 'AU',
	),
);
?>
<div class="home-hero-wrap alignfull">
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

	<section class="home-hero" aria-labelledby="home-hero-heading">
		<div class="home-hero__bg" aria-hidden="true">
			<div class="home-hero__dots"></div>
			<div class="home-hero__glow"></div>
		</div>

		<div class="home-hero__inner">
			<div class="home-hero__split">
				<div class="home-hero__headline-col">
					<p class="home-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<h1 id="home-hero-heading" class="home-hero__title"><?php echo esc_html( $headline ); ?></h1>
				</div>

				<div class="home-hero__pitch-col">
					<p class="home-hero__subhead"><?php echo esc_html( $subhead ); ?></p>
					<div class="home-hero__cta-row">
						<a class="home-hero__cta" href="<?php echo esc_url( $cta_url ); ?>">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="home-hero-visual" aria-label="<?php esc_attr_e( 'Security services overview', 'site-blocks' ); ?>">
		<div class="home-hero-visual__bg" aria-hidden="true">
			<div class="home-hero-visual__curve"></div>
		</div>

		<div class="home-hero-visual__inner">
			<div class="home-hero-visual__meta">
				<p class="home-hero-visual__trust"><?php echo esc_html( $trust_line ); ?></p>
				<p class="home-hero-visual__badge"><?php echo esc_html( $badge_line ); ?></p>
			</div>

			<div class="home-hero-visual__stage">
				<div class="home-hero-visual__radar" aria-hidden="true">
					<span class="home-hero-visual__ring home-hero-visual__ring--1"></span>
					<span class="home-hero-visual__ring home-hero-visual__ring--2"></span>
					<span class="home-hero-visual__ring home-hero-visual__ring--3"></span>
					<span class="home-hero-visual__ring home-hero-visual__ring--4"></span>

					<div class="home-hero-visual__orbit home-hero-visual__orbit--camera">
						<?php site_blocks_home_icon_camera(); ?>
					</div>
					<div class="home-hero-visual__orbit home-hero-visual__orbit--alarm">
						<?php site_blocks_home_icon_alarm(); ?>
					</div>
					<div class="home-hero-visual__orbit home-hero-visual__orbit--intercom">
						<?php site_blocks_home_icon_intercom(); ?>
					</div>
					<div class="home-hero-visual__orbit home-hero-visual__orbit--monitor">
						<?php site_blocks_home_icon_monitor(); ?>
					</div>
					<div class="home-hero-visual__orbit home-hero-visual__orbit--access">
						<?php site_blocks_home_icon_access(); ?>
					</div>

					<div class="home-hero-visual__shield">
						<?php site_blocks_home_icon_shield(); ?>
					</div>
				</div>

				<a class="home-hero-visual__orbit-cta" href="<?php echo esc_url( $orbit_url ); ?>">
					<span class="home-hero-visual__orbit-cta-ring" aria-hidden="true">
						<svg viewBox="0 0 120 120" class="home-hero-visual__orbit-cta-text">
							<defs>
								<path id="home-hero-cta-circle" d="M 60,60 m -44,0 a 44,44 0 1,1 88,0 a 44,44 0 1,1 -88,0"/>
							</defs>
							<text>
								<textPath href="#home-hero-cta-circle"><?php echo esc_html( strtoupper( $orbit_label ) . ' • ' . strtoupper( $orbit_label ) . ' • ' ); ?></textPath>
							</text>
						</svg>
					</span>
					<span class="home-hero-visual__orbit-cta-icon" aria-hidden="true">→</span>
					<span class="sr-only"><?php echo esc_html( $orbit_label ); ?></span>
				</a>
			</div>
		</div>
	</section>
</div>
