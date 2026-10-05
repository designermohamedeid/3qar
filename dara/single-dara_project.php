<?php
/**
 * Single developer project.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$dara_id           = get_the_ID();
	$dara_status       = dara_option_label( 'dara_project', '_dara_status', get_post_meta( $dara_id, '_dara_status', true ) );
	$dara_progress     = min( 100, (int) get_post_meta( $dara_id, '_dara_progress', true ) );
	$dara_from         = get_post_meta( $dara_id, '_dara_price_from', true );
	$dara_units_n      = get_post_meta( $dara_id, '_dara_units_count', true );
	$dara_delivery     = get_post_meta( $dara_id, '_dara_delivery', true );
	$dara_dev          = get_post_meta( $dara_id, '_dara_developer', true );
	$dara_brochure     = get_post_meta( $dara_id, '_dara_brochure', true );
	$dara_amen         = dara_lines( $dara_id, '_dara_amenities', 1 );
	$dara_units        = dara_lines( $dara_id, '_dara_units', 6 );
	$dara_payment      = dara_lines( $dara_id, '_dara_payment', 3 );
	$dara_phases       = dara_lines( $dara_id, '_dara_phases', 3 );
	$dara_gallery      = dara_gallery_ids( $dara_id );
	$dara_lat          = get_post_meta( $dara_id, '_dara_lat', true );
	$dara_lng          = get_post_meta( $dara_id, '_dara_lng', true );
	$dara_cities       = get_the_terms( $dara_id, 'property_city' );
	$dara_loc          = $dara_cities && ! is_wp_error( $dara_cities ) ? implode( '، ', wp_list_pluck( array_reverse( $dara_cities ), 'name' ) ) : get_post_meta( $dara_id, '_dara_address', true );
	$dara_states       = array(
		'available' => array( __( 'Available', 'dara' ), 'available' ),
		'reserved'  => array( __( 'Reserved', 'dara' ), 'reserved' ),
		'sold'      => array( __( 'Sold', 'dara' ), 'sold' ),
	);
	$dara_phase_states = array(
		'done'     => __( 'Completed', 'dara' ),
		'current'  => __( 'In progress', 'dara' ),
		'upcoming' => __( 'Upcoming', 'dara' ),
	);
	?>
	<section class="project-hero">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail(
				'dara-hero',
				array(
					'class'         => 'hero__bg',
					'alt'           => '',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '100vw',
				)
			);
		}
		?>
		<div class="container project-hero__inner">
			<?php dara_breadcrumbs(); ?>
			<div class="badges">
				<?php if ( $dara_status ) : ?>
					<span class="badge badge--light"><?php echo esc_html( $dara_status ); ?></span>
				<?php endif; ?>
				<?php if ( get_post_meta( $dara_id, '_dara_wafi', true ) ) : ?>
					<span class="badge badge--glass"><?php dara_the_icon( 'shield', 14 ); ?><?php esc_html_e( 'Licensed off-plan sales', 'dara' ); ?></span>
				<?php endif; ?>
			</div>
			<h1 class="project-hero__title"><?php the_title(); ?></h1>
			<p class="project-hero__sub">
				<?php
				echo esc_html( $dara_loc );
				if ( $dara_dev ) {
					echo ' · ' . esc_html( sprintf( /* translators: %s: developer. */ __( 'Developed by %s', 'dara' ), $dara_dev ) );
				}
				?>
			</p>
			<div class="project-hero__actions">
				<a class="btn btn--primary" href="#lead-form"><?php esc_html_e( 'Register your interest', 'dara' ); ?></a>
				<?php if ( $dara_brochure ) : ?>
					<a class="btn btn--ghost-light" href="<?php echo esc_url( $dara_brochure ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'download', 20 ); ?><?php esc_html_e( 'Download brochure', 'dara' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="container">
		<div class="key-facts">
			<?php if ( '' !== $dara_from ) : ?>
				<div><span><?php esc_html_e( 'Prices from', 'dara' ); ?></span><strong class="is-primary"><?php echo esc_html( dara_number( $dara_from ) . ' ' . dara_setting( 'currency' ) ); ?></strong></div>
			<?php endif; ?>
			<?php if ( $dara_units_n ) : ?>
				<div><span><?php esc_html_e( 'Units', 'dara' ); ?></span><strong><?php echo esc_html( number_format_i18n( (int) $dara_units_n ) ); ?></strong></div>
			<?php endif; ?>
			<?php if ( $dara_delivery ) : ?>
				<div><span><?php esc_html_e( 'Delivery', 'dara' ); ?></span><strong><?php echo esc_html( $dara_delivery ); ?></strong></div>
			<?php endif; ?>
			<?php if ( $dara_progress ) : ?>
				<div><span><?php esc_html_e( 'Completion', 'dara' ); ?></span><div class="key-facts__progress"><strong><?php echo esc_html( number_format_i18n( $dara_progress ) ); ?>%</strong><span class="progress__track" role="progressbar" aria-valuenow="<?php echo (int) $dara_progress; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php esc_attr_e( 'Completion', 'dara' ); ?>"><span style="width:<?php echo (int) $dara_progress; ?>%"></span></span></div></div>
			<?php endif; ?>
		</div>

		<nav class="in-page-nav" aria-label="<?php esc_attr_e( 'Project sections', 'dara' ); ?>">
			<a href="#overview"><?php esc_html_e( 'Overview', 'dara' ); ?></a>
			<?php
			if ( $dara_units ) :
				?>
				<a href="#units"><?php esc_html_e( 'Units', 'dara' ); ?></a><?php endif; ?>
			<?php
			if ( $dara_payment ) :
				?>
				<a href="#payment"><?php esc_html_e( 'Payment plan', 'dara' ); ?></a><?php endif; ?>
			<?php
			if ( $dara_phases ) :
				?>
				<a href="#phases"><?php esc_html_e( 'Progress', 'dara' ); ?></a><?php endif; ?>
			<?php
			if ( is_numeric( $dara_lat ) ) :
				?>
				<a href="#location"><?php esc_html_e( 'Location', 'dara' ); ?></a><?php endif; ?>
		</nav>

		<section class="pj-section pj-overview" id="overview">
			<div class="pj-overview__text">
				<h2><?php esc_html_e( 'About the project', 'dara' ); ?></h2>
				<div class="entry-content"><?php the_content(); ?></div>
				<?php if ( $dara_amen ) : ?>
					<ul class="dots">
						<?php foreach ( $dara_amen as $dara_a ) : ?>
							<li><?php echo esc_html( $dara_a[0] ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php if ( count( $dara_gallery ) > 1 ) : ?>
				<div class="pj-overview__media" data-gallery>
					<?php foreach ( array_slice( $dara_gallery, 1, 3 ) as $dara_n => $dara_att ) : ?>
						<a class="gallery__item" href="<?php echo esc_url( wp_get_attachment_image_url( $dara_att, 'large' ) ); ?>" data-index="<?php echo (int) ( $dara_n + 1 ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: photo number, 2: total photos. */ __( 'Open photo %1$s of %2$s', 'dara' ), number_format_i18n( $dara_n + 2 ), number_format_i18n( count( $dara_gallery ) ) ) ); ?>"><?php echo dara_img( $dara_att, 0 === $dara_n ? 'dara-wide' : 'dara-card', array( 'sizes' => '(max-width: 900px) 100vw, 560px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endforeach; ?>
				</div>
				<?php
				$dara_lightbox = array();
				foreach ( $dara_gallery as $dara_att ) {
					$dara_src = wp_get_attachment_image_src( $dara_att, 'large' );
					if ( $dara_src ) {
						$dara_lightbox[] = array(
							'src' => $dara_src[0],
							'alt' => '',
						);
					}
				}
				?>
				<script type="application/json" id="gallery-data"><?php echo wp_json_encode( $dara_lightbox, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ); ?></script>
			<?php endif; ?>
		</section>

		<?php if ( $dara_units ) : ?>
			<section class="pj-section" id="units">
				<div class="section-row">
					<h2><?php esc_html_e( 'Available units', 'dara' ); ?></h2>
					<ul class="legend">
						<?php foreach ( $dara_states as $dara_st ) : ?>
							<li><span class="dot dot--<?php echo esc_attr( $dara_st[1] ); ?>"></span><?php echo esc_html( $dara_st[0] ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="table-wrap">
					<table class="units">
						<thead><tr>
							<th scope="col"><?php esc_html_e( 'Model', 'dara' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Type', 'dara' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Area', 'dara' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Bedrooms', 'dara' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Price', 'dara' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'dara' ); ?></th>
						</tr></thead>
						<tbody>
							<?php foreach ( $dara_units as $dara_u ) : ?>
								<?php $dara_state = isset( $dara_states[ strtolower( $dara_u[5] ) ] ) ? $dara_states[ strtolower( $dara_u[5] ) ] : $dara_states['available']; ?>
								<tr>
									<th scope="row"><?php echo esc_html( $dara_u[0] ); ?></th>
									<td><?php echo esc_html( $dara_u[1] ); ?></td>
									<td><?php echo esc_html( is_numeric( $dara_u[2] ) ? dara_number( $dara_u[2] ) . ' ' . __( 'm²', 'dara' ) : $dara_u[2] ); ?></td>
									<td><?php echo esc_html( $dara_u[3] ); ?></td>
									<td class="units__price"><?php echo esc_html( is_numeric( str_replace( ',', '', $dara_u[4] ) ) ? dara_number( str_replace( ',', '', $dara_u[4] ) ) . ' ' . dara_setting( 'currency' ) : $dara_u[4] ); ?></td>
									<td><span class="status status--<?php echo esc_attr( $dara_state[1] ); ?>"><?php echo esc_html( $dara_state[0] ); ?></span></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $dara_payment ) : ?>
			<section class="pj-section" id="payment">
				<h2><?php esc_html_e( 'Payment plan', 'dara' ); ?></h2>
				<ol class="plan">
					<?php foreach ( $dara_payment as $dara_n => $dara_p ) : ?>
						<li class="<?php echo 0 === $dara_n ? 'is-first' : ''; ?>"><strong><?php echo esc_html( $dara_p[0] ); ?></strong><span><?php echo esc_html( $dara_p[1] ); ?></span><small><?php echo esc_html( $dara_p[2] ); ?></small></li>
					<?php endforeach; ?>
				</ol>
			</section>
		<?php endif; ?>

		<?php if ( $dara_phases ) : ?>
			<section class="pj-section" id="phases">
				<h2><?php esc_html_e( 'Construction progress', 'dara' ); ?></h2>
				<ol class="timeline">
					<?php foreach ( $dara_phases as $dara_ph ) : ?>
						<?php $dara_ps = isset( $dara_phase_states[ $dara_ph[2] ] ) ? $dara_ph[2] : 'upcoming'; ?>
						<li class="timeline__item timeline__item--<?php echo esc_attr( $dara_ps ); ?>">
							<span class="timeline__bar"></span>
							<small><?php echo esc_html( $dara_ph[0] ); ?></small>
							<strong><?php echo esc_html( $dara_ph[1] ); ?></strong>
							<em><?php echo esc_html( $dara_phase_states[ $dara_ps ] ); ?></em>
						</li>
					<?php endforeach; ?>
				</ol>
			</section>
		<?php endif; ?>

		<?php if ( is_numeric( $dara_lat ) && is_numeric( $dara_lng ) ) : ?>
			<section class="pj-section" id="location">
				<h2><?php esc_html_e( 'Location', 'dara' ); ?></h2>
				<div class="map" role="region" aria-label="<?php esc_attr_e( 'Map', 'dara' ); ?>" data-map="single" data-lat="<?php echo esc_attr( $dara_lat ); ?>" data-lng="<?php echo esc_attr( $dara_lng ); ?>"></div>
			</section>
		<?php endif; ?>

		<section class="register" id="lead-form">
			<div class="register__text">
				<h2><?php esc_html_e( 'Register your interest', 'dara' ); ?></h2>
				<p><?php esc_html_e( 'Get updated prices, available units and booking priority before launch.', 'dara' ); ?></p>
			</div>
			<form class="form form--dark register__form" method="post" action="<?php echo esc_url( dara_lead_action() ); ?>">
				<?php echo dara_lead_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php dara_lead_hidden_fields( 'project', $dara_id ); ?>
				<label class="form__field"><span><?php esc_html_e( 'Name', 'dara' ); ?></span><input type="text" name="lead_name" required autocomplete="name"></label>
				<label class="form__field"><span><?php esc_html_e( 'Mobile number', 'dara' ); ?></span><input type="tel" name="lead_phone" required dir="ltr" inputmode="tel" autocomplete="tel"></label>
				<?php
				$dara_unit_types = array_unique( array_filter( wp_list_pluck( $dara_units, 1 ) ) );
				if ( $dara_unit_types ) :
					?>
					<label class="form__field form__field--full"><span><?php esc_html_e( 'Unit type', 'dara' ); ?></span>
						<select name="lead_unit">
							<?php foreach ( $dara_unit_types as $dara_ut ) : ?>
								<option><?php echo esc_html( $dara_ut ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
				<button type="submit" class="btn btn--primary btn--block form__field--full"><?php esc_html_e( 'Send', 'dara' ); ?></button>
			</form>
		</section>
	</div>
	<?php
endwhile;

get_footer();
