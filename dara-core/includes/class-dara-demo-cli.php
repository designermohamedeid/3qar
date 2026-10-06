<?php
/**
 * WP-CLI: wp dara demo import|remove.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Import or remove the Dara demo content.
 */
class Dara_Demo_CLI {

	/**
	 * Import the demo content.
	 *
	 * ## OPTIONS
	 *
	 * [--lang=<lang>]
	 * : Demo language.
	 * ---
	 * default: ar
	 * options:
	 *   - ar
	 *   - en
	 * ---
	 *
	 * [--skip-front]
	 * : Keep the current home page settings.
	 *
	 * [--skip-menus]
	 * : Do not create menus.
	 *
	 * ## EXAMPLES
	 *
	 *     wp dara demo import --lang=en
	 *
	 * @param array $args  Positional arguments.
	 * @param array $assoc Options.
	 */
	public function import( $args, $assoc ) {
		if ( ! get_current_user_id() ) {
			$admins = get_users(
				array(
					'role'   => 'administrator',
					'number' => 1,
					'fields' => 'ID',
				)
			);
			wp_set_current_user( $admins ? (int) $admins[0] : 0 );
		}
		if ( ! dara_core_premium() ) {
			WP_CLI::error( 'Demo import needs an active Dara license (Properties → License).' );
		}
		$lang = isset( $assoc['lang'] ) ? $assoc['lang'] : 'ar';
		if ( ! dara_demo_run_import( $lang, empty( $assoc['skip-front'] ), empty( $assoc['skip-menus'] ) ) ) {
			WP_CLI::error( 'Demo content is already installed. Run "wp dara demo remove" first.' );
		}
		flush_rewrite_rules();
		WP_CLI::success( 'Dara demo imported (' . $lang . ').' );
	}

	/**
	 * Remove the demo content and restore the previous settings.
	 *
	 * ## EXAMPLES
	 *
	 *     wp dara demo remove
	 */
	public function remove() {
		dara_demo_run_remove();
		WP_CLI::success( 'Dara demo removed.' );
	}
}
