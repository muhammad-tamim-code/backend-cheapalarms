<?php
/**
 * Safeguard homepage HTML markup.
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render complete Safeguard homepage.
 */
function site_blocks_render_safeguard_homepage(): void {
	require_once SITE_BLOCKS_DIR . 'inc/safeguard-chrome.php';
	require_once SITE_BLOCKS_DIR . 'inc/safeguard-section-icons.php';
	require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';
	require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow.php';
	require_once SITE_BLOCKS_DIR . 'inc/safeguard-process-flow-configs.php';

	$hero_house  = site_blocks_asset_url( 'images/hero/house.png' );
	$hero_dash   = site_blocks_asset_url( 'images/hero/dashboard.png' );
	$portal_dash = site_blocks_asset_url( 'images/portal/portal-dashboard.png' );
	$logo_mark   = site_blocks_asset_url( 'images/brand/safeguard-logo-mark.png' );
	$ajax_img    = site_blocks_asset_url( 'images/ajax/ajax-products.png' );
	$quote       = esc_url( home_url( '/get-an-instant-quote/' ) );
	$design      = esc_url( home_url( '/design-my-solution/' ) );
	$ajax_page   = esc_url( home_url( '/ajax-alarm-systems/' ) );

	$service_cards = array(
		array(
			'href'  => '/alarm-systems/',
			'title' => __( 'Alarm Systems', 'site-blocks' ),
			'desc'  => __( 'Advanced intrusion detection with smart alerts.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_alarm',
		),
		array(
			'href'  => '/cctv-security-cameras/',
			'title' => __( 'CCTV & Cameras', 'site-blocks' ),
			'desc'  => __( 'High-definition surveillance you can trust.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_cctv',
		),
		array(
			'href'  => '/intercom-systems/',
			'title' => __( 'Video Intercoms', 'site-blocks' ),
			'desc'  => __( 'See, speak and grant access remotely.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_intercom',
		),
		array(
			'href'  => '/access-control/',
			'title' => __( 'Access Control', 'site-blocks' ),
			'desc'  => __( 'Secure entry for people, vehicles and assets.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_access',
		),
		array(
			'href'  => '/monitoring/',
			'title' => __( 'Monitoring & Response', 'site-blocks' ),
			'desc'  => __( '24/7 monitoring with rapid response when needed.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_monitoring',
		),
		array(
			'href'  => '/preventative-maintenance/',
			'title' => __( 'Preventative Maintenance', 'site-blocks' ),
			'desc'  => __( 'Keep your system reliable with scheduled servicing.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_maintenance',
		),
		array(
			'href'  => '/commercial-security/',
			'title' => __( 'Commercial', 'site-blocks' ),
			'desc'  => __( 'Scalable solutions for offices, retail and facilities.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_commercial',
		),
		array(
			'href'  => '/residential-security/',
			'title' => __( 'Residential', 'site-blocks' ),
			'desc'  => __( 'Smart protection for your property and family.', 'site-blocks' ),
			'icon'  => 'site_blocks_sg_icon_service_residential',
		),
	);

	require_once SITE_BLOCKS_DIR . 'inc/safeguard-faq.php';
	$faq_items = site_blocks_get_safeguard_faq_items();
	?>
<div class="sg-page alignfull">
	<?php site_blocks_render_safeguard_header(); ?>

	<main id="main" class="sg-main">
		<section class="sg-home-hero-v2 sg-reveal" aria-labelledby="sg-home-hero-v2-heading">
			<div class="sg-container sg-home-hero-v2__grid">
				<div class="sg-home-hero-v2__copy">
					<p class="sg-home-hero-v2__badge">
						<span class="sg-home-hero-v2__badge-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'shield-check', 16 ); ?></span>
						<?php esc_html_e( 'People on the ground. Backed by technology.', 'site-blocks' ); ?>
					</p>
					<h1 id="sg-home-hero-v2-heading" class="sg-home-hero-v2__title">
						<?php esc_html_e( 'Security you can rely on. Every site. Every shift.', 'site-blocks' ); ?>
					</h1>
					<p class="sg-home-hero-v2__lead">
						<?php esc_html_e( 'Professional security services across Sydney. Tailored solutions. Trained people. Real results.', 'site-blocks' ); ?>
					</p>
					<div class="sg-home-hero-v2__ctas">
						<a class="sg-btn sg-btn--soft-blue sg-home-hero-v2__btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
							<span class="sg-home-hero-v2__btn-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'calendar', 18 ); ?></span>
							<?php esc_html_e( 'Request a Quote', 'site-blocks' ); ?>
						</a>
						<a class="sg-btn sg-btn--secondary sg-home-hero-v2__btn" href="tel:1300225276">
							<span class="sg-home-hero-v2__btn-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'phone', 18 ); ?></span>
							<?php esc_html_e( 'Speak to Our Team', 'site-blocks' ); ?>
						</a>
					</div>
					<ul class="sg-home-hero-v2__trust" role="list">
						<li>
							<span class="sg-home-hero-v2__trust-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'clock', 18 ); ?></span>
							<?php esc_html_e( '24/7 Operations', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-home-hero-v2__trust-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'user-check', 18 ); ?></span>
							<?php esc_html_e( 'Licensed NSW Officers', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-home-hero-v2__trust-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'zap', 18 ); ?></span>
							<?php esc_html_e( 'Fast Response', 'site-blocks' ); ?>
						</li>
					</ul>
				</div>

				<div class="sg-home-hero-v2__visual">
					<div class="sg-home-hero-v2__media">
						<div class="sg-home-hero-v2__composition">
							<div class="sg-home-hero-v2__house">
								<img
									class="sg-home-hero-v2__house-img"
									src="<?php echo esc_url( $hero_house ); ?>"
									alt="<?php esc_attr_e( 'Modern property with Safeguard security coverage', 'site-blocks' ); ?>"
									width="750"
									height="563"
									loading="eager"
									decoding="async"
								/>
							</div>
							<div class="sg-home-hero-v2__portal">
								<img
									class="sg-home-hero-v2__portal-img"
									src="<?php echo esc_url( $hero_dash ); ?>"
									alt="<?php esc_attr_e( 'Safeguard customer portal dashboard', 'site-blocks' ); ?>"
									width="951"
									height="468"
									loading="eager"
									decoding="async"
								/>
							</div>
						</div>
						<div class="sg-home-hero-v2__card sg-home-hero-v2__card--portal">
							<span class="sg-home-hero-v2__card-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'monitor', 18 ); ?></span>
							<div class="sg-home-hero-v2__card-body">
								<p class="sg-home-hero-v2__card-label"><?php esc_html_e( 'Custom Portal', 'site-blocks' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php
		$proof_solutions = array(
			array(
				'label' => __( 'Static Guards', 'site-blocks' ),
				'icon'  => 'user',
				'url'   => home_url( '/physical-security/static-guards/' ),
			),
			array(
				'label' => __( 'Mobile Patrols', 'site-blocks' ),
				'icon'  => 'car',
				'url'   => home_url( '/physical-security/mobile-patrols/' ),
			),
			array(
				'label' => __( 'Alarm Response', 'site-blocks' ),
				'icon'  => 'siren',
				'url'   => home_url( '/physical-security/' ),
			),
			array(
				'label' => __( 'Event Security', 'site-blocks' ),
				'icon'  => 'users',
				'url'   => home_url( '/physical-security/' ),
			),
			array(
				'label' => __( 'Concierge Security', 'site-blocks' ),
				'icon'  => 'bell',
				'url'   => home_url( '/physical-security/static-guards/' ),
			),
			array(
				'label' => __( 'Retail Security', 'site-blocks' ),
				'icon'  => 'shopping-bag',
				'url'   => home_url( '/physical-security/static-guards/' ),
			),
		);
		$proof_why = array(
			__( 'Licensed NSW Security Officers', 'site-blocks' ),
			__( 'Integrated Alarm Monitoring', 'site-blocks' ),
			__( 'GPS Patrol Tracking', 'site-blocks' ),
			__( 'Digital Incident Reporting', 'site-blocks' ),
			__( '24/7 Support & Operations', 'site-blocks' ),
		);
		?>
		<section class="sg-home-proof sg-reveal" aria-label="<?php esc_attr_e( 'Why Safeguard', 'site-blocks' ); ?>">
			<div class="sg-container sg-home-proof__grid">
				<article class="sg-home-proof__panel sg-home-proof__panel--experience">
					<img
						class="sg-home-proof__logo-bg"
						src="<?php echo esc_url( $logo_mark ); ?>"
						alt=""
						width="180"
						height="180"
						decoding="async"
						aria-hidden="true"
					/>
					<p class="sg-home-proof__stat"><?php esc_html_e( '24+', 'site-blocks' ); ?></p>
					<h2 class="sg-home-proof__experience-title"><?php esc_html_e( 'Years of Protecting What Matters', 'site-blocks' ); ?></h2>
					<p class="sg-home-proof__experience-copy"><?php esc_html_e( 'Trusted by businesses, communities and government across Australia.', 'site-blocks' ); ?></p>
				</article>

				<article class="sg-home-proof__panel sg-home-proof__panel--solutions">
					<h2 class="sg-home-proof__heading"><?php esc_html_e( 'Our Security Solutions', 'site-blocks' ); ?></h2>
					<ul class="sg-home-proof__solutions" role="list">
						<?php foreach ( $proof_solutions as $solution ) : ?>
							<li>
								<a class="sg-home-proof__solution" href="<?php echo esc_url( (string) $solution['url'] ); ?>">
									<span class="sg-home-proof__solution-icon" aria-hidden="true"><?php site_blocks_lucide_icon( (string) $solution['icon'], 22 ); ?></span>
									<span class="sg-home-proof__solution-label"><?php echo esc_html( (string) $solution['label'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</article>

				<article class="sg-home-proof__panel sg-home-proof__panel--why">
					<h2 class="sg-home-proof__heading"><?php esc_html_e( 'Why Choose Safeguard?', 'site-blocks' ); ?></h2>
					<ul class="sg-home-proof__why" role="list">
						<?php foreach ( $proof_why as $item ) : ?>
							<li>
								<span class="sg-home-proof__why-icon" aria-hidden="true"><?php site_blocks_lucide_icon( 'circle-check', 18, 'sg-lucide-icon--filled-check' ); ?></span>
								<span><?php echo esc_html( $item ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</article>
			</div>
		</section>

		<section class="sg-home-orgs sg-reveal" aria-labelledby="sg-home-orgs-heading">
			<div class="sg-container sg-home-orgs__inner">
				<p id="sg-home-orgs-heading" class="sg-home-orgs__label"><?php esc_html_e( 'Trusted by leading organisations', 'site-blocks' ); ?></p>
				<ul class="sg-home-orgs__list" role="list">
					<li><?php esc_html_e( 'Mirvac', 'site-blocks' ); ?></li>
					<li><?php esc_html_e( 'Ampol', 'site-blocks' ); ?></li>
					<li><?php esc_html_e( 'Woolworths Group', 'site-blocks' ); ?></li>
					<li><?php esc_html_e( 'Lendlease', 'site-blocks' ); ?></li>
					<li><?php esc_html_e( 'Stockland', 'site-blocks' ); ?></li>
					<li><?php esc_html_e( 'NSW Government', 'site-blocks' ); ?></li>
				</ul>
			</div>
		</section>

		<section class="sg-hero sg-hero--dark sg-reveal" aria-labelledby="sg-hero-heading">
			<div class="sg-hero__dark-bg" aria-hidden="true"></div>
			<div class="sg-container sg-hero__grid sg-hero__grid--dark">
				<div class="sg-hero__copy sg-hero__copy--dark">
					<p class="sg-hero__badge"><?php esc_html_e( 'SMART SECURITY, DESIGNED AROUND YOU.', 'site-blocks' ); ?></p>
					<h2 id="sg-hero-heading" class="sg-hero__title sg-hero__title--dark">
						<?php esc_html_e( 'Most security quotes take days. Yours takes ', 'site-blocks' ); ?>
						<span class="sg-hero__title-accent"><?php esc_html_e( 'minutes', 'site-blocks' ); ?></span><?php esc_html_e( '.', 'site-blocks' ); ?>
					</h2>
					<p class="sg-hero__sub sg-hero__sub--dark"><?php esc_html_e( 'Build it online and get your price on the spot, or within 24 hours for complex sites, checked by a real technician. No 48-hour wait, no salesperson at your door.', 'site-blocks' ); ?></p>
					<div class="sg-hero__ctas">
						<a class="sg-btn sg-btn--primary" href="<?php echo $quote; ?>"><?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?></a>
						<a class="sg-btn sg-btn--ghost" href="<?php echo $design; ?>"><?php esc_html_e( 'Help Me Choose', 'site-blocks' ); ?></a>
					</div>
					<ul class="sg-hero__features" role="list">
						<li>
							<span class="sg-hero__feature-icon" aria-hidden="true"><?php site_blocks_sg_icon_feature_shield(); ?></span>
							<?php esc_html_e( 'Expert review by real technicians', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-hero__feature-icon" aria-hidden="true"><?php site_blocks_sg_icon_feature_target(); ?></span>
							<?php esc_html_e( 'Tailored to your site', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-hero__feature-icon" aria-hidden="true"><?php site_blocks_sg_icon_feature_gear(); ?></span>
							<?php esc_html_e( 'Premium systems professionally installed', 'site-blocks' ); ?>
						</li>
					</ul>
				</div>

				<div class="sg-hero__visual sg-hero__visual--dark">
					<div class="sg-hero__composition">
						<div class="sg-hero__dashboard-wrap">
							<img
								class="sg-hero__dashboard-img"
								src="<?php echo $hero_dash; ?>"
								alt="<?php esc_attr_e( 'Safeguard customer portal showing quote progress, uploaded photos and system status', 'site-blocks' ); ?>"
								width="951"
								height="468"
								loading="eager"
								decoding="async"
							/>
						</div>

						<div class="sg-hero__house-wrap">
							<img
								class="sg-hero__house-img"
								src="<?php echo $hero_house; ?>"
								alt="<?php esc_attr_e( 'Isometric illustration of a modern residential property with security coverage', 'site-blocks' ); ?>"
								width="750"
								height="563"
								loading="eager"
								decoding="async"
							/>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php
		site_blocks_render_logo_marquee(
			array(
				'title'      => __( 'Clients Portfolio', 'site-blocks' ),
				'subtitle'   => __( 'A selection of sites and businesses across retail, logistics and commercial property.', 'site-blocks' ),
				'source'     => 'portfolio',
				'variant'    => 'wash',
				'heading_id' => 'sg-home-portfolio-heading',
				'class'      => 'sg-logo-marquee--home',
			)
		);
		?>

		<?php site_blocks_render_process_flow( site_blocks_process_flow_config( 'homepage' ) ); ?>

		<section class="sg-band sg-band--white sg-reveal" aria-labelledby="sg-funnel-heading">
			<div class="sg-container">
				<h2 id="sg-funnel-heading" class="sg-section-title sg-section-title--center">
					<?php esc_html_e( 'Two ways to start, whichever suits ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'you', 'site-blocks' ); ?></span>
				</h2>

				<div class="sg-funnel-grid">
					<article class="sg-funnel-card">
						<div class="sg-funnel-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_funnel_quote(); ?></div>
						<div class="sg-funnel-card__body">
							<h3><?php esc_html_e( 'I know what I need', 'site-blocks' ); ?></h3>
							<p><?php esc_html_e( 'Tell us what you\'re looking for and we\'ll design a tailored solution for your site.', 'site-blocks' ); ?></p>
							<a class="sg-btn sg-btn--primary" href="<?php echo $quote; ?>"><?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?></a>
						</div>
					</article>
					<article class="sg-funnel-card">
						<div class="sg-funnel-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_funnel_help(); ?></div>
						<div class="sg-funnel-card__body">
							<h3><?php esc_html_e( 'Help me choose', 'site-blocks' ); ?></h3>
							<p><?php esc_html_e( 'Not sure where to start? We\'ll guide you to the right system for your needs.', 'site-blocks' ); ?></p>
							<a class="sg-btn sg-btn--secondary" href="<?php echo $design; ?>"><?php esc_html_e( 'Help Me Choose', 'site-blocks' ); ?></a>
						</div>
					</article>
				</div>

				<div class="sg-design">
					<div class="sg-design__main">
						<h2 id="sg-design-heading" class="sg-design__title">
							<?php esc_html_e( 'What we ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'design', 'site-blocks' ); ?></span><?php esc_html_e( ' and install', 'site-blocks' ); ?>
						</h2>

						<div class="sg-design__grid">
							<?php foreach ( $service_cards as $card ) : ?>
								<article class="sg-design-card">
									<div class="sg-design-card__icon" aria-hidden="true">
										<?php
										if ( is_callable( $card['icon'] ) ) {
											call_user_func( $card['icon'] );
										}
										?>
									</div>
									<h3 class="sg-design-card__title">
										<a href="<?php echo esc_url( home_url( $card['href'] ) ); ?>"><?php echo esc_html( $card['title'] ); ?></a>
									</h3>
									<p class="sg-design-card__desc"><?php echo esc_html( $card['desc'] ); ?></p>
								</article>
							<?php endforeach; ?>
						</div>
					</div>

					<aside class="sg-design__aside" aria-labelledby="sg-ajax-heading">
						<div class="sg-ajax-card">
							<h2 id="sg-ajax-heading" class="sg-ajax-card__title">
								<?php esc_html_e( 'Safeguard + Ajax,', 'site-blocks' ); ?><br>
								<?php esc_html_e( 'professionally installed.', 'site-blocks' ); ?>
							</h2>
							<p class="sg-ajax-card__intro"><?php esc_html_e( 'We partner with Ajax Systems to deliver intelligent, reliable security, installed and supported by experienced technicians.', 'site-blocks' ); ?></p>
							<div class="sg-ajax-visual">
								<img
									src="<?php echo $ajax_img; ?>"
									alt="<?php esc_attr_e( 'Ajax security hardware including motion sensor, hub, smartphone app and siren', 'site-blocks' ); ?>"
									width="750"
									height="563"
									loading="lazy"
									decoding="async"
								/>
							</div>
							<ul class="sg-ajax-trust" role="list">
								<li>
									<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_grade2(); ?></span>
									<span class="sg-ajax-trust__label"><?php esc_html_e( 'Grade 2 security', 'site-blocks' ); ?></span>
								</li>
								<li>
									<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_encrypted(); ?></span>
									<span class="sg-ajax-trust__label"><?php esc_html_e( 'Encrypted end-to-end', 'site-blocks' ); ?></span>
								</li>
								<li>
									<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_scalable(); ?></span>
									<span class="sg-ajax-trust__label"><?php esc_html_e( 'Scalable and future-ready', 'site-blocks' ); ?></span>
								</li>
								<li>
									<span class="sg-ajax-trust__icon" aria-hidden="true"><?php site_blocks_sg_icon_ajax_europe(); ?></span>
									<span class="sg-ajax-trust__label"><?php esc_html_e( 'Designed in Europe', 'site-blocks' ); ?></span>
								</li>
							</ul>
							<div class="sg-ajax-card__actions">
								<a class="sg-btn sg-btn--orange" href="<?php echo $quote; ?>">
									<?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?>
									<?php site_blocks_lucide_icon( 'arrow-right', 16 ); ?>
								</a>
								<a class="sg-btn sg-btn--secondary" href="<?php echo $ajax_page; ?>">
									<?php esc_html_e( 'Explore Ajax', 'site-blocks' ); ?>
									<?php site_blocks_lucide_icon( 'arrow-right', 16 ); ?>
								</a>
							</div>
						</div>
					</aside>
				</div>
			</div>
		</section>

		<section class="sg-band sg-portal-band sg-reveal" aria-labelledby="sg-portal-heading">
			<div class="sg-container sg-portal-band__grid">
				<div class="sg-portal-band__copy">
					<h2 id="sg-portal-heading" class="sg-portal-band__title">
						<?php esc_html_e( 'Your quote, photos and approvals in ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'one place', 'site-blocks' ); ?></span><?php esc_html_e( '.', 'site-blocks' ); ?>
					</h2>
					<p class="sg-portal-band__intro"><?php esc_html_e( 'Our secure portal keeps everything organised so you always know where your quote stands.', 'site-blocks' ); ?></p>
					<ul class="sg-portal-band__list" role="list">
						<li>
							<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
							<?php esc_html_e( 'Track your quote progress in real time', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
							<?php esc_html_e( 'View and upload photos and documents', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
							<?php esc_html_e( 'Chat with our team directly in the portal', 'site-blocks' ); ?>
						</li>
						<li>
							<span class="sg-portal-band__check" aria-hidden="true"><?php site_blocks_sg_icon_portal_check(); ?></span>
							<?php esc_html_e( 'Approve your estimate when you\'re ready', 'site-blocks' ); ?>
						</li>
					</ul>
				</div>
				<div class="sg-portal-band__visual">
					<img
						class="sg-portal-band__img"
						src="<?php echo $portal_dash; ?>"
						alt="<?php esc_attr_e( 'Safeguard customer portal dashboard showing quote status, uploaded photos, messages and system details', 'site-blocks' ); ?>"
						width="928"
						height="458"
						loading="lazy"
						decoding="async"
					/>
				</div>
			</div>
		</section>

		<section class="sg-band sg-value-stack sg-reveal" aria-label="<?php esc_attr_e( 'Why Safeguard', 'site-blocks' ); ?>">
			<div class="sg-value-row sg-value-row--white">
				<div class="sg-container sg-value-row__grid">
					<div class="sg-value-row__copy">
						<h2 class="sg-value-row__title" id="sg-benefit-quotes-heading">
							<?php esc_html_e( 'Less waiting.', 'site-blocks' ); ?><br>
							<?php esc_html_e( 'Less guesswork.', 'site-blocks' ); ?><br>
							<span class="sg-accent"><?php esc_html_e( 'Better-prepared', 'site-blocks' ); ?></span><br>
							<?php esc_html_e( 'quotes.', 'site-blocks' ); ?>
						</h2>
					</div>
					<div class="sg-value-row__content sg-value-row__content--cards">
						<article class="sg-benefit-card">
							<div class="sg-benefit-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_benefit_remote(); ?></div>
							<h3><?php esc_html_e( 'We review remotely where possible', 'site-blocks' ); ?></h3>
							<p><?php esc_html_e( 'Our team reviews your photos and details to understand your site before any visit.', 'site-blocks' ); ?></p>
						</article>
						<article class="sg-benefit-card">
							<div class="sg-benefit-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_benefit_visit(); ?></div>
							<h3><?php esc_html_e( 'Site visits when they\'re needed', 'site-blocks' ); ?></h3>
							<p><?php esc_html_e( 'When required, we visit your site to confirm details and ensure accuracy.', 'site-blocks' ); ?></p>
						</article>
						<article class="sg-benefit-card">
							<div class="sg-benefit-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_benefit_estimate(); ?></div>
							<h3><?php esc_html_e( 'Better-prepared estimates', 'site-blocks' ); ?></h3>
							<p><?php esc_html_e( 'You receive a complete, accurate quote that reflects your site and requirements.', 'site-blocks' ); ?></p>
						</article>
					</div>
				</div>
			</div>

			<div class="sg-value-row sg-value-row--peach">
				<div class="sg-container sg-value-row__grid">
					<div class="sg-value-row__copy">
						<h2 class="sg-value-row__title" id="sg-benefit-estimate-heading">
							<span class="sg-accent"><?php esc_html_e( 'One clear', 'site-blocks' ); ?></span>
							<?php esc_html_e( ' estimate for the whole solution.', 'site-blocks' ); ?>
						</h2>
						<p class="sg-value-row__intro"><?php esc_html_e( 'No part-by-part pricing. You get one comprehensive estimate covering the complete system we design and install for your site.', 'site-blocks' ); ?></p>
					</div>
					<div class="sg-value-row__content sg-value-row__content--estimate">
						<article class="sg-benefit-estimate-card">
							<div class="sg-benefit-estimate-card__price">
								<span class="sg-benefit-estimate-card__label"><?php esc_html_e( 'Estimate subtotal (incl. GST)', 'site-blocks' ); ?></span>
								<span class="sg-benefit-estimate-card__amount"><?php esc_html_e( 'Estimate in review', 'site-blocks' ); ?></span>
							</div>
							<ul class="sg-benefit-estimate-card__includes" role="list">
								<li>
									<span class="sg-benefit-estimate-card__check" aria-hidden="true"><?php site_blocks_sg_icon_benefit_check(); ?></span>
									<?php esc_html_e( 'System design and equipment', 'site-blocks' ); ?>
								</li>
								<li>
									<span class="sg-benefit-estimate-card__check" aria-hidden="true"><?php site_blocks_sg_icon_benefit_check(); ?></span>
									<?php esc_html_e( 'Professional installation', 'site-blocks' ); ?>
								</li>
								<li>
									<span class="sg-benefit-estimate-card__check" aria-hidden="true"><?php site_blocks_sg_icon_benefit_check(); ?></span>
									<?php esc_html_e( 'Testing and commissioning', 'site-blocks' ); ?>
								</li>
								<li>
									<span class="sg-benefit-estimate-card__check" aria-hidden="true"><?php site_blocks_sg_icon_benefit_check(); ?></span>
									<?php esc_html_e( 'Support and documentation', 'site-blocks' ); ?>
								</li>
							</ul>
						</article>
						<aside class="sg-benefit-trust-card">
							<div class="sg-benefit-trust-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_benefit_shield(); ?></div>
							<p class="sg-benefit-trust-card__text">
								<?php esc_html_e( 'Transparent.', 'site-blocks' ); ?><br>
								<?php esc_html_e( 'Complete.', 'site-blocks' ); ?><br>
								<?php esc_html_e( 'No surprises.', 'site-blocks' ); ?>
							</p>
						</aside>
					</div>
				</div>
			</div>

			<div class="sg-value-row sg-value-row--white">
				<div class="sg-container sg-value-row__grid">
					<div class="sg-value-row__copy">
						<h2 class="sg-value-row__title" id="sg-benefit-team-heading">
							<?php esc_html_e( 'Designed and supported by ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'experienced', 'site-blocks' ); ?></span><br>
							<?php esc_html_e( 'technicians.', 'site-blocks' ); ?>
						</h2>
					</div>
					<div class="sg-value-row__content sg-value-row__content--team">
						<article class="sg-team-card">
							<div class="sg-team-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_team_technician(); ?></div>
							<div class="sg-team-card__body">
								<h3><?php esc_html_e( 'Experienced technicians', 'site-blocks' ); ?></h3>
								<p><?php esc_html_e( 'Qualified, industry-trained professionals you can trust.', 'site-blocks' ); ?></p>
							</div>
						</article>
						<article class="sg-team-card">
							<div class="sg-team-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_team_buildings(); ?></div>
							<div class="sg-team-card__body">
								<h3><?php esc_html_e( 'Residential & commercial', 'site-blocks' ); ?></h3>
								<p><?php esc_html_e( 'Solutions for homes, businesses and large-scale sites.', 'site-blocks' ); ?></p>
							</div>
						</article>
						<article class="sg-team-card">
							<div class="sg-team-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_team_docs(); ?></div>
							<div class="sg-team-card__body">
								<h3><?php esc_html_e( 'Support & documentation', 'site-blocks' ); ?></h3>
								<p><?php esc_html_e( 'Clear handovers, user guides and ongoing support.', 'site-blocks' ); ?></p>
							</div>
						</article>
						<article class="sg-team-card">
							<div class="sg-team-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_team_monitor(); ?></div>
							<div class="sg-team-card__body">
								<h3><?php esc_html_e( 'Maintenance & Monitoring', 'site-blocks' ); ?></h3>
								<p><?php esc_html_e( 'Keep your system performing with proactive support.', 'site-blocks' ); ?></p>
							</div>
						</article>
					</div>
				</div>
			</div>

			<?php
			site_blocks_render_faq_section(
				$faq_items,
				array(
					'heading_id'     => 'sg-faq-heading',
					'heading_before' => __( 'Frequently asked ', 'site-blocks' ),
					'heading_accent' => __( 'questions', 'site-blocks' ),
					'id_prefix'      => 'sg-faq-',
					'element'        => 'div',
					'alignfull'      => false,
					'columns_split'  => 3,
				)
			);
			?>
		</section>

		<?php
		site_blocks_render_quote_cta(
			array(
				'heading_id'    => 'sg-cta-heading',
				'before'        => __( 'A clearer path to the right system ', 'site-blocks' ),
				'accent'        => __( 'starts here.', 'site-blocks' ),
				'sub'           => __( 'Start your quote today and let our technicians design the right solution for your site.', 'site-blocks' ),
				'section_class' => 'sg-reveal',
				'alignfull'     => false,
			)
		);
		?>
	</main>

	<?php
	require_once SITE_BLOCKS_DIR . 'inc/safeguard-footer.php';
	site_blocks_render_safeguard_footer();
	site_blocks_render_safeguard_mobile_bar();
	?>
</div>
	<?php
}

function site_blocks_sg_icon_portal_check(): void {
	site_blocks_lucide_icon( 'circle-check', 22, 'sg-lucide-icon--filled-check' );
}

function site_blocks_sg_icon_feature_shield(): void {
	site_blocks_lucide_icon( 'shield-check', 18 );
}

function site_blocks_sg_icon_feature_target(): void {
	site_blocks_lucide_icon( 'target', 18 );
}

function site_blocks_sg_icon_feature_gear(): void {
	site_blocks_lucide_icon( 'settings', 18 );
}
