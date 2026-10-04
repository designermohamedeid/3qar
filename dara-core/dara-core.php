<?php
/**
 * Plugin Name:       Dara Core
 * Plugin URI:        https://github.com/designermohamedeid/3qar
 * Description:       Real estate engine for the Dara theme: properties, developer projects, agents, advanced search, leads (viewing requests, project interest, contact, list-your-property) and structured data.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Mohamed Eid
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dara-core
 * Domain Path:       /languages
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

define( 'DARA_CORE_VERSION', '1.0.0' );
define( 'DARA_CORE_FILE', __FILE__ );
define( 'DARA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DARA_CORE_URL', plugin_dir_url( __FILE__ ) );

require DARA_CORE_DIR . 'includes/helpers.php';
require DARA_CORE_DIR . 'includes/post-types.php';
require DARA_CORE_DIR . 'includes/fields.php';
require DARA_CORE_DIR . 'includes/admin.php';
require DARA_CORE_DIR . 'includes/query.php';
require DARA_CORE_DIR . 'includes/leads.php';
require DARA_CORE_DIR . 'includes/schema.php';

/**
 * Translations.
 */
function dara_core_load_textdomain() {
	load_plugin_textdomain( 'dara-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'dara_core_load_textdomain', 0 );

/**
 * Flush rewrite rules on activation / deactivation.
 */
function dara_core_activate() {
	dara_core_register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dara_core_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
