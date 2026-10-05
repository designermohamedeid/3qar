<?php
/**
 * Property card. Optional $args: 'lazy' (bool), 'heading' (h2|h3).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_id      = get_the_ID();
$dara_price   = dara_price_parts( $dara_id );
$dara_purpose = get_post_meta( $dara_id, '_dara_purpose', true );
$dara_cities  = get_the_terms( $dara_id, 'property_city' );
$dara_loc     = $dara_cities && ! is_wp_error( $dara_cities ) ? implode( '، ', wp_list_pluck( array_reverse( $dara_cities ), 'name' ) ) : get_post_meta( $dara_id, '_dara_address', true );
$dara_photos  = count( dara_gallery_ids( $dara_id ) );
$dara_heading = isset( $args['heading'] ) ? $args['heading'] : 'h3';
$dara_lazy    = ! isset( $args['lazy'] ) || $args['lazy'];
$dara_meta    = array(
	'bed'  => get_post_meta( $dara_id, '_dara_beds', true ),
	'bath' => get_post_meta( $dara_id, '_dara_baths', true ),
	'area' => get_post_meta( $dara_id, '_dara_area', true ),
);
$dara_labels  = array(
	'bed'  => __( 'Bedrooms', 'dara' ),
	'bath' => __( 'Bathrooms', 'dara' ),
	'area' => __( 'Area', 'dara' ),
);
?>
<article class="card card--property" data-id="<?php echo (int) $dara_id; ?>">
	<div class="card__media">
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo dara_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
				get_post_thumbnail_id(),
				'dara-card',
				array(
					'loading' => $dara_lazy ? 'lazy' : 'eager',
					'sizes'   => '(max-width: 640px) 100vw, (max-width: 1100px) 50vw, 400px',
					'alt'     => '',
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput 
			?>
		</a>
		<div class="card__badges">
			<span class="badge badge--<?php echo 'rent' === $dara_purpose ? 'dark' : 'primary'; ?>"><?php echo esc_html( dara_purpose_label( $dara_purpose ) ); ?></span>
			<?php if ( get_post_meta( $dara_id, '_dara_featured', true ) ) : ?>
				<span class="badge badge--highlight"><?php esc_html_e( 'Featured', 'dara' ); ?></span>
			<?php endif; ?>
		</div>
		<button type="button" class="fav-btn" data-fav="<?php echo (int) $dara_id; ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: property. */ __( 'Save %s to favorites', 'dara' ), get_the_title() ) ); ?>"><?php dara_the_icon( 'heart', 20 ); ?></button>
		<?php dara_compare_button( $dara_id ); ?>
		<?php if ( $dara_photos > 1 ) : ?>
			<span class="card__count"><?php dara_the_icon( 'camera', 14 ); ?><?php echo esc_html( number_format_i18n( $dara_photos ) ); ?></span>
		<?php endif; ?>
	</div>
	<div class="card__body">
		<p class="price"><span class="price__amount"><?php echo esc_html( $dara_price['amount'] ); ?></span> <span class="price__unit"><?php echo esc_html( $dara_price['unit'] ); ?></span></p>
		<<?php echo esc_html( $dara_heading ); ?> class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_html( $dara_heading ); ?>>
		<?php if ( $dara_loc ) : ?>
			<p class="card__loc"><?php dara_the_icon( 'pin', 16 ); ?><?php echo esc_html( $dara_loc ); ?></p>
		<?php endif; ?>
		<?php if ( array_filter( $dara_meta, 'strlen' ) ) : ?>
			<ul class="card__meta">
				<?php foreach ( $dara_meta as $dara_icon => $dara_value ) : ?>
					<?php if ( '' !== $dara_value ) : ?>
						<li><?php dara_the_icon( $dara_icon, 18 ); ?><span class="screen-reader-text"><?php echo esc_html( $dara_labels[ $dara_icon ] ); ?>: </span><?php echo esc_html( dara_number( $dara_value ) . ( 'area' === $dara_icon ? ' ' . __( 'm²', 'dara' ) : '' ) ); ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</article>
