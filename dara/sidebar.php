<?php
/**
 * Blog sidebar.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'dara' ); ?>">
	<?php dynamic_sidebar( 'sidebar-blog' ); ?>
</aside>
