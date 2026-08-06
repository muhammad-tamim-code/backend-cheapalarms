<?php
/**
 * Ajax Alarm Systems, monitoring options row.
 *
 * @package Site_Blocks
 *
 * @var array $attributes Block attributes.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/ajax-alarm-systems-icons.php';

$options = array(
	array(
		'title'       => __( 'Self-monitoring via app', 'site-blocks' ),
		'description' => __( 'Monitor your Ajax system directly from the Ajax app on your phone with instant alerts when sensors trigger.', 'site-blocks' ),
		'icon'        => 'wifi.png',
	),
	array(
		'title'       => __( 'Professional monitoring', 'site-blocks' ),
		'description' => __( 'Connect your system to a monitoring centre for 24/7 response when alarms are activated.', 'site-blocks' ),
		'icon'        => 'shield.png',
	),
	array(
		'title'       => __( 'Backup communication', 'site-blocks' ),
		'description' => __( 'Dual-path communication using Ethernet, Wi-Fi and cellular to keep the system online.', 'site-blocks' ),
		'icon'        => '4g.png',
	),
);
?>
<section class="sg-ajax-section sg-ajax-monitoring alignfull" aria-labelledby="sg-ajax-monitoring-heading">
	<div class="sg-container">
		<header class="sg-ajax-section__header">
			<p class="sg-ajax-section__eyebrow"><?php esc_html_e( 'Monitoring', 'site-blocks' ); ?></p>
			<h2 id="sg-ajax-monitoring-heading" class="sg-ajax-section__title">
				<?php esc_html_e( 'Choose the alarm response that suits how you want to manage security.', 'site-blocks' ); ?>
			</h2>
		</header>

		<div class="sg-ajax-monitoring__grid">
			<?php foreach ( $options as $option ) : ?>
				<article class="sg-ajax-icon-card">
					<div class="sg-ajax-icon-card__icon" aria-hidden="true">
						<?php site_blocks_ajax_hero_icon( $option['icon'] ); ?>
					</div>
					<h3 class="sg-ajax-icon-card__title"><?php echo esc_html( $option['title'] ); ?></h3>
					<p class="sg-ajax-icon-card__desc"><?php echo esc_html( $option['description'] ); ?></p>
				</article>
			<?php endforeach; ?>

			<article class="sg-ajax-icon-card sg-ajax-icon-card--cta">
				<h3 class="sg-ajax-icon-card__title"><?php esc_html_e( 'Not sure which monitoring option fits?', 'site-blocks' ); ?></h3>
				<p class="sg-ajax-icon-card__desc">
					<?php esc_html_e( 'Every property is different. We can recommend the right monitoring setup based on how you want to manage security.', 'site-blocks' ); ?>
				</p>
				<a class="sg-ajax-icon-card__link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<?php esc_html_e( 'Speak to Us', 'site-blocks' ); ?>
					<?php site_blocks_lucide_icon( 'arrow-right', 14 ); ?>
				</a>
			</article>
		</div>
	</div>
</section>
