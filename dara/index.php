<?php
/**
 * Blog, archives and search results.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );
$dara_sidebar = is_active_sidebar( 'sidebar-blog' );
?>
<div class="container section section--top-0<?php echo $dara_sidebar ? ' with-sidebar' : ''; ?>">
	<div class="content-area">
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="archive-desc"><?php the_archive_description(); ?></div>
		<?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--cards">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/' . ( 'dara_property' === get_post_type() ? 'property' : ( 'dara_project' === get_post_type() ? 'project' : 'post' ) ) );
				endwhile;
				?>
			</div>
			<?php dara_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content-none' ); ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
