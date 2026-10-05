<?php
/**
 * 404.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container section error-404">
	<p class="error-404__code">404</p>
	<h1><?php esc_html_e( 'This page could not be found', 'dara' ); ?></h1>
	<p><?php esc_html_e( 'It may have been moved or removed. Try searching or browse our properties.', 'dara' ); ?></p>
	<?php get_search_form(); ?>
	<div class="error-404__links">
		<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'dara' ); ?></a>
		<?php if ( dara_has_core() ) : ?>
			<a class="btn btn--outline" href="<?php echo esc_url( dara_listings_url() ); ?>"><?php esc_html_e( 'Browse properties', 'dara' ); ?></a>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
