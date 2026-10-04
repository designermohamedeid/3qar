<?php
/**
 * Home: latest news (blog posts).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_news = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, absint( thara_option( 'news_count' ) ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $thara_news->have_posts() && ! thara_option( 'demo_content' ) ) {
	return;
}
?>
<section class="news" id="news">
	<div class="container">
		<h2 class="text-center"><?php echo esc_html( thara_option( 'news_title' ) ); ?></h2>
		<div class="row">
			<?php if ( $thara_news->have_posts() ) : ?>
				<?php
				while ( $thara_news->have_posts() ) :
					$thara_news->the_post();
					?>
					<div class="col-sm-6">
						<?php get_template_part( 'template-parts/content/card' ); ?>
					</div>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php foreach ( array( 1, 2 ) as $thara_n ) : ?>
					<div class="col-sm-6">
						<div class="new">
							<img class="img-responsive" src="<?php echo esc_url( thara_asset( 'images/news' . $thara_n . '.jpg' ) ); ?>" alt="" loading="lazy">
							<a><?php esc_html_e( 'Your latest news will appear here', 'thara' ); ?></a>
							<p><?php esc_html_e( 'Publish posts from Posts > Add New and the two most recent ones will be shown in this section.', 'thara' ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
