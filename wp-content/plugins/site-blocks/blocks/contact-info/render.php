<?php
/**
 * Contact Info block render.
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

$org_name  = ! empty( $attributes['organizationName'] ) ? $attributes['organizationName'] : get_bloginfo( 'name' );
$street    = isset( $attributes['streetAddress'] ) ? $attributes['streetAddress'] : '';
$city      = isset( $attributes['city'] ) ? $attributes['city'] : '';
$region    = isset( $attributes['region'] ) ? $attributes['region'] : '';
$postal    = isset( $attributes['postalCode'] ) ? $attributes['postalCode'] : '';
$country   = isset( $attributes['country'] ) ? $attributes['country'] : '';
$phone     = ! empty( $attributes['phone'] ) ? $attributes['phone'] : '';
$email     = ! empty( $attributes['email'] ) ? $attributes['email'] : get_option( 'admin_email' );
$hours     = isset( $attributes['hoursNote'] ) ? $attributes['hoursNote'] : __( 'We respond to messages within two business days.', 'site-blocks' );

$has_address = $street || $city || $region || $postal || $country;

$locality_parts = array_filter( array( $city, $region, $postal ) );
$locality_line  = implode( ', ', $locality_parts );
?>
<section class="contact-info" aria-labelledby="contact-info-heading">
	<h2 id="contact-info-heading" class="contact-info__heading"><?php esc_html_e( 'Reach us', 'site-blocks' ); ?></h2>

	<?php if ( $has_address ) : ?>
		<address class="contact-info__address">
			<?php if ( $org_name ) : ?>
				<span class="contact-info__org"><?php echo esc_html( $org_name ); ?></span>
			<?php endif; ?>
			<?php if ( $street ) : ?>
				<span class="contact-info__line"><?php echo esc_html( $street ); ?></span>
			<?php endif; ?>
			<?php if ( $locality_line ) : ?>
				<span class="contact-info__line"><?php echo esc_html( $locality_line ); ?></span>
			<?php endif; ?>
			<?php if ( $country ) : ?>
				<span class="contact-info__line"><?php echo esc_html( $country ); ?></span>
			<?php endif; ?>
		</address>
	<?php endif; ?>

	<dl class="contact-info__details">
		<?php if ( $email ) : ?>
			<div class="contact-info__item">
				<dt class="contact-info__label"><?php esc_html_e( 'Email', 'site-blocks' ); ?></dt>
				<dd class="contact-info__value">
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="contact-info__link"><?php echo esc_html( $email ); ?></a>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $phone ) : ?>
			<div class="contact-info__item">
				<dt class="contact-info__label"><?php esc_html_e( 'Phone', 'site-blocks' ); ?></dt>
				<dd class="contact-info__value">
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>" class="contact-info__link"><?php echo esc_html( $phone ); ?></a>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $hours ) : ?>
			<div class="contact-info__item">
				<dt class="contact-info__label"><?php esc_html_e( 'Response', 'site-blocks' ); ?></dt>
				<dd class="contact-info__value"><?php echo esc_html( $hours ); ?></dd>
			</div>
		<?php endif; ?>
	</dl>
</section>
