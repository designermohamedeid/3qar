<?php
/**
 * Compare page body (called from the theme's "Compare properties" template).
 *
 * Rendered from ?ids=1,2,3 so the comparison link can be shared.
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view.
$dara_ids   = isset( $_GET['ids'] ) ? array_slice( array_unique( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) ) ) ), 0, 4 ) : array();
$dara_props = array();
if ( $dara_ids && dara_has_core() ) {
	$dara_props = get_posts(
		array(
			'post_type'      => 'dara_property',
			'post__in'       => $dara_ids,
			'orderby'        => 'post__in',
			'posts_per_page' => 4,
		)
	);
}
?>
<div class="container section section--top-0 compare" data-compare-page data-ids="<?php echo esc_attr( implode( ',', wp_list_pluck( $dara_props, 'ID' ) ) ); ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		if ( '' !== trim( get_the_content() ) ) {
			echo '<div class="entry-content compare__intro">';
			the_content();
			echo '</div>';
		}
	endwhile;

	if ( count( $dara_props ) < 1 ) :
		?>
		<div class="empty compare__empty">
			<?php dara_the_icon( 'compare', 40 ); ?>
			<h2><?php esc_html_e( 'No properties to compare yet', 'dara-pro' ); ?></h2>
			<p><?php esc_html_e( 'Tap the compare button on up to 4 properties, then come back here.', 'dara-pro' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( dara_listings_url() ); ?>"><?php esc_html_e( 'Browse properties', 'dara-pro' ); ?></a>
		</div>
		<?php
	else :
		// Collect values.
		$dara_rows         = array();
		$dara_data         = array();
		$dara_all_features = array();
		foreach ( $dara_props as $dara_p ) {
			$dara_id               = $dara_p->ID;
			$dara_price            = (float) get_post_meta( $dara_id, '_dara_price', true );
			$dara_area             = (float) get_post_meta( $dara_id, '_dara_area', true );
			$dara_types            = get_the_terms( $dara_id, 'property_type' );
			$dara_city             = get_the_terms( $dara_id, 'property_city' );
			$dara_feats            = get_the_terms( $dara_id, 'property_feature' );
			$dara_feats            = $dara_feats && ! is_wp_error( $dara_feats ) ? wp_list_pluck( $dara_feats, 'name', 'term_id' ) : array();
			$dara_all_features    += $dara_feats;
			$dara_agent            = dara_get_agent( $dara_id );
			$dara_data[ $dara_id ] = array(
				'price'     => $dara_price,
				'purpose'   => get_post_meta( $dara_id, '_dara_purpose', true ),
				'ppm'       => $dara_price > 0 && $dara_area > 0 && 'rent' !== get_post_meta( $dara_id, '_dara_purpose', true ) ? $dara_price / $dara_area : 0,
				'area'      => $dara_area,
				'beds'      => (float) get_post_meta( $dara_id, '_dara_beds', true ),
				'baths'     => (float) get_post_meta( $dara_id, '_dara_baths', true ),
				'type'      => $dara_types && ! is_wp_error( $dara_types ) ? $dara_types[0]->name : '',
				'location'  => $dara_city && ! is_wp_error( $dara_city ) ? implode( '، ', wp_list_pluck( array_reverse( $dara_city ), 'name' ) ) : get_post_meta( $dara_id, '_dara_address', true ),
				'age'       => get_post_meta( $dara_id, '_dara_age', true ),
				'facing'    => dara_option_label( 'dara_property', '_dara_facing', get_post_meta( $dara_id, '_dara_facing', true ) ),
				'condition' => dara_option_label( 'dara_property', '_dara_condition', get_post_meta( $dara_id, '_dara_condition', true ) ),
				'features'  => $dara_feats,
				'agent'     => $dara_agent,
			);
		}
		asort( $dara_all_features );

		// Best value per numeric row (only among comparable, non-empty values).
		$dara_best         = function ( $key, $mode ) use ( $dara_data ) {
			$vals = array_filter(
				wp_list_pluck( $dara_data, $key ),
				function ( $v ) {
					return $v > 0;
				}
			);
			if ( count( $vals ) < 2 || count( array_unique( $vals ) ) < 2 ) {
				return null;
			}
			return 'min' === $mode ? min( $vals ) : max( $vals );
		};
		$dara_same_purpose = 1 === count( array_unique( wp_list_pluck( $dara_data, 'purpose' ) ) );
		$dara_best_vals    = array(
			'price' => $dara_same_purpose ? $dara_best( 'price', 'min' ) : null,
			'ppm'   => $dara_best( 'ppm', 'min' ),
			'area'  => $dara_best( 'area', 'max' ),
			'beds'  => $dara_best( 'beds', 'max' ),
			'baths' => $dara_best( 'baths', 'max' ),
		);
		$dara_cur          = dara_setting( 'currency' );
		$dara_cell         = function ( $id, $key, $text ) use ( $dara_data, $dara_best_vals ) {
			$best = isset( $dara_best_vals[ $key ] ) && null !== $dara_best_vals[ $key ] && (float) $dara_data[ $id ][ $key ] === (float) $dara_best_vals[ $key ];
			printf(
				'<td class="%1$s">%2$s%3$s</td>',
				$best ? 'is-best' : '',
				'' === (string) $text ? '<span class="compare__na">—</span>' : esc_html( $text ),
				$best ? ' <span class="compare__best">' . esc_html__( 'Best', 'dara-pro' ) . '</span>' : ''
			);
		};
		$dara_wa_lines     = array();
		?>
		<div class="compare__bar">
			<p><?php echo esc_html( sprintf( /* translators: %s: number of properties. */ _n( 'Comparing %s property', 'Comparing %s properties', count( $dara_props ), 'dara-pro' ), number_format_i18n( count( $dara_props ) ) ) ); ?></p>
			<div class="compare__tools">
				<button type="button" class="btn btn--outline btn--sm" data-share data-title="<?php echo esc_attr( get_the_title() ); ?>"><?php dara_the_icon( 'share', 18 ); ?><?php esc_html_e( 'Share comparison', 'dara-pro' ); ?></button>
				<button type="button" class="btn btn--outline btn--sm" data-print><?php dara_the_icon( 'print', 18 ); ?><?php esc_html_e( 'Print', 'dara-pro' ); ?></button>
			</div>
		</div>

		<div class="table-wrap compare__wrap">
			<table class="compare__table">
				<caption class="screen-reader-text"><?php esc_html_e( 'Property comparison', 'dara-pro' ); ?></caption>
				<thead>
					<tr>
						<td class="compare__corner"></td>
						<?php foreach ( $dara_props as $dara_p ) : ?>
							<?php $dara_wa_lines[] = '• ' . get_the_title( $dara_p ) . ' — ' . get_permalink( $dara_p ); ?>
							<th scope="col" class="compare__prop">
								<a class="compare__img" href="<?php echo esc_url( get_permalink( $dara_p ) ); ?>" tabindex="-1" aria-hidden="true">
								<?php
								echo dara_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() markup.
									get_post_thumbnail_id( $dara_p ),
									'dara-card',
									array(
										'alt'   => '',
										'sizes' => '260px',
									)
								); // phpcs:ignore WordPress.Security.EscapeOutput 
								?>
																</a>
								<a class="compare__name" href="<?php echo esc_url( get_permalink( $dara_p ) ); ?>"><?php echo esc_html( get_the_title( $dara_p ) ); ?></a>
								<?php $dara_pp = dara_price_parts( $dara_p->ID ); ?>
								<span class="compare__price"><?php echo esc_html( $dara_pp['amount'] ); ?> <small><?php echo esc_html( $dara_pp['unit'] ); ?></small></span>
								<a class="compare__remove" href="<?php echo esc_url( add_query_arg( 'ids', implode( ',', array_diff( wp_list_pluck( $dara_props, 'ID' ), array( $dara_p->ID ) ) ) ) ); ?>" data-remove="<?php echo (int) $dara_p->ID; ?>"><?php dara_the_icon( 'close', 14 ); ?><?php esc_html_e( 'Remove', 'dara-pro' ); ?></a>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr class="compare__group"><th scope="rowgroup" colspan="<?php echo count( $dara_props ) + 1; ?>"><?php esc_html_e( 'Price', 'dara-pro' ); ?></th></tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Price', 'dara-pro' ); ?></th>
						<?php foreach ( $dara_props as $dara_p ) : ?>
							<?php $dara_pp = dara_price_parts( $dara_p->ID ); ?>
							<?php $dara_cell( $dara_p->ID, 'price', trim( $dara_pp['amount'] . ' ' . $dara_pp['unit'] ) ); ?>
						<?php endforeach; ?>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Price per m²', 'dara-pro' ); ?></th>
						<?php foreach ( $dara_props as $dara_p ) : ?>
							<?php $dara_cell( $dara_p->ID, 'ppm', $dara_data[ $dara_p->ID ]['ppm'] ? dara_number( round( $dara_data[ $dara_p->ID ]['ppm'] ) ) . ' ' . $dara_cur : '' ); ?>
						<?php endforeach; ?>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Purpose', 'dara-pro' ); ?></th>
						<?php foreach ( $dara_props as $dara_p ) : ?>
							<?php $dara_cell( $dara_p->ID, 'purpose', dara_purpose_label( $dara_data[ $dara_p->ID ]['purpose'] ) ); ?>
						<?php endforeach; ?>
					</tr>

					<tr class="compare__group"><th scope="rowgroup" colspan="<?php echo count( $dara_props ) + 1; ?>"><?php esc_html_e( 'Details', 'dara-pro' ); ?></th></tr>
					<?php
					$dara_detail_rows = array(
						'type'      => __( 'Type', 'dara-pro' ),
						'location'  => __( 'Location', 'dara-pro' ),
						'area'      => __( 'Area', 'dara-pro' ),
						'beds'      => __( 'Bedrooms', 'dara-pro' ),
						'baths'     => __( 'Bathrooms', 'dara-pro' ),
						'age'       => __( 'Age', 'dara-pro' ),
						'facing'    => __( 'Facing', 'dara-pro' ),
						'condition' => __( 'Condition', 'dara-pro' ),
					);
					foreach ( $dara_detail_rows as $dara_key => $dara_label ) :
						$dara_values = wp_list_pluck( $dara_data, $dara_key );
						if ( ! array_filter( array_map( 'strval', $dara_values ), 'strlen' ) || ! array_filter( $dara_values ) ) {
							continue; // Skip rows with no data at all.
						}
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $dara_label ); ?></th>
							<?php foreach ( $dara_props as $dara_p ) : ?>
								<?php
								$dara_v = $dara_data[ $dara_p->ID ][ $dara_key ];
								if ( 'area' === $dara_key ) {
									$dara_v = $dara_v ? dara_number( $dara_v ) . ' ' . __( 'm²', 'dara-pro' ) : '';
								} elseif ( in_array( $dara_key, array( 'beds', 'baths' ), true ) ) {
									$dara_v = $dara_v ? dara_number( $dara_v ) : '';
								}
								$dara_cell( $dara_p->ID, $dara_key, $dara_v );
								?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>

					<?php if ( $dara_all_features ) : ?>
						<tr class="compare__group"><th scope="rowgroup" colspan="<?php echo count( $dara_props ) + 1; ?>"><?php esc_html_e( 'Features & amenities', 'dara-pro' ); ?></th></tr>
						<?php foreach ( $dara_all_features as $dara_fid => $dara_fname ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $dara_fname ); ?></th>
								<?php foreach ( $dara_props as $dara_p ) : ?>
									<?php if ( isset( $dara_data[ $dara_p->ID ]['features'][ $dara_fid ] ) ) : ?>
										<td><span class="compare__yes"><?php dara_the_icon( 'check', 16 ); ?><span class="screen-reader-text"><?php esc_html_e( 'Yes', 'dara-pro' ); ?></span></span></td>
									<?php else : ?>
										<td><span class="compare__no"><?php dara_the_icon( 'close', 14 ); ?><span class="screen-reader-text"><?php esc_html_e( 'No', 'dara-pro' ); ?></span></span></td>
									<?php endif; ?>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>

					<tr class="compare__group"><th scope="rowgroup" colspan="<?php echo count( $dara_props ) + 1; ?>"><?php esc_html_e( 'Contact', 'dara-pro' ); ?></th></tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Agent', 'dara-pro' ); ?></th>
						<?php foreach ( $dara_props as $dara_p ) : ?>
							<?php $dara_ag = $dara_data[ $dara_p->ID ]['agent']; ?>
							<td>
								<strong class="compare__agent"><?php echo esc_html( $dara_ag['name'] ); ?></strong>
								<span class="compare__actions">
									<?php if ( $dara_ag['phone'] ) : ?>
										<a class="btn btn--primary btn--sm" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dara_ag['phone'] ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: agent. */ __( 'Call %s', 'dara-pro' ), $dara_ag['name'] ) ); ?>"><?php dara_the_icon( 'phone', 16 ); ?></a>
									<?php endif; ?>
									<?php if ( $dara_ag['whatsapp'] ) : ?>
										<a class="btn btn--outline btn--sm" href="<?php echo esc_url( dara_whatsapp_url( $dara_ag['whatsapp'], sprintf( /* translators: 1: property title, 2: URL. */ __( 'Hello, I am interested in: %1$s %2$s', 'dara-pro' ), get_the_title( $dara_p ), get_permalink( $dara_p ) ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'WhatsApp', 'dara-pro' ); ?>"><?php dara_the_icon( 'whatsapp', 16 ); ?></a>
									<?php endif; ?>
									<a class="btn btn--outline btn--sm" href="<?php echo esc_url( get_permalink( $dara_p ) ); ?>#lead-form"><?php esc_html_e( 'Request a viewing', 'dara-pro' ); ?></a>
								</span>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>

		<?php if ( dara_mod( 'whatsapp' ) ) : ?>
			<div class="compare__help">
				<p><?php esc_html_e( 'Not sure which one to choose? Send the comparison to our team and we will help you decide.', 'dara-pro' ); ?></p>
				<a class="btn btn--primary" target="_blank" rel="noopener" href="<?php echo esc_url( dara_whatsapp_url( dara_mod( 'whatsapp' ), __( 'Hello, please help me choose between these properties:', 'dara-pro' ) . "\n" . implode( "\n", $dara_wa_lines ) ) ); ?>"><?php dara_the_icon( 'whatsapp', 18 ); ?><?php esc_html_e( 'Ask an advisor on WhatsApp', 'dara-pro' ); ?></a>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
