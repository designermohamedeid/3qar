<?php
/**
 * Services archive.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="service-section services-archive">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="row services-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<div class="col-sm-6 col-md-3">
						<a class="card-box" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="icon-box"><?php the_post_thumbnail( 'thumbnail', array( 'alt' => '' ) ); ?></div>
							<?php endif; ?>
							<h3><?php the_title(); ?></h3>
						</a>
					</div>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/none' ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
