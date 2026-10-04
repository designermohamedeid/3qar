<?php
/**
 * Dara blocks: every home section as a server-rendered block.
 *
 * Blocks reuse the same templates as the Customizer home page
 * (template-parts/home/*.php), so the front end ships no extra JS or CSS.
 * Empty block settings fall back to the Customizer values.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block definitions.
 *
 * Each control: [ type, label, extra ]. Types: text, textarea, toggle, number, select, image, repeater.
 * Attribute keys equal the option keys the section templates read.
 *
 * @return array
 */
function dara_blocks_config() {
	$eyebrow = __( 'Small label', 'dara' );
	$title   = __( 'Title', 'dara' );
	$text    = __( 'Text', 'dara' );
	$count   = __( 'Number of items', 'dara' );

	return array(
		'hero'           => array(
			'title'       => __( 'Hero & search', 'dara' ),
			'description' => __( 'Full-width image with title and the property search.', 'dara' ),
			'icon'        => 'search',
			'requires'    => '',
			'controls'    => array(
				'hero_image'   => array( 'image', __( 'Background image', 'dara' ) ),
				'hero_eyebrow' => array( 'text', $eyebrow ),
				'hero_title'   => array( 'textarea', $title ),
				'hero_text'    => array( 'textarea', $text ),
				'show_search'  => array( 'toggle', __( 'Show property search', 'dara' ), true ),
				'search_tab'   => array( 'select', __( 'Search opens on', 'dara' ), array( '' => __( 'Buy', 'dara' ), 'rent' => __( 'Rent', 'dara' ), 'projects' => __( 'New projects', 'dara' ) ) ),
				'hero_popular' => array( 'textarea', __( 'Popular searches', 'dara' ), __( 'One per line: Label | URL. Leave empty to show your top cities.', 'dara' ) ),
			),
		),
		'properties'     => array(
			'title'       => __( 'Properties grid', 'dara' ),
			'description' => __( 'Latest or featured properties, filterable by purpose and type.', 'dara' ),
			'icon'        => 'admin-home',
			'requires'    => 'dara_property',
			'template'    => 'featured',
			'controls'    => array(
				'featured_eyebrow' => array( 'text', $eyebrow ),
				'featured_title'   => array( 'text', $title ),
				'featured_count'   => array( 'number', $count, array( 1, 24 ) ),
				'featured_only'    => array( 'toggle', __( 'Only properties marked "Featured"', 'dara' ), false ),
				'purpose'          => array( 'select', __( 'Purpose', 'dara' ), array( '' => __( 'All', 'dara' ), 'sale' => __( 'Sale', 'dara' ), 'rent' => __( 'Rent', 'dara' ) ) ),
				'type'             => array( 'select', __( 'Property type', 'dara' ), 'property_types' ),
				'show_chips'       => array( 'toggle', __( 'Show type links', 'dara' ), true ),
				'show_button'      => array( 'toggle', __( 'Show "View all" button', 'dara' ), true ),
			),
		),
		'property-types' => array(
			'title'       => __( 'Property types', 'dara' ),
			'description' => __( 'Tiles linking to each property type.', 'dara' ),
			'icon'        => 'screenoptions',
			'requires'    => 'dara_property',
			'template'    => 'types',
			'controls'    => array(
				'types_eyebrow' => array( 'text', $eyebrow ),
				'types_title'   => array( 'text', $title ),
			),
		),
		'projects'       => array(
			'title'       => __( 'Developer projects', 'dara' ),
			'description' => __( 'Projects with completion progress on a dark band.', 'dara' ),
			'icon'        => 'building',
			'requires'    => 'dara_project',
			'controls'    => array(
				'projects_eyebrow' => array( 'text', $eyebrow ),
				'projects_title'   => array( 'text', $title ),
				'projects_text'    => array( 'textarea', $text ),
				'projects_count'   => array( 'number', $count, array( 1, 12 ) ),
			),
		),
		'services'       => array(
			'title'       => __( 'Services', 'dara' ),
			'description' => __( 'Service cards with icons.', 'dara' ),
			'icon'        => 'awards',
			'requires'    => '',
			'controls'    => array(
				'services_eyebrow' => array( 'text', $eyebrow ),
				'services_title'   => array( 'text', $title ),
				'services_items'   => array(
					'repeater',
					__( 'Services', 'dara' ),
					array(
						'icon'  => array( 'select', __( 'Icon', 'dara' ), dara_block_icon_options() ),
						'title' => array( 'text', $title ),
						'text'  => array( 'textarea', $text ),
						'url'   => array( 'text', __( 'Link', 'dara' ) ),
					),
				),
			),
		),
		'why'            => array(
			'title'       => __( 'Image & checklist', 'dara' ),
			'description' => __( 'Image with a badge, text, checklist and button.', 'dara' ),
			'icon'        => 'yes-alt',
			'requires'    => '',
			'controls'    => array(
				'why_image'       => array( 'image', __( 'Image', 'dara' ) ),
				'why_eyebrow'     => array( 'text', $eyebrow ),
				'why_title'       => array( 'text', $title ),
				'why_text'        => array( 'textarea', $text ),
				'why_points'      => array( 'textarea', __( 'Points (one per line)', 'dara' ) ),
				'why_badge_title' => array( 'text', __( 'Badge title', 'dara' ) ),
				'why_badge_text'  => array( 'text', __( 'Badge text', 'dara' ) ),
				'why_btn_text'    => array( 'text', __( 'Button text', 'dara' ) ),
				'why_btn_url'     => array( 'text', __( 'Button link', 'dara' ) ),
			),
		),
		'agents'         => array(
			'title'       => __( 'Agents', 'dara' ),
			'description' => __( 'Your agents with call and WhatsApp buttons.', 'dara' ),
			'icon'        => 'groups',
			'requires'    => 'dara_agent',
			'controls'    => array(
				'agents_eyebrow' => array( 'text', $eyebrow ),
				'agents_title'   => array( 'text', $title ),
				'agents_count'   => array( 'number', $count, array( 1, 12 ) ),
			),
		),
		'posts'          => array(
			'title'       => __( 'Latest posts', 'dara' ),
			'description' => __( 'Latest blog posts as cards.', 'dara' ),
			'icon'        => 'admin-post',
			'requires'    => '',
			'template'    => 'blog',
			'controls'    => array(
				'blog_eyebrow' => array( 'text', $eyebrow ),
				'blog_title'   => array( 'text', $title ),
				'blog_count'   => array( 'number', $count, array( 1, 12 ) ),
			),
		),
		'cta'            => array(
			'title'       => __( 'Call to action', 'dara' ),
			'description' => __( 'Colored band with buttons.', 'dara' ),
			'icon'        => 'megaphone',
			'requires'    => '',
			'controls'    => array(
				'cta_title'    => array( 'text', $title ),
				'cta_text'     => array( 'textarea', $text ),
				'cta_btn_text' => array( 'text', __( 'Button text', 'dara' ) ),
				'cta_btn_url'  => array( 'text', __( 'Button link', 'dara' ) ),
			),
		),
	);
}

/**
 * Icons available for services.
 *
 * @return array
 */
function dara_block_icon_options() {
	$out = array();
	foreach ( array( 'megaphone', 'key', 'chart', 'clipboard', 'home', 'building', 'apartment', 'land', 'office', 'shield', 'calendar', 'user', 'phone', 'map', 'search', 'heart' ) as $icon ) {
		$out[ $icon ] = $icon;
	}
	return $out;
}

/**
 * Register the block category and blocks.
 */
function dara_register_blocks() {
	foreach ( dara_blocks_config() as $slug => $block ) {
		$attributes = array();
		foreach ( $block['controls'] as $key => $control ) {
			switch ( $control[0] ) {
				case 'toggle':
					$attributes[ $key ] = array( 'type' => 'boolean', 'default' => (bool) $control[2] );
					break;
				case 'number':
				case 'image':
					$attributes[ $key ] = array( 'type' => 'number' );
					break;
				case 'repeater':
					$attributes[ $key ] = array( 'type' => 'array' );
					break;
				default:
					$attributes[ $key ] = array( 'type' => 'string' );
			}
		}
		register_block_type(
			'dara/' . $slug,
			array(
				'api_version'     => 3,
				'title'           => $block['title'],
				'description'     => $block['description'],
				'category'        => 'dara',
				'icon'            => $block['icon'],
				'attributes'      => $attributes,
				'supports'        => array(
					'html'     => false,
					'align'    => false,
					'multiple' => true,
				),
				'editor_script'   => 'dara-blocks',
				'render_callback' => function ( $attrs ) use ( $slug, $block ) {
					return dara_render_block( $slug, $block, $attrs );
				},
			)
		);
	}
}
add_action( 'init', 'dara_register_blocks' );

/**
 * Block category.
 *
 * @param array $categories Categories.
 * @return array
 */
function dara_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'dara',
			'title' => __( 'Dara real estate', 'dara' ),
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'dara_block_category' );

/**
 * Render a block with its section template.
 *
 * @param string $slug  Block slug.
 * @param array  $block Config.
 * @param array  $attrs Attributes.
 * @return string
 */
function dara_render_block( $slug, $block, $attrs ) {
	if ( $block['requires'] && ! post_type_exists( $block['requires'] ) ) {
		return '';
	}
	$args = array();
	foreach ( $block['controls'] as $key => $control ) {
		if ( ! isset( $attrs[ $key ] ) ) {
			continue;
		}
		$value = $attrs[ $key ];
		if ( 'repeater' === $control[0] ) {
			// Back to the "a | b | c" format the templates read.
			$lines = array();
			foreach ( (array) $value as $row ) {
				$cells = array();
				foreach ( array_keys( $control[2] ) as $field ) {
					$cells[] = isset( $row[ $field ] ) ? str_replace( array( '|', "\n" ), array( '/', ' ' ), (string) $row[ $field ] ) : '';
				}
				$lines[] = implode( ' | ', $cells );
			}
			$value = implode( "\n", $lines );
		}
		if ( in_array( $control[0], array( 'text', 'textarea', 'select' ), true ) && '' === $value ) {
			continue; // Empty = use the Customizer value.
		}
		$args[ $key ] = $value;
	}

	ob_start();
	get_template_part( 'template-parts/home/' . ( isset( $block['template'] ) ? $block['template'] : $slug ), null, $args );
	$html = trim( ob_get_clean() );

	if ( '' === $html && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		/* translators: %s: block name. */
		return '<p class="dara-block-empty">' . esc_html( sprintf( __( '%s: nothing to show yet. Add content first.', 'dara' ), $block['title'] ) ) . '</p>';
	}
	return '' === $html ? '' : '<div class="dara-block dara-block--' . esc_attr( $slug ) . '">' . $html . '</div>';
}

/**
 * Editor script + data for the controls.
 */
function dara_blocks_editor_assets() {
	wp_register_script(
		'dara-blocks',
		DARA_URI . '/assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		dara_asset_ver( 'assets/js/blocks.js' ),
		true
	);

	$types = array( '' => __( 'All', 'dara' ) );
	if ( taxonomy_exists( 'property_type' ) ) {
		foreach ( (array) get_terms( array( 'taxonomy' => 'property_type', 'hide_empty' => false ) ) as $term ) {
			if ( $term instanceof WP_Term ) {
				$types[ $term->slug ] = $term->name;
			}
		}
	}

	$controls = array();
	foreach ( dara_blocks_config() as $slug => $block ) {
		$controls[ 'dara/' . $slug ] = array();
		foreach ( $block['controls'] as $key => $control ) {
			$item = array(
				'key'   => $key,
				'type'  => $control[0],
				'label' => $control[1],
			);
			if ( 'select' === $control[0] ) {
				$opts = 'property_types' === $control[2] ? $types : $control[2];
				$item['options'] = array();
				foreach ( $opts as $value => $label ) {
					$item['options'][] = array( 'value' => (string) $value, 'label' => $label );
				}
			} elseif ( 'number' === $control[0] && isset( $control[2] ) ) {
				$item['min'] = $control[2][0];
				$item['max'] = $control[2][1];
			} elseif ( 'textarea' === $control[0] && isset( $control[2] ) ) {
				$item['help'] = $control[2];
			} elseif ( 'repeater' === $control[0] ) {
				$item['fields'] = array();
				foreach ( $control[2] as $fkey => $field ) {
					$f = array( 'key' => $fkey, 'type' => $field[0], 'label' => $field[1] );
					if ( 'select' === $field[0] ) {
						$f['options'] = array();
						foreach ( $field[2] as $value => $label ) {
							$f['options'][] = array( 'value' => (string) $value, 'label' => $label );
						}
					}
					$item['fields'][] = $f;
				}
			}
			$controls[ 'dara/' . $slug ][] = $item;
		}
	}

	wp_add_inline_script(
		'dara-blocks',
		'window.daraBlocks = ' . wp_json_encode(
			array(
				'controls' => $controls,
				'i18n'     => array(
					'settings'   => __( 'Settings', 'dara' ),
					'fallback'   => __( 'Empty fields use the values from Appearance > Customize > Dara theme.', 'dara' ),
					'choose'     => __( 'Choose image', 'dara' ),
					'replace'    => __( 'Replace image', 'dara' ),
					'remove'     => __( 'Remove', 'dara' ),
					'add'        => __( 'Add item', 'dara' ),
					'item'       => __( 'Item', 'dara' ),
					'up'         => __( 'Move up', 'dara' ),
					'down'       => __( 'Move down', 'dara' ),
				),
			)
		) . ';',
		'before'
	);
}
add_action( 'init', 'dara_blocks_editor_assets', 5 );

/**
 * Front-end styles in the editor so previews look like the site.
 */
function dara_blocks_editor_styles() {
	add_editor_style( array( 'assets/css/core.css', 'assets/css/home.css', 'assets/css/agents.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'dara_blocks_editor_styles', 20 );

/**
 * Block pattern: the full home page built from Dara blocks.
 *
 * @return string
 */
function dara_home_pattern_content() {
	$blocks = array( 'hero', 'properties', 'property-types', 'projects', 'services', 'why', 'posts', 'cta' );
	return implode( "\n\n", array_map( function ( $b ) {
		return '<!-- wp:dara/' . $b . ' /-->';
	}, $blocks ) );
}

/**
 * Register patterns.
 */
function dara_register_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}
	register_block_pattern_category( 'dara', array( 'label' => __( 'Dara real estate', 'dara' ) ) );
	register_block_pattern(
		'dara/home',
		array(
			'title'       => __( 'Real estate home page', 'dara' ),
			'description' => __( 'Hero with search, properties, types, projects, services, why us, posts and call to action.', 'dara' ),
			'categories'  => array( 'dara' ),
			'content'     => dara_home_pattern_content(),
		)
	);
	register_block_pattern(
		'dara/landing',
		array(
			'title'       => __( 'Listings landing page', 'dara' ),
			'description' => __( 'Hero with search, properties for sale, properties for rent and a call to action.', 'dara' ),
			'categories'  => array( 'dara' ),
			'content'     => "<!-- wp:dara/hero /-->\n\n<!-- wp:dara/properties {\"purpose\":\"sale\",\"featured_title\":\"" . esc_attr__( 'Properties for sale', 'dara' ) . "\",\"show_chips\":false} /-->\n\n<!-- wp:dara/properties {\"purpose\":\"rent\",\"featured_title\":\"" . esc_attr__( 'Properties for rent', 'dara' ) . "\",\"show_chips\":false} /-->\n\n<!-- wp:dara/cta /-->",
		)
	);
}
add_action( 'init', 'dara_register_patterns' );
