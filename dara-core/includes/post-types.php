<?php
/**
 * Post types and taxonomies.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a labels array.
 *
 * @param string $plural   Plural name.
 * @param string $singular Singular name.
 * @param array  $extra    Extra labels.
 * @return array
 */
function dara_core_labels( $plural, $singular, $extra = array() ) {
	return array_merge(
		array(
			'name'          => $plural,
			'singular_name' => $singular,
			'menu_name'     => $plural,
			'all_items'     => $plural,
			/* translators: %s: item name. */
			'add_new_item'  => sprintf( __( 'Add %s', 'dara-core' ), $singular ),
			/* translators: %s: item name. */
			'edit_item'     => sprintf( __( 'Edit %s', 'dara-core' ), $singular ),
			/* translators: %s: item name. */
			'view_item'     => sprintf( __( 'View %s', 'dara-core' ), $singular ),
			/* translators: %s: items name. */
			'search_items'  => sprintf( __( 'Search %s', 'dara-core' ), $plural ),
			'not_found'     => __( 'Nothing found.', 'dara-core' ),
		),
		$extra
	);
}

/**
 * Register everything.
 */
function dara_core_register_post_types() {
	register_post_type(
		'dara_property',
		array(
			'labels'        => dara_core_labels( __( 'Properties', 'dara-core' ), __( 'Property', 'dara-core' ) ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-admin-home',
			'menu_position' => 5,
			'rewrite'       => array(
				'slug'       => 'properties',
				'with_front' => false,
			),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	register_post_type(
		'dara_project',
		array(
			'labels'        => dara_core_labels( __( 'Projects', 'dara-core' ), __( 'Project', 'dara-core' ) ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-building',
			'menu_position' => 6,
			'rewrite'       => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	register_post_type(
		'dara_agent',
		array(
			'labels'       => dara_core_labels( __( 'Agents', 'dara-core' ), __( 'Agent', 'dara-core' ), array( 'featured_image' => __( 'Photo', 'dara-core' ) ) ),
			'public'       => true,
			'has_archive'  => true,
			'show_in_menu' => 'edit.php?post_type=dara_property',
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'agents',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		)
	);

	register_post_type(
		'dara_lead',
		array(
			'labels'        => dara_core_labels( __( 'Leads', 'dara-core' ), __( 'Lead', 'dara-core' ) ),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-email-alt',
			'menu_position' => 7,
			'supports'      => array( 'title' ),
			'capabilities'  => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'  => true,
		)
	);

	$tax_defaults = array(
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
	);

	register_taxonomy(
		'property_type',
		array( 'dara_property' ),
		array_merge(
			$tax_defaults,
			array(
				'labels'  => dara_core_labels( __( 'Property Types', 'dara-core' ), __( 'Property Type', 'dara-core' ) ),
				'rewrite' => array( 'slug' => 'property-type' ),
			)
		)
	);

	register_taxonomy(
		'property_city',
		array( 'dara_property', 'dara_project' ),
		array_merge(
			$tax_defaults,
			array(
				'labels'  => dara_core_labels( __( 'Cities & Districts', 'dara-core' ), __( 'City / District', 'dara-core' ) ),
				'rewrite' => array(
					'slug'         => 'location',
					'hierarchical' => true,
				),
			)
		)
	);

	register_taxonomy(
		'property_feature',
		array( 'dara_property' ),
		array_merge(
			$tax_defaults,
			array(
				'labels'            => dara_core_labels( __( 'Features', 'dara-core' ), __( 'Feature', 'dara-core' ) ),
				'show_admin_column' => false,
				'public'            => false,
				'show_ui'           => true,
				'rewrite'           => false,
			)
		)
	);
}
add_action( 'init', 'dara_core_register_post_types' );
