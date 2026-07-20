<?php
/**
 * Alarm Systems services grid block render.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/alarm-systems-icons.php';

$heading = isset( $attributes['heading'] ) ? (string) $attributes['heading'] : __( 'What we install', 'site-blocks' );
$intro   = isset( $attributes['intro'] ) ? (string) $attributes['intro'] : __( 'Wireless, wired and smart alarm solutions, specified for your property, not pulled off a shelf.', 'site-blocks' );

$cards = array(
	array(
		'title'       => __( 'Wireless & smart alarms', 'site-blocks' ),
		'description' => __( 'Ajax-grade systems, app-controlled, no mess of cabling.', 'site-blocks' ),
		'url'         => '/get-an-instant-quote/',
		'icon'        => 'site_blocks_alarm_icon_wireless',
	),
	array(
		'title'       => __( 'Home alarm systems', 'site-blocks' ),
		'description' => __( 'Considered protection for the family home, without the clutter.', 'site-blocks' ),
		'url'         => '/get-an-instant-quote/',
		'icon'        => 'site_blocks_alarm_icon_home',
	),
	array(
		'title'       => __( 'Business alarm systems', 'site-blocks' ),
		'description' => __( 'Multi-zone intrusion detection for offices, retail and warehouses.', 'site-blocks' ),
		'url'         => '/get-an-instant-quote/',
		'icon'        => 'site_blocks_alarm_icon_business',
	),
	array(
		'title'       => __( 'Alarm upgrades', 'site-blocks' ),
		'description' => __( 'Replace an old or false-triggering panel with something you\'ll actually trust.', 'site-blocks' ),
		'url'         => '/contact/',
		'icon'        => 'site_blocks_alarm_icon_upgrade',
	),
	array(
		'title'       => __( 'Servicing & repairs', 'site-blocks' ),
		'description' => __( 'Keep an existing system performing, or get a dead one back online.', 'site-blocks' ),
		'url'         => '/contact/',
		'icon'        => 'site_blocks_alarm_icon_service',
	),
	array(
		'title'       => __( 'Back-to-base monitoring', 'site-blocks' ),
		'description' => __( 'Someone watching when the alarm trips, 24/7.', 'site-blocks' ),
		'url'         => '/monitoring/back-to-base/',
		'icon'        => 'site_blocks_alarm_icon_monitoring',
	),
);
?>
<section class="sg-band sg-band--white sg-alarm-services alignfull" aria-labelledby="sg-alarm-services-heading">
	<div class="sg-container">
		<header class="sg-alarm-services__header">
			<h2 id="sg-alarm-services-heading" class="sg-section-title sg-section-title--center sg-section-title--ink">
				<?php echo esc_html( $heading ); ?>
			</h2>
			<?php if ( '' !== $intro ) : ?>
				<p class="sg-section-intro sg-section-intro--center"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sg-alarm-services__grid">
			<?php foreach ( $cards as $card ) : ?>
				<article class="sg-alarm-service-card">
					<div class="sg-alarm-service-card__icon" aria-hidden="true">
						<?php
						if ( is_callable( $card['icon'] ) ) {
							call_user_func( $card['icon'] );
						}
						?>
					</div>
					<h3 class="sg-alarm-service-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
					<p class="sg-alarm-service-card__desc"><?php echo esc_html( $card['description'] ); ?></p>
					<a class="sg-alarm-service-card__link" href="<?php echo esc_url( home_url( $card['url'] ) ); ?>">
						<?php esc_html_e( 'Learn more', 'site-blocks' ); ?>
						<?php site_blocks_lucide_icon( 'arrow-right', 14 ); ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
