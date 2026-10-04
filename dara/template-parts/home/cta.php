<?php
/**
 * Home: call to action band.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--tight">
	<div class="container">
		<div class="cta">
			<div class="cta__text">
				<h2><?php echo esc_html( dara_mod( 'cta_title' ) ); ?></h2>
				<p><?php echo esc_html( dara_mod( 'cta_text' ) ); ?></p>
			</div>
			<div class="cta__actions">
				<a class="btn btn--light" href="<?php echo esc_url( dara_list_property_url( 'cta_btn_url' ) ); ?>"><?php echo esc_html( dara_mod( 'cta_btn_text' ) ); ?></a>
				<?php if ( dara_mod( 'whatsapp' ) ) : ?>
					<a class="btn btn--ghost-light" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D+/', '', dara_mod( 'whatsapp' ) ) ); ?>" target="_blank" rel="noopener"><?php dara_the_icon( 'whatsapp', 20 ); ?><?php esc_html_e( 'WhatsApp', 'dara' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
