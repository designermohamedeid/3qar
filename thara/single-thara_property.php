<?php
/**
 * Single property.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-section single-property">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			$thara_details = thara_property_details();
			$thara_phone   = thara_option( 'phone' );
			$thara_wa      = thara_option( 'social_whatsapp' );
			?>
			<div class="row">
				<div class="col-md-8">
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="entry-thumb"><?php the_post_thumbnail( 'large', array( 'class' => 'img-responsive' ) ); ?></figure>
						<?php endif; ?>
						<div class="entry-content"><?php the_content(); ?></div>
						<?php
						$thara_terms = get_the_term_list( get_the_ID(), 'property_type', '', ' ' );
						if ( $thara_terms && ! is_wp_error( $thara_terms ) ) {
							echo '<div class="entry-footer"><div class="entry-terms"><span class="lnr lnr-tag" aria-hidden="true"></span>' . $thara_terms . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
					</article>
				</div>
				<aside class="col-md-4">
					<div class="property-box">
						<?php if ( isset( $thara_details['_thara_price'] ) ) : ?>
							<p class="property-box__price"><?php echo esc_html( $thara_details['_thara_price']['value'] ); ?></p>
						<?php endif; ?>
						<?php if ( $thara_details ) : ?>
							<ul class="res property-box__list">
								<?php foreach ( $thara_details as $thara_key => $thara_detail ) : ?>
									<?php
									if ( '_thara_price' === $thara_key ) {
										continue;
									}
									?>
									<li><span class="<?php echo esc_attr( $thara_detail['icon'] ); ?>" aria-hidden="true"></span><strong><?php echo esc_html( $thara_detail['label'] ); ?></strong><span><?php echo esc_html( $thara_detail['value'] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( $thara_phone ) : ?>
							<a class="thara-btn thara-btn--block" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $thara_phone ) ); ?>"><span class="lnr lnr-phone-handset" aria-hidden="true"></span> <?php esc_html_e( 'Call us', 'thara' ); ?></a>
						<?php endif; ?>
						<?php if ( $thara_wa ) : ?>
							<a class="thara-btn thara-btn--block thara-btn--outline" href="<?php echo esc_url( $thara_wa ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> <?php esc_html_e( 'Ask on WhatsApp', 'thara' ); ?></a>
						<?php endif; ?>
					</div>
				</aside>
			</div>
			<?php
		endwhile;

		$thara_related = new WP_Query(
			array(
				'post_type'           => 'thara_property',
				'posts_per_page'      => 3,
				'post__not_in'        => array( get_queried_object_id() ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( $thara_related->have_posts() ) :
			?>
			<div class="related-properties">
				<h2 class="section-heading"><?php esc_html_e( 'More properties', 'thara' ); ?></h2>
				<div class="properties-grid">
					<?php
					while ( $thara_related->have_posts() ) :
						$thara_related->the_post();
						get_template_part( 'template-parts/content/property-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
