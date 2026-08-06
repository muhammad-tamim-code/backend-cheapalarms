<?php
/**
 * ManPower hub hero — 3-column layout (features | portrait | copy).
 *
 * @package Site_Blocks
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/manpower-config.php';
require_once SITE_BLOCKS_DIR . 'inc/safeguard-stage-hero.php';
require_once SITE_BLOCKS_DIR . 'inc/lucide-icons.php';

$page_key = site_blocks_get_manpower_page_key();

if ( null === $page_key ) {
	return;
}

$config = site_blocks_manpower_hero_config( $page_key );

if ( null === $config ) {
	return;
}

$id       = (string) $config['id'];
$features = isset( $config['features'] ) && is_array( $config['features'] ) ? $config['features'] : array();
$stats    = isset( $config['stats'] ) && is_array( $config['stats'] ) ? $config['stats'] : array();
$leads    = isset( $config['leads'] ) && is_array( $config['leads'] ) ? $config['leads'] : array();

if ( $leads === array() && '' !== (string) ( $config['lead'] ?? '' ) ) {
	$leads = array( (string) $config['lead'] );
}

$primary_url   = (string) ( $config['primary_url'] ?? home_url( '/get-an-instant-quote/' ) );
$primary_label = (string) ( $config['primary_label'] ?? __( 'Hire ManPower', 'site-blocks' ) );

$hero_image = 'images/manpower/' . ltrim( (string) $config['hero_image'], '/' );
$hero_alt   = (string) $config['hero_alt'];

$title = trim(
	(string) ( $config['title_before'] ?? '' )
	. (string) ( $config['title_accent'] ?? '' )
	. (string) ( $config['title_after'] ?? '' )
);

$breadcrumb = isset( $config['breadcrumb'] ) && is_array( $config['breadcrumb'] ) ? $config['breadcrumb'] : array();
?>
<section class="sg-mp-trio-hero alignfull" aria-labelledby="<?php echo esc_attr( $id ); ?>">
	<div class="sg-mp-trio-hero__inner">
		<?php if ( $breadcrumb !== array() ) : ?>
			<nav class="sg-mp-trio-hero__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'site-blocks' ); ?>">
				<?php
				$i     = 0;
				$count = count( $breadcrumb );
				foreach ( $breadcrumb as $crumb ) :
					++$i;
					$label   = (string) ( $crumb['label'] ?? '' );
					$url     = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
					$current = ! empty( $crumb['current'] );
					?>
					<?php if ( $current || '' === $url ) : ?>
						<span<?php echo $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></span>
					<?php else : ?>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endif; ?>
					<?php if ( $i < $count ) : ?>
						<span class="sg-mp-trio-hero__crumb-sep" aria-hidden="true">&gt;</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<div class="sg-mp-trio-hero__grid">
			<?php if ( $features !== array() ) : ?>
				<ul class="sg-mp-trio-hero__features" role="list">
					<?php foreach ( $features as $item ) : ?>
						<li class="sg-mp-trio-hero__feature">
							<span class="sg-mp-trio-hero__feature-icon" aria-hidden="true">
								<?php site_blocks_lucide_icon( (string) ( $item['icon'] ?? 'user-check' ), 22 ); ?>
							</span>
							<div class="sg-mp-trio-hero__feature-copy">
								<span class="sg-mp-trio-hero__feature-title"><?php echo esc_html( (string) ( $item['label'] ?? $item['title'] ?? '' ) ); ?></span>
								<?php if ( '' !== (string) ( $item['desc'] ?? '' ) ) : ?>
									<span class="sg-mp-trio-hero__feature-desc"><?php echo esc_html( (string) $item['desc'] ); ?></span>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="sg-mp-trio-hero__visual">
				<figure class="sg-mp-trio-hero__figure">
					<?php
					site_blocks_stage_hero_media(
						$hero_image,
						$hero_alt,
						'sg-mp-trio-hero__img',
						'eager'
					);
					?>
				</figure>
			</div>

			<div class="sg-mp-trio-hero__copy">
				<?php if ( '' !== (string) ( $config['badge'] ?? '' ) ) : ?>
					<p class="sg-mp-trio-hero__eyebrow"><?php echo esc_html( (string) $config['badge'] ); ?></p>
				<?php endif; ?>

				<h1 id="<?php echo esc_attr( $id ); ?>" class="sg-mp-trio-hero__title">
					<?php echo esc_html( $title ); ?>
				</h1>

				<?php foreach ( $leads as $lead_para ) : ?>
					<?php if ( '' !== (string) $lead_para ) : ?>
						<p class="sg-mp-trio-hero__lead"><?php echo esc_html( (string) $lead_para ); ?></p>
					<?php endif; ?>
				<?php endforeach; ?>

				<div class="sg-mp-trio-hero__ctas">
					<a class="sg-btn sg-btn--soft-blue" href="<?php echo esc_url( $primary_url ); ?>">
						<?php echo esc_html( $primary_label ); ?>
					</a>
				</div>

				<?php if ( $stats !== array() ) : ?>
					<ul class="sg-mp-trio-hero__stats" role="list">
						<?php foreach ( $stats as $stat ) : ?>
							<li>
								<span class="sg-mp-trio-hero__stat-value"><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?></span>
								<span class="sg-mp-trio-hero__stat-label"><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php
$trust_strip = $config['trust_strip'] ?? array();

if ( is_array( $trust_strip ) && $trust_strip !== array() ) {
	site_blocks_render_trust_strip(
		array(
			'items'         => $trust_strip,
			'section_class' => 'sg-mp-trust-strip',
		)
	);
}
