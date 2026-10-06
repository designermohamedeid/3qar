<?php
/**
 * Dara Pro guard: its own license verification and file integrity check.
 *
 * Independent from Dara Core's license code on purpose: editing one file
 * is not enough to unlock Pro. Every Pro download from the license server
 * carries a signed manifest (file hashes, build ID, license hash).
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds without a public key (development) or marketplace builds skip the checks.
 *
 * @return bool
 */
function dara_pro_guarded() {
	$e = dara_pro_edition();
	return 'direct' === $e['edition'] && '' !== $e['pubkey'];
}

/**
 * Verify an Ed25519 signature with the build's public key.
 *
 * @param string $message   Message.
 * @param string $signature Base64 signature.
 * @return bool
 */
function dara_pro_sig_ok( $message, $signature ) {
	$e = dara_pro_edition();
	// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$sig = base64_decode( (string) $signature, true );
	$pub = base64_decode( $e['pubkey'], true );
	// phpcs:enable
	if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) || ! $sig || ! $pub || SODIUM_CRYPTO_SIGN_BYTES !== strlen( $sig ) || SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES !== strlen( $pub ) ) {
		return false;
	}
	try {
		return sodium_crypto_sign_verify_detached( $sig, (string) $message, $pub );
	} catch ( Exception $ex ) {
		return false;
	}
}

/**
 * This site's host (www removed).
 *
 * @return string
 */
function dara_pro_host() {
	return preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
}

/**
 * Verified license token for this domain, or null.
 *
 * @return array|null
 */
function dara_pro_token() {
	static $memo = false;
	if ( false !== $memo ) {
		return $memo;
	}
	$memo = null;
	$lic  = (array) get_option( 'dara_license', array() );
	if ( empty( $lic['payload'] ) || empty( $lic['key'] ) ) {
		return $memo;
	}
	$json = base64_decode( (string) $lic['payload'], true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	if ( false === $json || ! dara_pro_sig_ok( $json, isset( $lic['signature'] ) ? $lic['signature'] : '' ) ) {
		return $memo;
	}
	$t = json_decode( $json, true );
	if ( ! is_array( $t ) || 'valid' !== ( isset( $t['s'] ) ? $t['s'] : '' ) || dara_pro_host() !== ( isset( $t['d'] ) ? $t['d'] : '' ) ) {
		return $memo;
	}
	if ( substr( hash( 'sha256', (string) $lic['key'] ), 0, 16 ) !== $t['k'] || time() > (int) $t['x'] + 7 * DAY_IN_SECONDS ) {
		return $memo;
	}
	$memo = $t;
	return $memo;
}

/**
 * Build information written by the license server.
 *
 * @return array
 */
function dara_pro_build() {
	$b = include DARA_PRO_DIR . 'includes/build.php';
	return is_array( $b ) ? $b : array( 'b' => '' );
}

/**
 * Signed manifest of this download, or null.
 *
 * @return array|null
 */
function dara_pro_manifest() {
	$file = DARA_PRO_DIR . 'manifest.json';
	$sig  = DARA_PRO_DIR . 'manifest.sig';
	if ( ! is_readable( $file ) || ! is_readable( $sig ) ) {
		return null;
	}
	$json = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! dara_pro_sig_ok( $json, trim( (string) file_get_contents( $sig ) ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return null;
	}
	$m = json_decode( $json, true );
	return is_array( $m ) && ! empty( $m['files'] ) ? $m : null;
}

/**
 * Files are exactly as the license server signed them (cached for a day per file set).
 *
 * @return bool
 */
function dara_pro_intact() {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$m = dara_pro_manifest();
	if ( ! $m || dara_pro_build()['b'] !== $m['b'] ) {
		$memo = false;
		return $memo;
	}
	$stamp = '';
	foreach ( array_keys( $m['files'] ) as $rel ) {
		$path   = DARA_PRO_DIR . $rel;
		$stamp .= $rel . ( file_exists( $path ) ? filemtime( $path ) . filesize( $path ) : 'x' );
	}
	$cache_key = 'dara_pro_i_' . md5( $stamp . $m['b'] );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		$memo = 'ok' === $cached;
		return $memo;
	}
	$memo = true;
	foreach ( $m['files'] as $rel => $hash ) {
		$path = DARA_PRO_DIR . $rel;
		if ( false !== strpos( $rel, '..' ) || ! is_readable( $path ) || ! hash_equals( (string) $hash, hash_file( 'sha256', $path ) ) ) {
			$memo = false;
			break;
		}
	}
	set_transient( $cache_key, $memo ? 'ok' : 'bad', DAY_IN_SECONDS );
	return $memo;
}

/**
 * Pro features are allowed on this site.
 *
 * @return bool
 */
function dara_pro_active() {
	if ( ! dara_pro_guarded() ) {
		return true;
	}
	$t = dara_pro_token();
	$m = $t ? dara_pro_manifest() : null;
	return $t && $m && dara_pro_intact() && $m['k'] === $t['k'];
}

/**
 * Admin notice when Pro is installed but locked.
 */
function dara_pro_locked_notice() {
	if ( dara_pro_active() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$why = ! dara_pro_token()
		? __( 'Dara Pro is locked: activate your license in Properties → License.', 'dara-pro' )
		: __( 'Dara Pro files were changed or are not from your license. Reinstall Dara Pro from Properties → License.', 'dara-pro' );
	echo '<div class="notice notice-error"><p>' . esc_html( $why ) . '</p></div>';
}
add_action( 'admin_notices', 'dara_pro_locked_notice' );
