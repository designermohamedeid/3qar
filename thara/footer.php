<?php
/**
 * Site footer.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_phone = thara_option( 'phone' );
$thara_email = thara_option( 'email' );
?>
</main><!-- #main -->

<footer class="site-footer">
	<div class="container">
		<div class="row">
			<?php if ( $thara_phone || $thara_email ) : ?>
				<div class="col-xs-12">
					<div class="contact">
						<?php if ( $thara_phone ) : ?>
							<div class="right">
								<div class="icon"><span class="lnr lnr-phone-handset" aria-hidden="true"></span></div>
								<div class="details">
									<p><?php echo esc_html( thara_option( 'contact_call_label' ) ); ?></p>
									<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $thara_phone ) ); ?>" dir="ltr"><?php echo esc_html( $thara_phone ); ?></a>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( $thara_email ) : ?>
							<div class="left">
								<div class="icon"><span class="lnr lnr-envelope" aria-hidden="true"></span></div>
								<div class="details">
									<p><?php echo esc_html( thara_option( 'contact_mail_label' ) ); ?></p>
									<a href="mailto:<?php echo esc_attr( antispambot( $thara_email ) ); ?>"><?php echo esc_html( antispambot( $thara_email ) ); ?></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="col-md-4">
				<div class="foot-links">
					<button type="button" data-target="#footer-links" aria-controls="footer-links"><?php echo esc_html( thara_option( 'footer_links_title' ) ); ?></button>
					<nav class="collapse" id="footer-links" aria-label="<?php echo esc_attr( thara_option( 'footer_links_title' ) ); ?>">
						<?php thara_footer_menu(); ?>
					</nav>
				</div>
			</div>

			<div class="col-md-4">
				<?php if ( thara_has_social_links() ) : ?>
					<p class="socialP"><?php echo esc_html( thara_option( 'social_title' ) ); ?></p>
					<div class="social"><?php thara_social_links(); ?></div>
				<?php endif; ?>
			</div>

			<div class="col-md-4">
				<?php if ( thara_option( 'newsletter_show' ) ) : ?>
					<?php get_template_part( 'template-parts/footer/newsletter' ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>

<div class="end">
	<div class="container">
		<div class="content">
			<p><?php echo esc_html( thara_copyright() ); ?></p>
			<?php
			$thara_credit_text = thara_option( 'credit_text' );
			$thara_credit_logo = thara_option( 'credit_logo' );
			if ( $thara_credit_text || $thara_credit_logo ) :
				$thara_credit_url = thara_option( 'credit_url' );
				echo $thara_credit_url ? '<a href="' . esc_url( $thara_credit_url ) . '" target="_blank" rel="noopener">' : '<span class="credit">';
				echo esc_html( $thara_credit_text );
				if ( $thara_credit_logo ) {
					echo '<img src="' . esc_url( $thara_credit_logo ) . '" alt="">';
				}
				echo $thara_credit_url ? '</a>' : '</span>';
			endif;
			?>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
