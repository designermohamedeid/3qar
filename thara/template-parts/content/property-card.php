<?php
/**
 * Property card.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_details = thara_property_details();
$thara_status  = get_post_meta( get_the_ID(), '_thara_status', true );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'property-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="property-card__thumb">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'thara-property', array( 'class' => 'img-responsive', 'loading' => 'lazy' ) );
		} else {
			printf( '<img class="img-responsive" src="%s" alt="" loading="lazy">', esc_url( thara_asset( 'images/project1.jpg' ) ) );
		}
		if ( isset( $thara_details['_thara_status'] ) ) {
			printf( '<span class="property-badge property-badge--%1$s">%2$s</span>', esc_attr( $thara_status ), esc_html( $thara_details['_thara_status']['value'] ) );
		}
		?>
	</a>
	<div class="property-card__body">
		<h2 class="property-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php if ( isset( $thara_details['_thara_location'] ) ) : ?>
			<p class="property-card__location"><span class="lnr lnr-map-marker" aria-hidden="true"></span><?php echo esc_html( $thara_details['_thara_location']['value'] ); ?></p>
		<?php endif; ?>
		<ul class="res property-card__meta">
			<?php foreach ( array( '_thara_area', '_thara_bedrooms', '_thara_bathrooms' ) as $thara_key ) : ?>
				<?php if ( isset( $thara_details[ $thara_key ] ) ) : ?>
					<li title="<?php echo esc_attr( $thara_details[ $thara_key ]['label'] ); ?>"><span class="<?php echo esc_attr( $thara_details[ $thara_key ]['icon'] ); ?>" aria-hidden="true"></span><span class="screen-reader-text"><?php echo esc_html( $thara_details[ $thara_key ]['label'] ); ?>: </span><?php echo esc_html( $thara_details[ $thara_key ]['value'] ); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php if ( isset( $thara_details['_thara_price'] ) ) : ?>
			<p class="property-card__price"><?php echo esc_html( $thara_details['_thara_price']['value'] ); ?></p>
		<?php endif; ?>
	</div>
</article>
