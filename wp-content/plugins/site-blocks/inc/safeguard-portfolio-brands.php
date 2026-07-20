<?php

/**

 * Portfolio brands for homepage logo strip.

 *

 * Auto-fetched logos are NOT trusted until manually verified, favicons often

 * return WordPress, wrong entities, or tiny broken assets.

 *

 * @package Site_Blocks

 */



declare( strict_types=1 );



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



/**

 * Brands Safeguard has worked with (logo strip / portfolio page).

 *

 * @param bool $verified_only When true, only brands with verified artwork are returned.

 * @return array<int, array<string, mixed>>

 */

function site_blocks_get_portfolio_brands( bool $verified_only = false ): array {

	$base = SITE_BLOCKS_DIR . 'assets/images/portfolio/';



	$brands = array(

		array( 'slug' => 'woolworths', 'name' => 'Woolworths', 'file' => 'woolworths.png', 'verified' => true ),

		array( 'slug' => 'kfc', 'name' => 'KFC', 'file' => 'kfc.png', 'verified' => true ),

		array( 'slug' => 'australia-post', 'name' => 'Australia Post', 'file' => 'australia-post.png', 'verified' => true ),

		array( 'slug' => 'nab', 'name' => 'NAB', 'file' => 'nab.png', 'verified' => true ),

		array( 'slug' => 'storageplus', 'name' => 'StoragePlus', 'file' => 'storageplus.png', 'verified' => true ),

		array( 'slug' => 'zone-bowling', 'name' => 'Zone Bowling', 'file' => 'zone-bowling.png', 'verified' => true ),

		array( 'slug' => 'jas-forwarding', 'name' => 'JAS Forwarding', 'file' => 'jas-forwarding.png', 'verified' => true ),

		array( 'slug' => 'specific-freight', 'name' => 'Specific Freight', 'file' => 'specific-freight.png', 'verified' => true ),

		// Needs official artwork before showing on site.

		array( 'slug' => 'rathdrum-properties', 'name' => 'Rathdrum Properties', 'file' => 'rathdrum-properties.png', 'verified' => false ),

		array( 'slug' => 'sbm', 'name' => 'SBM', 'file' => 'sbm.png', 'verified' => false ),

		array( 'slug' => 'freechoice', 'name' => 'Freechoice', 'file' => 'freechoice.png', 'verified' => false ),

		array( 'slug' => 'timezone', 'name' => 'Timezone', 'file' => 'timezone.png', 'verified' => false ),

		array( 'slug' => 'kingpin', 'name' => 'Kingpin', 'file' => 'kingpin.png', 'verified' => false ),

		array( 'slug' => 'zambrero', 'name' => 'Zambrero', 'file' => 'zambrero.svg', 'verified' => false ),

		array( 'slug' => 'bp', 'name' => 'BP', 'file' => 'bp.svg', 'verified' => false ),

	);



	foreach ( $brands as $i => $brand ) {

		$path = $base . $brand['file'];

		if ( is_readable( $path ) ) {

			$brands[ $i ]['url'] = site_blocks_asset_url( 'images/portfolio/' . $brand['file'] );

			if ( str_ends_with( $brand['file'], '.svg' ) ) {

				$brands[ $i ]['status'] = 'placeholder';

			} elseif ( empty( $brand['verified'] ) ) {

				$brands[ $i ]['status'] = 'unverified';

			} else {

				$brands[ $i ]['status'] = 'verified';

			}

		} else {

			$brands[ $i ]['url']    = '';

			$brands[ $i ]['status'] = 'missing';

		}

	}



	if ( $verified_only ) {

		$brands = array_values(

			array_filter(

				$brands,

				static function ( array $brand ): bool {

					return ! empty( $brand['verified'] ) && ! empty( $brand['url'] );

				}

			)

		);

	}



	return $brands;

}


