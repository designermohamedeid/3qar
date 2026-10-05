<?php
/**
 * Customizer: Appearance > Customize > Dara.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sections and their fields: key => array( type, label, [description] ).
 *
 * @return array
 */
function dara_customizer_map() {
	$show       = __( 'Show this section', 'dara' );
	$lines_help = __( 'One item per line.', 'dara' );
	return array(
		'dara_brand'    => array(
			__( 'Brand & header', 'dara' ),
			array(
				'primary_color'   => array( 'color', __( 'Primary color', 'dara' ) ),
				'ink_color'       => array( 'color', __( 'Headings / dark color', 'dara' ) ),
				'header_cta_text' => array( 'text', __( 'Header button text', 'dara' ) ),
				'header_cta_url'  => array( 'url', __( 'Header button link', 'dara' ), __( 'e.g. your "List your property" page.', 'dara' ) ),
			),
		),
		'dara_hero'     => array(
			__( 'Home: hero & search', 'dara' ),
			array(
				'hero_image'   => array( 'image', __( 'Background image', 'dara' ), __( 'Recommended 1920×1080, compressed (WebP/JPEG under 250 KB).', 'dara' ) ),
				'hero_eyebrow' => array( 'text', __( 'Small label', 'dara' ) ),
				'hero_title'   => array( 'textarea', __( 'Title', 'dara' ) ),
				'hero_text'    => array( 'textarea', __( 'Text', 'dara' ) ),
				'hero_popular' => array( 'textarea', __( 'Popular searches', 'dara' ), __( 'One per line: Label | URL. Leave empty to show your top cities automatically.', 'dara' ) ),
			),
		),
		'dara_featured' => array(
			__( 'Home: featured properties', 'dara' ),
			array(
				'show_featured'    => array( 'checkbox', $show ),
				'featured_eyebrow' => array( 'text', __( 'Small label', 'dara' ) ),
				'featured_title'   => array( 'text', __( 'Title', 'dara' ) ),
				'featured_count'   => array( 'number', __( 'Number of properties', 'dara' ) ),
				'featured_only'    => array( 'checkbox', __( 'Only properties marked "Featured"', 'dara' ) ),
			),
		),
		'dara_types'    => array(
			__( 'Home: property types', 'dara' ),
			array(
				'show_types'    => array( 'checkbox', $show ),
				'types_eyebrow' => array( 'text', __( 'Small label', 'dara' ) ),
				'types_title'   => array( 'text', __( 'Title', 'dara' ) ),
			),
		),
		'dara_projects' => array(
			__( 'Home: projects', 'dara' ),
			array(
				'show_projects'    => array( 'checkbox', $show ),
				'projects_eyebrow' => array( 'text', __( 'Small label', 'dara' ) ),
				'projects_title'   => array( 'text', __( 'Title', 'dara' ) ),
				'projects_text'    => array( 'textarea', __( 'Text', 'dara' ) ),
				'projects_count'   => array( 'number', __( 'Number of projects', 'dara' ) ),
			),
		),
		'dara_services' => array(
			__( 'Home: services', 'dara' ),
			array(
				'show_services'    => array( 'checkbox', $show ),
				'services_eyebrow' => array( 'text', __( 'Small label', 'dara' ) ),
				'services_title'   => array( 'text', __( 'Title', 'dara' ) ),
				'services_items'   => array( 'textarea', __( 'Services', 'dara' ), __( 'One per line: icon | title | text | link. Icons: megaphone, key, chart, clipboard, home, building, shield, calendar, user.', 'dara' ) ),
			),
		),
		'dara_why'      => array(
			__( 'Home: why us', 'dara' ),
			array(
				'show_why'        => array( 'checkbox', $show ),
				'why_image'       => array( 'image', __( 'Image', 'dara' ) ),
				'why_eyebrow'     => array( 'text', __( 'Small label', 'dara' ) ),
				'why_title'       => array( 'text', __( 'Title', 'dara' ) ),
				'why_text'        => array( 'textarea', __( 'Text', 'dara' ) ),
				'why_points'      => array( 'textarea', __( 'Points', 'dara' ), $lines_help ),
				'why_badge_title' => array( 'text', __( 'Badge title', 'dara' ) ),
				'why_badge_text'  => array( 'text', __( 'Badge text', 'dara' ) ),
				'why_btn_text'    => array( 'text', __( 'Button text', 'dara' ) ),
				'why_btn_url'     => array( 'url', __( 'Button link', 'dara' ) ),
			),
		),
		'dara_blog'     => array(
			__( 'Home: blog & call to action', 'dara' ),
			array(
				'show_blog'    => array( 'checkbox', __( 'Show latest posts', 'dara' ) ),
				'blog_eyebrow' => array( 'text', __( 'Small label', 'dara' ) ),
				'blog_title'   => array( 'text', __( 'Title', 'dara' ) ),
				'blog_count'   => array( 'number', __( 'Number of posts', 'dara' ) ),
				'show_cta'     => array( 'checkbox', __( 'Show call to action', 'dara' ) ),
				'cta_title'    => array( 'text', __( 'Call to action title', 'dara' ) ),
				'cta_text'     => array( 'textarea', __( 'Call to action text', 'dara' ) ),
				'cta_btn_text' => array( 'text', __( 'Button text', 'dara' ) ),
				'cta_btn_url'  => array( 'url', __( 'Button link', 'dara' ) ),
			),
		),
		'dara_contact'  => array(
			__( 'Contact & social', 'dara' ),
			array(
				'phone'            => array( 'text', __( 'Phone', 'dara' ) ),
				'whatsapp'         => array( 'text', __( 'WhatsApp number', 'dara' ), __( 'International format without +, e.g. 9665XXXXXXXX', 'dara' ) ),
				'email'            => array( 'email', __( 'Email', 'dara' ) ),
				'address'          => array( 'textarea', __( 'Address', 'dara' ) ),
				'map_lat'          => array( 'text', __( 'Office latitude', 'dara' ) ),
				'map_lng'          => array( 'text', __( 'Office longitude', 'dara' ) ),
				'social_x'         => array( 'url', 'X' ),
				'social_instagram' => array( 'url', 'Instagram' ),
				'social_snapchat'  => array( 'url', 'Snapchat' ),
				'social_tiktok'    => array( 'url', 'TikTok' ),
				'social_youtube'   => array( 'url', 'YouTube' ),
				'social_linkedin'  => array( 'url', 'LinkedIn' ),
				'social_facebook'  => array( 'url', 'Facebook' ),
			),
		),
		'dara_footer'   => array(
			__( 'Footer', 'dara' ),
			array(
				'footer_about'    => array( 'textarea', __( 'About text', 'dara' ) ),
				'newsletter_show' => array( 'checkbox', __( 'Show newsletter', 'dara' ) ),
				'newsletter_text' => array( 'text', __( 'Newsletter text', 'dara' ) ),
				'copyright'       => array( 'text', __( 'Copyright', 'dara' ), __( 'You can use {year} and {site}.', 'dara' ) ),
				'designer_credit' => array( 'checkbox', __( 'Show designer credit', 'dara' ) ),
			),
		),
		'dara_mortgage' => array(
			__( 'Mortgage calculator', 'dara' ),
			array(
				'mortgage_show'      => array( 'checkbox', __( 'Show on properties for sale', 'dara' ) ),
				'mortgage_method'    => array(
					'select',
					__( 'Calculation method', 'dara' ),
					__( 'Declining balance is the standard mortgage formula. Flat rate (murabaha) applies the profit rate to the full amount for every year.', 'dara' ),
					array(
						'amortized' => __( 'Declining balance', 'dara' ),
						'flat'      => __( 'Flat rate (murabaha)', 'dara' ),
					),
				),
				'mortgage_rate'      => array( 'float', __( 'Default annual profit rate (%)', 'dara' ) ),
				'mortgage_down'      => array( 'number', __( 'Default down payment (%)', 'dara' ) ),
				'mortgage_years'     => array( 'number', __( 'Default term (years)', 'dara' ) ),
				'mortgage_max_years' => array( 'number', __( 'Maximum term (years)', 'dara' ) ),
				'mortgage_note'      => array( 'textarea', __( 'Disclaimer', 'dara' ) ),
			),
		),
		'dara_perf'     => array(
			__( 'Performance & maps', 'dara' ),
			array(
				'perf_emoji'      => array( 'checkbox', __( 'Disable WordPress emoji script', 'dara' ) ),
				'perf_block_css'  => array( 'checkbox', __( 'Load block CSS only on pages that use blocks', 'dara' ) ),
				'map_tiles'       => array( 'url', __( 'Map tiles URL', 'dara' ), __( 'Default: OpenStreetMap. For heavy traffic use a tile provider (MapTiler, Stadia...) with your key.', 'dara' ) ),
				'map_attribution' => array( 'text', __( 'Map attribution', 'dara' ) ),
			),
		),
	);
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wpc Manager.
 */
function dara_customize_register( $wpc ) {
	$defaults   = dara_defaults();
	$sanitizers = array(
		'text'     => 'sanitize_text_field',
		'textarea' => 'sanitize_textarea_field',
		'url'      => 'esc_url_raw',
		'email'    => 'sanitize_email',
		'number'   => 'absint',
		'image'    => 'absint',
		'color'    => 'sanitize_hex_color',
		'checkbox' => function ( $v ) {
			return (bool) $v;
		},
		'float'    => function ( $v ) {
			return is_numeric( $v ) ? (string) max( 0, min( 100, (float) $v ) ) : '';
		},
		'select'   => 'sanitize_key',
	);

	$wpc->add_panel(
		'dara',
		array(
			'title'    => __( 'Dara theme', 'dara' ),
			'priority' => 25,
		)
	);

	foreach ( dara_customizer_map() as $section => $def ) {
		$wpc->add_section(
			$section,
			array(
				'title' => $def[0],
				'panel' => 'dara',
			)
		);
		foreach ( $def[1] as $key => $field ) {
			$wpc->add_setting(
				$key,
				array(
					'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
					'sanitize_callback' => $sanitizers[ $field[0] ],
				)
			);
			$args = array(
				'label'       => $field[1],
				'description' => isset( $field[2] ) ? $field[2] : '',
				'section'     => $section,
			);
			if ( 'image' === $field[0] ) {
				$args['mime_type'] = 'image';
				$wpc->add_control( new WP_Customize_Media_Control( $wpc, $key, $args ) );
			} elseif ( 'select' === $field[0] ) {
				$args['type']    = 'select';
				$args['choices'] = $field[3];
				$wpc->add_control( $key, $args );
			} elseif ( 'float' === $field[0] ) {
				$args['type']        = 'number';
				$args['input_attrs'] = array(
					'min'  => 0,
					'max'  => 100,
					'step' => 0.01,
				);
				$wpc->add_control( $key, $args );
			} elseif ( 'color' === $field[0] ) {
				$wpc->add_control( new WP_Customize_Color_Control( $wpc, $key, $args ) );
			} else {
				$args['type'] = $field[0];
				$wpc->add_control( $key, $args );
			}
		}
	}
}
add_action( 'customize_register', 'dara_customize_register' );
