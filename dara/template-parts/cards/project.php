<?php
/**
 * Project card (dark band on the home page, light on the archive).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_id       = get_the_ID();
$dara_progress = min( 100, (int) get_post_meta( $dara_id, '_dara_progress', true ) );
$dara_status   = dara_option_label( 'dara_project', '_dara_status', get_post_meta( $dara_id, '_dara_status', true ) );
$dara_from     = get_post_meta( $dara_id, '_dara_price_from', true );
$dara_cities   = get_the_terms( $dara_id, 'property_city' );
$dara_loc      = $dara_cities && ! is_wp_error( $dara_cities ) ? implode( '، ', wp_list_pluck( array_reverse( $dara_cities ), 'name' ) ) : get_post_meta( $dara_id, '_dara_address', true );
$dara_facts    = array_filter(
	array(
		get_post_meta( $dara_id, '_dara_units_count', true ) ? sprintf( /* translators: %s: number of units. */ _n( '%s unit', '%s units', (int) get_post_meta( $dara_id, '_dara_units_count', true ), 'dara' ), number_format_i18n( (int) get_post_meta( $dara_id, '_dara_units_count', true ) ) ) : '',
		get_post_meta( $dara_id, '_dara_unit_types', true ),
		get_post_meta( $dara_id, '_dara_delivery', true ) ? sprintf( /* translators: %s: date. */ __( 'Delivery: %s', 'dara' ), get_post_meta( $dara_id, '_dara_delivery', true ) ) : '',
	)
);
?>
<article class="card card--project">
	<a class="card--project__link" href="<?php the_permalink(); ?>">
		<div class="card__media card__media--wide">
			<?php echo dara_img( get_post_thumbnail_id(), 'dara-wide', array( 'sizes' => '(max-width: 900px) 100vw, 600px', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $dara_status ) : ?>
				<span class="badge badge--light card__status"><?php echo esc_html( $dara_status ); ?></span>
			<?php endif; ?>
		</div>
		<div class="card__body">
			<div class="card--project__top">
				<div>
					<h3 class="card__title"><?php the_title(); ?></h3>
					<?php if ( $dara_loc ) : ?>
						<p class="card__loc"><?php echo esc_html( $dara_loc ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $dara_from ) : ?>
					<p class="card--project__from"><span><?php esc_html_e( 'Prices from', 'dara' ); ?></span><strong><?php echo esc_html( dara_number( $dara_from ) . ' ' . dara_setting( 'currency' ) ); ?></strong></p>
				<?php endif; ?>
			</div>
			<?php if ( $dara_progress ) : ?>
				<div class="progress">
					<div class="progress__label"><span><?php esc_html_e( 'Completion', 'dara' ); ?></span><strong><?php echo esc_html( number_format_i18n( $dara_progress ) ); ?>%</strong></div>
					<div class="progress__track" role="progressbar" aria-valuenow="<?php echo (int) $dara_progress; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php esc_attr_e( 'Completion', 'dara' ); ?>"><span style="width:<?php echo (int) $dara_progress; ?>%"></span></div>
				</div>
			<?php endif; ?>
			<?php if ( $dara_facts ) : ?>
				<ul class="card--project__facts">
					<?php foreach ( $dara_facts as $dara_fact ) : ?>
						<li><?php echo esc_html( $dara_fact ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</a>
</article>
