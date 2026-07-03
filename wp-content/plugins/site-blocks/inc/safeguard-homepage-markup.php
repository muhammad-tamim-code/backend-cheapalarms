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

	$hero_house  = site_blocks_asset_url( 'images/hero/house.png' );
	$hero_dash   = site_blocks_asset_url( 'images/hero/dashboard.png' );
	$portal_dash = site_blocks_asset_url( 'images/portal/portal-dashboard.png' );
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
		<section class="sg-hero sg-hero--dark sg-reveal" aria-labelledby="sg-hero-heading">
			<div class="sg-hero__dark-bg" aria-hidden="true"></div>
			<div class="sg-container sg-hero__grid sg-hero__grid--dark">
				<div class="sg-hero__copy sg-hero__copy--dark">
					<p class="sg-hero__badge"><?php esc_html_e( 'SMART SECURITY, DESIGNED AROUND YOU.', 'site-blocks' ); ?></p>
					<h1 id="sg-hero-heading" class="sg-hero__title sg-hero__title--dark">
						<?php esc_html_e( 'Most security quotes take days. Yours takes ', 'site-blocks' ); ?>
						<span class="sg-hero__title-accent"><?php esc_html_e( 'minutes', 'site-blocks' ); ?></span><?php esc_html_e( '.', 'site-blocks' ); ?>
					</h1>
					<p class="sg-hero__sub sg-hero__sub--dark"><?php esc_html_e( 'Build it online and get your price on the spot — or within 24 hours for complex sites, checked by a real technician. No 48-hour wait, no salesperson at your door.', 'site-blocks' ); ?></p>
					<div class="sg-hero__ctas">
						<a class="sg-btn sg-btn--primary" href="<?php echo $quote; ?>"><?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?></a>
						<a class="sg-btn sg-btn--ghost-dark" href="<?php echo $design; ?>"><?php esc_html_e( 'Help Me Choose', 'site-blocks' ); ?></a>
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

		<section class="sg-band sg-band--blue sg-reveal" aria-labelledby="sg-steps-heading">
			<div class="sg-container">
				<h2 id="sg-steps-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
					<?php esc_html_e( 'A ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'smarter', 'site-blocks' ); ?></span><?php esc_html_e( ' way to plan your security system', 'site-blocks' ); ?>
				</h2>

				<ol class="sg-steps sg-steps--connected" role="list">
					<li class="sg-step-card">
						<span class="sg-step-card__num" aria-hidden="true">1</span>
						<div class="sg-step-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_step_clipboard(); ?></div>
						<h3><?php esc_html_e( 'Tell us what you need', 'site-blocks' ); ?></h3>
						<p><?php esc_html_e( 'Answer a few simple questions about your property, goals and security needs.', 'site-blocks' ); ?></p>
					</li>
					<li class="sg-step-card">
						<span class="sg-step-card__num" aria-hidden="true">2</span>
						<div class="sg-step-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_step_camera(); ?></div>
						<h3><?php esc_html_e( 'Share your site details and photos', 'site-blocks' ); ?></h3>
						<p><?php esc_html_e( 'Upload photos and site details so our team can understand your space.', 'site-blocks' ); ?></p>
					</li>
					<li class="sg-step-card">
						<span class="sg-step-card__num" aria-hidden="true">3</span>
						<div class="sg-step-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_step_calculator(); ?></div>
						<h3><?php esc_html_e( 'Receive a tailored estimate', 'site-blocks' ); ?></h3>
						<p><?php esc_html_e( 'We design the right solution and send you one clear package estimate.', 'site-blocks' ); ?></p>
					</li>
					<li class="sg-step-card">
						<span class="sg-step-card__num" aria-hidden="true">4</span>
						<div class="sg-step-card__icon" aria-hidden="true"><?php site_blocks_sg_icon_step_review(); ?></div>
						<h3><?php esc_html_e( 'Expert review before you approve', 'site-blocks' ); ?></h3>
						<p><?php esc_html_e( 'Our technicians review your site and proposal, then you approve with confidence.', 'site-blocks' ); ?></p>
					</li>
				</ol>
			</div>
		</section>

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
							<p class="sg-ajax-card__intro"><?php esc_html_e( 'We partner with Ajax Systems to deliver intelligent, reliable security—installed and supported by experienced technicians.', 'site-blocks' ); ?></p>
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
								<a class="sg-btn sg-btn--ajax-primary" href="<?php echo $quote; ?>">
									<?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?>
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
								</a>
								<a class="sg-btn sg-btn--ajax-outline" href="<?php echo $ajax_page; ?>">
									<?php esc_html_e( 'Explore Ajax', 'site-blocks' ); ?>
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
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

			<div class="sg-value-row sg-value-row--peach">
				<div class="sg-container sg-value-row__grid">
					<div class="sg-value-row__copy">
						<h2 class="sg-value-row__title" id="sg-faq-heading">
							<?php esc_html_e( 'Frequently asked ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'questions', 'site-blocks' ); ?></span>
						</h2>
					</div>
					<div class="sg-value-row__content sg-value-row__content--faq">
						<div class="sg-value-faq">
							<div class="sg-value-faq__column">
								<?php
								foreach ( array_slice( $faq_items, 0, 3 ) as $faq_index => $faq_item ) {
									site_blocks_render_value_faq_item( $faq_item, $faq_index + 1 );
								}
								?>
							</div>
							<div class="sg-value-faq__column">
								<?php
								foreach ( array_slice( $faq_items, 3 ) as $faq_index => $faq_item ) {
									site_blocks_render_value_faq_item( $faq_item, $faq_index + 4 );
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="sg-cta sg-reveal" aria-labelledby="sg-cta-heading">
			<div class="sg-container">
				<div class="sg-cta-card">
					<h2 id="sg-cta-heading" class="sg-cta-card__head">
						<?php esc_html_e( 'A clearer path to the right system ', 'site-blocks' ); ?><span class="sg-accent"><?php esc_html_e( 'starts here.', 'site-blocks' ); ?></span>
					</h2>
					<p class="sg-cta-card__text"><?php esc_html_e( 'Start your quote today and let our technicians design the right solution for your site.', 'site-blocks' ); ?></p>
					<div class="sg-cta-card__btns">
						<a class="sg-btn sg-btn--primary" href="<?php echo $quote; ?>"><?php esc_html_e( 'Start My Quote', 'site-blocks' ); ?></a>
						<a class="sg-btn sg-btn--cta-ghost" href="<?php echo $design; ?>"><?php esc_html_e( 'Help Me Choose', 'site-blocks' ); ?></a>
					</div>
				</div>
			</div>
		</section>
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
	echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="11" cy="11" r="11" fill="#1769A1"/><path d="M7 11.2L9.6 13.8L15 8.4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function site_blocks_sg_icon_feature_shield(): void {
	echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>';
}

function site_blocks_sg_icon_feature_target(): void {
	echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h14V9.5"/><path d="M10 20v-6h4v6"/></svg>';
}

function site_blocks_sg_icon_feature_gear(): void {
	echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>';
}

function site_blocks_sg_icon_step_clipboard(): void {
	echo '<svg width="72" height="66" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="22" y="10" width="28" height="42" rx="3" stroke="#1769A1" stroke-width="3"/><path d="M42 10V20H50" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M28 28H44M28 35H40M28 42H36" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/><path d="M47 43L57 33C58.2 31.8 60.2 31.8 61.4 33L62 33.6C63.2 34.8 63.2 36.8 62 38L52 48L46 50L47 43Z" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M56 34L61 39" stroke="#FB7523" stroke-width="3" stroke-linecap="round"/></svg>';
}

function site_blocks_sg_icon_step_camera(): void {
	echo '<svg width="72" height="66" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="13" y="20" width="38" height="30" rx="5" stroke="#1769A1" stroke-width="3"/><path d="M25 20L28 14H38L41 20" stroke="#1769A1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><circle cx="32" cy="35" r="8" stroke="#1769A1" stroke-width="3"/><circle cx="45" cy="27" r="2" fill="#FB7523"/><path d="M54 33L62 36V43C62 50 58 55 54 57C50 55 46 50 46 43V36L54 33Z" stroke="#1769A1" stroke-width="3" stroke-linejoin="round"/><path d="M51 44L53.5 46.5L58 41.5" stroke="#FB7523" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function site_blocks_sg_icon_step_calculator(): void {
	echo '<svg width="72" height="66" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="20" y="10" width="32" height="52" rx="4" stroke="#1769A1" stroke-width="3"/><rect x="26" y="17" width="20" height="11" rx="2" stroke="#1769A1" stroke-width="3"/><circle cx="27" cy="38" r="2" fill="#1769A1"/><circle cx="36" cy="38" r="2" fill="#1769A1"/><circle cx="45" cy="38" r="2" fill="#1769A1"/><circle cx="27" cy="47" r="2" fill="#1769A1"/><circle cx="36" cy="47" r="2" fill="#1769A1"/><circle cx="45" cy="47" r="2" fill="#1769A1"/><path d="M28 56H44" stroke="#FB7523" stroke-width="3" stroke-linecap="round"/></svg>';
}

function site_blocks_sg_icon_step_review(): void {
	echo '<svg width="72" height="66" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M30 10L48 17V30C48 42 40.5 50 30 55C19.5 50 12 42 12 30V17L30 10Z" stroke="#1769A1" stroke-width="3" stroke-linejoin="round"/><path d="M23 31L28 36L38 25" stroke="#FB7523" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><circle cx="53" cy="31" r="6" stroke="#1769A1" stroke-width="3"/><path d="M42 58C43.5 50 48 45 53 45C58 45 62.5 50 64 58" stroke="#1769A1" stroke-width="3" stroke-linecap="round"/></svg>';
}
