<?php
/**
 * Page.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( dara_has_dara_blocks() ) {
	// Page built with Dara blocks: full width sections, no title bar.
	while ( have_posts() ) {
		the_post();
		echo '<div class="block-page">';
		the_content();
		echo '</div>';
	}
	get_footer();
	return;
}

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
