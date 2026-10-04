<?php
/**
 * Home hero with the property search.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_hero  = (int) dara_part_opt( $args, 'hero_image' );
$dara_types = dara_has_core() ? get_terms( array( 'taxonomy' => 'property_type', 'hide_empty' => false ) ) : array();
$dara_city_terms = dara_has_core() ? get_terms( array( 'taxonomy' => 'property_city', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 40 ) ) : array();
$dara_popular = dara_part_lines( $args, 'hero_popular', 2 );
if ( ! $dara_popular && $dara_city_terms && ! is_wp_error( $dara_city_terms ) ) {
	foreach ( array_slice( $dara_city_terms, 0, 5 ) as $dara_term ) {
		$dara_popular[] = array( $dara_term->name, get_term_link( $dara_term ) );
	}
}
$dara_sale_ranges = array(
	''                => __( 'Any price', 'dara' ),
	'0-1000000'       => __( 'Up to 1 million', 'dara' ),
	'1000000-3000000' => __( '1 – 3 million', 'dara' ),
	'3000000-'        => __( 'Over 3 million', 'dara' ),
);
$dara_rent_ranges = array(
	''             => __( 'Any price', 'dara' ),
	'0-50000'      => __( 'Up to 50,000', 'dara' ),
	'50000-100000' => __( '50,000 – 100,000', 'dara' ),
	'100000-'      => __( 'Over 100,000', 'dara' ),
);
?>
<section class="hero">
	<?php
	if ( $dara_hero ) {
		echo wp_get_attachment_image( $dara_hero, 'dara-hero', false, array( 'class' => 'hero__bg', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '100vw' ) );
	}
	?>
	<div class="container hero__inner">
		<?php if ( dara_part_opt( $args, 'hero_eyebrow' ) ) : ?>
			<span class="hero__eyebrow"><?php echo esc_html( dara_part_opt( $args, 'hero_eyebrow' ) ); ?></span>
		<?php endif; ?>
		<h1 class="hero__title"><?php echo esc_html( dara_part_opt( $args, 'hero_title' ) ); ?></h1>
		<?php if ( dara_part_opt( $args, 'hero_text' ) ) : ?>
			<p class="hero__text"><?php echo esc_html( dara_part_opt( $args, 'hero_text' ) ); ?></p>
		<?php endif; ?>

		<?php if ( dara_has_core() && ( ! isset( $args['show_search'] ) || $args['show_search'] ) ) : ?>
			<div class="search-box" data-search<?php echo ! empty( $args['search_tab'] ) ? ' data-default="' . esc_attr( $args['search_tab'] ) . '"' : ''; ?>>
				<div class="search-box__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Search type', 'dara' ); ?>">
					<button type="button" role="tab" aria-selected="true" data-purpose="sale"><?php esc_html_e( 'Buy', 'dara' ); ?></button>
					<button type="button" role="tab" aria-selected="false" data-purpose="rent"><?php esc_html_e( 'Rent', 'dara' ); ?></button>
					<button type="button" role="tab" aria-selected="false" data-purpose="projects" data-action="<?php echo esc_url( get_post_type_archive_link( 'dara_project' ) ); ?>"><?php esc_html_e( 'New projects', 'dara' ); ?></button>
				</div>
				<form class="search-box__form" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'dara_property' ) ); ?>" data-action="<?php echo esc_url( get_post_type_archive_link( 'dara_property' ) ); ?>">
					<input type="hidden" name="purpose" value="sale">
					<label class="field field--grow">
						<span class="field__label"><?php esc_html_e( 'Location', 'dara' ); ?></span>
						<span class="field__control"><?php dara_the_icon( 'pin', 18 ); ?><input type="text" name="location" list="dara-cities" placeholder="<?php esc_attr_e( 'City or district', 'dara' ); ?>" autocomplete="off"></span>
					</label>
					<?php if ( $dara_city_terms && ! is_wp_error( $dara_city_terms ) ) : ?>
						<datalist id="dara-cities">
							<?php foreach ( $dara_city_terms as $dara_term ) : ?>
								<option value="<?php echo esc_attr( $dara_term->name ); ?>"></option>
							<?php endforeach; ?>
						</datalist>
					<?php endif; ?>
					<label class="field" data-hide-projects>
						<span class="field__label"><?php esc_html_e( 'Property type', 'dara' ); ?></span>
						<select name="type">
							<option value=""><?php esc_html_e( 'All', 'dara' ); ?></option>
							<?php foreach ( (array) $dara_types as $dara_type ) : ?>
								<?php if ( $dara_type instanceof WP_Term ) : ?>
									<option value="<?php echo esc_attr( $dara_type->slug ); ?>"><?php echo esc_html( $dara_type->name ); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field" data-hide-projects data-purpose-only="sale">
						<span class="field__label"><?php esc_html_e( 'Price', 'dara' ); ?></span>
						<select name="price">
							<?php foreach ( $dara_sale_ranges as $dara_v => $dara_l ) : ?>
								<option value="<?php echo esc_attr( $dara_v ); ?>"><?php echo esc_html( $dara_l ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field" data-hide-projects data-purpose-only="rent" hidden>
						<span class="field__label"><?php esc_html_e( 'Yearly rent', 'dara' ); ?></span>
						<select name="price" disabled>
							<?php foreach ( $dara_rent_ranges as $dara_v => $dara_l ) : ?>
								<option value="<?php echo esc_attr( $dara_v ); ?>"><?php echo esc_html( $dara_l ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field field--sm" data-hide-projects>
						<span class="field__label"><?php esc_html_e( 'Bedrooms', 'dara' ); ?></span>
						<select name="beds">
							<option value=""><?php esc_html_e( 'Any', 'dara' ); ?></option>
							<?php for ( $dara_i = 1; $dara_i <= 5; $dara_i++ ) : ?>
								<option value="<?php echo (int) $dara_i; ?>"><?php echo esc_html( number_format_i18n( $dara_i ) ); ?>+</option>
							<?php endfor; ?>
						</select>
					</label>
					<button type="submit" class="btn btn--primary search-box__submit"><?php dara_the_icon( 'search', 20 ); ?><?php esc_html_e( 'Search', 'dara' ); ?></button>
				</form>
			</div>
			<?php if ( $dara_popular ) : ?>
				<div class="hero__popular">
					<span><?php esc_html_e( 'Popular:', 'dara' ); ?></span>
					<?php foreach ( $dara_popular as $dara_p ) : ?>
						<a href="<?php echo esc_url( $dara_p[1] ? $dara_p[1] : dara_listings_url( array( 'location' => $dara_p[0] ) ) ); ?>"><?php echo esc_html( $dara_p[0] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
