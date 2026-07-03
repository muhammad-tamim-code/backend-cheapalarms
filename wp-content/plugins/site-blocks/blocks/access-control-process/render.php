<?php

/**

 * Access Control — radial hub-and-spoke process (reference layout).

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



require_once SITE_BLOCKS_DIR . 'inc/access-control-media.php';

require_once SITE_BLOCKS_DIR . 'inc/access-control-process-icons.php';



$steps = array(

	array(

		'num'         => '01',

		'title'       => __( 'Tell us your doors and users', 'site-blocks' ),

		'description' => __( 'Entry points and who needs access.', 'site-blocks' ),

	),

	array(

		'num'         => '02',

		'title'       => __( 'Share site details and photos', 'site-blocks' ),

		'description' => __( 'So we understand the layout.', 'site-blocks' ),

	),

	array(

		'num'         => '03',

		'title'       => __( 'Receive a tailored estimate', 'site-blocks' ),

		'description' => __( 'Scoped around your actual site.', 'site-blocks' ),

	),

	array(

		'num'         => '04',

		'title'       => __( 'Technician review', 'site-blocks' ),

		'description' => __( 'A real installer checks it first.', 'site-blocks' ),

	),

	array(

		'num'         => '05',

		'title'       => __( 'Professional installation', 'site-blocks' ),

		'description' => __( 'Fitted and tested cleanly.', 'site-blocks' ),

	),

	array(

		'num'         => '06',

		'title'       => __( 'Handover, training & support', 'site-blocks' ),

		'description' => __( 'Set up, trained, supported after.', 'site-blocks' ),

	),

);



$heading_id = 'sg-access-control-process-heading';
$hub_logo   = site_blocks_asset_url( 'images/brand/safeguard-logo.png' );

?>

<section class="sg-band sg-access-control-process sg-ac-process alignfull" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">

	<div class="sg-container sg-ac-process__inner">

		<div class="sg-ac-process__head">

			<span class="sg-ac-process__eyebrow"><?php esc_html_e( 'The Process', 'site-blocks' ); ?></span>

			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="sg-ac-process__title">

				<?php

				echo wp_kses(

					__( 'One team, from site visit to final <span class="accent">handover</span>', 'site-blocks' ),

					array(

						'span' => array(

							'class' => array(),

						),

					)

				);

				?>

			</h2>

		</div>



		<div class="sg-ac-process__radial" aria-hidden="true">

			<?php for ( $i = 0; $i < 6; $i++ ) : ?>

				<span class="sg-ac-process__spoke" style="<?php echo esc_attr( '--i: ' . $i . ';' ); ?>"></span>

			<?php endfor; ?>



			<div class="sg-ac-process__hub">
				<img
					class="sg-ac-process__hub-logo"
					src="<?php echo esc_url( $hub_logo ); ?>"
					alt="<?php esc_attr_e( 'Safeguard Security Services', 'site-blocks' ); ?>"
					width="140"
					height="28"
					loading="lazy"
					decoding="async"
				/>
			</div>



			<ol class="sg-ac-process__orbit" role="list">

				<?php foreach ( $steps as $index => $step ) : ?>

					<li

						class="sg-ac-process__step"

						style="<?php echo esc_attr( '--i: ' . $index . ';' ); ?>"

						data-pos="<?php echo esc_attr( site_blocks_access_control_process_pos( $index ) ); ?>"

					>

						<div class="sg-ac-process__node" aria-hidden="true">

							<?php site_blocks_access_control_process_thumb( (int) $step['num'], $step['title'] ); ?>

						</div>

						<div class="sg-ac-process__label">

							<p class="sg-ac-process__kicker">

								<?php

								printf(

									/* translators: %s: step number e.g. 01 */

									esc_html__( 'Step %s', 'site-blocks' ),

									esc_html( $step['num'] )

								);

								?>

							</p>

							<h3 class="sg-ac-process__name"><?php echo esc_html( $step['title'] ); ?></h3>

							<p class="sg-ac-process__desc"><?php echo esc_html( $step['description'] ); ?></p>

						</div>

					</li>

				<?php endforeach; ?>

			</ol>

		</div>



		<ol class="sg-ac-process__stack" role="list">

			<?php foreach ( $steps as $step ) : ?>

				<li class="sg-ac-process__stack-row">

					<div class="sg-ac-process__node" aria-hidden="true">

						<?php site_blocks_access_control_process_thumb( (int) $step['num'], $step['title'] ); ?>

					</div>

					<div class="sg-ac-process__stack-body">

						<p class="sg-ac-process__kicker">

							<?php

							printf(

								esc_html__( 'Step %s', 'site-blocks' ),

								esc_html( $step['num'] )

							);

							?>

						</p>

						<h3 class="sg-ac-process__name"><?php echo esc_html( $step['title'] ); ?></h3>

						<p class="sg-ac-process__desc"><?php echo esc_html( $step['description'] ); ?></p>

					</div>

				</li>

			<?php endforeach; ?>

		</ol>

	</div>

</section>

