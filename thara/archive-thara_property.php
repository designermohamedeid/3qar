<?php
/**
 * Properties archive (also used by the "Property Type" taxonomy).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();

$thara_types = get_terms(
	array(
		'taxonomy'   => 'property_type',
		'hide_empty' => true,
	)
);
?>
<section class="page-section properties-archive">
	<div class="container">
		<?php if ( ! is_wp_error( $thara_types ) && $thara_types ) : ?>
			<ul class="res property-filter">
				<li class="<?php echo is_post_type_archive( 'thara_property' ) ? 'active' : ''; ?>"><a href="<?php echo esc_url( get_post_type_archive_link( 'thara_property' ) ); ?>"><?php esc_html_e( 'All', 'thara' ); ?></a></li>
				<?php foreach ( $thara_types as $thara_type ) : ?>
					<li class="<?php echo is_tax( 'property_type', $thara_type->term_id ) ? 'active' : ''; ?>"><a href="<?php echo esc_url( get_term_link( $thara_type ) ); ?>"><?php echo esc_html( $thara_type->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="properties-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/property-card' );
				endwhile;
				?>
			</div>
			<?php thara_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/none' ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
