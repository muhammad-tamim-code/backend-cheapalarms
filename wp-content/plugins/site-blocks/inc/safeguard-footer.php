<?php
/**
 * Shared Safeguard site footer and mobile bar.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer service links.
 *
 * @return array<string, string>
 */
function site_blocks_get_safeguard_footer_services(): array {
	return array(
		'/enterprise-solutions/'  => __( 'Enterprise Solutions', 'site-blocks' ),
		'/safeguard-solutions/'   => __( 'Safeguard Solutions', 'site-blocks' ),
		'/alarm-systems/'         => __( 'Alarm Systems', 'site-blocks' ),
		'/cctv-security-cameras/' => __( 'CCTV & Security Cameras', 'site-blocks' ),
		'/access-control/'        => __( 'Access Control', 'site-blocks' ),
		'/physical-security/'     => __( 'Physical Security', 'site-blocks' ),
		'/intercom-systems/'      => __( 'Intercoms', 'site-blocks' ),
		'/monitoring/'            => __( 'Alarm Monitoring', 'site-blocks' ),
	);
}

/**
 * Render Safeguard footer.
 */
function site_blocks_render_safeguard_footer(): void {
	$logo_footer = site_blocks_asset_url( 'images/brand/safeguard-logo-footer.png' );
	$phone       = '1300 225 276';
	$phone_h     = 'tel:1300225276';
	$email       = 'sales@safeguardsecurity.com.au';
	$facebook    = 'https://www.facebook.com/sgsaus';
	$address     = '2/2 Stennett Road, Ingleburn NSW 2565';
	$maps_embed  = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3312.619235886837!2d150.85348587031246!3d-33.9994517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b12bea03e504021%3A0x2840053cf7bd776d!2sSafeguard%20Security%20Services!5e0!3m2!1sen!2sau!4v1718025600000!5m2!1sen!2sau';
	$footer_services = site_blocks_get_safeguard_footer_services();
	?>
	<footer class="sg-footer" id="sg-footer">
		<div class="sg-container sg-footer__grid">
			<div class="sg-footer__brand">
				<img
					class="sg-footer__logo"
					src="<?php echo esc_url( $logo_footer ); ?>"
					alt="<?php esc_attr_e( 'Safeguard Security Services', 'site-blocks' ); ?>"
					width="766"
					height="210"
					decoding="async"
				/>
				<p><?php esc_html_e( 'Commercial & residential security, installed across Greater Sydney.', 'site-blocks' ); ?></p>
				<div class="sg-footer__map">
					<iframe
						class="sg-footer__map-frame"
						src="<?php echo esc_url( $maps_embed ); ?>"
						width="320"
						height="200"
						allowfullscreen=""
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						title="<?php esc_attr_e( 'Safeguard Security Services location on Google Maps', 'site-blocks' ); ?>"
					></iframe>
				</div>
				<div class="sg-social" aria-label="<?php esc_attr_e( 'Social media', 'site-blocks' ); ?>">
					<a href="<?php echo esc_url( $facebook ); ?>" rel="noopener noreferrer" target="_blank" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
				</div>
			</div>
			<div>
				<h3 class="sg-footer__heading"><?php esc_html_e( 'Services', 'site-blocks' ); ?></h3>
				<ul class="sg-footer__links" role="list">
					<?php foreach ( $footer_services as $path => $label ) : ?>
						<li><a href="<?php echo esc_url( home_url( $path ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<h3 class="sg-footer__heading"><?php esc_html_e( 'Company', 'site-blocks' ); ?></h3>
				<ul class="sg-footer__links" role="list">
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'site-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ajax-alarm-systems/' ) ); ?>"><?php esc_html_e( 'Ajax Alarm Systems', 'site-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/service-area/' ) ); ?>"><?php esc_html_e( 'Service Area', 'site-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>"><?php esc_html_e( 'Reviews', 'site-blocks' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'site-blocks' ); ?></a></li>
				</ul>
			</div>
			<div>
				<h3 class="sg-footer__heading"><?php esc_html_e( 'Contact', 'site-blocks' ); ?></h3>
				<address class="sg-footer__contact">
					<p><?php echo esc_html( $address ); ?></p>
					<p><a href="<?php echo esc_attr( $phone_h ); ?>"><?php echo esc_html( $phone ); ?></a></p>
					<p><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
					<p><a href="<?php echo esc_url( $facebook ); ?>" rel="noopener noreferrer" target="_blank">facebook.com/sgsaus</a></p>
					<p><?php esc_html_e( 'Mon–Fri 8 AM – 6 PM · Sat–Sun closed', 'site-blocks' ); ?></p>
				</address>
			</div>
		</div>
		<div class="sg-footer__bar">
			<div class="sg-container sg-footer__bar-inner">
				<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'Safeguard Security Services · Master Licence No. 000103619', 'site-blocks' ); ?></p>
				<p><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'site-blocks' ); ?></a> · <a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'site-blocks' ); ?></a></p>
			</div>
		</div>
	</footer>
	<?php
}

/**
 * Render sticky mobile CTA bar.
 */
function site_blocks_render_safeguard_mobile_bar(): void {
	$contact = site_blocks_get_safeguard_contact();
	?>
	<div class="sg-mobile-bar" id="sg-mobile-bar" aria-hidden="true">
		<a class="sg-btn sg-btn--primary" href="<?php echo esc_url( $contact['quote'] ); ?>"><?php esc_html_e( 'Instant Quote', 'site-blocks' ); ?></a>
		<a class="sg-btn sg-btn--secondary" href="<?php echo esc_attr( $contact['phone_href'] ); ?>"><?php esc_html_e( 'Call', 'site-blocks' ); ?></a>
	</div>
	<?php
}
