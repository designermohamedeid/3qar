<?php
/**
 * Template Name: Developers
 *
 * Directory of real estate developers and their projects.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-title', null, array( 'subtitle' => get_the_excerpt() ? get_the_excerpt() : __( 'The developers behind our projects, with their full portfolios.', 'dara' ) ) );
	$dara_devs = taxonomy_exists( 'project_developer' ) ? get_terms(
		array(
			'taxonomy'   => 'project_developer',
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	) : array();
	?>
	<div class="container section section--top-0">
		<?php if ( '' !== trim( get_the_content() ) ) : ?>
			<div class="entry-content developers-intro"><?php the_content(); ?></div>
		<?php endif; ?>
		<?php if ( $dara_devs && ! is_wp_error( $dara_devs ) ) : ?>
			<div class="grid grid--developers">
				<?php
				foreach ( $dara_devs as $dara_dev ) {
					get_template_part( 'template-parts/cards/developer', null, array( 'term' => $dara_dev ) );
				}
				?>
			</div>
		<?php else : ?>
			<p class="empty"><?php esc_html_e( 'No developers added yet.', 'dara' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
