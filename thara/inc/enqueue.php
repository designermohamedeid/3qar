<?php
/**
 * Styles and scripts.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end assets.
 */
function thara_enqueue_assets() {
	$css = THARA_URI . '/assets/css/';
	$js  = THARA_URI . '/assets/js/';
	$ver = THARA_VERSION;

	wp_enqueue_style( 'thara-fonts', $css . 'fonts.css', array(), $ver );
	wp_enqueue_style( 'bootstrap', $css . 'bootstrap3.min.css', array(), '3.3.7' );
	$main_deps = array( 'thara-fonts', 'bootstrap' );
	if ( is_rtl() ) {
		wp_enqueue_style( 'bootstrap-rtl', $css . 'bootstrap3-rtl.min.css', array( 'bootstrap' ), '3.3.7' );
		$main_deps[] = 'bootstrap-rtl';
	}
	wp_enqueue_style( 'font-awesome', $css . 'fontawesome.min.css', array(), '5.15.4' );
	wp_enqueue_style( 'linearicons', $css . 'linearicons.css', array(), '1.0.0' );
	wp_enqueue_style( 'owl-carousel', $css . 'owl.carousel.min.css', array(), '2.3.4' );
	wp_enqueue_style( 'thara-main', $css . 'main.css', array_merge( $main_deps, array( 'font-awesome', 'linearicons', 'owl-carousel' ) ), $ver );
	wp_enqueue_style( 'thara-theme', $css . 'theme.css', array( 'thara-main' ), $ver );
	wp_add_inline_style( 'thara-main', thara_inline_css() );

	wp_enqueue_script( 'bootstrap', $js . 'bootstrap.min.js', array( 'jquery' ), '3.3.7', true );
	wp_enqueue_script( 'owl-carousel', $js . 'owl.carousel.min.js', array( 'jquery' ), '2.3.4', true );
	wp_enqueue_script( 'jquery-countto', $js . 'jquery.countTo.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'thara-main', $js . 'main.js', array( 'jquery', 'bootstrap', 'owl-carousel', 'jquery-countto' ), $ver, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'thara_enqueue_assets' );

/**
 * CSS variables and options coming from the Customizer.
 *
 * @return string
 */
function thara_inline_css() {
	$primary = sanitize_hex_color( thara_option( 'primary_color' ) );
	$dark    = sanitize_hex_color( thara_option( 'dark_color' ) );
	$footer  = thara_option( 'footer_bg' );

	$css = ':root{';
	if ( $primary ) {
		$css .= '--thara-primary:' . $primary . ';';
	}
	if ( $dark ) {
		$css .= '--thara-dark:' . $dark . ';';
	}
	$css .= '}';

	if ( $footer ) {
		$css .= 'footer.site-footer{background-image:url("' . esc_url_raw( $footer ) . '");}';
	}

	return $css;
}

/**
 * Favicon fallback when no Site Icon is set.
 */
function thara_favicon_fallback() {
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" type="image/png" href="%s">' . "\n", esc_url( thara_asset( 'images/logo-side.png' ) ) );
	}
}
add_action( 'wp_head', 'thara_favicon_fallback' );
