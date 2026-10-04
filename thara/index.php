<?php
/**
 * Main template: blog index and generic fallback.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-section">
	<div class="container">
		<div class="row">
			<div class="<?php echo is_active_sidebar( 'sidebar-1' ) ? 'col-md-8' : 'col-md-12'; ?>">
				<?php if ( is_archive() && get_the_archive_description() ) : ?>
					<div class="archive-description"><?php the_archive_description(); ?></div>
				<?php endif; ?>

				<?php if ( have_posts() ) : ?>
					<div class="posts-grid <?php echo is_active_sidebar( 'sidebar-1' ) ? 'posts-grid--2' : 'posts-grid--3'; ?>">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content/card' );
						endwhile;
						?>
					</div>
					<?php thara_pagination(); ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content/none' ); ?>
				<?php endif; ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</section>
<?php
get_footer();
