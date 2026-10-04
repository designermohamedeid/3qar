<?php
/**
 * Public helpers used by the theme.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin settings with defaults.
 *
 * @param string $key Setting key.
 * @return string
 */
function dara_setting( $key ) {
	$defaults = array(
		'currency'    => __( 'SAR', 'dara-core' ),
		'lead_email'  => get_option( 'admin_email' ),
		'office_fal'  => '',
		'office_cr'   => '',
		'agent_name'  => '',
		'agent_phone' => '',
		'agent_wa'    => '',
	);
	$options = (array) get_option( 'dara_core_settings', array() );
	$value   = isset( $options[ $key ] ) && '' !== $options[ $key ] ? $options[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	return apply_filters( 'dara_setting', $value, $key );
}

/**
 * Format a number with the site locale.
 *
 * @param float|string $number Number.
 * @return string
 */
function dara_number( $number ) {
	return number_format_i18n( (float) $number, floor( (float) $number ) == (float) $number ? 0 : 1 ); // phpcs:ignore Universal.Operators.StrictComparisons
}

/**
 * Purpose label.
 *
 * @param string $purpose sale|rent.
 * @return string
 */
function dara_purpose_label( $purpose ) {
	return 'rent' === $purpose ? __( 'For rent', 'dara-core' ) : __( 'For sale', 'dara-core' );
}

/**
 * Price split in amount + unit, for display.
 *
 * @param int $post_id Property or project ID.
 * @param string $key  Meta key.
 * @return array{amount:string,unit:string,raw:float}
 */
function dara_price_parts( $post_id, $key = '_dara_price' ) {
	$raw = get_post_meta( $post_id, $key, true );
	if ( '' === $raw ) {
		return array(
			'amount' => __( 'Price on request', 'dara-core' ),
			'unit'   => '',
			'raw'    => 0,
		);
	}
	$unit   = dara_setting( 'currency' );
	$period = get_post_meta( $post_id, '_dara_price_period', true );
	$labels = array(
		'year'  => __( 'yearly', 'dara-core' ),
		'month' => __( 'monthly', 'dara-core' ),
		'day'   => __( 'daily', 'dara-core' ),
	);
	if ( '_dara_price' === $key && 'rent' === get_post_meta( $post_id, '_dara_purpose', true ) && isset( $labels[ $period ] ) ) {
		$unit .= ' / ' . $labels[ $period ];
	}
	return array(
		'amount' => dara_number( $raw ),
		'unit'   => $unit,
		'raw'    => (float) $raw,
	);
}

/**
 * Price as one string.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function dara_format_price( $post_id ) {
	$parts = dara_price_parts( $post_id );
	return trim( $parts['amount'] . ' ' . $parts['unit'] );
}

/**
 * Short price for map pins (1.2M / 850K).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function dara_short_price( $post_id ) {
	$raw = (float) get_post_meta( $post_id, '_dara_price', true );
	if ( $raw >= 1000000 ) {
		return round( $raw / 1000000, 2 ) . 'M';
	}
	if ( $raw >= 1000 ) {
		return round( $raw / 1000 ) . 'K';
	}
	return $raw ? (string) $raw : '•';
}

/**
 * Gallery attachment IDs (featured image first).
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return int[]
 */
function dara_gallery_ids( $post_id, $key = '_dara_gallery' ) {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, $key, true ) ) ) );
	if ( '_dara_gallery' === $key && has_post_thumbnail( $post_id ) ) {
		array_unshift( $ids, (int) get_post_thumbnail_id( $post_id ) );
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Parse "a | b | c" lines.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param int    $columns Expected columns.
 * @return array[]
 */
function dara_lines( $post_id, $key, $columns = 1 ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) get_post_meta( $post_id, $key, true ) ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$cells  = array_map( 'trim', explode( '|', $line ) );
		$rows[] = array_pad( array_slice( $cells, 0, $columns ), $columns, '' );
	}
	return $rows;
}

/**
 * Key facts of a property.
 *
 * @param int $post_id Post ID.
 * @return array[] label, value, icon
 */
function dara_property_facts( $post_id ) {
	$facts = array();
	$types = get_the_terms( $post_id, 'property_type' );
	if ( $types && ! is_wp_error( $types ) ) {
		$facts[] = array( __( 'Type', 'dara-core' ), $types[0]->name, 'home' );
	}
	$map = array(
		'_dara_area'  => array( __( 'Area', 'dara-core' ), 'area', __( 'm²', 'dara-core' ) ),
		'_dara_beds'  => array( __( 'Bedrooms', 'dara-core' ), 'bed', '' ),
		'_dara_baths' => array( __( 'Bathrooms', 'dara-core' ), 'bath', '' ),
		'_dara_age'   => array( __( 'Age', 'dara-core' ), 'calendar', '' ),
	);
	foreach ( $map as $key => $def ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( '' !== $value ) {
			$facts[] = array( $def[0], trim( ( is_numeric( $value ) ? dara_number( $value ) : $value ) . ' ' . $def[2] ), $def[1] );
		}
	}
	$selects = array(
		'_dara_facing'    => __( 'Facing', 'dara-core' ),
		'_dara_condition' => __( 'Condition', 'dara-core' ),
	);
	$fields  = dara_core_flat_fields( 'dara_property' );
	foreach ( $selects as $key => $label ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( $value && isset( $fields[ $key ][2][ $value ] ) ) {
			$facts[] = array( $label, $fields[ $key ][2][ $value ], 'compass' );
		}
	}
	return $facts;
}

/**
 * Select option label for a field.
 *
 * @param string $post_type Post type.
 * @param string $key       Meta key.
 * @param string $value     Stored value.
 * @return string
 */
function dara_option_label( $post_type, $key, $value ) {
	$fields = dara_core_flat_fields( $post_type );
	return isset( $fields[ $key ][2][ $value ] ) ? $fields[ $key ][2][ $value ] : '';
}

/**
 * Agent / contact for a property.
 *
 * @param int $post_id Property ID.
 * @return array
 */
function dara_get_agent( $post_id = 0 ) {
	$agent_id = $post_id ? (int) get_post_meta( $post_id, '_dara_agent', true ) : 0;
	if ( $agent_id && 'dara_agent' === get_post_type( $agent_id ) ) {
		$agent = array(
			'name'     => get_the_title( $agent_id ),
			'role'     => get_the_excerpt( $agent_id ),
			'phone'    => get_post_meta( $agent_id, '_dara_phone', true ),
			'whatsapp' => get_post_meta( $agent_id, '_dara_whatsapp', true ),
			'email'    => get_post_meta( $agent_id, '_dara_email', true ),
			'fal'      => get_post_meta( $agent_id, '_dara_fal', true ),
			'photo'    => (int) get_post_thumbnail_id( $agent_id ),
			'url'      => get_permalink( $agent_id ),
			'id'       => $agent_id,
		);
	} else {
		$agent = array(
			'name'     => dara_setting( 'agent_name' ) ? dara_setting( 'agent_name' ) : get_bloginfo( 'name' ),
			'role'     => __( 'Sales team', 'dara-core' ),
			'phone'    => dara_setting( 'agent_phone' ),
			'whatsapp' => dara_setting( 'agent_wa' ),
			'email'    => '',
			'fal'      => dara_setting( 'office_fal' ),
			'photo'    => 0,
			'url'      => '',
			'id'       => 0,
		);
	}
	return apply_filters( 'dara_agent', $agent, $post_id );
}

/**
 * WhatsApp link with a prefilled message.
 *
 * @param string $number  Number in international format.
 * @param string $message Message.
 * @return string
 */
function dara_whatsapp_url( $number, $message = '' ) {
	$number = preg_replace( '/\D+/', '', (string) $number );
	if ( ! $number ) {
		return '';
	}
	return 'https://wa.me/' . $number . ( $message ? '?text=' . rawurlencode( $message ) : '' );
}

/**
 * Lead types.
 *
 * @return array
 */
function dara_lead_types() {
	return array(
		'viewing' => __( 'Viewing request', 'dara-core' ),
		'project' => __( 'Project interest', 'dara-core' ),
		'listing' => __( 'List a property', 'dara-core' ),
		'contact' => __( 'Contact', 'dara-core' ),
		'newsletter' => __( 'Newsletter', 'dara-core' ),
	);
}

/**
 * Initials for avatar placeholders ("Mohamed Eid" -> "ME", "محمد العتيبي" -> "م ع").
 *
 * @param string $name Name.
 * @return string
 */
function dara_initials( $name ) {
	$words = preg_split( '/\s+/u', trim( (string) $name ) );
	$out   = array();
	foreach ( array_slice( array_filter( $words ), 0, 2 ) as $w ) {
		$w     = preg_replace( '/^ال(?=\p{Arabic}{2,})/u', '', $w ); // Skip the Arabic article.
		$out[] = mb_substr( $w, 0, 1 );
	}
	return implode( preg_match( '/\p{Arabic}/u', $name ) ? ' ' : '', $out );
}

/**
 * Number of published properties assigned to an agent.
 *
 * @param int $agent_id Agent ID.
 * @return int
 */
function dara_agent_listing_count( $agent_id ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'dara_property',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_dara_agent', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => (int) $agent_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return (int) $q->found_posts;
}
