<?php
/**
 * Plugin Name:       Dara Pro
 * Plugin URI:        https://mansourahost.com
 * Description:       Premium features for the Dara theme: Gutenberg section blocks and patterns, one-click demo content, mortgage calculator, property comparison and Google Maps.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Requires Plugins:  dara-core
 * Author:            Mansoura Host
 * Author URI:        https://mansourahost.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dara-pro
 * Domain Path:       /languages
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

define( 'DARA_PRO_VERSION', '1.0.0' );
define( 'DARA_PRO_FILE', __FILE__ );
define( 'DARA_PRO_DIR', plugin_dir_path( __FILE__ ) );
define( 'DARA_PRO_URL', plugin_dir_url( __FILE__ ) );

require DARA_PRO_DIR . 'includes/edition.php';
require DARA_PRO_DIR . 'includes/guard.php';

/**
 * Boot after Dara Core.
 */
function dara_pro_boot() {
	load_plugin_textdomain( 'dara-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	if ( ! defined( 'DARA_CORE_VERSION' ) ) {
		add_action( 'admin_notices', 'dara_pro_needs_core' );
		return;
	}
	require DARA_PRO_DIR . 'includes/features.php';
	require DARA_PRO_DIR . 'includes/blocks.php';
	require DARA_PRO_DIR . 'includes/demo.php';
}
add_action( 'plugins_loaded', 'dara_pro_boot', 5 );

/**
 * Notice when Dara Core is missing.
 */
function dara_pro_needs_core() {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'Dara Pro needs the Dara Core plugin.', 'dara-pro' ) . '</p></div>';
}
