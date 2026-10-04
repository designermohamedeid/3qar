<?php
/**
 * Agents section (block).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

if ( ! post_type_exists( 'dara_agent' ) ) {
	return;
}
$dara_q = new WP_Query(
	array(
		'post_type'      => 'dara_agent',
		'posts_per_page' => max( 1, (int) ( isset( $args['agents_count'] ) ? $args['agents_count'] : 4 ) ),
		'no_found_rows'  => true,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	)
);
if ( ! $dara_q->have_posts() ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<div class="section-row">
			<?php dara_section_head( isset( $args['agents_eyebrow'] ) ? $args['agents_eyebrow'] : '', isset( $args['agents_title'] ) ? $args['agents_title'] : __( 'Our agents', 'dara' ) ); ?>
			<a class="link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'dara_agent' ) ); ?>"><?php esc_html_e( 'All agents', 'dara' ); ?></a>
		</div>
		<div class="grid grid--agents">
			<?php
			while ( $dara_q->have_posts() ) :
				$dara_q->the_post();
				get_template_part( 'template-parts/cards/agent' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
