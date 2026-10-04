<?php
/**
 * Theme setup: supports, menus, image sizes, sidebars.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and features.
 */
function thara_setup() {
	load_theme_textdomain( 'thara', THARA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 125,
			'width'       => 308,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu (header & mobile side menu)', 'thara' ),
			'footer'  => __( 'Footer Quick Links', 'thara' ),
		)
	);

	add_image_size( 'thara-property', 720, 9999, false );
	add_image_size( 'thara-card', 1108, 456, true );
	add_image_size( 'thara-hero', 1920, 1100, true );
}
add_action( 'after_setup_theme', 'thara_setup' );

/**
 * Content width.
 */
function thara_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'thara_content_width', 1140 );
}
add_action( 'after_setup_theme', 'thara_content_width', 0 );

/**
 * Register widget areas.
 */
function thara_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'thara' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Shown next to posts and archives.', 'thara' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'thara_widgets_init' );

/**
 * WordPress only prints dir="rtl"; the design also targets html[dir="ltr"].
 *
 * @param string $output Language attributes.
 * @return string
 */
function thara_language_attributes( $output ) {
	if ( false === strpos( $output, 'dir=' ) ) {
		$output .= ' dir="ltr"';
	}
	return $output;
}
add_filter( 'language_attributes', 'thara_language_attributes' );

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function thara_body_classes( $classes ) {
	$classes[] = is_front_page() ? 'thara-home' : 'thara-inner';
	if ( is_active_sidebar( 'sidebar-1' ) && ( is_home() || is_archive() || is_search() || is_singular( 'post' ) ) ) {
		$classes[] = 'has-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'thara_body_classes' );

/**
 * Shorter, cleaner excerpts.
 *
 * @return int
 */
function thara_excerpt_length() {
	return 28;
}
add_filter( 'excerpt_length', 'thara_excerpt_length' );

/**
 * Excerpt "more" string.
 *
 * @return string
 */
function thara_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'thara_excerpt_more' );

/**
 * Pingback header.
 */
function thara_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'thara_pingback_header' );
