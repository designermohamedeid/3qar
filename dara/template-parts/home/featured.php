<?php
/**
 * Home: featured properties.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_args = array(
	'post_type'           => 'dara_property',
	'posts_per_page'      => max( 1, (int) dara_mod( 'featured_count' ) ),
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
);
if ( dara_mod( 'featured_only' ) ) {
	$dara_args['meta_key']   = '_dara_featured'; // phpcs:ignore WordPress.DB.SlowDBQuery
	$dara_args['meta_value'] = '1'; // phpcs:ignore WordPress.DB.SlowDBQuery
}
$dara_q = new WP_Query( $dara_args );
if ( ! $dara_q->have_posts() ) {
	return;
}
$dara_types = get_terms( array( 'taxonomy' => 'property_type', 'number' => 4, 'orderby' => 'count', 'order' => 'DESC' ) );
?>
<section class="section">
	<div class="container">
		<div class="section-row">
			<?php dara_section_head( dara_mod( 'featured_eyebrow' ), dara_mod( 'featured_title' ) ); ?>
			<?php if ( $dara_types && ! is_wp_error( $dara_types ) ) : ?>
				<nav class="chips" aria-label="<?php esc_attr_e( 'Property types', 'dara' ); ?>">
					<a class="chip is-active" href="<?php echo esc_url( dara_listings_url() ); ?>"><?php esc_html_e( 'All', 'dara' ); ?></a>
					<?php foreach ( $dara_types as $dara_type ) : ?>
						<a class="chip" href="<?php echo esc_url( get_term_link( $dara_type ) ); ?>"><?php echo esc_html( $dara_type->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
		<div class="grid grid--cards">
			<?php
			while ( $dara_q->have_posts() ) :
				$dara_q->the_post();
				get_template_part( 'template-parts/cards/property' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<div class="section-foot">
			<a class="btn btn--outline" href="<?php echo esc_url( dara_listings_url() ); ?>"><?php esc_html_e( 'View all properties', 'dara' ); ?><?php dara_the_icon( 'arrow', 18, 'flip-rtl' ); ?></a>
		</div>
	</div>
</section>
