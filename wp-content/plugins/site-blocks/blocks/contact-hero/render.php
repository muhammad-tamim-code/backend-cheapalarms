<?php
/**
 * Contact Hero block render.
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

require_once SITE_BLOCKS_DIR . 'inc/safeguard-contact-details.php';

$defaults = site_blocks_get_safeguard_contact_details();

$headline = ! empty( $attributes['headline'] ) ? (string) $attributes['headline'] : __( 'Contact us', 'site-blocks' );
$subhead  = ! empty( $attributes['subhead'] ) ? (string) $attributes['subhead'] : __( 'Questions about quotes, installs or support? Call, email or send a message and our team will get back to you.', 'site-blocks' );
$phone    = $defaults['phone'];
$phone_h  = 'tel:' . preg_replace( '/[^\d+]/', '', $phone );

$schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'ContactPage',
	'name'        => get_bloginfo( 'name' ) . ', ' . __( 'Contact', 'site-blocks' ),
	'description' => wp_strip_all_tags( $subhead ),
	'url'         => get_permalink(),
);
?>
<section class="sg-band sg-band--white sg-contact-hero alignfull" aria-labelledby="sg-contact-hero-heading">
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<div class="sg-container">
		<nav class="sg-pillar-hero__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'site-blocks' ); ?></a>
			<span aria-hidden="true">›</span>
			<span aria-current="page"><?php esc_html_e( 'Contact', 'site-blocks' ); ?></span>
		</nav>
		<p class="sg-hero__badge"><?php esc_html_e( 'Contact · Sydney', 'site-blocks' ); ?></p>
		<h1 id="sg-contact-hero-heading" class="sg-section-title sg-section-title--ink">
			<?php
			$parts = explode( ' ', $headline, 2 );
			if ( count( $parts ) === 2 ) {
				echo esc_html( $parts[0] . ' ' );
				echo '<span class="sg-accent">' . esc_html( $parts[1] ) . '</span>';
			} else {
				echo esc_html( $headline );
			}
			?>
		</h1>
		<p class="sg-section-intro sg-contact-hero__intro"><?php echo esc_html( $subhead ); ?></p>
		<p class="sg-contact-hero__phone">
			<a class="sg-contact-hero__phone-link" href="<?php echo esc_attr( $phone_h ); ?>"><?php echo esc_html( $phone ); ?></a>
		</p>
	</div>
</section>
