<?php
/**
 * Home: latest properties (masonry grid, carousel on mobile).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_properties = new WP_Query(
	array(
		'post_type'           => 'thara_property',
		'posts_per_page'      => max( 1, absint( thara_option( 'properties_count' ) ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$thara_items = array();
if ( $thara_properties->have_posts() ) {
	while ( $thara_properties->have_posts() ) {
		$thara_properties->the_post();
		$thara_items[] = array(
			'title' => get_the_title(),
			'url'   => get_permalink(),
			'image' => has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'thara-property' ) : thara_asset( 'images/project1.jpg' ),
		);
	}
	wp_reset_postdata();
} elseif ( thara_option( 'demo_content' ) ) {
	foreach ( array( 1, 2, 3, 4, 7, 6, 5, 8 ) as $thara_n ) {
		$thara_items[] = array(
			'title' => __( 'Sample property', 'thara' ),
			'url'   => '',
			'image' => thara_asset( 'images/project' . $thara_n . '.jpg' ),
		);
	}
}

if ( ! $thara_items ) {
	return;
}

$thara_archive = get_post_type_archive_link( 'thara_property' );
?>
<section class="projects" id="properties">
	<div class="container">
		<div class="head">
			<h2 class="section-title"><?php echo esc_html( thara_option( 'properties_title' ) ); ?></h2>
			<?php if ( $thara_archive && thara_option( 'properties_link_text' ) ) : ?>
				<a href="<?php echo esc_url( $thara_archive ); ?>"><?php echo esc_html( thara_option( 'properties_link_text' ) ); ?></a>
			<?php endif; ?>
		</div>
		<div class="projectss">
			<?php foreach ( $thara_items as $thara_item ) : ?>
				<div class="project">
					<img class="img-responsive" src="<?php echo esc_url( $thara_item['image'] ); ?>" alt="<?php echo esc_attr( $thara_item['title'] ); ?>" loading="lazy">
					<?php if ( $thara_item['url'] ) : ?>
						<div class="overlay">
							<a href="<?php echo esc_url( $thara_item['url'] ); ?>"><span><?php esc_html_e( 'View more', 'thara' ); ?></span><span class="screen-reader-text"> - <?php echo esc_html( $thara_item['title'] ); ?></span></a>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
