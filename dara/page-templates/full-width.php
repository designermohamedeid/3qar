<?php
/**
 * Template Name: Full width (no title)
 *
 * For pages built with blocks or a page builder.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	echo '<div class="entry-content entry-content--full">';
	the_content();
	echo '</div>';
endwhile;
get_footer();
