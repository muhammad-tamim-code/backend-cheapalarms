<?php
/**
 * Alarm Systems, Why Safeguard block render.
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

$eyebrow          = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : __( 'Why Safeguard for alarms', 'site-blocks' );
$headline_before  = isset( $attributes['headlineBefore'] ) ? (string) $attributes['headlineBefore'] : __( 'One', 'site-blocks' );
$headline_accent  = isset( $attributes['headlineAccent'] ) ? (string) $attributes['headlineAccent'] : __( 'team', 'site-blocks' );
$headline_after   = isset( $attributes['headlineAfter'] ) ? (string) $attributes['headlineAfter'] : __( ', start to finish', 'site-blocks' );

$items = array(
	array(
		'title'       => __( 'Designed around your home', 'site-blocks' ),
		'description' => __( 'A real installer plans the system for your property, not a generic kit.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_why_design',
	),
	array(
		'title'       => __( 'Installed properly', 'site-blocks' ),
		'description' => __( 'Tidy work, clean cabling, and a clear walkthrough before we leave.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_why_install',
	),
	array(
		'title'       => __( 'Supported for years', 'site-blocks' ),
		'description' => __( 'Servicing and monitoring long after install, not just on day one.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_why_support',
	),
);
?>
<section class="sg-band sg-alarm-why alignfull" aria-labelledby="sg-alarm-why-heading">
	<div class="sg-container">
		<header class="sg-alarm-why__header">
			<p class="sg-alarm-why__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h2 id="sg-alarm-why-heading" class="sg-alarm-why__title">
				<?php echo esc_html( $headline_before ); ?>
				<span class="sg-accent"><?php echo esc_html( $headline_accent ); ?></span><?php echo esc_html( $headline_after ); ?>
			</h2>
		</header>

		<div class="sg-alarm-why__panel">
			<?php foreach ( $items as $item ) : ?>
				<article class="sg-alarm-why__item">
					<div class="sg-alarm-why__icon" aria-hidden="true">
						<?php
						if ( is_callable( $item['icon'] ) ) {
							call_user_func( $item['icon'] );
						}
						?>
					</div>
					<h3 class="sg-alarm-why__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="sg-alarm-why__item-desc"><?php echo esc_html( $item['description'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
