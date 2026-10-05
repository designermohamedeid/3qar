<?php
/**
 * Template Name: Favorites
 *
 * Saved properties live in the visitor's browser (no account needed) and
 * are loaded through the REST API.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/page-title' );
?>
<div class="container section section--top-0">
	<div class="grid grid--cards" data-favorites aria-live="polite"></div>
	<noscript><p><?php esc_html_e( 'Please enable JavaScript to see your saved properties.', 'dara' ); ?></p></noscript>
</div>
<?php
get_footer();
