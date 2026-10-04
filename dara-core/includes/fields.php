<?php
/**
 * Custom fields: definitions, meta registration, meta boxes and saving.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions per post type, grouped in meta boxes.
 *
 * Each field: type (text|number|select|checkbox|url|textarea|lines|gallery|agent|email), label, [options], [help].
 *
 * @return array post_type => array( box_id => array( title, fields ) )
 */
function dara_core_fields() {
	static $fields = null;
	if ( null !== $fields ) {
		return $fields;
	}

	$fields = array(
		'dara_property' => array(
			'dara-property-main'     => array(
				'title'  => __( 'Property details', 'dara-core' ),
				'fields' => array(
					'_dara_purpose'      => array( 'select', __( 'Purpose', 'dara-core' ), array( 'sale' => __( 'For sale', 'dara-core' ), 'rent' => __( 'For rent', 'dara-core' ) ) ),
					'_dara_price'        => array( 'number', __( 'Price', 'dara-core' ), null, __( 'Numbers only. Leave empty to show "Price on request".', 'dara-core' ) ),
					'_dara_price_period' => array( 'select', __( 'Rent period', 'dara-core' ), array( '' => '—', 'year' => __( 'Yearly', 'dara-core' ), 'month' => __( 'Monthly', 'dara-core' ), 'day' => __( 'Daily', 'dara-core' ) ) ),
					'_dara_area'         => array( 'number', __( 'Area (m²)', 'dara-core' ) ),
					'_dara_beds'         => array( 'number', __( 'Bedrooms', 'dara-core' ) ),
					'_dara_baths'        => array( 'number', __( 'Bathrooms', 'dara-core' ) ),
					'_dara_age'          => array( 'text', __( 'Property age', 'dara-core' ), null, __( 'e.g. New, 5 years', 'dara-core' ) ),
					'_dara_facing'       => array( 'select', __( 'Facing', 'dara-core' ), array( '' => '—', 'north' => __( 'North', 'dara-core' ), 'south' => __( 'South', 'dara-core' ), 'east' => __( 'East', 'dara-core' ), 'west' => __( 'West', 'dara-core' ), 'corner' => __( 'Corner', 'dara-core' ) ) ),
					'_dara_condition'    => array( 'select', __( 'Condition', 'dara-core' ), array( '' => '—', 'ready' => __( 'Ready to move', 'dara-core' ), 'construction' => __( 'Under construction', 'dara-core' ), 'offplan' => __( 'Off-plan', 'dara-core' ) ) ),
					'_dara_featured'     => array( 'checkbox', __( 'Featured property', 'dara-core' ) ),
					'_dara_agent'        => array( 'agent', __( 'Agent', 'dara-core' ) ),
				),
			),
			'dara-property-media'    => array(
				'title'  => __( 'Gallery, floor plans & video', 'dara-core' ),
				'fields' => array(
					'_dara_gallery'    => array( 'gallery', __( 'Photo gallery', 'dara-core' ) ),
					'_dara_floorplans' => array( 'gallery', __( 'Floor plans', 'dara-core' ), null, __( 'Use each image caption as the floor name (e.g. Ground floor).', 'dara-core' ) ),
					'_dara_video'      => array( 'url', __( 'Video URL (YouTube / Vimeo)', 'dara-core' ) ),
				),
			),
			'dara-property-location' => array(
				'title'  => __( 'Location', 'dara-core' ),
				'fields' => array(
					'_dara_address' => array( 'text', __( 'Address', 'dara-core' ) ),
					'_dara_lat'     => array( 'text', __( 'Latitude', 'dara-core' ), null, __( 'Right-click the location in Google Maps to copy the coordinates.', 'dara-core' ) ),
					'_dara_lng'     => array( 'text', __( 'Longitude', 'dara-core' ) ),
					'_dara_nearby'  => array( 'lines', __( 'Nearby places', 'dara-core' ), null, __( 'One per line: Place | Distance', 'dara-core' ) ),
				),
			),
			'dara-property-license'  => array(
				'title'  => __( 'Advertising license', 'dara-core' ),
				'fields' => array(
					'_dara_ad_license' => array( 'text', __( 'Ad license number', 'dara-core' ) ),
				),
			),
		),
		'dara_project'  => array(
			'dara-project-main'   => array(
				'title'  => __( 'Project details', 'dara-core' ),
				'fields' => array(
					'_dara_developer'   => array( 'text', __( 'Developer', 'dara-core' ) ),
					'_dara_status'      => array( 'select', __( 'Status', 'dara-core' ), array( 'offplan' => __( 'Off-plan sales', 'dara-core' ), 'construction' => __( 'Under construction', 'dara-core' ), 'ready' => __( 'Ready', 'dara-core' ) ) ),
					'_dara_progress'    => array( 'number', __( 'Completion (%)', 'dara-core' ) ),
					'_dara_price_from'  => array( 'number', __( 'Prices from', 'dara-core' ) ),
					'_dara_units_count' => array( 'number', __( 'Number of units', 'dara-core' ) ),
					'_dara_unit_types'  => array( 'text', __( 'Unit types', 'dara-core' ), null, __( 'e.g. Apartments 2–4 bedrooms', 'dara-core' ) ),
					'_dara_delivery'    => array( 'text', __( 'Delivery date', 'dara-core' ) ),
					'_dara_wafi'        => array( 'checkbox', __( 'Licensed off-plan sales (Wafi)', 'dara-core' ) ),
					'_dara_brochure'    => array( 'url', __( 'Brochure URL (PDF)', 'dara-core' ) ),
					'_dara_address'     => array( 'text', __( 'Address', 'dara-core' ) ),
					'_dara_lat'         => array( 'text', __( 'Latitude', 'dara-core' ) ),
					'_dara_lng'         => array( 'text', __( 'Longitude', 'dara-core' ) ),
				),
			),
			'dara-project-tables' => array(
				'title'  => __( 'Units, payment plan & phases', 'dara-core' ),
				'fields' => array(
					'_dara_amenities' => array( 'lines', __( 'Amenities', 'dara-core' ), null, __( 'One per line.', 'dara-core' ) ),
					'_dara_units'     => array( 'lines', __( 'Units table', 'dara-core' ), null, __( 'One per line: Model | Type | Area | Bedrooms | Price | available/reserved/sold', 'dara-core' ) ),
					'_dara_payment'   => array( 'lines', __( 'Payment plan', 'dara-core' ), null, __( 'One per line: Percent | Title | Note', 'dara-core' ) ),
					'_dara_phases'    => array( 'lines', __( 'Construction phases', 'dara-core' ), null, __( 'One per line: Date | Title | done/current/upcoming', 'dara-core' ) ),
					'_dara_gallery'   => array( 'gallery', __( 'Photo gallery', 'dara-core' ) ),
				),
			),
		),
		'dara_agent'    => array(
			'dara-agent-main' => array(
				'title'  => __( 'Contact details', 'dara-core' ),
				'fields' => array(
					'_dara_phone'    => array( 'text', __( 'Phone', 'dara-core' ) ),
					'_dara_whatsapp' => array( 'text', __( 'WhatsApp number', 'dara-core' ), null, __( 'International format without + (e.g. 9665XXXXXXXX)', 'dara-core' ) ),
					'_dara_email'    => array( 'email', __( 'Email', 'dara-core' ) ),
					'_dara_fal'      => array( 'text', __( 'FAL license number', 'dara-core' ) ),
				),
			),
		),
	);

	return $fields;
}

/**
 * Flat list of fields for a post type.
 *
 * @param string $post_type Post type.
 * @return array
 */
function dara_core_flat_fields( $post_type ) {
	$all  = dara_core_fields();
	$flat = array();
	if ( isset( $all[ $post_type ] ) ) {
		foreach ( $all[ $post_type ] as $box ) {
			$flat = array_merge( $flat, $box['fields'] );
		}
	}
	return $flat;
}

/**
 * Register meta for REST / block editor and typed sanitizing.
 */
function dara_core_register_meta() {
	foreach ( array_keys( dara_core_fields() ) as $post_type ) {
		foreach ( dara_core_flat_fields( $post_type ) as $key => $field ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => function ( $value ) use ( $field ) {
						return dara_core_sanitize_field( $value, $field );
					},
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'dara_core_register_meta', 20 );

/**
 * Sanitize a single field value.
 *
 * @param mixed $value Raw value.
 * @param array $field Field definition.
 * @return string
 */
function dara_core_sanitize_field( $value, $field ) {
	$value = is_string( $value ) ? $value : (string) $value;
	switch ( $field[0] ) {
		case 'number':
			$value = str_replace( array( ',', ' ' ), '', $value );
			return is_numeric( $value ) ? (string) ( 0 + $value ) : '';
		case 'select':
			return array_key_exists( $value, $field[2] ) ? $value : '';
		case 'checkbox':
			return $value ? '1' : '';
		case 'url':
			return esc_url_raw( $value );
		case 'email':
			return sanitize_email( $value );
		case 'lines':
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'gallery':
			return implode( ',', array_filter( array_map( 'absint', explode( ',', $value ) ) ) );
		case 'agent':
			return (string) absint( $value );
		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Add meta boxes.
 */
function dara_core_add_meta_boxes() {
	foreach ( dara_core_fields() as $post_type => $boxes ) {
		foreach ( $boxes as $box_id => $box ) {
			add_meta_box( $box_id, $box['title'], 'dara_core_render_box', $post_type, 'normal', 'high', array( 'fields' => $box['fields'] ) );
		}
	}
}
add_action( 'add_meta_boxes', 'dara_core_add_meta_boxes' );

/**
 * Render a meta box.
 *
 * @param WP_Post $post Post.
 * @param array   $box  Box args.
 */
function dara_core_render_box( $post, $box ) {
	wp_nonce_field( 'dara_core_save', 'dara_core_nonce' );
	echo '<div class="dara-fields">';
	foreach ( $box['args']['fields'] as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$type  = $field[0];
		$id    = esc_attr( $key );
		$wide  = in_array( $type, array( 'lines', 'textarea', 'gallery' ), true ) ? ' dara-field--wide' : '';

		echo '<div class="dara-field' . esc_attr( $wide ) . '">';
		if ( 'checkbox' === $type ) {
			printf( '<label><input type="checkbox" name="%1$s" value="1"%2$s> %3$s</label>', $id, checked( $value, '1', false ), esc_html( $field[1] ) );
		} else {
			printf( '<label for="%1$s">%2$s</label>', $id, esc_html( $field[1] ) );
			switch ( $type ) {
				case 'select':
					echo '<select id="' . $id . '" name="' . $id . '">';
					foreach ( $field[2] as $opt => $label ) {
						printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
					}
					echo '</select>';
					break;
				case 'lines':
				case 'textarea':
					printf( '<textarea id="%1$s" name="%1$s" rows="5">%2$s</textarea>', $id, esc_textarea( $value ) );
					break;
				case 'gallery':
					echo '<div class="dara-gallery" data-multiple="1">';
					echo '<input type="hidden" id="' . $id . '" name="' . $id . '" value="' . esc_attr( $value ) . '">';
					echo '<ul class="dara-gallery__list">';
					foreach ( array_filter( explode( ',', (string) $value ) ) as $att_id ) {
						$thumb = wp_get_attachment_image_url( (int) $att_id, 'thumbnail' );
						if ( $thumb ) {
							printf( '<li data-id="%1$d"><img src="%2$s" alt=""><button type="button" class="dara-gallery__remove" aria-label="%3$s">×</button></li>', (int) $att_id, esc_url( $thumb ), esc_attr__( 'Remove', 'dara-core' ) );
						}
					}
					echo '</ul>';
					echo '<button type="button" class="button dara-gallery__add">' . esc_html__( 'Add images', 'dara-core' ) . '</button>';
					echo '</div>';
					break;
				case 'agent':
					$agents = get_posts(
						array(
							'post_type'      => 'dara_agent',
							'posts_per_page' => 200,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'fields'         => 'ids',
						)
					);
					echo '<select id="' . $id . '" name="' . $id . '"><option value="">' . esc_html__( '— Default contact —', 'dara-core' ) . '</option>';
					foreach ( $agents as $agent_id ) {
						printf( '<option value="%1$d"%2$s>%3$s</option>', (int) $agent_id, selected( (int) $value, (int) $agent_id, false ), esc_html( get_the_title( $agent_id ) ) );
					}
					echo '</select>';
					break;
				default:
					$input_type = in_array( $type, array( 'number', 'url', 'email' ), true ) ? $type : 'text';
					printf( '<input type="%1$s" id="%2$s" name="%2$s" value="%3$s"%4$s>', esc_attr( $input_type ), $id, esc_attr( $value ), 'number' === $type ? ' step="any" min="0"' : '' );
			}
		}
		if ( ! empty( $field[3] ) ) {
			echo '<p class="description">' . esc_html( $field[3] ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Save meta.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function dara_core_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['dara_core_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dara_core_nonce'] ), 'dara_core_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( dara_core_flat_fields( $post->post_type ) as $key => $field ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$value = dara_core_sanitize_field( $raw, $field );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post', 'dara_core_save_meta', 10, 2 );

/**
 * Term meta: icon for property types (shown on the home page tiles).
 *
 * @return array
 */
function dara_core_type_icons() {
	return array(
		'villa'      => __( 'Villa', 'dara-core' ),
		'apartment'  => __( 'Apartment', 'dara-core' ),
		'land'       => __( 'Land', 'dara-core' ),
		'office'     => __( 'Office / commercial', 'dara-core' ),
		'building'   => __( 'Building', 'dara-core' ),
		'rest-house' => __( 'Rest house', 'dara-core' ),
	);
}

/**
 * Icon field on the "add term" screen.
 */
function dara_core_type_icon_add() {
	echo '<div class="form-field"><label for="dara_icon">' . esc_html__( 'Icon', 'dara-core' ) . '</label><select name="dara_icon" id="dara_icon">';
	foreach ( dara_core_type_icons() as $key => $label ) {
		printf( '<option value="%1$s">%2$s</option>', esc_attr( $key ), esc_html( $label ) );
	}
	echo '</select></div>';
}
add_action( 'property_type_add_form_fields', 'dara_core_type_icon_add' );

/**
 * Icon field on the "edit term" screen.
 *
 * @param WP_Term $term Term.
 */
function dara_core_type_icon_edit( $term ) {
	$current = get_term_meta( $term->term_id, 'dara_icon', true );
	echo '<tr class="form-field"><th><label for="dara_icon">' . esc_html__( 'Icon', 'dara-core' ) . '</label></th><td><select name="dara_icon" id="dara_icon">';
	foreach ( dara_core_type_icons() as $key => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
	}
	echo '</select></td></tr>';
}
add_action( 'property_type_edit_form_fields', 'dara_core_type_icon_edit' );

/**
 * Save the term icon.
 *
 * @param int $term_id Term ID.
 */
function dara_core_type_icon_save( $term_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- core verifies the term form nonce.
	if ( isset( $_POST['dara_icon'] ) && current_user_can( 'manage_categories' ) ) {
		$icon = sanitize_key( wp_unslash( $_POST['dara_icon'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( array_key_exists( $icon, dara_core_type_icons() ) ) {
			update_term_meta( $term_id, 'dara_icon', $icon );
		}
	}
}
add_action( 'created_property_type', 'dara_core_type_icon_save' );
add_action( 'edited_property_type', 'dara_core_type_icon_save' );
