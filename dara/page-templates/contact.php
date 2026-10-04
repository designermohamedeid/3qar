<?php
/**
 * Template Name: Contact
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );
?>
<div class="container section section--top-0 contact">
	<div class="contact__info">
		<?php
		while ( have_posts() ) :
			the_post();
			echo '<div class="entry-content">';
			the_content();
			echo '</div>';
		endwhile;
		?>
		<ul class="contact-cards">
			<?php if ( dara_mod( 'phone' ) ) : ?>
				<li><span class="contact-cards__icon"><?php dara_the_icon( 'phone', 22 ); ?></span><span><small><?php esc_html_e( 'Call us', 'dara' ); ?></small><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', dara_mod( 'phone' ) ) ); ?>" dir="ltr"><?php echo esc_html( dara_mod( 'phone' ) ); ?></a></span></li>
			<?php endif; ?>
			<?php if ( dara_mod( 'whatsapp' ) ) : ?>
				<li><span class="contact-cards__icon"><?php dara_the_icon( 'whatsapp', 22 ); ?></span><span><small><?php esc_html_e( 'WhatsApp', 'dara' ); ?></small><a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D+/', '', dara_mod( 'whatsapp' ) ) ); ?>" target="_blank" rel="noopener" dir="ltr">+<?php echo esc_html( preg_replace( '/\D+/', '', dara_mod( 'whatsapp' ) ) ); ?></a></span></li>
			<?php endif; ?>
			<?php if ( dara_mod( 'email' ) ) : ?>
				<li><span class="contact-cards__icon"><?php dara_the_icon( 'mail', 22 ); ?></span><span><small><?php esc_html_e( 'Email', 'dara' ); ?></small><a href="mailto:<?php echo esc_attr( antispambot( dara_mod( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( dara_mod( 'email' ) ) ); ?></a></span></li>
			<?php endif; ?>
			<?php if ( dara_mod( 'address' ) ) : ?>
				<li><span class="contact-cards__icon"><?php dara_the_icon( 'pin', 22 ); ?></span><span><small><?php esc_html_e( 'Office', 'dara' ); ?></small><span><?php echo esc_html( dara_mod( 'address' ) ); ?></span></span></li>
			<?php endif; ?>
		</ul>
		<?php dara_social_links(); ?>
	</div>
	<?php if ( dara_has_core() ) : ?>
		<form class="form form--card" id="lead-form" method="post" action="<?php echo esc_url( dara_lead_action() ); ?>">
			<h2 class="form__title"><?php esc_html_e( 'Send us a message', 'dara' ); ?></h2>
			<?php echo dara_lead_notice(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php dara_lead_hidden_fields( 'contact' ); ?>
			<div class="form__grid">
				<label class="form__field"><span><?php esc_html_e( 'Name', 'dara' ); ?></span><input type="text" name="lead_name" required autocomplete="name"></label>
				<label class="form__field"><span><?php esc_html_e( 'Mobile number', 'dara' ); ?></span><input type="tel" name="lead_phone" dir="ltr" inputmode="tel" autocomplete="tel"></label>
				<label class="form__field form__field--full"><span><?php esc_html_e( 'Email', 'dara' ); ?></span><input type="email" name="lead_email" autocomplete="email"></label>
				<label class="form__field form__field--full"><span><?php esc_html_e( 'Message', 'dara' ); ?></span><textarea name="lead_message" rows="5" required></textarea></label>
			</div>
			<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Send message', 'dara' ); ?></button>
		</form>
	<?php endif; ?>
</div>
<?php if ( is_numeric( dara_mod( 'map_lat' ) ) && is_numeric( dara_mod( 'map_lng' ) ) ) : ?>
	<div class="container section section--top-0"><div class="map map--wide" role="region" aria-label="<?php esc_attr_e( 'Map', 'dara' ); ?>" data-map="single" data-lat="<?php echo esc_attr( dara_mod( 'map_lat' ) ); ?>" data-lng="<?php echo esc_attr( dara_mod( 'map_lng' ) ); ?>"></div></div>
<?php endif; ?>
<?php
get_footer();
