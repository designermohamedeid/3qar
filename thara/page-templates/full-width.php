<?php
/**
 * Template Name: Full Width (no title banner)
 * Template Post Type: page
 *
 * Useful for pages built with the block editor or a page builder.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="entry-content entry-content--full">
		<?php the_content(); ?>
	</div>
	<?php
endwhile;

get_footer();
