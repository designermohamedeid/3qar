<?php
/**
 * Dara Child functions.
 *
 * @package DaraChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the child stylesheet after the parent bundles.
 */
function dara_child_styles() {
	wp_enqueue_style( 'dara-child', get_stylesheet_uri(), array( 'dara' ), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'dara_child_styles', 20 );

/**
 * Child theme translations (wp-content/themes/dara-child/languages).
 */
function dara_child_setup() {
	load_child_theme_textdomain( 'dara-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'dara_child_setup' );
