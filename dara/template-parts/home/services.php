<?php
/**
 * Home: services.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

$dara_items = dara_mod_lines( 'services_items', 4 );
if ( ! $dara_items ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<?php dara_section_head( dara_mod( 'services_eyebrow' ), dara_mod( 'services_title' ), '', 'section-head--center' ); ?>
		<div class="grid grid--services">
			<?php foreach ( $dara_items as $dara_item ) : ?>
				<div class="service">
					<span class="service__icon"><?php dara_the_icon( sanitize_key( $dara_item[0] ), 28 ); ?></span>
					<h3 class="service__title"><?php echo esc_html( $dara_item[1] ); ?></h3>
					<p><?php echo esc_html( $dara_item[2] ); ?></p>
					<?php if ( $dara_item[3] ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( $dara_item[3] ); ?>"><?php esc_html_e( 'Learn more', 'dara' ); ?><span class="screen-reader-text"> — <?php echo esc_html( $dara_item[1] ); ?></span></a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
