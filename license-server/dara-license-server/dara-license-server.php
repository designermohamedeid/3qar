<?php
/**
 * Plugin Name:       Dara License Server
 * Description:       Issues and verifies per-domain license keys for Dara, signs responses with Ed25519 and serves updates to licensed sites only.
 * Version:           1.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Mansoura Host
 * Author URI:        https://mansourahost.com
 * License:           GPL-2.0-or-later
 * Text Domain:       dara-license-server
 *
 * @package DaraLicenseServer
 */

defined( 'ABSPATH' ) || exit;

define( 'DLS_VERSION', '1.1.0' );
define( 'DLS_FILE', __FILE__ );
define( 'DLS_DIR', plugin_dir_path( __FILE__ ) );

require DLS_DIR . 'includes/core.php';
require DLS_DIR . 'includes/api.php';
require DLS_DIR . 'includes/admin.php';
require DLS_DIR . 'includes/watermark.php';
if ( in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins', array() ), true ) ) {
	require DLS_DIR . 'includes/woocommerce.php';
}

register_activation_hook( __FILE__, 'dls_install' );
add_action( 'plugins_loaded', 'dls_maybe_upgrade' );
