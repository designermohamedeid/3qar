<?php
/**
 * Post card (used on the home page, blog, archives and search).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'new' ); ?>>
	<a href="<?php the_permalink(); ?>" class="new__thumb" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'thara-card', array( 'class' => 'img-responsive', 'loading' => 'lazy' ) );
		} else {
			printf( '<img class="img-responsive" src="%s" alt="" loading="lazy">', esc_url( thara_asset( 'images/news1.jpg' ) ) );
		}
		?>
	</a>
	<a href="<?php the_permalink(); ?>" class="new__title"><?php the_title(); ?></a>
	<div class="entry-meta"><?php thara_posted_on(); ?></div>
	<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
	<a href="<?php the_permalink(); ?>" class="new__more"><?php esc_html_e( 'Read more', 'thara' ); ?><span class="screen-reader-text"> - <?php the_title(); ?></span></a>
</article>
