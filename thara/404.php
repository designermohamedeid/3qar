<?php
/**
 * 404 page.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-section error-404">
	<div class="container text-center">
		<p class="error-404__code">404</p>
		<p class="error-404__text"><?php esc_html_e( 'The page you are looking for may have been moved or deleted.', 'thara' ); ?></p>
		<div class="error-404__search"><?php get_search_form(); ?></div>
		<a class="thara-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'thara' ); ?></a>
	</div>
</section>
<?php
get_footer();
