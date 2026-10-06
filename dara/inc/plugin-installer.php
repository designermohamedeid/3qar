<?php
/**
 * Required plugin installer.
 *
 * Shows an admin notice until the bundled "Dara Core" plugin is installed and
 * active, and installs / activates it in one click from plugins/dara-core.zip.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin file relative to the plugins folder.
 */
const DARA_PLUGIN_FILE = 'dara-core/dara-core.php';

/**
 * Path of a bundled plugin package.
 *
 * @param string $slug Plugin slug.
 * @return string Empty when the package is not shipped.
 */
function dara_plugin_package( $slug = 'dara-core' ) {
	$zip = DARA_DIR . '/plugins/' . $slug . '.zip';
	return file_exists( $zip ) ? $zip : '';
}

/**
 * State of a plugin: active, inactive or missing.
 *
 * @param string $slug Plugin slug.
 * @return string
 */
function dara_plugin_state( $slug = 'dara-core' ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$file = $slug . '/' . $slug . '.php';
	if ( is_plugin_active( $file ) ) {
		return 'active';
	}
	return isset( get_plugins()[ $file ] ) ? 'inactive' : 'missing';
}

/**
 * Bundled plugins that still need installing or activating.
 *
 * @return array Slugs.
 */
function dara_plugins_pending() {
	$pending = array();
	foreach ( array( 'dara-core', 'dara-pro' ) as $slug ) {
		if ( 'active' !== dara_plugin_state( $slug ) && ( 'dara-core' === $slug || dara_plugin_package( $slug ) ) ) {
			$pending[] = $slug;
		}
	}
	return $pending;
}

/**
 * Admin notice.
 */
function dara_plugin_notice() {
	if ( ! current_user_can( 'install_plugins' ) || ! dara_plugins_pending() ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && ! in_array( $screen->id, array( 'dashboard', 'themes', 'plugins' ), true ) ) {
		return;
	}
	$state = dara_plugin_state();
	if ( 'active' === $state ) {
		$button = '<a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dara_install_core' ), 'dara_install_core' ) ) . '">' . esc_html__( 'Install and activate Dara Pro', 'dara' ) . '</a>';
	} elseif ( 'missing' === $state && ! dara_plugin_package() ) {
		$button = '<a class="button button-primary" href="' . esc_url( admin_url( 'plugin-install.php?tab=upload' ) ) . '">' . esc_html__( 'Upload Dara Core', 'dara' ) . '</a>';
	} else {
		$label = 'missing' === $state ? __( 'Install and activate Dara Core', 'dara' ) : __( 'Activate Dara Core', 'dara' );
		if ( in_array( 'dara-pro', dara_plugins_pending(), true ) ) {
			$label = __( 'Install and activate Dara Core and Dara Pro', 'dara' );
		}
		$url    = wp_nonce_url( admin_url( 'admin-post.php?action=dara_install_core' ), 'dara_install_core' );
		$button = '<a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}
	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p><p>%3$s</p></div>',
		'active' === $state ? esc_html__( 'Finish setting up Dara.', 'dara' ) : esc_html__( 'Dara needs the Dara Core plugin.', 'dara' ),
		'active' === $state ? esc_html__( 'Dara Pro adds the blocks, demo importer, mortgage calculator, comparison and Google Maps.', 'dara' ) : esc_html__( 'It adds properties, projects, agents, search, lead forms, blocks and the demo importer.', 'dara' ),
		$button // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	);
}
add_action( 'admin_notices', 'dara_plugin_notice' );

/**
 * Install (from the bundled zip) and activate Dara Core.
 */
function dara_install_core() {
	check_admin_referer( 'dara_install_core' );
	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to install plugins.', 'dara' ) );
	}

	foreach ( dara_plugins_pending() as $slug ) {
		$file = $slug . '/' . $slug . '.php';
		if ( 'missing' === dara_plugin_state( $slug ) ) {
			$package = dara_plugin_package( $slug );
			if ( ! $package ) {
				wp_safe_redirect( admin_url( 'plugin-install.php?tab=upload' ) );
				exit;
			}
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			// Needs direct file access or saved FTP credentials, like any plugin upload.
			if ( ! WP_Filesystem() ) {
				wp_safe_redirect( admin_url( 'plugin-install.php?tab=upload' ) );
				exit;
			}
			$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
			$result   = $upgrader->install( $package );
			if ( ! $result || is_wp_error( $result ) ) {
				wp_die(
					esc_html( is_wp_error( $result ) ? $result->get_error_message() : __( 'Dara Core could not be installed. Upload plugins/dara-core.zip from Plugins > Add New.', 'dara' ) ),
					'',
					array( 'back_link' => true )
				);
			}
			wp_clean_plugins_cache();
		}
		$activated = activate_plugin( $file );
		if ( is_wp_error( $activated ) ) {
			wp_die( esc_html( $activated->get_error_message() ), '', array( 'back_link' => true ) );
		}
	}
	// Licensed (direct) builds get Dara Pro from the license page; marketplace builds go to the demo importer.
	$page = function_exists( 'dara_license_enabled' ) && dara_license_enabled() ? 'dara-license' : 'dara-demo';
	wp_safe_redirect( admin_url( 'edit.php?post_type=dara_property&page=' . $page ) );
	exit;
}
add_action( 'admin_post_dara_install_core', 'dara_install_core' );
