<?php
/**
 * Developers: a taxonomy for projects with logo and contact details.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the taxonomy.
 */
function dara_core_register_developers() {
	register_taxonomy(
		'project_developer',
		array( 'dara_project' ),
		array(
			'labels'            => dara_core_labels( __( 'Developers', 'dara-core' ), __( 'Developer', 'dara-core' ) ),
			'hierarchical'      => true, // Checkbox list in the editor.
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'developer' ),
		)
	);
}
add_action( 'init', 'dara_core_register_developers' );

/**
 * Developer term fields: key => [ type, label ].
 *
 * @return array
 */
function dara_core_developer_fields() {
	return array(
		'dara_logo'    => array( 'image', __( 'Logo', 'dara-core' ) ),
		'dara_founded' => array( 'text', __( 'Founded', 'dara-core' ) ),
		'dara_website' => array( 'url', __( 'Website', 'dara-core' ) ),
		'dara_phone'   => array( 'text', __( 'Phone', 'dara-core' ) ),
		'dara_email'   => array( 'email', __( 'Email', 'dara-core' ) ),
	);
}

/**
 * Render one developer field.
 *
 * @param string $key   Meta key.
 * @param array  $field Field definition.
 * @param mixed  $value Current value.
 */
function dara_core_developer_input( $key, $field, $value ) {
	if ( 'image' === $field[0] ) {
		echo '<div class="dara-gallery" data-single="1">';
		echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
		echo '<ul class="dara-gallery__list">';
		$thumb = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
		if ( $thumb ) {
			printf( '<li data-id="%1$d"><img src="%2$s" alt=""><button type="button" class="dara-gallery__remove" aria-label="%3$s">×</button></li>', (int) $value, esc_url( $thumb ), esc_attr__( 'Remove', 'dara-core' ) );
		}
		echo '</ul><button type="button" class="button dara-gallery__add">' . esc_html__( 'Select logo', 'dara-core' ) . '</button></div>';
		return;
	}
	$type = 'text' === $field[0] ? 'text' : $field[0];
	printf( '<input type="%1$s" name="%2$s" id="%2$s" value="%3$s"%4$s>', esc_attr( $type ), esc_attr( $key ), esc_attr( $value ), 'text' !== $type ? ' dir="ltr"' : '' );
}

/**
 * Fields on the "add developer" form.
 */
function dara_core_developer_add_fields() {
	foreach ( dara_core_developer_fields() as $key => $field ) {
		echo '<div class="form-field"><label for="' . esc_attr( $key ) . '">' . esc_html( $field[1] ) . '</label>';
		dara_core_developer_input( $key, $field, '' );
		echo '</div>';
	}
}
add_action( 'project_developer_add_form_fields', 'dara_core_developer_add_fields' );

/**
 * Fields on the "edit developer" form.
 *
 * @param WP_Term $term Term.
 */
function dara_core_developer_edit_fields( $term ) {
	foreach ( dara_core_developer_fields() as $key => $field ) {
		echo '<tr class="form-field"><th><label for="' . esc_attr( $key ) . '">' . esc_html( $field[1] ) . '</label></th><td>';
		dara_core_developer_input( $key, $field, get_term_meta( $term->term_id, $key, true ) );
		echo '</td></tr>';
	}
}
add_action( 'project_developer_edit_form_fields', 'dara_core_developer_edit_fields' );

/**
 * Save developer fields.
 *
 * @param int $term_id Term ID.
 */
function dara_core_developer_save( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	foreach ( dara_core_developer_fields() as $key => $field ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- core verifies the term form nonce.
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below per type.
		switch ( $field[0] ) {
			case 'image':
				$value = absint( $raw );
				break;
			case 'url':
				$value = esc_url_raw( $raw );
				break;
			case 'email':
				$value = sanitize_email( $raw );
				break;
			default:
				$value = sanitize_text_field( $raw );
		}
		if ( $value ) {
			update_term_meta( $term_id, $key, $value );
		} else {
			delete_term_meta( $term_id, $key );
		}
	}
}
add_action( 'created_project_developer', 'dara_core_developer_save' );
add_action( 'edited_project_developer', 'dara_core_developer_save' );

/**
 * Logo column in the developers list.
 *
 * @param array $columns Columns.
 * @return array
 */
function dara_core_developer_columns( $columns ) {
	return array_slice( $columns, 0, 1, true ) + array( 'dara_logo' => __( 'Logo', 'dara-core' ) ) + array_slice( $columns, 1, null, true );
}
add_filter( 'manage_edit-project_developer_columns', 'dara_core_developer_columns' );

/**
 * Logo column content.
 *
 * @param string $out     Output.
 * @param string $column  Column.
 * @param int    $term_id Term ID.
 * @return string
 */
function dara_core_developer_column( $out, $column, $term_id ) {
	if ( 'dara_logo' === $column ) {
		$out = wp_get_attachment_image( (int) get_term_meta( $term_id, 'dara_logo', true ), array( 48, 48 ) );
	}
	return $out;
}
add_filter( 'manage_project_developer_custom_column', 'dara_core_developer_column', 10, 3 );

/**
 * Developer of a project: the assigned term, or the legacy text field.
 *
 * @param int $post_id Project ID.
 * @return array|null { name, url, logo, term }
 */
function dara_project_developer( $post_id ) {
	$terms = get_the_terms( $post_id, 'project_developer' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$term = $terms[0];
		return array(
			'name' => $term->name,
			'url'  => get_term_link( $term ),
			'logo' => (int) get_term_meta( $term->term_id, 'dara_logo', true ),
			'term' => $term,
		);
	}
	$name = get_post_meta( $post_id, '_dara_developer', true );
	return $name ? array(
		'name' => $name,
		'url'  => '',
		'logo' => 0,
		'term' => null,
	) : null;
}
