<?php
/**
 * Nothing found.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="empty">
	<?php dara_the_icon( 'search', 40 ); ?>
	<h2><?php esc_html_e( 'Nothing found', 'dara' ); ?></h2>
	<p><?php echo is_search() ? esc_html__( 'No results matched your search. Try different words.', 'dara' ) : esc_html__( 'There is nothing here yet.', 'dara' ); ?></p>
	<?php get_search_form(); ?>
</div>
