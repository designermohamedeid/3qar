<?php
/**
 * Default page.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-section">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'thara' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</section>
<?php
get_footer();
