<?php
/**
 * Front page hero.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

$thara_btn_url = thara_option( 'hero_btn_url' );
if ( ! $thara_btn_url ) {
	$thara_btn_url = get_post_type_archive_link( 'thara_property' );
}
?>
<div class="content">
	<div class="container">
		<?php if ( thara_option( 'hero_subtitle' ) ) : ?>
			<p><?php echo esc_html( thara_option( 'hero_subtitle' ) ); ?></p>
		<?php endif; ?>
		<?php if ( thara_option( 'hero_title' ) ) : ?>
			<h1 class="hero-title"><?php echo wp_kses_post( thara_option( 'hero_title' ) ); ?></h1>
		<?php endif; ?>
		<?php if ( thara_option( 'hero_btn_text' ) && $thara_btn_url ) : ?>
			<a href="<?php echo esc_url( $thara_btn_url ); ?>">
				<p><?php echo esc_html( thara_option( 'hero_btn_text' ) ); ?></p><span class="<?php echo esc_attr( thara_arrow_class() ); ?>" aria-hidden="true"></span>
			</a>
		<?php endif; ?>
	</div>
</div>
<?php if ( thara_option( 'hero_social' ) && thara_has_social_links() ) : ?>
	<div class="social">
		<div class="shape"></div>
		<?php thara_social_links(); ?>
	</div>
<?php endif; ?>
