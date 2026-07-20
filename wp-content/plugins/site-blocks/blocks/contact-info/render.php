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

require_once SITE_BLOCKS_DIR . 'inc/safeguard-contact-details.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

$info = site_blocks_merge_contact_info_attributes( $attributes );

$org_name   = $info['organizationName'];
$street     = $info['streetAddress'];
$city       = $info['city'];
$region     = $info['region'];
$postal     = $info['postalCode'];
$country    = $info['country'];
$phone      = $info['phone'];
$email      = $info['email'];
$hours      = $info['hoursNote'];
$response   = $info['responseNote'];
$maps_link  = $info['mapsLink'];
$maps_embed = $info['mapsEmbed'];
$show_map   = $info['showMap'];

$locality_parts = array_filter( array( $city, $region, $postal ) );
$locality_line  = implode( ', ', $locality_parts );

$rows = array(
	array(
		'icon'  => 'phone',
		'label' => __( 'Phone', 'site-blocks' ),
		'value' => sprintf(
			'<a class="sg-contact-detail__link" href="tel:%s">%s</a>',
			esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ),
			esc_html( $phone )
		),
	),
	array(
		'icon'  => 'mail',
		'label' => __( 'Email', 'site-blocks' ),
		'value' => sprintf(
			'<a class="sg-contact-detail__link" href="mailto:%s">%s</a>',
			esc_attr( $email ),
			esc_html( $email )
		),
	),
	array(
		'icon'  => 'clock',
		'label' => __( 'Hours', 'site-blocks' ),
		'value' => esc_html( $hours ),
	),
	array(
		'icon'  => 'message-circle',
		'label' => __( 'Response time', 'site-blocks' ),
		'value' => esc_html( $response ),
	),
);
?>
<div class="sg-contact-details" aria-labelledby="sg-contact-details-heading">
	<h2 id="sg-contact-details-heading" class="sg-contact-details__title">
		<?php esc_html_e( 'Get in ', 'site-blocks' ); ?>
		<span class="sg-accent"><?php esc_html_e( 'touch', 'site-blocks' ); ?></span>
	</h2>

	<address class="sg-contact-details__address">
		<span class="sg-contact-details__org"><?php echo esc_html( $org_name ); ?></span>
		<?php if ( $street ) : ?>
			<span><?php echo esc_html( $street ); ?></span>
		<?php endif; ?>
		<?php if ( $locality_line ) : ?>
			<span><?php echo esc_html( $locality_line ); ?></span>
		<?php endif; ?>
		<?php if ( $country ) : ?>
			<span><?php echo esc_html( $country ); ?></span>
		<?php endif; ?>
		<?php if ( $maps_link ) : ?>
			<a class="sg-contact-details__directions" href="<?php echo esc_url( $maps_link ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Get directions', 'site-blocks' ); ?>
			</a>
		<?php endif; ?>
	</address>

	<dl class="sg-contact-details__list">
		<?php foreach ( $rows as $row ) : ?>
			<div class="sg-contact-detail">
				<dt class="sg-contact-detail__label">
					<span class="sg-contact-detail__icon" aria-hidden="true"><?php site_blocks_lucide_icon( $row['icon'] ); ?></span>
					<?php echo esc_html( $row['label'] ); ?>
				</dt>
				<dd class="sg-contact-detail__value"><?php echo $row['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>

	<p class="sg-contact-details__quote">
		<?php esc_html_e( 'Need a price first?', 'site-blocks' ); ?>
		<a href="<?php echo esc_url( home_url( '/get-an-instant-quote/' ) ); ?>"><?php esc_html_e( 'Start your quote online', 'site-blocks' ); ?></a>
	</p>

	<?php if ( $show_map && $maps_embed ) : ?>
		<div class="sg-contact-details__map">
			<iframe
				class="sg-contact-details__map-frame"
				src="<?php echo esc_url( $maps_embed ); ?>"
				width="100%"
				height="220"
				allowfullscreen=""
				loading="lazy"
				referrerpolicy="no-referrer-when-downgrade"
				title="<?php esc_attr_e( 'Safeguard Security Services on Google Maps', 'site-blocks' ); ?>"
			></iframe>
		</div>
	<?php endif; ?>
</div>
