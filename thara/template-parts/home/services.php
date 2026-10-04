<?php
/**
 * Home: services (from the "Services" post type, with demo fallback).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_services = new WP_Query(
	array(
		'post_type'           => 'thara_service',
		'posts_per_page'      => max( 1, absint( thara_option( 'services_count' ) ) ),
		'orderby'             => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$thara_items = array();
if ( $thara_services->have_posts() ) {
	while ( $thara_services->have_posts() ) {
		$thara_services->the_post();
		$thara_items[] = array(
			'title' => get_the_title(),
			'url'   => get_permalink(),
			'icon'  => has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'thumbnail' ) : '',
		);
	}
	wp_reset_postdata();
} elseif ( thara_option( 'demo_content' ) ) {
	// Demo content shown until the first service is published.
	$thara_demo = array(
		__( 'Financing solutions', 'thara' ),
		__( 'Property management', 'thara' ),
		__( 'Real estate marketing', 'thara' ),
		__( 'Real estate consulting', 'thara' ),
	);
	foreach ( $thara_demo as $thara_index => $thara_title ) {
		$thara_items[] = array(
			'title' => $thara_title,
			'url'   => '',
			'icon'  => thara_asset( 'images/feat' . ( $thara_index + 1 ) . '.png' ),
		);
	}
}

if ( ! $thara_items ) {
	return;
}

$thara_col = count( $thara_items ) >= 4 ? 'col-sm-3' : ( 3 === count( $thara_items ) ? 'col-sm-4' : 'col-sm-6' );
?>
<section class="service-section" id="services">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Our services', 'thara' ); ?></h2>
	<div class="container">
		<div class="row">
			<?php foreach ( $thara_items as $thara_item ) : ?>
				<div class="<?php echo esc_attr( $thara_col ); ?>">
					<?php echo $thara_item['url'] ? '<a class="card-box" href="' . esc_url( $thara_item['url'] ) . '">' : '<div class="card-box">'; ?>
						<?php if ( $thara_item['icon'] ) : ?>
							<div class="icon-box"><img src="<?php echo esc_url( $thara_item['icon'] ); ?>" alt="" aria-hidden="true" loading="lazy"></div>
						<?php endif; ?>
						<h3><?php echo esc_html( $thara_item['title'] ); ?></h3>
					<?php echo $thara_item['url'] ? '</a>' : '</div>'; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
