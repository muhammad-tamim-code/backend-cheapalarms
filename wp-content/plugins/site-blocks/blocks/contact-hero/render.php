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

$headline = isset( $attributes['headline'] ) ? $attributes['headline'] : __( 'Write to us.', 'site-blocks' );
$subhead  = isset( $attributes['subhead'] ) ? $attributes['subhead'] : __( 'A direct line for questions, partnerships, and project inquiries.', 'site-blocks' );

$schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'ContactPage',
	'name'        => get_bloginfo( 'name' ) . ' — ' . __( 'Contact', 'site-blocks' ),
	'description' => wp_strip_all_tags( $subhead ),
	'url'         => get_permalink(),
);
?>
<section class="contact-hero" aria-labelledby="contact-hero-heading">
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<p class="contact-hero__eyebrow"><?php esc_html_e( 'Contact', 'site-blocks' ); ?></p>
	<h1 id="contact-hero-heading" class="contact-hero__title"><?php echo esc_html( $headline ); ?></h1>
	<p class="contact-hero__subhead"><?php echo esc_html( $subhead ); ?></p>
</section>
