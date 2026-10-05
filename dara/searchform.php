<?php
/**
 * Search form.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
$dara_sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $dara_sid ); ?>"><?php esc_html_e( 'Search for:', 'dara' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $dara_sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'dara' ); ?>">
	<button type="submit" class="btn btn--primary" aria-label="<?php esc_attr_e( 'Search', 'dara' ); ?>"><?php dara_the_icon( 'search', 20 ); ?></button>
</form>
