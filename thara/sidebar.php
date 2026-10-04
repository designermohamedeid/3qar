<?php
/**
 * Sidebar.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="col-md-4 widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'thara' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
