<?php

/**

 * Intercom, better with the rest of your system.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



require_once SITE_BLOCKS_DIR . 'inc/intercom-icons.php';

require_once SITE_BLOCKS_DIR . 'inc/safeguard-ajax-card.php';



$links = array(

	array(

		'title' => __( 'Access Control', 'site-blocks' ),

		'desc'  => __( 'Decide who enters, and when.', 'site-blocks' ),

		'url'   => home_url( '/access-control/' ),

		'icon'  => 'access-control.png',

	),

	array(

		'title' => __( 'CCTV & Security Cameras', 'site-blocks' ),

		'desc'  => __( 'See and record every entry.', 'site-blocks' ),

		'url'   => home_url( '/cctv-security-cameras/' ),

		'icon'  => 'ip-camera.png',

	),

	array(

		'title' => __( 'Alarm Systems', 'site-blocks' ),

		'desc'  => __( 'Detect and respond to intrusion.', 'site-blocks' ),

		'url'   => home_url( '/alarm-systems/' ),

		'icon'  => 'alarm-systems.png',

	),

);

?>

<section class="sg-band sg-band--white sg-cctv-layered sg-intercom-layered alignfull" aria-labelledby="sg-intercom-layered-heading">

	<div class="sg-container sg-intercom-layered__inner">

		<header class="sg-cctv-layered__header">

			<h2 id="sg-intercom-layered-heading" class="sg-section-title sg-section-title--ink sg-cctv-layered__title">

				<?php esc_html_e( 'Better with the rest of your ', 'site-blocks' ); ?>

				<span class="sg-accent"><?php esc_html_e( 'system', 'site-blocks' ); ?></span>

			</h2>

			<span class="sg-cctv-layered__rule" aria-hidden="true"></span>

			<p class="sg-cctv-layered__lead">

				<?php esc_html_e( 'Link your intercom with CCTV, access control and alarms, one connected system, one team, one quote.', 'site-blocks' ); ?>

			</p>

		</header>



		<div class="sg-cctv-layered__split sg-intercom-layered__split">

			<div class="sg-intercom-layered__cards" role="list">

				<?php foreach ( $links as $link ) : ?>

					<a class="sg-cctv-layered__card sg-intercom-layered__card" href="<?php echo esc_url( $link['url'] ); ?>" role="listitem">

						<span class="sg-cctv-icon sg-cctv-layered__card-icon" aria-hidden="true">

							<?php site_blocks_intercom_icon( $link['icon'] ); ?>

						</span>

						<span class="sg-cctv-layered__card-body">

							<span class="sg-cctv-layered__card-title"><?php echo esc_html( $link['title'] ); ?></span>

							<span class="sg-cctv-layered__card-desc"><?php echo esc_html( $link['desc'] ); ?></span>

						</span>

						<span class="sg-cctv-layered__card-chevron" aria-hidden="true">&rsaquo;</span>

					</a>

				<?php endforeach; ?>

			</div>



			<aside class="sg-cctv-layered__aside sg-intercom-layered__aside" aria-label="<?php esc_attr_e( 'Safeguard + Ajax', 'site-blocks' ); ?>">

				<?php site_blocks_render_safeguard_ajax_card(); ?>

			</aside>

		</div>

	</div>

</section>

