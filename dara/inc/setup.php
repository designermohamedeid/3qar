<?php
/**
 * Theme setup.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports, menus, image sizes.
 */
function dara_setup() {
	load_theme_textdomain( 'dara', DARA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'dara-blocks' ); // Section blocks from the Dara Core plugin.
	add_editor_style( array( 'assets/css/core.css', 'assets/css/home.css', 'assets/css/agents.css', 'assets/css/editor.css' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 88,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Primary', 'dara' ),
				'slug'  => 'primary',
				'color' => '#0B6E5F',
			),
			array(
				'name'  => __( 'Ink', 'dara' ),
				'slug'  => 'ink',
				'color' => '#0E1A2B',
			),
			array(
				'name'  => __( 'Surface', 'dara' ),
				'slug'  => 'surface',
				'color' => '#F5F7FA',
			),
			array(
				'name'  => __( 'White', 'dara' ),
				'slug'  => 'white',
				'color' => '#FFFFFF',
			),
		)
	);
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Main menu', 'dara' ),
			'footer'  => __( 'Footer: quick links', 'dara' ),
		)
	);

	// Sizes tuned to the layout (2x for retina on cards).
	add_image_size( 'dara-card', 640, 480, true );
	add_image_size( 'dara-wide', 1280, 720, true );
	add_image_size( 'dara-hero', 1920, 1080, true );
}
add_action( 'after_setup_theme', 'dara_setup' );

/**
 * Content width.
 */
function dara_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'dara_content_width', 0 );

/**
 * Widget areas.
 */
function dara_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Blog sidebar', 'dara' ),
			'id'            => 'sidebar-blog',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'dara_widgets_init' );

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function dara_body_class( $classes ) {
	if ( dara_header_is_overlay() ) {
		$classes[] = 'has-overlay-header';
	}
	return $classes;
}
add_filter( 'body_class', 'dara_body_class' );

/**
 * Excerpt length.
 *
 * @return int
 */
function dara_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'dara_excerpt_length' );

/**
 * Excerpt more.
 *
 * @return string
 */
function dara_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'dara_excerpt_more' );
