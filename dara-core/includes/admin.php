<?php
/**
 * Admin UI: assets, list columns, lead viewer.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin assets on Dara edit screens.
 *
 * @param string $hook Hook suffix.
 */
function dara_core_admin_assets( $hook ) {
	$screen  = get_current_screen();
	$is_edit = $screen && in_array( $screen->post_type, array( 'dara_property', 'dara_project', 'dara_agent' ), true ) && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	$is_term = $screen && 'project_developer' === $screen->taxonomy && in_array( $hook, array( 'edit-tags.php', 'term.php' ), true );
	if ( ! $is_edit && ! $is_term ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'dara-core-admin', DARA_CORE_URL . 'assets/admin.css', array(), DARA_CORE_VERSION );
	wp_enqueue_script( 'dara-core-admin', DARA_CORE_URL . 'assets/admin.js', array(), DARA_CORE_VERSION, true );
	wp_localize_script(
		'dara-core-admin',
		'daraCoreAdmin',
		array(
			'title'  => __( 'Select images', 'dara-core' ),
			'button' => __( 'Use these images', 'dara-core' ),
			'remove' => __( 'Remove', 'dara-core' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'dara_core_admin_assets' );

/**
 * Property list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function dara_core_property_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['dara_thumb'] = '<span class="screen-reader-text">' . esc_html__( 'Image', 'dara-core' ) . '</span>';
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['dara_price']   = __( 'Price', 'dara-core' );
			$new['dara_purpose'] = __( 'Purpose', 'dara-core' );
		}
	}
	return $new;
}
add_filter( 'manage_dara_property_posts_columns', 'dara_core_property_columns' );

/**
 * Property column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function dara_core_property_column_value( $column, $post_id ) {
	switch ( $column ) {
		case 'dara_thumb':
			echo get_the_post_thumbnail( $post_id, array( 60, 60 ), array( 'style' => 'border-radius:6px;width:60px;height:60px;object-fit:cover' ) );
			break;
		case 'dara_price':
			echo esc_html( dara_format_price( $post_id ) );
			if ( get_post_meta( $post_id, '_dara_featured', true ) ) {
				echo ' <span class="dashicons dashicons-star-filled" style="color:#c98a2b" title="' . esc_attr__( 'Featured', 'dara-core' ) . '"></span>';
			}
			break;
		case 'dara_purpose':
			echo esc_html( dara_purpose_label( get_post_meta( $post_id, '_dara_purpose', true ) ) );
			break;
	}
}
add_action( 'manage_dara_property_posts_custom_column', 'dara_core_property_column_value', 10, 2 );

/**
 * Lead list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function dara_core_lead_columns( $columns ) {
	return array(
		'cb'         => $columns['cb'],
		'title'      => __( 'Lead', 'dara-core' ),
		'dara_type'  => __( 'Type', 'dara-core' ),
		'dara_phone' => __( 'Phone', 'dara-core' ),
		'dara_about' => __( 'About', 'dara-core' ),
		'date'       => $columns['date'],
	);
}
add_filter( 'manage_dara_lead_posts_columns', 'dara_core_lead_columns' );

/**
 * Lead column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function dara_core_lead_column_value( $column, $post_id ) {
	switch ( $column ) {
		case 'dara_type':
			$types = dara_lead_types();
			$type  = get_post_meta( $post_id, '_lead_type', true );
			echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : $type );
			break;
		case 'dara_phone':
			$phone = get_post_meta( $post_id, '_lead_phone', true );
			printf( '<a href="tel:%1$s" dir="ltr">%2$s</a>', esc_attr( $phone ), esc_html( $phone ) );
			break;
		case 'dara_about':
			$related = (int) get_post_meta( $post_id, '_lead_post', true );
			if ( $related ) {
				printf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $related ) ), esc_html( get_the_title( $related ) ) );
			}
			break;
	}
}
add_action( 'manage_dara_lead_posts_custom_column', 'dara_core_lead_column_value', 10, 2 );

/**
 * Lead details box.
 */
function dara_core_lead_box() {
	add_meta_box(
		'dara-lead',
		__( 'Lead details', 'dara-core' ),
		function ( $post ) {
			$rows = array(
				'_lead_type'    => __( 'Type', 'dara-core' ),
				'_lead_name'    => __( 'Name', 'dara-core' ),
				'_lead_phone'   => __( 'Phone', 'dara-core' ),
				'_lead_email'   => __( 'Email', 'dara-core' ),
				'_lead_date'    => __( 'Preferred date', 'dara-core' ),
				'_lead_extra'   => __( 'Details', 'dara-core' ),
				'_lead_message' => __( 'Message', 'dara-core' ),
				'_lead_source'  => __( 'Page', 'dara-core' ),
			);
			echo '<table class="widefat striped"><tbody>';
			foreach ( $rows as $key => $label ) {
				$value = get_post_meta( $post->ID, $key, true );
				if ( '' === $value ) {
					continue;
				}
				printf( '<tr><th style="width:180px">%1$s</th><td>%2$s</td></tr>', esc_html( $label ), nl2br( esc_html( $value ) ) );
			}
			echo '</tbody></table>';
		},
		'dara_lead',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_dara_lead', 'dara_core_lead_box' );

/**
 * Settings page: Properties > Settings.
 */
function dara_core_settings_page() {
	add_submenu_page(
		'edit.php?post_type=dara_property',
		__( 'Dara settings', 'dara-core' ),
		__( 'Settings', 'dara-core' ),
		'manage_options',
		'dara-settings',
		'dara_core_render_settings'
	);
}
add_action( 'admin_menu', 'dara_core_settings_page' );

/**
 * Register the settings option.
 */
function dara_core_register_settings() {
	register_setting(
		'dara_core_settings',
		'dara_core_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => function ( $input ) {
				$clean = array();
				foreach ( array( 'currency', 'office_fal', 'office_cr', 'agent_name', 'agent_phone', 'agent_wa' ) as $key ) {
					$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
				}
				$clean['lead_email'] = isset( $input['lead_email'] ) ? sanitize_email( $input['lead_email'] ) : '';
				return $clean;
			},
		)
	);
}
add_action( 'admin_init', 'dara_core_register_settings' );

/**
 * Render the settings page.
 */
function dara_core_render_settings() {
	$fields = array(
		'currency'    => array( __( 'Currency label', 'dara-core' ), __( 'Shown after prices, e.g. SAR, EGP, AED.', 'dara-core' ) ),
		'lead_email'  => array( __( 'Leads email', 'dara-core' ), __( 'Viewing requests and forms are sent here (and to the property agent).', 'dara-core' ) ),
		'office_fal'  => array( __( 'Office FAL license', 'dara-core' ), '' ),
		'office_cr'   => array( __( 'Commercial registration', 'dara-core' ), '' ),
		'agent_name'  => array( __( 'Default contact name', 'dara-core' ), __( 'Used when a property has no agent.', 'dara-core' ) ),
		'agent_phone' => array( __( 'Default contact phone', 'dara-core' ), '' ),
		'agent_wa'    => array( __( 'Default WhatsApp number', 'dara-core' ), __( 'International format without +', 'dara-core' ) ),
	);
	echo '<div class="wrap"><h1>' . esc_html__( 'Dara settings', 'dara-core' ) . '</h1><form method="post" action="options.php">';
	settings_fields( 'dara_core_settings' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $fields as $key => $field ) {
		printf(
			'<tr><th scope="row"><label for="dara-%1$s">%2$s</label></th><td><input class="regular-text" id="dara-%1$s" name="dara_core_settings[%1$s]" value="%3$s">%4$s</td></tr>',
			esc_attr( $key ),
			esc_html( $field[0] ),
			esc_attr( dara_setting( $key ) ),
			$field[1] ? '<p class="description">' . esc_html( $field[1] ) . '</p>' : ''
		);
	}
	echo '</tbody></table>';
	submit_button();
	echo '</form></div>';
}
