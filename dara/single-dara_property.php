<?php
/**
 * Single property.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$dara_id      = get_the_ID();
	$dara_price   = dara_price_parts( $dara_id );
	$dara_purpose = get_post_meta( $dara_id, '_dara_purpose', true );
	$dara_cond    = dara_option_label( 'dara_property', '_dara_condition', get_post_meta( $dara_id, '_dara_condition', true ) );
	$dara_cities  = get_the_terms( $dara_id, 'property_city' );
	$dara_loc     = $dara_cities && ! is_wp_error( $dara_cities ) ? implode( '، ', wp_list_pluck( array_reverse( $dara_cities ), 'name' ) ) : '';
	$dara_address = get_post_meta( $dara_id, '_dara_address', true );
	$dara_gallery = dara_gallery_ids( $dara_id );
	$dara_plans   = dara_gallery_ids( $dara_id, '_dara_floorplans' );
	$dara_feats   = get_the_terms( $dara_id, 'property_feature' );
	$dara_nearby  = dara_lines( $dara_id, '_dara_nearby', 2 );
	$dara_lat     = get_post_meta( $dara_id, '_dara_lat', true );
	$dara_lng     = get_post_meta( $dara_id, '_dara_lng', true );
	$dara_video   = get_post_meta( $dara_id, '_dara_video', true );
	$dara_agent   = dara_get_agent( $dara_id );
	$dara_license = get_post_meta( $dara_id, '_dara_ad_license', true );
	$dara_wa_msg  = sprintf( /* translators: 1: property title, 2: URL. */ __( 'Hello, I am interested in: %1$s %2$s', 'dara' ), get_the_title(), get_permalink() );

	$dara_lightbox = array();
	foreach ( $dara_gallery as $dara_att ) {
		$dara_src = wp_get_attachment_image_src( $dara_att, 'large' );
		if ( $dara_src ) {
			$dara_lightbox[] = array(
				'src' => $dara_src[0],
				'alt' => get_post_meta( $dara_att, '_wp_attachment_image_alt', true ),
			);
		}
	}
	?>
	<div class="container single-property">
		<?php dara_breadcrumbs(); ?>

		<div class="sp-head">
			<div class="sp-head__main">
				<div class="badges">
					<span class="badge badge--<?php echo 'rent' === $dara_purpose ? 'dark' : 'primary'; ?>"><?php echo esc_html( dara_purpose_label( $dara_purpose ) ); ?></span>
					<?php if ( get_post_meta( $dara_id, '_dara_featured', true ) ) : ?>
						<span class="badge badge--highlight"><?php esc_html_e( 'Featured', 'dara' ); ?></span>
					<?php endif; ?>
					<?php if ( $dara_cond ) : ?>
						<span class="badge badge--soft"><?php echo esc_html( $dara_cond ); ?></span>
					<?php endif; ?>
				</div>
				<h1 class="sp-head__title"><?php the_title(); ?></h1>
				<?php if ( $dara_loc || $dara_address ) : ?>
					<p class="sp-head__loc"><?php dara_the_icon( 'pin', 18 ); ?><?php echo esc_html( $dara_address ? $dara_address : $dara_loc ); ?></p>
				<?php endif; ?>
			</div>
			<div class="sp-head__side">
				<p class="price price--lg"><span class="price__amount"><?php echo esc_html( $dara_price['amount'] ); ?></span> <span class="price__unit"><?php echo esc_html( $dara_price['unit'] ); ?></span></p>
				<div class="sp-head__actions">
					<button type="button" class="btn btn--outline btn--sm" data-share data-title="<?php echo esc_attr( get_the_title() ); ?>"><?php dara_the_icon( 'share', 18 ); ?><?php esc_html_e( 'Share', 'dara' ); ?></button>
					<button type="button" class="btn btn--outline btn--sm fav-btn--inline" data-fav="<?php echo (int) $dara_id; ?>" aria-pressed="false"><?php dara_the_icon( 'heart', 18 ); ?><?php esc_html_e( 'Save', 'dara' ); ?></button>
					<button type="button" class="btn btn--outline btn--sm" data-print><?php dara_the_icon( 'print', 18 ); ?><?php esc_html_e( 'Print', 'dara' ); ?></button>
				</div>
			</div>
		</div>

		<?php if ( $dara_gallery ) : ?>
			<div class="gallery gallery--<?php echo esc_attr( min( 5, count( $dara_gallery ) ) ); ?>" data-gallery>
				<?php foreach ( array_slice( $dara_gallery, 0, 5 ) as $dara_n => $dara_att ) : ?>
					<a class="gallery__item" href="<?php echo esc_url( wp_get_attachment_image_url( $dara_att, 'large' ) ); ?>" data-index="<?php echo (int) $dara_n; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: photo number, 2: total photos. */ __( 'Open photo %1$s of %2$s', 'dara' ), number_format_i18n( $dara_n + 1 ), number_format_i18n( count( $dara_gallery ) ) ) ); ?>">
						<?php
						echo wp_get_attachment_image(
							$dara_att,
							0 === $dara_n ? 'dara-wide' : 'dara-card',
							false,
							array(
								'loading'       => 0 === $dara_n ? 'eager' : 'lazy',
								'fetchpriority' => 0 === $dara_n ? 'high' : 'auto',
								'decoding'      => 'async',
								'sizes'         => 0 === $dara_n ? '(max-width: 900px) 100vw, 620px' : '(max-width: 900px) 50vw, 310px',
							)
						);
						?>
						<?php if ( 4 === $dara_n && count( $dara_gallery ) > 5 ) : ?>
							<span class="gallery__more"><?php dara_the_icon( 'image', 20 ); ?><?php echo esc_html( sprintf( /* translators: %s: photo count. */ __( 'All photos (%s)', 'dara' ), number_format_i18n( count( $dara_gallery ) ) ) ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
			<script type="application/json" id="gallery-data"><?php echo wp_json_encode( $dara_lightbox, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ); ?></script>
		<?php endif; ?>

		<div class="sp-layout">
			<div class="sp-main">
				<?php $dara_facts = dara_property_facts( $dara_id ); ?>
				<?php if ( $dara_facts ) : ?>
					<ul class="facts">
						<?php foreach ( $dara_facts as $dara_fact ) : ?>
							<li><span class="facts__icon"><?php dara_the_icon( $dara_fact[2], 20 ); ?></span><span><small><?php echo esc_html( $dara_fact[0] ); ?></small><strong><?php echo esc_html( $dara_fact[1] ); ?></strong></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( '' !== trim( get_the_content() ) ) : ?>
					<section class="sp-section">
						<h2><?php esc_html_e( 'Description', 'dara' ); ?></h2>
						<div class="entry-content"><?php the_content(); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $dara_feats && ! is_wp_error( $dara_feats ) ) : ?>
					<section class="sp-section">
						<h2><?php esc_html_e( 'Features & amenities', 'dara' ); ?></h2>
						<ul class="checklist checklist--grid">
							<?php foreach ( $dara_feats as $dara_feat ) : ?>
								<li><span class="checklist__icon"><?php dara_the_icon( 'check', 14 ); ?></span><?php echo esc_html( $dara_feat->name ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( $dara_plans ) : ?>
					<section class="sp-section">
						<h2><?php esc_html_e( 'Floor plans', 'dara' ); ?></h2>
						<div class="tabs" data-tabs>
							<div class="tabs__list" role="tablist">
								<?php foreach ( $dara_plans as $dara_n => $dara_plan ) : ?>
									<?php $dara_cap = wp_get_attachment_caption( $dara_plan ); ?>
									<button type="button" role="tab" id="plan-tab-<?php echo (int) $dara_n; ?>" aria-controls="plan-<?php echo (int) $dara_n; ?>" aria-selected="<?php echo 0 === $dara_n ? 'true' : 'false'; ?>"><?php echo esc_html( $dara_cap ? $dara_cap : sprintf( /* translators: %s: number. */ __( 'Plan %s', 'dara' ), number_format_i18n( $dara_n + 1 ) ) ); ?></button>
								<?php endforeach; ?>
							</div>
							<?php foreach ( $dara_plans as $dara_n => $dara_plan ) : ?>
								<div class="tabs__panel" role="tabpanel" id="plan-<?php echo (int) $dara_n; ?>" aria-labelledby="plan-tab-<?php echo (int) $dara_n; ?>" <?php echo 0 === $dara_n ? '' : 'hidden'; ?>>
									<a href="<?php echo esc_url( wp_get_attachment_url( $dara_plan ) ); ?>" target="_blank" rel="noopener"><?php echo dara_img( $dara_plan, 'large', array( 'class' => 'plan-img', 'alt' => wp_get_attachment_caption( $dara_plan ) ? wp_get_attachment_caption( $dara_plan ) : get_the_title() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
								</div>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( $dara_video ) : ?>
					<section class="sp-section">
						<h2><?php esc_html_e( 'Video tour', 'dara' ); ?></h2>
						<?php
						$dara_embed = wp_oembed_get( $dara_video );
						if ( $dara_embed ) :
							?>
							<div class="video" data-video>
								<template><?php echo $dara_embed; // phpcs:ignore WordPress.Security.EscapeOutput -- oEmbed from trusted providers. ?></template>
								<button type="button" class="video__play" aria-label="<?php esc_attr_e( 'Play video', 'dara' ); ?>">
									<?php echo dara_img( get_post_thumbnail_id(), 'dara-wide', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<span class="video__icon"><?php dara_the_icon( 'play', 56 ); ?></span>
								</button>
							</div>
						<?php else : ?>
							<a class="btn btn--outline" href="<?php echo esc_url( $dara_video ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'play', 18 ); ?><?php esc_html_e( 'Watch the video', 'dara' ); ?></a>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<?php if ( ( is_numeric( $dara_lat ) && is_numeric( $dara_lng ) ) || $dara_nearby ) : ?>
					<section class="sp-section">
						<h2><?php esc_html_e( 'Location & nearby', 'dara' ); ?></h2>
						<?php if ( is_numeric( $dara_lat ) && is_numeric( $dara_lng ) ) : ?>
							<div class="map" role="region" data-map="single" data-lat="<?php echo esc_attr( $dara_lat ); ?>" data-lng="<?php echo esc_attr( $dara_lng ); ?>" aria-label="<?php esc_attr_e( 'Map', 'dara' ); ?>"></div>
							<a class="link-arrow" href="<?php echo esc_url( 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $dara_lat . ',' . $dara_lng ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'dara' ); ?></a>
						<?php endif; ?>
						<?php if ( $dara_nearby ) : ?>
							<ul class="nearby">
								<?php foreach ( $dara_nearby as $dara_place ) : ?>
									<li><span><?php echo esc_html( $dara_place[0] ); ?></span><span><?php echo esc_html( $dara_place[1] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
				<?php endif; ?>
			</div>

			<aside class="sp-side">
				<div class="agent-box">
					<div class="agent">
						<span class="agent__avatar">
							<?php
							if ( $dara_agent['photo'] ) {
								echo wp_get_attachment_image( $dara_agent['photo'], 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) );
							} else {
								dara_the_icon( 'user', 26 );
							}
							?>
						</span>
						<span class="agent__info">
							<strong><?php echo esc_html( $dara_agent['name'] ); ?></strong>
							<small>
								<?php
								echo esc_html( $dara_agent['role'] );
								if ( $dara_agent['fal'] ) {
									echo ' · ' . esc_html( sprintf( /* translators: %s: license. */ __( 'FAL %s', 'dara' ), $dara_agent['fal'] ) );
								}
								?>
							</small>
						</span>
					</div>
					<div class="agent-box__actions">
						<?php if ( $dara_agent['phone'] ) : ?>
							<a class="btn btn--primary" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $dara_agent['phone'] ) ); ?>"><?php dara_the_icon( 'phone', 18 ); ?><?php esc_html_e( 'Call', 'dara' ); ?></a>
						<?php endif; ?>
						<?php if ( $dara_agent['whatsapp'] ) : ?>
							<a class="btn btn--outline" href="<?php echo esc_url( dara_whatsapp_url( $dara_agent['whatsapp'], $dara_wa_msg ) ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'whatsapp', 18 ); ?><?php esc_html_e( 'WhatsApp', 'dara' ); ?></a>
						<?php endif; ?>
					</div>
					<?php get_template_part( 'template-parts/forms/viewing' ); ?>
				</div>

				<div class="license-box">
					<strong><?php esc_html_e( 'License information', 'dara' ); ?></strong>
					<?php if ( $dara_license ) : ?>
						<p><span><?php esc_html_e( 'Ad license number', 'dara' ); ?></span><span><?php echo esc_html( $dara_license ); ?></span></p>
					<?php endif; ?>
					<?php if ( dara_setting( 'office_fal' ) ) : ?>
						<p><span><?php esc_html_e( 'Office FAL license', 'dara' ); ?></span><span><?php echo esc_html( dara_setting( 'office_fal' ) ); ?></span></p>
					<?php endif; ?>
					<p><span><?php esc_html_e( 'Listed on', 'dara' ); ?></span><span><?php echo esc_html( get_the_date() ); ?></span></p>
				</div>
			</aside>
		</div>

		<?php
		$dara_type_ids = wp_get_post_terms( $dara_id, 'property_type', array( 'fields' => 'ids' ) );
		$dara_similar  = new WP_Query(
			array(
				'post_type'      => 'dara_property',
				'posts_per_page' => 3,
				'post__not_in'   => array( $dara_id ),
				'no_found_rows'  => true,
				'tax_query'      => $dara_type_ids ? array( array( 'taxonomy' => 'property_type', 'terms' => $dara_type_ids ) ) : array(), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( $dara_similar->have_posts() ) :
			?>
			<section class="related">
				<h2 class="section-title"><?php esc_html_e( 'Similar properties', 'dara' ); ?></h2>
				<div class="grid grid--cards">
					<?php
					while ( $dara_similar->have_posts() ) :
						$dara_similar->the_post();
						get_template_part( 'template-parts/cards/property' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
