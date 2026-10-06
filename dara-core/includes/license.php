<?php
/**
 * License: activation per domain, signed answers, weekly checks, premium features and updates.
 *
 * The license server signs every answer with Ed25519; this file only trusts answers that
 * verify against the public key of the build (includes/edition.php).
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

const DARA_LICENSE_TRIAL_DAYS = 14;
const DARA_LICENSE_GRACE_DAYS = 7;

/**
 * Licensing applies to this build (direct edition with a public key).
 *
 * @return bool
 */
function dara_license_enabled() {
	$e = dara_core_edition();
	return 'direct' === $e['edition'] && '' !== $e['pubkey'];
}

/**
 * This site's domain as the server sees it.
 *
 * @return string
 */
function dara_license_domain() {
	$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return preg_replace( '/^www\./', '', $host );
}

/**
 * Local and staging sites are always fully unlocked (same rules as the server).
 *
 * @return bool
 */
function dara_license_is_local() {
	$d = dara_license_domain();
	if ( 'localhost' === $d ) {
		return true;
	}
	if ( filter_var( $d, FILTER_VALIDATE_IP ) ) {
		return ! filter_var( $d, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	}
	return (bool) preg_match( '/(^|\.)(staging|stage|dev|test|local)\.|\.(local|test|localhost|invalid|example)$/', $d );
}

/**
 * Stored license data.
 *
 * @return array
 */
function dara_license_data() {
	return wp_parse_args(
		(array) get_option( 'dara_license', array() ),
		array(
			'key'        => '',
			'payload'    => '',
			'signature'  => '',
			'message'    => '',
			'checked_at' => 0,
		)
	);
}

/**
 * Verified token payload, or null.
 *
 * @param string $payload   Base64 JSON.
 * @param string $signature Base64 signature.
 * @return array|null
 */
function dara_license_verify( $payload, $signature ) {
	$e = dara_core_edition();
	if ( ! $payload || ! $signature || ! function_exists( 'sodium_crypto_sign_verify_detached' ) ) {
		return null;
	}
	// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$json = base64_decode( $payload, true );
	$sig  = base64_decode( $signature, true );
	$pub  = base64_decode( $e['pubkey'], true );
	// phpcs:enable
	if ( false === $json || false === $sig || false === $pub || SODIUM_CRYPTO_SIGN_BYTES !== strlen( $sig ) || SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES !== strlen( $pub ) ) {
		return null;
	}
	try {
		if ( ! sodium_crypto_sign_verify_detached( $sig, $json, $pub ) ) {
			return null;
		}
	} catch ( Exception $ex ) {
		return null;
	}
	$data = json_decode( $json, true );
	return is_array( $data ) ? $data : null;
}

/**
 * Current license state (cached per request).
 *
 * @return array { status: valid|invalid|none, reason, expires, updates }
 */
function dara_license_state() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}
	$data  = dara_license_data();
	$token = dara_license_verify( $data['payload'], $data['signature'] );
	$state = array(
		'status'  => $data['key'] ? 'invalid' : 'none',
		'reason'  => $data['key'] ? 'unverified' : '',
		'expires' => 0,
		'updates' => false,
	);
	if ( ! $token || ! $data['key'] ) {
		return $state;
	}
	if ( 'valid' !== $token['s'] ) {
		$state['reason'] = (string) $token['r'];
		return $state;
	}
	if ( ! hash_equals( (string) $token['k'], substr( hash( 'sha256', $data['key'] ), 0, 16 ) ) || dara_license_domain() !== $token['d'] ) {
		$state['reason'] = 'domain_changed';
		return $state;
	}
	if ( time() > (int) $token['x'] + DARA_LICENSE_GRACE_DAYS * DAY_IN_SECONDS ) {
		$state['reason'] = 'stale';
		return $state;
	}
	$state = array(
		'status'  => 'valid',
		'reason'  => '',
		'expires' => (int) $token['e'],
		'updates' => ! $token['e'] || (int) $token['e'] > time(),
	);
	return $state;
}

/**
 * The site has a valid license (always true for marketplace builds and local sites).
 *
 * @return bool
 */
function dara_core_licensed() {
	if ( ! dara_license_enabled() || dara_license_is_local() ) {
		return true;
	}
	return 'valid' === dara_license_state()['status'];
}

/**
 * Days left in the trial (0 when over).
 *
 * @return int
 */
function dara_license_trial_days() {
	$start = (int) get_option( 'dara_installed_at' );
	if ( ! $start ) {
		$start = time();
		add_option( 'dara_installed_at', $start );
	}
	return max( 0, (int) ceil( ( $start + DARA_LICENSE_TRIAL_DAYS * DAY_IN_SECONDS - time() ) / DAY_IN_SECONDS ) );
}

/**
 * Premium features are on: licensed, or still in the trial.
 *
 * @return bool
 */
function dara_core_premium() {
	return (bool) apply_filters( 'dara_core_premium', dara_core_licensed() || dara_license_trial_days() > 0 );
}

/**
 * Call the license server.
 *
 * @param string $route  Route (activate, check, deactivate, update).
 * @param array  $body   Body.
 * @return array|WP_Error Decoded JSON (with HTTP code in _code).
 */
function dara_license_request( $route, array $body ) {
	$e        = dara_core_edition();
	$response = wp_remote_post(
		trailingslashit( $e['api'] ) . $route,
		array(
			'timeout' => 15,
			'body'    => $body,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'dara_license_http', __( 'The license server sent an unexpected answer. Try again later.', 'dara-core' ) );
	}
	$data['_code'] = (int) wp_remote_retrieve_response_code( $response );
	return $data;
}

/**
 * Activate or check the key on this domain and store the signed answer.
 *
 * @param string $key    License key.
 * @param bool   $create Activate (true) or only check (false).
 * @return true|WP_Error
 */
function dara_license_call( $key, $create = true ) {
	$data   = dara_license_request(
		$create ? 'activate' : 'check',
		array(
			'license_key' => $key,
			'domain'      => dara_license_domain(),
			'product'     => 'dara',
			'version'     => DARA_CORE_VERSION,
		)
	);
	$stored = dara_license_data();
	if ( is_wp_error( $data ) ) {
		// Network problem: keep the last signed token (grace period).
		$stored['message'] = $data->get_error_message();
		update_option( 'dara_license', $stored, false );
		return $data;
	}
	$token = isset( $data['payload'], $data['signature'] ) ? dara_license_verify( $data['payload'], $data['signature'] ) : null;
	if ( ! $token ) {
		if ( 429 === $data['_code'] ) {
			return new WP_Error( 'dara_license_rate', __( 'Too many attempts. Wait a minute and try again.', 'dara-core' ) );
		}
		return new WP_Error( 'dara_license_sig', __( 'The answer could not be verified. Make sure this build is up to date.', 'dara-core' ) );
	}
	update_option(
		'dara_license',
		array(
			'key'        => $key,
			'payload'    => $data['payload'],
			'signature'  => $data['signature'],
			'message'    => empty( $data['success'] ) && isset( $data['message'] ) ? sanitize_text_field( $data['message'] ) : '',
			'checked_at' => time(),
		),
		false
	);
	delete_site_transient( 'dara_updates' );
	return empty( $data['success'] ) ? new WP_Error( isset( $data['code'] ) ? sanitize_key( $data['code'] ) : 'dara_license', isset( $data['message'] ) ? sanitize_text_field( $data['message'] ) : __( 'License error.', 'dara-core' ) ) : true;
}

/**
 * Weekly re-check.
 */
function dara_license_cron() {
	$data = dara_license_data();
	if ( dara_license_enabled() && $data['key'] ) {
		dara_license_call( $data['key'], false );
	}
}
add_action( 'dara_license_check', 'dara_license_cron' );

/**
 * Schedule the weekly check.
 */
function dara_license_schedule() {
	if ( dara_license_enabled() && ! wp_next_scheduled( 'dara_license_check' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'weekly', 'dara_license_check' );
	}
}
add_action( 'init', 'dara_license_schedule' );

/* ---------- Admin page ---------- */

/**
 * Menu.
 */
function dara_license_menu() {
	if ( dara_license_enabled() ) {
		add_submenu_page( 'edit.php?post_type=dara_property', __( 'Dara license', 'dara-core' ), __( 'License', 'dara-core' ), 'manage_options', 'dara-license', 'dara_license_page' );
	}
}
add_action( 'admin_menu', 'dara_license_menu', 20 );

/**
 * Human text for a reason code.
 *
 * @param string $reason Code.
 * @return string
 */
function dara_license_reason( $reason ) {
	$map = array(
		'invalid_key'    => __( 'This license key does not exist.', 'dara-core' ),
		'suspended'      => __( 'This license is suspended. Please contact support.', 'dara-core' ),
		'site_limit'     => __( 'This key is already active on another domain. Deactivate it there first, or contact support.', 'dara-core' ),
		'not_activated'  => __( 'This domain is no longer activated on this key.', 'dara-core' ),
		'domain_changed' => __( 'The site address changed. Activate the license again for the new domain.', 'dara-core' ),
		'stale'          => __( 'The license could not be checked for a while. Check your server can reach the license server.', 'dara-core' ),
		'bad_product'    => __( 'This key is for a different product.', 'dara-core' ),
		'unverified'     => __( 'The license is not verified.', 'dara-core' ),
	);
	return isset( $map[ $reason ] ) ? $map[ $reason ] : __( 'The license is not active.', 'dara-core' );
}

/**
 * License page.
 */
function dara_license_page() {
	$data  = dara_license_data();
	$state = dara_license_state();
	$e     = dara_core_edition();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$msg = isset( $_GET['dara_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['dara_msg'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Dara license', 'dara-core' ); ?></h1>
		<?php if ( $msg ) : ?>
			<div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div>
		<?php endif; ?>
		<div style="max-width:720px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px 24px;margin-top:16px">
			<p style="font-size:15px;margin-top:0">
				<?php if ( 'valid' === $state['status'] ) : ?>
					<span style="color:#00a32a;font-weight:600">● <?php esc_html_e( 'Active', 'dara-core' ); ?></span>
					— <?php echo esc_html( sprintf( /* translators: %s: domain. */ __( 'licensed for %s', 'dara-core' ), dara_license_domain() ) ); ?>
				<?php elseif ( dara_license_is_local() ) : ?>
					<span style="color:#2271b1;font-weight:600">● <?php esc_html_e( 'Local / staging site', 'dara-core' ); ?></span>
					— <?php esc_html_e( 'all features are unlocked for development. Activate on your live domain.', 'dara-core' ); ?>
				<?php else : ?>
					<span style="color:#b32d2e;font-weight:600">● <?php esc_html_e( 'Not activated', 'dara-core' ); ?></span>
					<?php if ( 'none' !== $state['status'] ) : ?>
						— <?php echo esc_html( dara_license_reason( $state['reason'] ) ); ?>
					<?php endif; ?>
				<?php endif; ?>
			</p>
			<?php if ( 'valid' === $state['status'] ) : ?>
				<p>
					<?php
					if ( $state['expires'] ) {
						/* translators: %s: date. */
						echo esc_html( sprintf( $state['updates'] ? __( 'Updates and support until %s.', 'dara-core' ) : __( 'Updates and support ended on %s. Renew to get new versions; all features keep working.', 'dara-core' ), wp_date( get_option( 'date_format' ), $state['expires'] ) ) );
					} else {
						esc_html_e( 'Lifetime updates and support.', 'dara-core' );
					}
					?>
				</p>
			<?php elseif ( ! dara_license_is_local() ) : ?>
				<p>
					<?php
					$trial = dara_license_trial_days();
					echo esc_html(
						$trial
							/* translators: %d: days. */
							? sprintf( _n( 'Trial: all features work for %d more day.', 'Trial: all features work for %d more days.', $trial, 'dara-core' ), $trial )
							: __( 'Dara Pro (blocks, demo import, mortgage calculator, comparison, Google Maps) and updates need an active license.', 'dara-core' )
					);
					?>
				</p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'dara_license' ); ?>
				<input type="hidden" name="action" value="dara_license">
				<?php if ( $data['key'] && 'valid' === $state['status'] ) : ?>
					<p><code style="font-size:14px"><?php echo esc_html( substr( $data['key'], 0, 10 ) . str_repeat( '•', 10 ) . substr( $data['key'], -5 ) ); ?></code></p>
					<p>
						<button class="button" name="do" value="check"><?php esc_html_e( 'Check now', 'dara-core' ); ?></button>
						<button class="button button-primary" name="do" value="pro"><?php echo esc_html( function_exists( 'dara_pro_active' ) && dara_pro_active() ? __( 'Reinstall Dara Pro', 'dara-core' ) : __( 'Install Dara Pro', 'dara-core' ) ); ?></button>
						<button class="button button-link-delete" name="do" value="deactivate" onclick="return confirm('<?php echo esc_js( __( 'Deactivate the license on this site? You can then use it on another domain.', 'dara-core' ) ); ?>')"><?php esc_html_e( 'Deactivate on this site', 'dara-core' ); ?></button>
					</p>
				<?php else : ?>
					<p><label for="dara_license_key"><strong><?php esc_html_e( 'License key', 'dara-core' ); ?></strong></label></p>
					<p><input id="dara_license_key" name="license_key" class="regular-text code" dir="ltr" placeholder="DARA-XXXXX-XXXXX-XXXXX-XXXXX" value="<?php echo esc_attr( $data['key'] ); ?>" required></p>
					<p><button class="button button-primary" name="do" value="activate"><?php esc_html_e( 'Activate', 'dara-core' ); ?></button>
					<a class="button" href="<?php echo esc_url( $e['store'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Buy a license', 'dara-core' ); ?></a></p>
					<p class="description"><?php esc_html_e( 'Your key is in your purchase email. One key works on one live domain; local and staging sites are free.', 'dara-core' ); ?></p>
				<?php endif; ?>
			</form>
		</div>
	</div>
	<?php
}

/**
 * Activate / check / deactivate.
 */
function dara_license_handle() {
	check_admin_referer( 'dara_license' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'dara-core' ) );
	}
	$do   = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : '';
	$data = dara_license_data();
	$msg  = '';
	if ( 'activate' === $do ) {
		$key    = isset( $_POST['license_key'] ) ? strtoupper( preg_replace( '/[^A-Za-z0-9-]/', '', wp_unslash( $_POST['license_key'] ) ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$result = dara_license_call( $key, true );
		$msg    = is_wp_error( $result ) ? $result->get_error_message() : __( 'License activated. Thank you!', 'dara-core' );
		if ( ! is_wp_error( $result ) && current_user_can( 'install_plugins' ) ) {
			$pro  = dara_license_install_pro();
			$msg .= ' ' . ( is_wp_error( $pro ) ? $pro->get_error_message() : __( 'Dara Pro is installed and active.', 'dara-core' ) );
		}
	} elseif ( 'pro' === $do && current_user_can( 'install_plugins' ) ) {
		$pro = dara_license_install_pro();
		$msg = is_wp_error( $pro ) ? $pro->get_error_message() : __( 'Dara Pro is installed and active.', 'dara-core' );
	} elseif ( 'check' === $do ) {
		$result = dara_license_call( $data['key'], false );
		$msg    = is_wp_error( $result ) ? $result->get_error_message() : __( 'License is active.', 'dara-core' );
	} elseif ( 'deactivate' === $do ) {
		dara_license_request(
			'deactivate',
			array(
				'license_key' => $data['key'],
				'domain'      => dara_license_domain(),
			)
		);
		delete_option( 'dara_license' );
		delete_site_transient( 'dara_updates' );
		$msg = __( 'License deactivated on this site.', 'dara-core' );
	}
	wp_safe_redirect( add_query_arg( 'dara_msg', rawurlencode( $msg ), admin_url( 'edit.php?post_type=dara_property&page=dara-license' ) ) );
	exit;
}
add_action( 'admin_post_dara_license', 'dara_license_handle' );

/**
 * Admin reminder on unlicensed live sites.
 */
function dara_license_notice() {
	if ( ! dara_license_enabled() || dara_core_licensed() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'dara_property_page_dara-license' === $screen->id ) {
		return;
	}
	$trial = dara_license_trial_days();
	$text  = $trial
		/* translators: %d: days. */
		? sprintf( _n( 'Activate your Dara license. Premium features stop in %d day.', 'Activate your Dara license. Premium features stop in %d days.', $trial, 'dara-core' ), $trial )
		: __( 'Dara is not activated on this domain: premium features and updates are locked, and visitors see an "unlicensed copy" notice.', 'dara-core' );
	printf(
		'<div class="notice notice-%1$s"><p>%2$s <a class="button button-primary" href="%3$s">%4$s</a></p></div>',
		$trial ? 'warning' : 'error',
		esc_html( $text ),
		esc_url( admin_url( 'edit.php?post_type=dara_property&page=dara-license' ) ),
		esc_html__( 'Activate license', 'dara-core' )
	);
}
add_action( 'admin_notices', 'dara_license_notice' );

/**
 * Visitor notice on unlicensed live sites after the trial.
 */
function dara_license_footer_notice() {
	if ( ! dara_license_enabled() || dara_core_premium() || is_admin() ) {
		return;
	}
	$e = dara_core_edition();
	printf(
		'<style>body{padding-bottom:44px}</style><div style="position:fixed;z-index:9999;inset-inline:0;bottom:0;padding:10px 16px;background:#b32d2e;color:#fff;text-align:center;font:600 14px/1.5 system-ui,sans-serif">%1$s <a href="%2$s" style="color:#fff;text-decoration:underline" rel="nofollow">%3$s</a></div>',
		esc_html__( 'This website uses an unlicensed copy of the Dara theme.', 'dara-core' ),
		esc_url( $e['store'] ),
		esc_html__( 'Get a license', 'dara-core' )
	);
}
add_action( 'wp_footer', 'dara_license_footer_notice', 100 );

/* ---------- Updates from the license server ---------- */

/**
 * Available updates (cached 12 hours).
 *
 * @param bool $force Skip the cache.
 * @return array package => info
 */
function dara_license_updates( $force = false ) {
	$cached = get_site_transient( 'dara_updates' );
	if ( ! $force && is_array( $cached ) ) {
		return $cached;
	}
	$theme = wp_get_theme( 'dara' );
	$data  = dara_license_request(
		'update',
		array(
			'license_key' => dara_license_data()['key'],
			'domain'      => dara_license_domain(),
			'packages'    => array(
				'dara'      => $theme->exists() ? $theme->get( 'Version' ) : '0',
				'dara-core' => DARA_CORE_VERSION,
				'dara-pro'  => dara_license_pro_version(),
			),
		)
	);
	$out   = is_wp_error( $data ) || empty( $data['packages'] ) ? array() : (array) $data['packages'];
	set_site_transient( 'dara_updates', $out, is_wp_error( $data ) ? HOUR_IN_SECONDS : 12 * HOUR_IN_SECONDS );
	return $out;
}

/**
 * Force a fresh check from Dashboard → Updates → "Check again".
 *
 * @return bool
 */
function dara_license_force_check() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $_GET['force-check'] ) && current_user_can( 'update_core' );
}

/**
 * Theme update.
 *
 * @param object $transient Update transient.
 * @return object
 */
function dara_license_theme_update( $transient ) {
	if ( ! is_object( $transient ) || ! dara_license_enabled() ) {
		return $transient;
	}
	$u = dara_license_updates( dara_license_force_check() );
	if ( ! empty( $u['dara'] ) ) {
		$transient->response['dara'] = array(
			'theme'        => 'dara',
			'new_version'  => $u['dara']['version'],
			'url'          => dara_core_edition()['store'],
			'package'      => $u['dara']['package'],
			'requires'     => $u['dara']['requires'],
			'requires_php' => $u['dara']['requires_php'],
		);
	}
	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'dara_license_theme_update' );

/**
 * Plugin update.
 *
 * @param object $transient Update transient.
 * @return object
 */
function dara_license_plugin_update( $transient ) {
	if ( ! is_object( $transient ) || ! dara_license_enabled() ) {
		return $transient;
	}
	$u     = dara_license_updates( dara_license_force_check() );
	$files = array(
		'dara-core' => plugin_basename( DARA_CORE_FILE ),
		'dara-pro'  => 'dara-pro/dara-pro.php',
	);
	foreach ( $files as $slug => $file ) {
		if ( empty( $u[ $slug ] ) || ( 'dara-pro' === $slug && '0' === dara_license_pro_version() ) ) {
			continue;
		}
		$transient->response[ $file ] = (object) array(
			'slug'         => $slug,
			'plugin'       => $file,
			'new_version'  => $u[ $slug ]['version'],
			'url'          => dara_core_edition()['store'],
			'package'      => $u[ $slug ]['package'],
			'tested'       => $u[ $slug ]['tested'],
			'requires_php' => $u[ $slug ]['requires_php'],
		);
	}
	return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'dara_license_plugin_update' );

/**
 * "View details" popup for the plugin update.
 *
 * @param false|object $result Result.
 * @param string       $action Action.
 * @param object       $args   Args.
 * @return false|object
 */
function dara_license_plugin_info( $result, $action, $args ) {
	if ( 'plugin_information' !== $action || empty( $args->slug ) || 'dara-core' !== $args->slug || ! dara_license_enabled() ) {
		return $result;
	}
	$u = dara_license_updates();
	if ( empty( $u['dara-core'] ) ) {
		return $result;
	}
	return (object) array(
		'name'          => 'Dara Core',
		'slug'          => 'dara-core',
		'version'       => $u['dara-core']['version'],
		'author'        => 'Mansoura Host',
		'requires'      => $u['dara-core']['requires'],
		'tested'        => $u['dara-core']['tested'],
		'requires_php'  => $u['dara-core']['requires_php'],
		'last_updated'  => $u['dara-core']['date'],
		'download_link' => $u['dara-core']['package'],
		'sections'      => array( 'changelog' => $u['dara-core']['changelog'] ? $u['dara-core']['changelog'] : '—' ),
	);
}
add_filter( 'plugins_api', 'dara_license_plugin_info', 10, 3 );

/**
 * Explain why an update has no download button.
 *
 * @param array  $plugin_data Plugin data.
 * @param object $response    Update response.
 */
function dara_license_update_row( $plugin_data, $response ) {
	if ( empty( $response->package ) ) {
		echo ' <strong>' . esc_html__( 'Activate a license with active updates to install this update.', 'dara-core' ) . '</strong>';
	}
}
add_action( 'in_plugin_update_message-dara-core/dara-core.php', 'dara_license_update_row', 10, 2 );
add_action( 'in_plugin_update_message-dara-pro/dara-pro.php', 'dara_license_update_row', 10, 2 );

/* ---------- Dara Pro: delivered by the license server ---------- */

/**
 * Installed Dara Pro version ('0' when not installed).
 *
 * @return string
 */
function dara_license_pro_version() {
	if ( defined( 'DARA_PRO_VERSION' ) ) {
		return DARA_PRO_VERSION;
	}
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all = get_plugins();
	return isset( $all['dara-pro/dara-pro.php'] ) ? $all['dara-pro/dara-pro.php']['Version'] : '0';
}

/**
 * Download (or re-download) Dara Pro for this license and activate it.
 *
 * Each download is built for this license, so Pro copied from another site does not work here.
 *
 * @return true|WP_Error
 */
function dara_license_install_pro() {
	$data = dara_license_request(
		'update',
		array(
			'license_key' => dara_license_data()['key'],
			'domain'      => dara_license_domain(),
			'packages'    => array( 'dara-pro' => '0' ),
		)
	);
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	$package = isset( $data['packages']['dara-pro']['package'] ) ? $data['packages']['dara-pro']['package'] : '';
	if ( ! $package ) {
		return new WP_Error( 'dara_pro_unavailable', __( 'Dara Pro is not available for this license yet. Contact support.', 'dara-core' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( ! WP_Filesystem() ) {
		return new WP_Error( 'dara_pro_fs', __( 'WordPress cannot write files on this server. Check the file permissions.', 'dara-core' ) );
	}
	$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
	$result   = $upgrader->install( $package, array( 'overwrite_package' => true ) );
	if ( ! $result || is_wp_error( $result ) ) {
		return is_wp_error( $result ) ? $result : new WP_Error( 'dara_pro_install', __( 'Dara Pro could not be installed.', 'dara-core' ) );
	}
	wp_clean_plugins_cache();
	$active = activate_plugin( 'dara-pro/dara-pro.php' );
	return is_wp_error( $active ) ? $active : true;
}
