<?php
/**
 * JSON-LD structured data for properties and projects (SEO).
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print JSON-LD on single property / project pages.
 */
function dara_core_schema() {
	if ( ! is_singular( array( 'dara_property', 'dara_project' ) ) || ! apply_filters( 'dara_output_schema', true ) ) {
		return;
	}
	$id     = get_queried_object_id();
	$images = array();
	foreach ( array_slice( dara_gallery_ids( $id ), 0, 5 ) as $att ) {
		$url = wp_get_attachment_image_url( $att, 'large' );
		if ( $url ) {
			$images[] = $url;
		}
	}

	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'RealEstateListing',
		'name'        => get_the_title( $id ),
		'url'         => get_permalink( $id ),
		'description' => wp_strip_all_tags( get_the_excerpt( $id ) ),
		'datePosted'  => get_the_date( 'c', $id ),
		'image'       => $images,
	);

	$key   = 'dara_project' === get_post_type( $id ) ? '_dara_price_from' : '_dara_price';
	$price = get_post_meta( $id, $key, true );
	if ( '' !== $price ) {
		$data['offers'] = array(
			'@type'         => 'Offer',
			'price'         => (float) $price,
			'priceCurrency' => apply_filters( 'dara_currency_code', 'SAR' ),
			'availability'  => 'https://schema.org/InStock',
		);
	}

	$lat     = get_post_meta( $id, '_dara_lat', true );
	$lng     = get_post_meta( $id, '_dara_lng', true );
	$address = get_post_meta( $id, '_dara_address', true );
	if ( $address || ( $lat && $lng ) ) {
		$place = array( '@type' => 'Place' );
		if ( $address ) {
			$place['address'] = $address;
		}
		if ( $lat && $lng ) {
			$place['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $lat,
				'longitude' => (float) $lng,
			);
		}
		$data['contentLocation'] = $place;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( apply_filters( 'dara_schema', $data, $id ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'dara_core_schema' );
