<?php
/**
 * Home: developer projects (dark band).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_q = new WP_Query(
	array(
		'post_type'      => 'dara_project',
		'posts_per_page' => max( 1, (int) dara_mod( 'projects_count' ) ),
		'no_found_rows'  => true,
	)
);
if ( ! $dara_q->have_posts() ) {
	return;
}
?>
<section class="section section--dark">
	<div class="container">
		<div class="section-row">
			<?php dara_section_head( dara_mod( 'projects_eyebrow' ), dara_mod( 'projects_title' ), dara_mod( 'projects_text' ) ); ?>
			<a class="btn btn--ghost-light" href="<?php echo esc_url( get_post_type_archive_link( 'dara_project' ) ); ?>"><?php esc_html_e( 'All projects', 'dara' ); ?><?php dara_the_icon( 'arrow', 18, 'flip-rtl' ); ?></a>
		</div>
		<div class="grid grid--projects">
			<?php
			while ( $dara_q->have_posts() ) :
				$dara_q->the_post();
				get_template_part( 'template-parts/cards/project' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
