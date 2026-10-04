<?php
/**
 * Front-end performance tweaks (each can be switched off in Customize > Dara > Performance).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the emoji detection script and styles.
 */
function dara_disable_emojis() {
	if ( ! dara_mod( 'perf_emoji' ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'dara_disable_emojis' );

/**
 * Head cleanup.
 */
function dara_head_cleanup() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'after_setup_theme', 'dara_head_cleanup' );

/**
 * Only load block-library CSS where blocks are actually used.
 */
function dara_trim_block_css() {
	if ( ! dara_mod( 'perf_block_css' ) ) {
		return;
	}
	$keep = is_home() || is_search()
		|| ( is_archive() && ! is_post_type_archive( array( 'dara_property', 'dara_project' ) ) && ! is_tax( array( 'property_type', 'property_city' ) ) )
		|| ( is_singular() && has_blocks( get_queried_object_id() ) );
	if ( $keep ) {
		return;
	}
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'dara_trim_block_css', 100 );

/**
 * Classic themes load the whole block library (~110 KB). Load only the CSS of
 * blocks that are actually rendered on the page instead.
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Meta description fallback when no SEO plugin is active.
 */
function dara_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		return;
	}
	$desc = '';
	if ( is_front_page() ) {
		$desc = get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : dara_mod( 'hero_text' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, '…' );
	} elseif ( is_home() ) {
		$desc = get_bloginfo( 'description' );
	} elseif ( is_post_type_archive( 'dara_property' ) ) {
		$desc = dara_listing_title() . ' — ' . get_bloginfo( 'name' );
	} elseif ( is_archive() ) {
		$desc = wp_strip_all_tags( get_the_archive_description() );
	}
	$desc = trim( preg_replace( '/\s+/', ' ', $desc ) );
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( wp_html_excerpt( $desc, 160, '…' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'dara_meta_description', 2 );
