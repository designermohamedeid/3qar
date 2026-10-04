<?php
/**
 * Customizer: every front page section is editable from Appearance > Customize.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize a checkbox.
 *
 * @param mixed $value Value.
 * @return bool
 */
function thara_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitize text that may contain a little HTML.
 *
 * @param string $value Value.
 * @return string
 */
function thara_sanitize_html( $value ) {
	return wp_kses_post( $value );
}

/**
 * Sanitize a map embed (iframe only).
 *
 * @param string $value Value.
 * @return string
 */
function thara_sanitize_embed( $value ) {
	return wp_kses(
		$value,
		array(
			'iframe' => array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'style'           => true,
				'allowfullscreen' => true,
				'loading'         => true,
				'referrerpolicy'  => true,
				'title'           => true,
			),
		)
	);
}

/**
 * Customizer structure: section => array( title, fields ).
 * Field: key => array( type, label [, description] ).
 *
 * @return array
 */
function thara_customizer_sections() {
	return array(
		'thara_general'    => array(
			'title'  => __( 'General', 'thara' ),
			'fields' => array(
				'primary_color'     => array( 'color', __( 'Primary (gold) color', 'thara' ) ),
				'dark_color'        => array( 'color', __( 'Dark color', 'thara' ) ),
				'preloader'         => array( 'checkbox', __( 'Show the page preloader', 'thara' ) ),
				'demo_content'      => array( 'checkbox', __( 'Show demo content on the home page', 'thara' ), __( 'Displays sample services, properties and news until you publish your own.', 'thara' ) ),
				'side_logo'         => array( 'image', __( 'Mobile side menu logo', 'thara' ) ),
				'lang_switch_label' => array( 'text', __( 'Language switch label', 'thara' ), __( 'Used only when Polylang/WPML is not active. Example: English', 'thara' ) ),
				'lang_switch_url'   => array( 'url', __( 'Language switch URL', 'thara' ), __( 'Link to the other language version of the site.', 'thara' ) ),
			),
		),
		'thara_hero'       => array(
			'title'  => __( 'Home: Hero', 'thara' ),
			'fields' => array(
				'hero_image'         => array( 'image', __( 'Background image', 'thara' ) ),
				'hero_subtitle'      => array( 'text', __( 'Small title', 'thara' ) ),
				'hero_title'         => array( 'textarea', __( 'Main title', 'thara' ) ),
				'hero_btn_text'      => array( 'text', __( 'Button text', 'thara' ) ),
				'hero_btn_url'       => array( 'url', __( 'Button URL', 'thara' ), __( 'Leave empty to link to the properties archive.', 'thara' ) ),
				'hero_social'        => array( 'checkbox', __( 'Show social icons on the side', 'thara' ) ),
				'inner_header_image' => array( 'image', __( 'Inner pages header image', 'thara' ), __( 'Used when a page has no featured image. Defaults to the hero image.', 'thara' ) ),
			),
		),
		'thara_about'      => array(
			'title'  => __( 'Home: About & Counters', 'thara' ),
			'fields' => array(
				'about_show'      => array( 'checkbox', __( 'Show this section', 'thara' ) ),
				'about_title'     => array( 'text', __( 'Title', 'thara' ) ),
				'about_text'      => array( 'textarea', __( 'Text', 'thara' ) ),
				'counter1_label'  => array( 'text', __( 'Counter 1 label', 'thara' ) ),
				'counter1_number' => array( 'number', __( 'Counter 1 number', 'thara' ) ),
				'counter2_label'  => array( 'text', __( 'Counter 2 label', 'thara' ) ),
				'counter2_number' => array( 'number', __( 'Counter 2 number', 'thara' ) ),
			),
		),
		'thara_vision'     => array(
			'title'  => __( 'Home: Vision & Goals', 'thara' ),
			'fields' => array(
				'vision_show'  => array( 'checkbox', __( 'Show this section', 'thara' ) ),
				'vision_image' => array( 'image', __( 'Image', 'thara' ) ),
				'vision_icon'  => array( 'image', __( 'Vision icon', 'thara' ) ),
				'vision_title' => array( 'text', __( 'Vision title', 'thara' ) ),
				'vision_text'  => array( 'textarea', __( 'Vision text', 'thara' ) ),
				'goals_icon'   => array( 'image', __( 'Goals icon', 'thara' ) ),
				'goals_title'  => array( 'text', __( 'Goals title', 'thara' ) ),
				'goals_text'   => array( 'textarea', __( 'Goals text', 'thara' ) ),
			),
		),
		'thara_services'   => array(
			'title'       => __( 'Home: Services', 'thara' ),
			'description' => __( 'Services are managed from the "Services" menu in the dashboard. The featured image is used as the icon.', 'thara' ),
			'fields'      => array(
				'services_show'  => array( 'checkbox', __( 'Show this section', 'thara' ) ),
				'services_count' => array( 'number', __( 'Number of services', 'thara' ) ),
			),
		),
		'thara_properties' => array(
			'title'       => __( 'Home: Properties', 'thara' ),
			'description' => __( 'Properties are managed from the "Properties" menu in the dashboard.', 'thara' ),
			'fields'      => array(
				'properties_show'      => array( 'checkbox', __( 'Show this section', 'thara' ) ),
				'properties_title'     => array( 'text', __( 'Title', 'thara' ) ),
				'properties_link_text' => array( 'text', __( '"View all" link text', 'thara' ) ),
				'properties_count'     => array( 'number', __( 'Number of properties', 'thara' ) ),
			),
		),
		'thara_news'       => array(
			'title'  => __( 'Home: News', 'thara' ),
			'fields' => array(
				'news_show'  => array( 'checkbox', __( 'Show this section', 'thara' ) ),
				'news_title' => array( 'text', __( 'Title', 'thara' ) ),
				'news_count' => array( 'number', __( 'Number of posts', 'thara' ) ),
			),
		),
		'thara_contact'    => array(
			'title'  => __( 'Contact Info', 'thara' ),
			'fields' => array(
				'phone'              => array( 'text', __( 'Phone', 'thara' ) ),
				'email'              => array( 'email', __( 'Email', 'thara' ) ),
				'address'            => array( 'textarea', __( 'Address', 'thara' ) ),
				'map_embed'          => array( 'embed', __( 'Google Maps embed code (iframe)', 'thara' ), __( 'Shown on the "Contact" page template.', 'thara' ) ),
				'contact_call_label' => array( 'text', __( 'Phone label', 'thara' ) ),
				'contact_mail_label' => array( 'text', __( 'Email label', 'thara' ) ),
			),
		),
		'thara_social'     => array(
			'title'       => __( 'Social Links', 'thara' ),
			'description' => __( 'Leave a field empty to hide its icon. WhatsApp example: https://wa.me/966500000000', 'thara' ),
			'fields'      => array(
				'social_facebook'  => array( 'url', 'Facebook' ),
				'social_twitter'   => array( 'url', 'X / Twitter' ),
				'social_instagram' => array( 'url', 'Instagram' ),
				'social_whatsapp'  => array( 'url', 'WhatsApp' ),
				'social_snapchat'  => array( 'url', 'Snapchat' ),
				'social_linkedin'  => array( 'url', 'LinkedIn' ),
				'social_youtube'   => array( 'url', 'YouTube' ),
			),
		),
		'thara_footer'     => array(
			'title'  => __( 'Footer', 'thara' ),
			'fields' => array(
				'footer_bg'            => array( 'image', __( 'Background image', 'thara' ) ),
				'footer_links_title'   => array( 'text', __( 'Quick links title', 'thara' ) ),
				'social_title'         => array( 'text', __( 'Social title', 'thara' ) ),
				'newsletter_show'      => array( 'checkbox', __( 'Show newsletter', 'thara' ) ),
				'newsletter_title'     => array( 'text', __( 'Newsletter title', 'thara' ) ),
				'newsletter_text'      => array( 'textarea', __( 'Newsletter text', 'thara' ) ),
				'newsletter_shortcode' => array( 'text', __( 'Newsletter form shortcode', 'thara' ), __( 'Optional: e.g. a Mailchimp / Contact Form 7 shortcode. Replaces the built-in form.', 'thara' ) ),
				'newsletter_action'    => array( 'url', __( 'External form action URL', 'thara' ), __( 'Optional: e.g. a Mailchimp form action. The email field is sent as "EMAIL". Leave empty to receive subscriptions by email.', 'thara' ) ),
				'copyright'            => array( 'text', __( 'Copyright text', 'thara' ), __( 'You can use {year} and {site}.', 'thara' ) ),
				'credit_text'          => array( 'text', __( 'Credit text', 'thara' ) ),
				'credit_url'           => array( 'url', __( 'Credit URL', 'thara' ) ),
				'credit_logo'          => array( 'image', __( 'Credit logo', 'thara' ) ),
			),
		),
	);
}

/**
 * Register Customizer panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function thara_customize_register( $wp_customize ) {
	$defaults = thara_defaults();

	$wp_customize->add_panel(
		'thara_options',
		array(
			'title'    => __( 'Thara Theme Options', 'thara' ),
			'priority' => 30,
		)
	);

	$sanitizers = array(
		'text'     => 'sanitize_text_field',
		'textarea' => 'thara_sanitize_html',
		'url'      => 'esc_url_raw',
		'image'    => 'esc_url_raw',
		'email'    => 'sanitize_email',
		'number'   => 'absint',
		'checkbox' => 'thara_sanitize_checkbox',
		'color'    => 'sanitize_hex_color',
		'embed'    => 'thara_sanitize_embed',
	);

	foreach ( thara_customizer_sections() as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $section['title'],
				'panel'       => 'thara_options',
				'description' => isset( $section['description'] ) ? $section['description'] : '',
			)
		);

		foreach ( $section['fields'] as $key => $field ) {
			$type = $field[0];
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
					'sanitize_callback' => $sanitizers[ $type ],
					'transport'         => 'refresh',
				)
			);

			$args = array(
				'label'       => $field[1],
				'description' => isset( $field[2] ) ? $field[2] : '',
				'section'     => $section_id,
				'settings'    => $key,
			);

			if ( 'image' === $type ) {
				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, $args ) );
			} elseif ( 'color' === $type ) {
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, $args ) );
			} else {
				$args['type'] = 'embed' === $type ? 'textarea' : $type;
				if ( 'number' === $type ) {
					$args['input_attrs'] = array( 'min' => 0 );
				}
				$wp_customize->add_control( $key, $args );
			}
		}
	}
}
add_action( 'customize_register', 'thara_customize_register' );
