<?php
/**
 * Properties archive / search results (also used by property type & city archives).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );

global $wp_query;
$dara_f      = dara_get_filters();
$dara_points = array();
?>
<div class="container listing">
	<?php get_template_part( 'template-parts/property/filters' ); ?>

	<div class="listing__results">
		<div class="results-bar">
			<p class="results-bar__count">
				<?php
				printf(
					/* translators: %s: number of properties. */
					esc_html( _n( '%s property found', '%s properties found', (int) $wp_query->found_posts, 'dara' ) ),
					'<strong>' . esc_html( number_format_i18n( (int) $wp_query->found_posts ) ) . '</strong>'
				);
				?>
			</p>
			<div class="results-bar__tools">
				<button type="button" class="btn btn--outline btn--sm filters-toggle" aria-controls="filters" aria-expanded="false"><?php dara_the_icon( 'filter', 18 ); ?><?php esc_html_e( 'Filters', 'dara' ); ?></button>
				<form method="get" class="sort" data-autosubmit>
					<?php
					foreach ( $dara_f as $dara_k => $dara_v ) {
						if ( 'sort' === $dara_k || '' === $dara_v || array() === $dara_v ) {
							continue;
						}
						foreach ( (array) $dara_v as $dara_item ) {
							printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( is_array( $dara_v ) ? $dara_k . '[]' : $dara_k ), esc_attr( $dara_item ) );
						}
					}
					?>
					<label><span><?php esc_html_e( 'Sort by', 'dara' ); ?></span>
						<select name="sort">
							<option value="newest" <?php selected( $dara_f['sort'], 'newest' ); ?>><?php esc_html_e( 'Newest', 'dara' ); ?></option>
							<option value="price_asc" <?php selected( $dara_f['sort'], 'price_asc' ); ?>><?php esc_html_e( 'Price: low to high', 'dara' ); ?></option>
							<option value="price_desc" <?php selected( $dara_f['sort'], 'price_desc' ); ?>><?php esc_html_e( 'Price: high to low', 'dara' ); ?></option>
							<option value="area_desc" <?php selected( $dara_f['sort'], 'area_desc' ); ?>><?php esc_html_e( 'Largest area', 'dara' ); ?></option>
						</select>
					</label>
					<noscript><button type="submit" class="btn btn--sm btn--outline"><?php esc_html_e( 'Apply', 'dara' ); ?></button></noscript>
				</form>
				<div class="view-toggle" role="group" aria-label="<?php esc_attr_e( 'View', 'dara' ); ?>">
					<button type="button" aria-pressed="true" data-view="grid"><?php dara_the_icon( 'grid', 18 ); ?><?php esc_html_e( 'Grid', 'dara' ); ?></button>
					<button type="button" aria-pressed="false" data-view="map"><?php dara_the_icon( 'map', 18 ); ?><?php esc_html_e( 'Map', 'dara' ); ?></button>
				</div>
			</div>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="map map--listing" id="listing-map" hidden data-map="listing"></div>
			<div class="grid grid--cards grid--results">
				<?php
				$dara_i = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/cards/property',
						null,
						array(
							'heading' => 'h2',
							'lazy'    => $dara_i > 2,
						)
					);
					$dara_lat = get_post_meta( get_the_ID(), '_dara_lat', true );
					$dara_lng = get_post_meta( get_the_ID(), '_dara_lng', true );
					if ( is_numeric( $dara_lat ) && is_numeric( $dara_lng ) ) {
						$dara_points[] = array(
							'lat'   => (float) $dara_lat,
							'lng'   => (float) $dara_lng,
							'label' => dara_short_price( get_the_ID() ),
							'title' => get_the_title(),
							'url'   => get_permalink(),
						);
					}
					++$dara_i;
				endwhile;
				?>
			</div>
			<script type="application/json" id="listing-points"><?php echo wp_json_encode( $dara_points, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ); ?></script>
			<?php dara_pagination(); ?>
		<?php else : ?>
			<div class="empty">
				<?php dara_the_icon( 'search', 40 ); ?>
				<h2><?php esc_html_e( 'No properties match your search', 'dara' ); ?></h2>
				<p><?php esc_html_e( 'Try removing some filters or searching another area.', 'dara' ); ?></p>
				<a class="btn btn--primary" href="<?php echo esc_url( dara_listings_url() ); ?>"><?php esc_html_e( 'See all properties', 'dara' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
