<?php
/**
 * Plugin dependency notice.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ask admins to activate Dara Core.
 */
function dara_core_notice() {
	if ( dara_has_core() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'plugins' !== $screen->id && 'dashboard' !== $screen->id && 'themes' !== $screen->id ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'The Dara theme needs the "Dara Core" plugin for properties, projects, search and lead forms. Please install and activate it.', 'dara' ) . '</p></div>';
}
add_action( 'admin_notices', 'dara_core_notice' );
