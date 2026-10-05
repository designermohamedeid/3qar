<?php
/**
 * Blog post card.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
$dara_cats = get_the_category();
?>
<article <?php post_class( 'card card--post' ); ?>>
	<a class="card__media card__media--wide" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		echo dara_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
			get_post_thumbnail_id(),
			'dara-card',
			array(
				'sizes' => '(max-width: 640px) 100vw, 400px',
				'alt'   => '',
			)
		); // phpcs:ignore WordPress.Security.EscapeOutput 
		?>
	</a>
	<div class="card__body">
		<p class="card__kicker">
			<?php if ( $dara_cats ) : ?>
				<a href="<?php echo esc_url( get_category_link( $dara_cats[0] ) ); ?>"><?php echo esc_html( $dara_cats[0]->name ); ?></a>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</p>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
	</div>
</article>
