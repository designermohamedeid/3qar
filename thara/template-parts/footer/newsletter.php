<?php
/**
 * Footer newsletter form.
 *
 * Priority: shortcode > external action URL > built-in form.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_shortcode = thara_option( 'newsletter_shortcode' );
$thara_action    = thara_option( 'newsletter_action' );
?>
<div class="newsletter" id="newsletter">
	<p><?php echo esc_html( thara_option( 'newsletter_title' ) ); ?></p>
	<span><?php echo esc_html( thara_option( 'newsletter_text' ) ); ?></span>

	<?php if ( $thara_shortcode ) : ?>
		<div class="newsletter-shortcode"><?php echo do_shortcode( $thara_shortcode ); ?></div>
	<?php else : ?>
		<?php echo thara_newsletter_message(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function. ?>
		<form method="post" action="<?php echo esc_url( $thara_action ? $thara_action : admin_url( 'admin-post.php' ) ); ?>"<?php echo $thara_action ? ' target="_blank"' : ''; ?>>
			<?php if ( ! $thara_action ) : ?>
				<input type="hidden" name="action" value="thara_newsletter">
				<?php wp_nonce_field( 'thara_newsletter', 'thara_newsletter_nonce' ); ?>
				<div class="hp-field" aria-hidden="true"><input type="text" name="thara_website" tabindex="-1" autocomplete="off"></div>
			<?php endif; ?>
			<div class="frm">
				<label class="screen-reader-text" for="newsletter-email"><?php esc_html_e( 'Email address', 'thara' ); ?></label>
				<input type="email" id="newsletter-email" name="EMAIL" required placeholder="<?php esc_attr_e( 'Enter your email', 'thara' ); ?>">
				<button type="submit" aria-label="<?php esc_attr_e( 'Subscribe', 'thara' ); ?>"><span class="<?php echo esc_attr( thara_arrow_class() ); ?>" aria-hidden="true"></span></button>
			</div>
		</form>
	<?php endif; ?>
</div>
