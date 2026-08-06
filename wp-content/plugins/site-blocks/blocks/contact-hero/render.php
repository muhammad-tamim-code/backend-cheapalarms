<?php
/**
 * Contact Hero block — V5 centered editorial.
 *
 * @package Site_Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SITE_BLOCKS_DIR . 'inc/safeguard-contact-details.php';
require_once SITE_BLOCKS_DIR . 'inc/hero-variants.php';

$defaults = site_blocks_get_safeguard_contact_details();

$headline = ! empty( $attributes['headline'] ) ? (string) $attributes['headline'] : __( 'Contact us', 'site-blocks' );
$subhead  = ! empty( $attributes['subhead'] ) ? (string) $attributes['subhead'] : __( 'Questions about quotes, installs or support? Call, email or send a message and our team will get back to you.', 'site-blocks' );
$phone    = $defaults['phone'];
$phone_h  = 'tel:' . preg_replace( '/[^\d+]/', '', $phone );

$schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'ContactPage',
	'name'        => get_bloginfo( 'name' ) . ', ' . __( 'Contact', 'site-blocks' ),
	'description' => wp_strip_all_tags( $subhead ),
	'url'         => get_permalink(),
);

$title_before = $headline;
$title_accent = '';
$title_after  = '';
$parts        = explode( ' ', $headline, 2 );
if ( count( $parts ) === 2 ) {
	$title_before = $parts[0] . ' ';
	$title_accent = $parts[1];
}

$phone_html = sprintf(
	'<a href="%s">%s</a>',
	esc_attr( $phone_h ),
	esc_html( $phone )
);
?>
<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php
site_blocks_render_hero_variant(
	'editorial',
	array(
		'id'              => 'sg-contact-hero-heading',
		'class'           => 'sg-contact-hero',
		'breadcrumb'      => array(
			array(
				'label' => __( 'Home', 'site-blocks' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label'   => __( 'Contact', 'site-blocks' ),
				'current' => true,
			),
		),
		'badge'           => __( 'Contact · Sydney', 'site-blocks' ),
		'title_before'    => $title_before,
		'title_accent'    => $title_accent,
		'title_after'     => $title_after,
		'lead'            => $subhead,
		'phone_html'      => $phone_html,
		'primary_label'   => __( 'Send a message', 'site-blocks' ),
		'primary_url'     => '#sg-contact-form',
		'secondary_label' => __( 'Call us', 'site-blocks' ),
		'secondary_url'   => $phone_h,
		'proof'           => array(
			array(
				'label' => __( 'Phone', 'site-blocks' ),
				'value' => $phone,
			),
			array(
				'label' => __( 'Hours', 'site-blocks' ),
				'value' => (string) ( $defaults['hoursNote'] ?? '' ),
			),
			array(
				'label' => __( 'Licensed', 'site-blocks' ),
				'value' => __( 'Master Licence #000103619', 'site-blocks' ),
			),
		),
	)
);
