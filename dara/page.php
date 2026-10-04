<?php
/**
 * Page.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );

while ( have_posts() ) :
	the_post();
	?>
	<div class="container container--narrow section section--top-0">
		<div class="entry-content"><?php the_content(); ?></div>
		<?php
		wp_link_pages( array( 'before' => '<nav class="page-links">', 'after' => '</nav>' ) );
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
