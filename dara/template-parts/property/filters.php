<?php
/**
 * Listing filters sidebar (GET form; works without JavaScript).
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_f        = dara_get_filters();
$dara_types    = get_terms( array( 'taxonomy' => 'property_type', 'hide_empty' => false ) );
$dara_features = get_terms( array( 'taxonomy' => 'property_feature', 'hide_empty' => false, 'number' => 12 ) );
$dara_action   = get_post_type_archive_link( 'dara_property' );
if ( is_tax( 'property_type' ) ) {
	$dara_f['type'] = array( get_queried_object()->slug );
}
?>
<aside class="filters" id="filters" aria-label="<?php esc_attr_e( 'Filter results', 'dara' ); ?>">
	<form method="get" action="<?php echo esc_url( $dara_action ); ?>" class="filters__form" data-filters>
		<div class="filters__head">
			<h2><?php esc_html_e( 'Filter results', 'dara' ); ?></h2>
			<a href="<?php echo esc_url( $dara_action ); ?>"><?php esc_html_e( 'Clear all', 'dara' ); ?></a>
		</div>

		<fieldset class="filters__group">
			<legend><?php esc_html_e( 'Purpose', 'dara' ); ?></legend>
			<div class="seg">
				<label><input type="radio" name="purpose" value="" <?php checked( $dara_f['purpose'], '' ); ?>><span><?php esc_html_e( 'All', 'dara' ); ?></span></label>
				<label><input type="radio" name="purpose" value="sale" <?php checked( $dara_f['purpose'], 'sale' ); ?>><span><?php esc_html_e( 'Sale', 'dara' ); ?></span></label>
				<label><input type="radio" name="purpose" value="rent" <?php checked( $dara_f['purpose'], 'rent' ); ?>><span><?php esc_html_e( 'Rent', 'dara' ); ?></span></label>
			</div>
		</fieldset>

		<label class="filters__group">
			<span class="filters__label"><?php esc_html_e( 'City / district', 'dara' ); ?></span>
			<input type="text" name="location" value="<?php echo esc_attr( $dara_f['location'] ); ?>" list="dara-cities-f" autocomplete="off">
			<?php
			$dara_cities = get_terms( array( 'taxonomy' => 'property_city', 'hide_empty' => true, 'number' => 60 ) );
			if ( $dara_cities && ! is_wp_error( $dara_cities ) ) {
				echo '<datalist id="dara-cities-f">';
				foreach ( $dara_cities as $dara_c ) {
					echo '<option value="' . esc_attr( $dara_c->name ) . '"></option>';
				}
				echo '</datalist>';
			}
			?>
		</label>

		<?php if ( $dara_types && ! is_wp_error( $dara_types ) ) : ?>
			<fieldset class="filters__group">
				<legend><?php esc_html_e( 'Property type', 'dara' ); ?></legend>
				<?php foreach ( $dara_types as $dara_t ) : ?>
					<label class="check"><input type="checkbox" name="type[]" value="<?php echo esc_attr( $dara_t->slug ); ?>" <?php checked( in_array( $dara_t->slug, $dara_f['type'], true ) ); ?>><?php echo esc_html( $dara_t->name ); ?></label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<fieldset class="filters__group">
			<legend><?php echo esc_html( sprintf( /* translators: %s: currency. */ __( 'Price (%s)', 'dara' ), dara_setting( 'currency' ) ) ); ?></legend>
			<div class="filters__pair">
				<label><span><?php esc_html_e( 'From', 'dara' ); ?></span><input type="number" inputmode="numeric" min="0" step="1000" name="min_price" value="<?php echo esc_attr( $dara_f['min_price'] ); ?>" placeholder="0"></label>
				<label><span><?php esc_html_e( 'To', 'dara' ); ?></span><input type="number" inputmode="numeric" min="0" step="1000" name="max_price" value="<?php echo esc_attr( $dara_f['max_price'] ); ?>" placeholder="<?php esc_attr_e( 'No limit', 'dara' ); ?>"></label>
			</div>
		</fieldset>

		<fieldset class="filters__group">
			<legend><?php esc_html_e( 'Bedrooms', 'dara' ); ?></legend>
			<div class="pills">
				<?php foreach ( array( '' => __( 'Any', 'dara' ), 1 => '1+', 2 => '2+', 3 => '3+', 4 => '4+', 5 => '5+' ) as $dara_v => $dara_l ) : ?>
					<label><input type="radio" name="beds" value="<?php echo esc_attr( $dara_v ); ?>" <?php checked( (string) $dara_f['beds'], (string) $dara_v ); ?>><span><?php echo esc_html( $dara_l ); ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="filters__group">
			<legend><?php esc_html_e( 'Area (m²)', 'dara' ); ?></legend>
			<div class="filters__pair">
				<label><span><?php esc_html_e( 'From', 'dara' ); ?></span><input type="number" inputmode="numeric" min="0" name="min_area" value="<?php echo esc_attr( $dara_f['min_area'] ); ?>"></label>
				<label><span><?php esc_html_e( 'To', 'dara' ); ?></span><input type="number" inputmode="numeric" min="0" name="max_area" value="<?php echo esc_attr( $dara_f['max_area'] ); ?>"></label>
			</div>
		</fieldset>

		<?php if ( $dara_features && ! is_wp_error( $dara_features ) ) : ?>
			<fieldset class="filters__group">
				<legend><?php esc_html_e( 'Features', 'dara' ); ?></legend>
				<?php foreach ( $dara_features as $dara_ft ) : ?>
					<label class="check"><input type="checkbox" name="features[]" value="<?php echo esc_attr( $dara_ft->slug ); ?>" <?php checked( in_array( $dara_ft->slug, $dara_f['features'], true ) ); ?>><?php echo esc_html( $dara_ft->name ); ?></label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<?php if ( 'newest' !== $dara_f['sort'] ) : ?>
			<input type="hidden" name="sort" value="<?php echo esc_attr( $dara_f['sort'] ); ?>">
		<?php endif; ?>
		<button type="submit" class="btn btn--primary btn--block"><?php esc_html_e( 'Show results', 'dara' ); ?></button>
	</form>
</aside>
