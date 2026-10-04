<?php
/**
 * Home: property types tiles.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_types = get_terms( array( 'taxonomy' => 'property_type', 'parent' => 0, 'number' => 6, 'hide_empty' => false ) );
if ( ! $dara_types || is_wp_error( $dara_types ) ) {
	return;
}
?>
<section class="section section--tight">
	<div class="container">
		<?php dara_section_head( dara_mod( 'types_eyebrow' ), dara_mod( 'types_title' ) ); ?>
		<div class="grid grid--tiles">
			<?php foreach ( $dara_types as $dara_type ) : ?>
				<?php $dara_icon = get_term_meta( $dara_type->term_id, 'dara_icon', true ); ?>
				<a class="tile" href="<?php echo esc_url( get_term_link( $dara_type ) ); ?>">
					<span class="tile__icon"><?php dara_the_icon( $dara_icon ? $dara_icon : 'home', 26 ); ?></span>
					<span class="tile__name"><?php echo esc_html( $dara_type->name ); ?></span>
					<?php if ( $dara_type->count ) : ?>
						<span class="tile__count"><?php echo esc_html( sprintf( /* translators: %s: number. */ _n( '%s property', '%s properties', $dara_type->count, 'dara' ), number_format_i18n( $dara_type->count ) ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
