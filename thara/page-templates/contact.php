<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * Shows the contact info from the Customizer next to the page content
 * (put your Contact Form 7 / WPForms shortcode in the page content).
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
$thara_phone   = thara_option( 'phone' );
$thara_email   = thara_option( 'email' );
$thara_address = thara_option( 'address' );
$thara_map     = thara_option( 'map_embed' );
?>
<section class="page-section contact-page">
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<ul class="res contact-info">
					<?php if ( $thara_phone ) : ?>
						<li><span class="lnr lnr-phone-handset" aria-hidden="true"></span><div><strong><?php echo esc_html( thara_option( 'contact_call_label' ) ); ?></strong><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $thara_phone ) ); ?>" dir="ltr"><?php echo esc_html( $thara_phone ); ?></a></div></li>
					<?php endif; ?>
					<?php if ( $thara_email ) : ?>
						<li><span class="lnr lnr-envelope" aria-hidden="true"></span><div><strong><?php echo esc_html( thara_option( 'contact_mail_label' ) ); ?></strong><a href="mailto:<?php echo esc_attr( antispambot( $thara_email ) ); ?>"><?php echo esc_html( antispambot( $thara_email ) ); ?></a></div></li>
					<?php endif; ?>
					<?php if ( $thara_address ) : ?>
						<li><span class="lnr lnr-map-marker" aria-hidden="true"></span><div><strong><?php esc_html_e( 'Address', 'thara' ); ?></strong><span><?php echo wp_kses_post( nl2br( $thara_address ) ); ?></span></div></li>
					<?php endif; ?>
				</ul>
				<?php if ( thara_has_social_links() ) : ?>
					<div class="contact-social"><?php thara_social_links(); ?></div>
				<?php endif; ?>
			</div>
			<div class="col-md-8">
				<?php
				while ( have_posts() ) :
					the_post();
					echo '<div class="entry-content">';
					the_content();
					echo '</div>';
				endwhile;
				?>
			</div>
		</div>
	</div>
	<?php if ( $thara_map ) : ?>
		<div class="contact-map"><?php echo thara_sanitize_embed( $thara_map ); // phpcs:ignore WordPress.Security.EscapeOutput -- sanitized with wp_kses. ?></div>
	<?php endif; ?>
</section>
<?php
get_footer();
