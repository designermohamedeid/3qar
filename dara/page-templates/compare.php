<?php
/**
 * Template Name: Compare properties
 *
 * The comparison table comes from the Dara Pro plugin.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );

if ( function_exists( 'dara_pro_compare_page' ) ) {
	dara_pro_compare_page();
} else {
	echo '<div class="container section section--top-0"><p class="empty">' . esc_html__( 'Property comparison needs the Dara Pro plugin.', 'dara' ) . '</p></div>';
}

get_footer();
