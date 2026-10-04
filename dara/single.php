<?php
/**
 * Single post.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$dara_cats = get_the_category();
	?>
	<article <?php post_class( 'article' ); ?>>
		<div class="container container--narrow article__head">
			<?php dara_breadcrumbs(); ?>
			<p class="card__kicker">
				<?php if ( $dara_cats ) : ?>
					<a href="<?php echo esc_url( get_category_link( $dara_cats[0] ) ); ?>"><?php echo esc_html( $dara_cats[0]->name ); ?></a>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<span><?php echo esc_html( sprintf( /* translators: %s: minutes. */ __( '%s min read', 'dara' ), number_format_i18n( max( 1, (int) ceil( count( preg_split( '/\s+/u', trim( wp_strip_all_tags( get_the_content() ) ) ) ) / 200 ) ) ) ) ); ?></span>
			</p>
			<h1 class="article__title"><?php the_title(); ?></h1>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="container article__thumb"><?php the_post_thumbnail( 'dara-wide', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 1240px) 100vw, 1192px' ) ); ?></figure>
		<?php endif; ?>
		<div class="container container--narrow">
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<nav class="page-links">', 'after' => '</nav>' ) );
				?>
			</div>
			<footer class="article__foot">
				<?php the_tags( '<div class="tags">', '', '</div>' ); ?>
				<button type="button" class="btn btn--outline btn--sm" data-share data-title="<?php echo esc_attr( get_the_title() ); ?>"><?php dara_the_icon( 'share', 18 ); ?><?php esc_html_e( 'Share', 'dara' ); ?></button>
			</footer>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<small>' . esc_html__( 'Previous', 'dara' ) . '</small><span>%title</span>',
					'next_text' => '<small>' . esc_html__( 'Next', 'dara' ) . '</small><span>%title</span>',
				)
			);
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
