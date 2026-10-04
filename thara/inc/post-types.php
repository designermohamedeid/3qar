<?php
/**
 * Custom post types: Properties and Services.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register post types and taxonomies.
 */
function thara_register_post_types() {
	register_post_type(
		'thara_property',
		array(
			'labels'        => array(
				'name'               => __( 'Properties', 'thara' ),
				'singular_name'      => __( 'Property', 'thara' ),
				'add_new'            => __( 'Add New', 'thara' ),
				'add_new_item'       => __( 'Add New Property', 'thara' ),
				'edit_item'          => __( 'Edit Property', 'thara' ),
				'new_item'           => __( 'New Property', 'thara' ),
				'view_item'          => __( 'View Property', 'thara' ),
				'search_items'       => __( 'Search Properties', 'thara' ),
				'not_found'          => __( 'No properties found.', 'thara' ),
				'not_found_in_trash' => __( 'No properties found in Trash.', 'thara' ),
				'all_items'          => __( 'All Properties', 'thara' ),
				'menu_name'          => __( 'Properties', 'thara' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-building',
			'menu_position' => 5,
			'rewrite'       => array( 'slug' => 'properties' ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'property_type',
		'thara_property',
		array(
			'labels'            => array(
				'name'          => __( 'Property Types', 'thara' ),
				'singular_name' => __( 'Property Type', 'thara' ),
				'add_new_item'  => __( 'Add New Property Type', 'thara' ),
				'edit_item'     => __( 'Edit Property Type', 'thara' ),
				'search_items'  => __( 'Search Property Types', 'thara' ),
				'all_items'     => __( 'All Property Types', 'thara' ),
				'menu_name'     => __( 'Property Types', 'thara' ),
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'property-type' ),
		)
	);

	register_post_type(
		'thara_service',
		array(
			'labels'        => array(
				'name'               => __( 'Services', 'thara' ),
				'singular_name'      => __( 'Service', 'thara' ),
				'add_new'            => __( 'Add New', 'thara' ),
				'add_new_item'       => __( 'Add New Service', 'thara' ),
				'edit_item'          => __( 'Edit Service', 'thara' ),
				'view_item'          => __( 'View Service', 'thara' ),
				'search_items'       => __( 'Search Services', 'thara' ),
				'not_found'          => __( 'No services found.', 'thara' ),
				'not_found_in_trash' => __( 'No services found in Trash.', 'thara' ),
				'all_items'          => __( 'All Services', 'thara' ),
				'menu_name'          => __( 'Services', 'thara' ),
				'featured_image'     => __( 'Service icon', 'thara' ),
				'set_featured_image' => __( 'Set service icon', 'thara' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-awards',
			'menu_position' => 6,
			'rewrite'       => array( 'slug' => 'services' ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);

	foreach ( thara_property_fields() as $key => $field ) {
		register_post_meta(
			'thara_property',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'thara_register_post_types' );

/**
 * Flush rewrite rules once when the theme is activated.
 */
function thara_flush_rewrites() {
	thara_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'thara_flush_rewrites' );

/**
 * Property meta fields.
 *
 * @return array
 */
function thara_property_fields() {
	return array(
		'_thara_status'    => array(
			'label'   => __( 'Status', 'thara' ),
			'icon'    => 'lnr lnr-tag',
			'type'    => 'select',
			'options' => array(
				''     => __( '— Select —', 'thara' ),
				'sale' => __( 'For sale', 'thara' ),
				'rent' => __( 'For rent', 'thara' ),
				'sold' => __( 'Sold', 'thara' ),
			),
		),
		'_thara_price'     => array(
			'label' => __( 'Price', 'thara' ),
			'icon'  => 'lnr lnr-diamond',
			'type'  => 'text',
		),
		'_thara_location'  => array(
			'label' => __( 'Location', 'thara' ),
			'icon'  => 'lnr lnr-map-marker',
			'type'  => 'text',
		),
		'_thara_area'      => array(
			'label'  => __( 'Area', 'thara' ),
			'icon'   => 'lnr lnr-frame-expand',
			'type'   => 'number',
			'suffix' => __( 'm²', 'thara' ),
		),
		'_thara_bedrooms'  => array(
			'label' => __( 'Bedrooms', 'thara' ),
			'icon'  => 'lnr lnr-home',
			'type'  => 'number',
		),
		'_thara_bathrooms' => array(
			'label' => __( 'Bathrooms', 'thara' ),
			'icon'  => 'lnr lnr-drop',
			'type'  => 'number',
		),
	);
}

/**
 * Property details meta box.
 */
function thara_add_property_meta_box() {
	add_meta_box( 'thara-property-details', __( 'Property Details', 'thara' ), 'thara_render_property_meta_box', 'thara_property', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'thara_add_property_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post.
 */
function thara_render_property_meta_box( $post ) {
	wp_nonce_field( 'thara_save_property', 'thara_property_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( thara_property_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		if ( 'select' === $field['type'] ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
			foreach ( $field['options'] as $option => $label ) {
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $label ) );
			}
			echo '</select>';
		} else {
			printf(
				'<input type="%1$s" class="regular-text" id="%2$s" name="%2$s" value="%3$s"%4$s>',
				esc_attr( $field['type'] ),
				esc_attr( $key ),
				esc_attr( $value ),
				'number' === $field['type'] ? ' min="0" step="any"' : ''
			);
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Save property meta.
 *
 * @param int $post_id Post ID.
 */
function thara_save_property_meta( $post_id ) {
	if ( ! isset( $_POST['thara_property_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['thara_property_nonce'] ), 'thara_save_property' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( thara_property_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		if ( 'select' === $field['type'] && ! array_key_exists( $value, $field['options'] ) ) {
			$value = '';
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_thara_property', 'thara_save_property_meta' );

/**
 * Show more properties per page on the archive.
 *
 * @param WP_Query $query Query.
 */
function thara_property_archive_query( $query ) {
	if ( ! is_admin() && $query->is_main_query() && ( $query->is_post_type_archive( 'thara_property' ) || $query->is_tax( 'property_type' ) ) ) {
		$query->set( 'posts_per_page', 9 );
	}
	if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'thara_service' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'thara_property_archive_query' );
