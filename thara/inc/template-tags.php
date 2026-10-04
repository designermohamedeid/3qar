<?php
/**
 * Helper functions and template tags.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every Customizer option.
 *
 * Strings are translatable so a fresh install shows Arabic or English
 * content depending on the site language.
 *
 * @return array
 */
function thara_defaults() {
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}

	$lorem = __( 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC.', 'thara' );

	$defaults = array(
		// General.
		'primary_color'        => '#c8ac66',
		'dark_color'           => '#404042',
		'preloader'            => true,
		'demo_content'         => true,
		'side_logo'            => '',
		'lang_switch_label'    => '',
		'lang_switch_url'      => '',
		// Hero.
		'hero_image'           => thara_asset( 'images/header.jpg' ),
		'hero_subtitle'        => __( 'Al-Thara Real Estate Company', 'thara' ),
		'hero_title'           => __( 'Huge spaces for affluence and luxury at the best prices', 'thara' ),
		'hero_btn_text'        => __( 'Browse properties', 'thara' ),
		'hero_btn_url'         => '',
		'hero_social'          => true,
		'inner_header_image'   => '',
		// About / counters.
		'about_show'           => true,
		'about_title'          => __( 'Get to know Al-Thara Real Estate', 'thara' ),
		'about_text'           => __( 'We specialize in real estate financing solutions, as well as buying, selling, renting and marketing properties.', 'thara' ),
		'counter1_label'       => __( 'Completed projects', 'thara' ),
		'counter1_number'      => 100,
		'counter2_label'       => __( 'Happy clients', 'thara' ),
		'counter2_number'      => 2000,
		// Vision & goals.
		'vision_show'          => true,
		'vision_image'         => thara_asset( 'images/overview.jpg' ),
		'vision_icon'          => thara_asset( 'images/shape1.png' ),
		'vision_title'         => __( 'Our Vision', 'thara' ),
		'vision_text'          => $lorem,
		'goals_icon'           => thara_asset( 'images/shape2.png' ),
		'goals_title'          => __( 'Our Goals', 'thara' ),
		'goals_text'           => $lorem,
		// Services.
		'services_show'        => true,
		'services_count'       => 4,
		// Properties.
		'properties_show'      => true,
		'properties_title'     => __( 'Browse the latest properties', 'thara' ),
		'properties_link_text' => __( 'View all', 'thara' ),
		'properties_count'     => 8,
		// News.
		'news_show'            => true,
		'news_title'           => __( 'Latest News', 'thara' ),
		'news_count'           => 2,
		// Contact.
		'phone'                => '(966+) 114544012',
		'email'                => 'info@tharaalaqariyah.com',
		'address'              => '',
		'map_embed'            => '',
		// Social.
		'social_facebook'      => '',
		'social_twitter'       => '',
		'social_instagram'     => '',
		'social_whatsapp'      => '',
		'social_snapchat'      => '',
		'social_linkedin'      => '',
		'social_youtube'       => '',
		// Footer.
		'footer_bg'            => '',
		'contact_call_label'   => __( 'Call us now', 'thara' ),
		'contact_mail_label'   => __( 'Or email us at', 'thara' ),
		'footer_links_title'   => __( 'Quick links', 'thara' ),
		'social_title'         => __( 'Follow us on social media', 'thara' ),
		'newsletter_show'      => true,
		'newsletter_title'     => __( 'Newsletter', 'thara' ),
		'newsletter_text'      => __( 'Subscribe to our newsletter to receive our latest offers and products.', 'thara' ),
		'newsletter_shortcode' => '',
		'newsletter_action'    => '',
		/* translators: Copyright line. Keep the {site} and {year} placeholders as they are. */
		'copyright'            => __( '© {site} {year}', 'thara' ),
		'credit_text'          => '',
		'credit_url'           => '',
		'credit_logo'          => '',
	);

	return $defaults;
}

/**
 * Get a theme option with its default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function thara_option( $key ) {
	$defaults = thara_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( $key, $default );
}

/**
 * URL of a bundled asset.
 *
 * @param string $path Path relative to /assets.
 * @return string
 */
function thara_asset( $path ) {
	return THARA_URI . '/assets/' . ltrim( $path, '/' );
}

/**
 * Print the site logo (custom logo or bundled default).
 */
function thara_site_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$file = is_rtl() ? 'images/logo.png' : 'images/logo-en.png';
	printf(
		'<a href="%1$s" rel="home"><img src="%2$s" alt="%3$s" width="308" height="125"></a>',
		esc_url( home_url( '/' ) ),
		esc_url( thara_asset( $file ) ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/**
 * Social networks supported by the theme.
 *
 * @return array key => array( icon class, label )
 */
function thara_social_networks() {
	return array(
		'facebook'  => array( 'fab fa-facebook-f', 'Facebook' ),
		'twitter'   => array( 'fab fa-twitter', 'X / Twitter' ),
		'whatsapp'  => array( 'fab fa-whatsapp', 'WhatsApp' ),
		'instagram' => array( 'fab fa-instagram', 'Instagram' ),
		'snapchat'  => array( 'fab fa-snapchat-ghost', 'Snapchat' ),
		'linkedin'  => array( 'fab fa-linkedin-in', 'LinkedIn' ),
		'youtube'   => array( 'fab fa-youtube', 'YouTube' ),
	);
}

/**
 * Print social links (only the ones that have a URL).
 *
 * @return bool Whether any link was printed.
 */
function thara_social_links() {
	$printed = false;
	foreach ( thara_social_networks() as $key => $network ) {
		$url = thara_option( 'social_' . $key );
		if ( ! $url ) {
			continue;
		}
		printf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s"><i class="%3$s" aria-hidden="true"></i></a>',
			esc_url( $url ),
			esc_attr( $network[1] ),
			esc_attr( $network[0] )
		);
		$printed = true;
	}
	return $printed;
}

/**
 * Whether at least one social link is configured.
 *
 * @return bool
 */
function thara_has_social_links() {
	foreach ( array_keys( thara_social_networks() ) as $key ) {
		if ( thara_option( 'social_' . $key ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Alternate languages for the switcher.
 *
 * Supports Polylang and WPML; falls back to the URL set in the Customizer.
 *
 * @return array List of array( 'url' => ..., 'name' => ... ).
 */
function thara_alternate_languages() {
	$languages = array();

	if ( function_exists( 'pll_the_languages' ) ) {
		$list = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
		foreach ( (array) $list as $lang ) {
			if ( empty( $lang['current_lang'] ) ) {
				$languages[] = array(
					'url'  => $lang['url'],
					'name' => $lang['name'],
				);
			}
		}
	} elseif ( has_filter( 'wpml_active_languages' ) ) {
		$list = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
		foreach ( (array) $list as $lang ) {
			if ( empty( $lang['active'] ) ) {
				$languages[] = array(
					'url'  => $lang['url'],
					'name' => $lang['native_name'],
				);
			}
		}
	} elseif ( thara_option( 'lang_switch_url' ) ) {
		$languages[] = array(
			'url'  => thara_option( 'lang_switch_url' ),
			'name' => thara_option( 'lang_switch_label' ) ? thara_option( 'lang_switch_label' ) : ( is_rtl() ? 'English' : 'عربي' ),
		);
	}

	return $languages;
}

/**
 * Print the language switcher link(s).
 *
 * @param string $class Link class.
 */
function thara_language_switcher( $class = '' ) {
	foreach ( thara_alternate_languages() as $lang ) {
		printf(
			'<a class="%1$s" href="%2$s">%3$s</a>',
			esc_attr( $class ),
			esc_url( $lang['url'] ),
			esc_html( $lang['name'] )
		);
	}
}

/**
 * Directional arrow icon class (points "forward" in reading direction).
 *
 * @return string
 */
function thara_arrow_class() {
	return is_rtl() ? 'lnr lnr-arrow-left' : 'lnr lnr-arrow-right';
}

/**
 * Title shown in the inner pages header.
 *
 * @return string
 */
function thara_page_title() {
	if ( is_home() ) {
		$page_for_posts = get_option( 'page_for_posts' );
		return $page_for_posts ? get_the_title( $page_for_posts ) : __( 'Latest News', 'thara' );
	}
	if ( is_search() ) {
		/* translators: %s: search query. */
		return sprintf( __( 'Search results for: %s', 'thara' ), get_search_query() );
	}
	if ( is_404() ) {
		return __( 'Page not found', 'thara' );
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	if ( is_singular() ) {
		return single_post_title( '', false );
	}
	return get_bloginfo( 'name' );
}

/**
 * Background image for the inner pages header.
 *
 * @return string
 */
function thara_inner_header_image() {
	if ( is_singular() && has_post_thumbnail() && ! is_singular( 'thara_service' ) ) {
		return get_the_post_thumbnail_url( null, 'thara-hero' );
	}
	$image = thara_option( 'inner_header_image' );
	return $image ? $image : thara_option( 'hero_image' );
}

/**
 * Post meta line: date and author.
 */
function thara_posted_on() {
	printf(
		'<span class="posted-on"><span class="lnr lnr-calendar-full" aria-hidden="true"></span><time datetime="%1$s">%2$s</time></span>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
	if ( 'post' === get_post_type() ) {
		printf(
			'<span class="byline"><span class="lnr lnr-user" aria-hidden="true"></span><a href="%1$s">%2$s</a></span>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}
}

/**
 * Numbered pagination.
 */
function thara_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => '<span class="' . ( is_rtl() ? 'lnr lnr-chevron-right' : 'lnr lnr-chevron-left' ) . '" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Previous', 'thara' ) . '</span>',
			'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Next', 'thara' ) . '</span><span class="' . ( is_rtl() ? 'lnr lnr-chevron-left' : 'lnr lnr-chevron-right' ) . '" aria-hidden="true"></span>',
		)
	);
}

/**
 * Copyright line with {year} and {site} placeholders.
 *
 * @return string
 */
function thara_copyright() {
	return str_replace(
		array( '{year}', '{site}' ),
		array( wp_date( 'Y' ), get_bloginfo( 'name' ) ),
		thara_option( 'copyright' )
	);
}

/**
 * Property details (meta) for display.
 *
 * @param int $post_id Post ID.
 * @return array label => value
 */
function thara_property_details( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$details = array();

	foreach ( thara_property_fields() as $key => $field ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( '' === $value || null === $value ) {
			continue;
		}
		if ( isset( $field['options'][ $value ] ) ) {
			$value = $field['options'][ $value ];
		}
		if ( ! empty( $field['suffix'] ) ) {
			$value .= ' ' . $field['suffix'];
		}
		$details[ $key ] = array(
			'label' => $field['label'],
			'icon'  => $field['icon'],
			'value' => $value,
		);
	}

	return $details;
}

/**
 * Breadcrumbs (uses Yoast SEO / Rank Math when available).
 */
function thara_breadcrumbs() {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<div class="breadcrumbs">', '</div>' );
		return;
	}
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<div class="breadcrumbs">';
		rank_math_the_breadcrumbs();
		echo '</div>';
		return;
	}

	$items = array(
		array( home_url( '/' ), __( 'Home', 'thara' ) ),
	);

	if ( is_singular() ) {
		$post_type = get_post_type();
		if ( 'post' === $post_type ) {
			$page_for_posts = get_option( 'page_for_posts' );
			if ( $page_for_posts ) {
				$items[] = array( get_permalink( $page_for_posts ), get_the_title( $page_for_posts ) );
			}
			$categories = get_the_category();
			if ( $categories ) {
				$items[] = array( get_category_link( $categories[0] ), $categories[0]->name );
			}
		} elseif ( 'page' !== $post_type ) {
			$object = get_post_type_object( $post_type );
			if ( $object && $object->has_archive ) {
				$items[] = array( get_post_type_archive_link( $post_type ), $object->labels->name );
			}
		} else {
			foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
				$items[] = array( get_permalink( $ancestor ), get_the_title( $ancestor ) );
			}
		}
	} elseif ( is_tax( 'property_type' ) ) {
		$items[] = array( get_post_type_archive_link( 'thara_property' ), get_post_type_object( 'thara_property' )->labels->name );
	}

	$items[] = array( '', thara_page_title() );

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'thara' ) . '"><ol class="res">';
	foreach ( $items as $item ) {
		if ( $item[0] ) {
			printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $item[0] ), esc_html( $item[1] ) );
		} else {
			printf( '<li aria-current="page">%s</li>', esc_html( $item[1] ) );
		}
	}
	echo '</ol></nav>';
}
