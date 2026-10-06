<?php
/**
 * Dara blocks: every home section as a server-rendered block.
 *
 * Blocks render the active theme's section templates (template-parts/home/*.php),
 * so the front end ships no extra JS or CSS. They are only registered when the
 * theme declares support: add_theme_support( 'dara-blocks' ).
 * Empty block settings fall back to the Customizer values.
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Third, independent check used by the blocks and patterns.
 *
 * @return bool
 */
function dara_pro_ok_b() {
	static $r = null;
	if ( null === $r ) {
		$e = dara_pro_edition();
		$r = 'direct' !== $e['edition'] || '' === $e['pubkey'] || dara_pro_ok_b_run( $e['pubkey'] );
	}
	return $r;
}

/**
 * Run the third check.
 *
 * @param string $pubkey Base64 public key.
 * @return bool
 */
function dara_pro_ok_b_run( $pubkey ) {
	$dir  = DARA_PRO_DIR;
	$list = @json_decode( (string) @file_get_contents( $dir . 'manifest.json' ), true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$sign = (string) @file_get_contents( $dir . 'manifest.sig' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $list ) || empty( $list['files'] ) ) {
		return false;
	}
	try {
		$valid = sodium_crypto_sign_verify_detached( base64_decode( trim( $sign ) ), (string) file_get_contents( $dir . 'manifest.json' ), base64_decode( $pubkey ) ); // phpcs:ignore
	} catch ( Exception $x ) {
		$valid = false;
	}
	$build = include $dir . 'includes/build.php';
	if ( ! $valid || ! is_array( $build ) || $build['b'] !== $list['b'] ) {
		return false;
	}
	foreach ( array( 'dara-pro.php', 'includes/blocks.php', 'includes/guard.php', 'includes/demo.php' ) as $f ) {
		if ( ! isset( $list['files'][ $f ] ) || ! hash_equals( $list['files'][ $f ], (string) hash_file( 'sha256', $dir . $f ) ) ) {
			return false;
		}
	}
	$opt  = (array) get_option( 'dara_license' );
	$tok  = json_decode( (string) base64_decode( isset( $opt['payload'] ) ? $opt['payload'] : '' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$site = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	if ( 0 === strpos( $site, 'www.' ) ) {
		$site = substr( $site, 4 );
	}
	return is_array( $tok ) && isset( $tok['k'], $tok['d'] ) && $tok['k'] === $list['k'] && $site === $tok['d'] && time() < (int) $tok['x'] + WEEK_IN_SECONDS;
}

/**
 * Block definitions.
 *
 * Each control: [ type, label, extra ]. Types: text, textarea, toggle, number, select, image, repeater.
 * Attribute keys equal the option keys the section templates read.
 *
 * @return array
 */
function dara_pro_blocks_config() {
	$eyebrow = __( 'Small label', 'dara-pro' );
	$title   = __( 'Title', 'dara-pro' );
	$text    = __( 'Text', 'dara-pro' );
	$count   = __( 'Number of items', 'dara-pro' );

	return array(
		'hero'           => array(
			'title'       => __( 'Hero & search', 'dara-pro' ),
			'description' => __( 'Full-width image with title and the property search.', 'dara-pro' ),
			'icon'        => 'search',
			'requires'    => '',
			'controls'    => array(
				'hero_image'   => array( 'image', __( 'Background image', 'dara-pro' ) ),
				'hero_eyebrow' => array( 'text', $eyebrow ),
				'hero_title'   => array( 'textarea', $title ),
				'hero_text'    => array( 'textarea', $text ),
				'show_search'  => array( 'toggle', __( 'Show property search', 'dara-pro' ), true ),
				'search_tab'   => array(
					'select',
					__( 'Search opens on', 'dara-pro' ),
					array(
						''         => __( 'Buy', 'dara-pro' ),
						'rent'     => __( 'Rent', 'dara-pro' ),
						'projects' => __( 'New projects', 'dara-pro' ),
					),
				),
				'hero_popular' => array( 'textarea', __( 'Popular searches', 'dara-pro' ), __( 'One per line: Label | URL. Leave empty to show your top cities.', 'dara-pro' ) ),
			),
		),
		'properties'     => array(
			'title'       => __( 'Properties grid', 'dara-pro' ),
			'description' => __( 'Latest or featured properties, filterable by purpose and type.', 'dara-pro' ),
			'icon'        => 'admin-home',
			'requires'    => 'dara_property',
			'template'    => 'featured',
			'controls'    => array(
				'featured_eyebrow' => array( 'text', $eyebrow ),
				'featured_title'   => array( 'text', $title ),
				'featured_count'   => array( 'number', $count, array( 1, 24 ) ),
				'featured_only'    => array( 'toggle', __( 'Only properties marked "Featured"', 'dara-pro' ), false ),
				'purpose'          => array(
					'select',
					__( 'Purpose', 'dara-pro' ),
					array(
						''     => __( 'All', 'dara-pro' ),
						'sale' => __( 'Sale', 'dara-pro' ),
						'rent' => __( 'Rent', 'dara-pro' ),
					),
				),
				'type'             => array( 'select', __( 'Property type', 'dara-pro' ), 'property_types' ),
				'show_chips'       => array( 'toggle', __( 'Show type links', 'dara-pro' ), true ),
				'show_button'      => array( 'toggle', __( 'Show "View all" button', 'dara-pro' ), true ),
			),
		),
		'property-types' => array(
			'title'       => __( 'Property types', 'dara-pro' ),
			'description' => __( 'Tiles linking to each property type.', 'dara-pro' ),
			'icon'        => 'screenoptions',
			'requires'    => 'dara_property',
			'template'    => 'types',
			'controls'    => array(
				'types_eyebrow' => array( 'text', $eyebrow ),
				'types_title'   => array( 'text', $title ),
			),
		),
		'projects'       => array(
			'title'       => __( 'Developer projects', 'dara-pro' ),
			'description' => __( 'Projects with completion progress on a dark band.', 'dara-pro' ),
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
			'title'       => __( 'Services', 'dara-pro' ),
			'description' => __( 'Service cards with icons.', 'dara-pro' ),
			'icon'        => 'awards',
			'requires'    => '',
			'controls'    => array(
				'services_eyebrow' => array( 'text', $eyebrow ),
				'services_title'   => array( 'text', $title ),
				'services_items'   => array(
					'repeater',
					__( 'Services', 'dara-pro' ),
					array(
						'icon'  => array( 'select', __( 'Icon', 'dara-pro' ), dara_pro_block_icon_options() ),
						'title' => array( 'text', $title ),
						'text'  => array( 'textarea', $text ),
						'url'   => array( 'text', __( 'Link', 'dara-pro' ) ),
					),
				),
			),
		),
		'why'            => array(
			'title'       => __( 'Image & checklist', 'dara-pro' ),
			'description' => __( 'Image with a badge, text, checklist and button.', 'dara-pro' ),
			'icon'        => 'yes-alt',
			'requires'    => '',
			'controls'    => array(
				'why_image'       => array( 'image', __( 'Image', 'dara-pro' ) ),
				'why_eyebrow'     => array( 'text', $eyebrow ),
				'why_title'       => array( 'text', $title ),
				'why_text'        => array( 'textarea', $text ),
				'why_points'      => array( 'textarea', __( 'Points (one per line)', 'dara-pro' ) ),
				'why_badge_title' => array( 'text', __( 'Badge title', 'dara-pro' ) ),
				'why_badge_text'  => array( 'text', __( 'Badge text', 'dara-pro' ) ),
				'why_btn_text'    => array( 'text', __( 'Button text', 'dara-pro' ) ),
				'why_btn_url'     => array( 'text', __( 'Button link', 'dara-pro' ) ),
			),
		),
		'agents'         => array(
			'title'       => __( 'Agents', 'dara-pro' ),
			'description' => __( 'Your agents with call and WhatsApp buttons.', 'dara-pro' ),
			'icon'        => 'groups',
			'requires'    => 'dara_agent',
			'controls'    => array(
				'agents_eyebrow' => array( 'text', $eyebrow ),
				'agents_title'   => array( 'text', $title ),
				'agents_count'   => array( 'number', $count, array( 1, 12 ) ),
			),
		),
		'posts'          => array(
			'title'       => __( 'Latest posts', 'dara-pro' ),
			'description' => __( 'Latest blog posts as cards.', 'dara-pro' ),
			'icon'        => 'admin-post',
			'requires'    => '',
			'template'    => 'blog',
			'controls'    => array(
				'blog_eyebrow' => array( 'text', $eyebrow ),
				'blog_title'   => array( 'text', $title ),
				'blog_count'   => array( 'number', $count, array( 1, 12 ) ),
			),
		),
		'mortgage'       => array(
			'title'       => __( 'Mortgage calculator', 'dara-pro' ),
			'description' => __( 'Estimate the monthly installment from price, down payment, term and profit rate.', 'dara-pro' ),
			'icon'        => 'calculator',
			'requires'    => '',
			'controls'    => array(
				'mortgage_title' => array( 'text', $title ),
				'mortgage_price' => array( 'amount', __( 'Default property price', 'dara-pro' ) ),
			),
		),
		'cta'            => array(
			'title'       => __( 'Call to action', 'dara-pro' ),
			'description' => __( 'Colored band with buttons.', 'dara-pro' ),
			'icon'        => 'megaphone',
			'requires'    => '',
			'controls'    => array(
				'cta_title'    => array( 'text', $title ),
				'cta_text'     => array( 'textarea', $text ),
				'cta_btn_text' => array( 'text', __( 'Button text', 'dara-pro' ) ),
				'cta_btn_url'  => array( 'text', __( 'Button link', 'dara-pro' ) ),
			),
		),
	);
}

/**
 * Icons available for services.
 *
 * @return array
 */
function dara_pro_block_icon_options() {
	$out = array();
	foreach ( array( 'megaphone', 'key', 'chart', 'clipboard', 'home', 'building', 'apartment', 'land', 'office', 'shield', 'calendar', 'user', 'phone', 'map', 'search', 'heart' ) as $icon ) {
		$out[ $icon ] = $icon;
	}
	return $out;
}

/**
 * Register the block category and blocks.
 */
function dara_pro_register_blocks() {
	if ( ! current_theme_supports( 'dara-blocks' ) ) {
		return;
	}
	foreach ( dara_pro_blocks_config() as $slug => $block ) {
		$attributes = array();
		foreach ( $block['controls'] as $key => $control ) {
			switch ( $control[0] ) {
				case 'toggle':
					$attributes[ $key ] = array(
						'type'    => 'boolean',
						'default' => (bool) $control[2],
					);
					break;
				case 'number':
				case 'amount':
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
					'inserter' => dara_pro_active() && dara_pro_ok_b(),
				),
				'editor_script'   => 'dara-blocks',
				'render_callback' => function ( $attrs ) use ( $slug, $block ) {
					return dara_pro_render_block( $slug, $block, $attrs );
				},
			)
		);
	}
}
add_action( 'init', 'dara_pro_register_blocks' );

/**
 * Block category.
 *
 * @param array $categories Categories.
 * @return array
 */
function dara_pro_block_category( $categories ) {
	if ( ! current_theme_supports( 'dara-blocks' ) ) {
		return $categories;
	}
	array_unshift(
		$categories,
		array(
			'slug'  => 'dara',
			'title' => __( 'Dara real estate', 'dara-pro' ),
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'dara_pro_block_category' );

/**
 * Render a block with its section template.
 *
 * @param string $slug  Block slug.
 * @param array  $block Config.
 * @param array  $attrs Attributes.
 * @return string
 */
function dara_pro_render_block( $slug, $block, $attrs ) {
	if ( ! dara_pro_active() || ! dara_pro_ok_b() || ( $block['requires'] && ! post_type_exists( $block['requires'] ) ) ) {
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
		return '<p class="dara-block-empty">' . esc_html( sprintf( __( '%s: nothing to show yet. Add content first.', 'dara-pro' ), $block['title'] ) ) . '</p>';
	}
	return '' === $html ? '' : '<div class="dara-block dara-block--' . esc_attr( $slug ) . '">' . $html . '</div>';
}

/**
 * Editor script + data for the controls.
 */
function dara_pro_blocks_editor_assets() {
	if ( ! current_theme_supports( 'dara-blocks' ) ) {
		return;
	}
	wp_register_script(
		'dara-blocks',
		DARA_PRO_URL . 'assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		DARA_PRO_VERSION,
		true
	);

	$types = array( '' => __( 'All', 'dara-pro' ) );
	if ( taxonomy_exists( 'property_type' ) ) {
		foreach ( (array) get_terms(
			array(
				'taxonomy'   => 'property_type',
				'hide_empty' => false,
			)
		) as $term ) {
			if ( $term instanceof WP_Term ) {
				$types[ $term->slug ] = $term->name;
			}
		}
	}

	$controls = array();
	foreach ( dara_pro_blocks_config() as $slug => $block ) {
		$controls[ 'dara/' . $slug ] = array();
		foreach ( $block['controls'] as $key => $control ) {
			$item = array(
				'key'   => $key,
				'type'  => $control[0],
				'label' => $control[1],
			);
			if ( 'select' === $control[0] ) {
				$opts            = 'property_types' === $control[2] ? $types : $control[2];
				$item['options'] = array();
				foreach ( $opts as $value => $label ) {
					$item['options'][] = array(
						'value' => (string) $value,
						'label' => $label,
					);
				}
			} elseif ( 'number' === $control[0] && isset( $control[2] ) ) {
				$item['min'] = $control[2][0];
				$item['max'] = $control[2][1];
			} elseif ( 'textarea' === $control[0] && isset( $control[2] ) ) {
				$item['help'] = $control[2];
			} elseif ( 'repeater' === $control[0] ) {
				$item['fields'] = array();
				foreach ( $control[2] as $fkey => $field ) {
					$f = array(
						'key'   => $fkey,
						'type'  => $field[0],
						'label' => $field[1],
					);
					if ( 'select' === $field[0] ) {
						$f['options'] = array();
						foreach ( $field[2] as $value => $label ) {
							$f['options'][] = array(
								'value' => (string) $value,
								'label' => $label,
							);
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
					'settings' => __( 'Settings', 'dara-pro' ),
					'fallback' => __( 'Empty fields use the values from Appearance > Customize > Dara theme.', 'dara-pro' ),
					'choose'   => __( 'Choose image', 'dara-pro' ),
					'replace'  => __( 'Replace image', 'dara-pro' ),
					'remove'   => __( 'Remove', 'dara-pro' ),
					'add'      => __( 'Add item', 'dara-pro' ),
					'item'     => __( 'Item', 'dara-pro' ),
					'up'       => __( 'Move up', 'dara-pro' ),
					'down'     => __( 'Move down', 'dara-pro' ),
				),
			)
		) . ';',
		'before'
	);
}
add_action( 'init', 'dara_pro_blocks_editor_assets', 5 );

/**
 * Block pattern: the full home page built from Dara blocks.
 *
 * @return string
 */
function dara_pro_home_pattern_content() {
	$blocks = array( 'hero', 'properties', 'property-types', 'projects', 'services', 'why', 'posts', 'cta' );
	return implode(
		"\n\n",
		array_map(
			function ( $b ) {
				return '<!-- wp:dara/' . $b . ' /-->';
			},
			$blocks
		)
	);
}

/**
 * Register patterns.
 */
function dara_pro_register_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) || ! current_theme_supports( 'dara-blocks' ) ) {
		return;
	}
	register_block_pattern_category( 'dara', array( 'label' => __( 'Dara real estate', 'dara-pro' ) ) );
	register_block_pattern(
		'dara/home',
		array(
			'title'       => __( 'Real estate home page', 'dara-pro' ),
			'description' => __( 'Hero with search, properties, types, projects, services, why us, posts and call to action.', 'dara-pro' ),
			'categories'  => array( 'dara-pro' ),
			'content'     => dara_pro_home_pattern_content(),
		)
	);
	register_block_pattern(
		'dara/landing',
		array(
			'title'       => __( 'Listings landing page', 'dara-pro' ),
			'description' => __( 'Hero with search, properties for sale, properties for rent and a call to action.', 'dara-pro' ),
			'categories'  => array( 'dara-pro' ),
			'content'     => "<!-- wp:dara/hero /-->\n\n<!-- wp:dara/properties {\"purpose\":\"sale\",\"featured_title\":\"" . esc_attr__( 'Properties for sale', 'dara-pro' ) . "\",\"show_chips\":false} /-->\n\n<!-- wp:dara/properties {\"purpose\":\"rent\",\"featured_title\":\"" . esc_attr__( 'Properties for rent', 'dara-pro' ) . "\",\"show_chips\":false} /-->\n\n<!-- wp:dara/cta /-->",
		)
	);
}
add_action( 'init', 'dara_pro_register_patterns' );
