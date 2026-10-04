<?php
/**
 * Single post (also used by services).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
$thara_has_sidebar = is_singular( 'post' ) && is_active_sidebar( 'sidebar-1' );
?>
<section class="page-section">
	<div class="container">
		<div class="row">
			<div class="<?php echo $thara_has_sidebar ? 'col-md-8' : 'col-md-10 col-md-offset-1'; ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
						<?php if ( 'post' === get_post_type() ) : ?>
							<div class="entry-meta"><?php thara_posted_on(); ?></div>
						<?php endif; ?>

						<?php if ( has_post_thumbnail() && 'thara_service' !== get_post_type() ) : ?>
							<figure class="entry-thumb"><?php the_post_thumbnail( 'large', array( 'class' => 'img-responsive' ) ); ?></figure>
						<?php endif; ?>

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

						<?php if ( 'post' === get_post_type() ) : ?>
							<div class="entry-footer">
								<?php
								$thara_cats = get_the_category_list( ' ' );
								$thara_tags = get_the_tag_list( '', ' ' );
								if ( $thara_cats ) {
									echo '<div class="entry-terms"><span class="lnr lnr-folder" aria-hidden="true"></span>' . $thara_cats . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
								}
								if ( $thara_tags ) {
									echo '<div class="entry-terms"><span class="lnr lnr-tag" aria-hidden="true"></span>' . $thara_tags . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
								}
								?>
							</div>
							<?php
							the_post_navigation(
								array(
									'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'thara' ) . '</span><span class="nav-title">%title</span>',
									'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'thara' ) . '</span><span class="nav-title">%title</span>',
								)
							);
							?>
						<?php endif; ?>
					</article>
					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				endwhile;
				?>
			</div>
			<?php
			if ( $thara_has_sidebar ) {
				get_sidebar();
			}
			?>
		</div>
	</div>
</section>
<?php
get_footer();
