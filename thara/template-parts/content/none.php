<?php
/**
 * No results.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="no-results">
	<span class="lnr lnr-sad" aria-hidden="true"></span>
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Sorry, nothing matched your search. Please try again with different keywords.', 'thara' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'There is nothing to show here yet.', 'thara' ); ?></p>
	<?php endif; ?>
</div>
