<?php
/**
 * Agents directory.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title', null, array( 'subtitle' => __( 'Licensed agents ready to help you buy, sell or rent.', 'dara' ) ) );
?>
<div class="container section section--top-0">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--agents">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/agent' );
			endwhile;
			?>
		</div>
		<?php dara_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content-none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
