<?php
/**
 * Alarm Systems — How it works block render.
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

$eyebrow          = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : __( 'How it works', 'site-blocks' );
$headline_before  = isset( $attributes['headlineBefore'] ) ? (string) $attributes['headlineBefore'] : __( 'Simple steps, done ', 'site-blocks' );
$headline_accent  = isset( $attributes['headlineAccent'] ) ? (string) $attributes['headlineAccent'] : __( 'properly', 'site-blocks' );
$intro            = isset( $attributes['intro'] ) ? (string) $attributes['intro'] : __( 'A fast online quote, reviewed by a technician before anything is installed.', 'site-blocks' );

$steps = array(
	array(
		'title'       => __( 'Tell us what you need', 'site-blocks' ),
		'description' => __( 'Answer a few quick questions about your home and goals.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_step_tell',
	),
	array(
		'title'       => __( 'Share a few photos', 'site-blocks' ),
		'description' => __( 'Upload photos so we can assess the space properly.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_step_photos',
	),
	array(
		'title'       => __( 'Get a tailored price', 'site-blocks' ),
		'description' => __( 'See an instant online quote based on your property.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_step_price',
	),
	array(
		'title'       => __( 'Reviewed by a technician', 'site-blocks' ),
		'description' => __( 'A real installer checks everything before you commit.', 'site-blocks' ),
		'icon'        => 'site_blocks_alarm_icon_step_review',
	),
);
?>
<section class="sg-alarm-steps alignfull" aria-labelledby="sg-alarm-steps-heading">
	<div class="sg-alarm-steps__bg" aria-hidden="true"></div>
	<div class="sg-container sg-alarm-steps__inner">
		<header class="sg-alarm-steps__header">
			<p class="sg-alarm-steps__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h2 id="sg-alarm-steps-heading" class="sg-alarm-steps__title">
				<?php echo esc_html( $headline_before ); ?><span class="sg-accent"><?php echo esc_html( $headline_accent ); ?></span>
			</h2>
			<?php if ( '' !== $intro ) : ?>
				<p class="sg-alarm-steps__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</header>

		<ol class="sg-alarm-steps__list" role="list">
			<?php foreach ( $steps as $index => $step ) : ?>
				<li class="sg-alarm-step-card">
					<span class="sg-alarm-step-card__num" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
					<div class="sg-alarm-step-card__icon" aria-hidden="true">
						<?php
						if ( is_callable( $step['icon'] ) ) {
							call_user_func( $step['icon'] );
						}
						?>
					</div>
					<h3 class="sg-alarm-step-card__title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="sg-alarm-step-card__desc"><?php echo esc_html( $step['description'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
