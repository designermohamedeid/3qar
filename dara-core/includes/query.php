<?php
/**
 * Property search & filtering on the archive (GET parameters).
 *
 * ?purpose=sale|rent &type=slug &location=text &price=min-max &min_price= &max_price=
 * &beds=N (minimum) &baths=N &min_area= &max_area= &features[]=slug &condition= &sort=newest|price_asc|price_desc|area_desc
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitized filter values from the request.
 *
 * @return array
 */
function dara_get_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public search form.
	$get = wp_unslash( $_GET );
	// phpcs:enable

	$num = function ( $key ) use ( $get ) {
		if ( ! isset( $get[ $key ] ) || '' === $get[ $key ] ) {
			return '';
		}
		$value = str_replace( array( ',', ' ' ), '', (string) $get[ $key ] );
		return is_numeric( $value ) ? (float) $value : '';
	};

	$filters = array(
		'purpose'   => isset( $get['purpose'] ) && in_array( $get['purpose'], array( 'sale', 'rent' ), true ) ? $get['purpose'] : '',
		'type'      => isset( $get['type'] ) ? array_filter( array_map( 'sanitize_title', (array) $get['type'] ) ) : array(),
		'location'  => isset( $get['location'] ) ? sanitize_text_field( $get['location'] ) : '',
		'min_price' => $num( 'min_price' ),
		'max_price' => $num( 'max_price' ),
		'beds'      => $num( 'beds' ),
		'baths'     => $num( 'baths' ),
		'min_area'  => $num( 'min_area' ),
		'max_area'  => $num( 'max_area' ),
		'features'  => isset( $get['features'] ) ? array_filter( array_map( 'sanitize_title', (array) $get['features'] ) ) : array(),
		'condition' => isset( $get['condition'] ) ? sanitize_key( $get['condition'] ) : '',
		'sort'      => isset( $get['sort'] ) && in_array( $get['sort'], array( 'newest', 'price_asc', 'price_desc', 'area_desc' ), true ) ? $get['sort'] : 'newest',
	);

	// "price=min-max" shortcut used by the hero search.
	if ( ! empty( $get['price'] ) && preg_match( '/^(\d*)-(\d*)$/', (string) $get['price'], $m ) ) {
		$filters['min_price'] = '' !== $m[1] ? (float) $m[1] : $filters['min_price'];
		$filters['max_price'] = '' !== $m[2] ? (float) $m[2] : $filters['max_price'];
	}

	return $filters;
}

/**
 * Whether any filter is active.
 *
 * @return bool
 */
function dara_filters_active() {
	$f = dara_get_filters();
	unset( $f['sort'] );
	return (bool) array_filter(
		$f,
		function ( $v ) {
			return '' !== $v && array() !== $v;
		}
	);
}

/**
 * Apply filters to the main property query.
 *
 * @param WP_Query $query Query.
 */
function dara_core_filter_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'dara_project' ) ) {
		$query->set( 'posts_per_page', 9 );
		return;
	}

	if ( ! ( $query->is_post_type_archive( 'dara_property' ) || $query->is_tax( array( 'property_type', 'property_city' ) ) ) ) {
		return;
	}

	$f    = dara_get_filters();
	$meta = array( 'relation' => 'AND' );
	$tax  = array( 'relation' => 'AND' );

	$query->set( 'posts_per_page', 12 );

	if ( $f['purpose'] ) {
		$meta[] = array(
			'key'   => '_dara_purpose',
			'value' => $f['purpose'],
		);
	}
	if ( $f['condition'] ) {
		$meta[] = array(
			'key'   => '_dara_condition',
			'value' => $f['condition'],
		);
	}

	$ranges = array(
		'min_price' => array( '_dara_price', '>=' ),
		'max_price' => array( '_dara_price', '<=' ),
		'beds'      => array( '_dara_beds', '>=' ),
		'baths'     => array( '_dara_baths', '>=' ),
		'min_area'  => array( '_dara_area', '>=' ),
		'max_area'  => array( '_dara_area', '<=' ),
	);
	foreach ( $ranges as $param => $def ) {
		if ( '' !== $f[ $param ] && ( $f[ $param ] > 0 || '>=' !== $def[1] ) ) {
			$meta[] = array(
				'key'     => $def[0],
				'value'   => $f[ $param ],
				'compare' => $def[1],
				'type'    => 'NUMERIC',
			);
		}
	}

	if ( $f['type'] ) {
		$tax[] = array(
			'taxonomy' => 'property_type',
			'field'    => 'slug',
			'terms'    => $f['type'],
		);
	}
	if ( $f['features'] ) {
		$tax[] = array(
			'taxonomy' => 'property_feature',
			'field'    => 'slug',
			'terms'    => $f['features'],
			'operator' => 'AND',
		);
	}

	if ( $f['location'] ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'property_city',
				'name__like' => $f['location'],
				'fields'     => 'ids',
				'hide_empty' => false,
				'number'     => 20,
			)
		);
		if ( $terms && ! is_wp_error( $terms ) ) {
			$tax[] = array(
				'taxonomy'         => 'property_city',
				'terms'            => $terms,
				'include_children' => true,
			);
		} else {
			$meta[] = array(
				'key'     => '_dara_address',
				'value'   => $f['location'],
				'compare' => 'LIKE',
			);
		}
	}

	if ( count( $meta ) > 1 ) {
		$query->set( 'meta_query', $meta );
	}
	if ( count( $tax ) > 1 ) {
		$existing = (array) $query->get( 'tax_query' );
		$query->set( 'tax_query', array_merge( $tax, array_filter( $existing ) ) );
	}

	switch ( $f['sort'] ) {
		case 'price_asc':
		case 'price_desc':
			$query->set( 'meta_key', '_dara_price' );
			$query->set(
				'orderby',
				array(
					'meta_value_num' => 'price_asc' === $f['sort'] ? 'ASC' : 'DESC',
					'date'           => 'DESC',
				)
			);
			break;
		case 'area_desc':
			$query->set( 'meta_key', '_dara_area' );
			$query->set(
				'orderby',
				array(
					'meta_value_num' => 'DESC',
					'date'           => 'DESC',
				)
			);
			break;
	}
}
add_action( 'pre_get_posts', 'dara_core_filter_query' );

/**
 * Prime post meta & thumbnails for listing loops (avoids N+1 queries).
 *
 * @param WP_Post[] $posts Posts.
 * @param WP_Query  $query Query.
 * @return WP_Post[]
 */
function dara_core_prime_caches( $posts, $query ) {
	if ( is_admin() || empty( $posts ) ) {
		return $posts;
	}
	$type = $query->get( 'post_type' );
	if ( in_array( $type, array( 'dara_property', 'dara_project' ), true ) || $query->is_post_type_archive( array( 'dara_property', 'dara_project' ) ) || $query->is_tax( array( 'property_type', 'property_city' ) ) ) {
		update_post_thumbnail_cache( $query );
	}
	return $posts;
}
add_filter( 'the_posts', 'dara_core_prime_caches', 10, 2 );
