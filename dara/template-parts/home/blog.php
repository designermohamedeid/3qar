<?php
/**
 * Home: latest posts.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_q = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, (int) dara_part_opt( $args, 'blog_count' ) ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);
if ( ! $dara_q->have_posts() ) {
	return;
}
$dara_blog = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : '';
?>
<section class="section section--surface">
	<div class="container">
		<div class="section-row">
			<?php dara_section_head( dara_part_opt( $args, 'blog_eyebrow' ), dara_part_opt( $args, 'blog_title' ) ); ?>
			<?php if ( $dara_blog ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( $dara_blog ); ?>"><?php esc_html_e( 'All articles', 'dara' ); ?></a>
			<?php endif; ?>
		</div>
		<div class="grid grid--cards">
			<?php
			while ( $dara_q->have_posts() ) :
				$dara_q->the_post();
				get_template_part( 'template-parts/cards/post' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
