<?php
/**
 * Mortgage calculator, comparison page and the Pro script.
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Second, independent check: verifies the manifest signature, the hashes of
 * the guard and this file, and the license token, without using guard.php.
 *
 * @return bool
 */
function dara_pro_ok_f() {
	static $ok = null;
	if ( null !== $ok ) {
		return $ok;
	}
	$ed = dara_pro_edition();
	if ( 'direct' !== $ed['edition'] || '' === $ed['pubkey'] ) {
		$ok = true;
		return $ok;
	}
	$ok   = false;
	$pk   = base64_decode( $ed['pubkey'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$mf   = DARA_PRO_DIR . 'manifest.json';
	$body = is_readable( $mf ) ? (string) file_get_contents( $mf ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$sig  = is_readable( DARA_PRO_DIR . 'manifest.sig' ) ? base64_decode( trim( (string) file_get_contents( DARA_PRO_DIR . 'manifest.sig' ) ) ) : ''; // phpcs:ignore
	if ( '' === $body || 64 !== strlen( $sig ) || 32 !== strlen( $pk ) || ! sodium_crypto_sign_verify_detached( $sig, $body, $pk ) ) {
		return $ok;
	}
	$m = json_decode( $body, true );
	foreach ( array( 'includes/guard.php', 'includes/features.php', 'includes/build.php', 'dara-pro.php' ) as $f ) {
		if ( empty( $m['files'][ $f ] ) || hash_file( 'sha256', DARA_PRO_DIR . $f ) !== $m['files'][ $f ] ) {
			return $ok;
		}
	}
	$lic = (array) get_option( 'dara_license', array() );
	$raw = isset( $lic['payload'] ) ? base64_decode( (string) $lic['payload'] ) : ''; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	$ls  = isset( $lic['signature'] ) ? base64_decode( (string) $lic['signature'] ) : ''; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	if ( '' === $raw || 64 !== strlen( $ls ) || ! sodium_crypto_sign_verify_detached( $ls, $raw, $pk ) ) {
		return $ok;
	}
	$tok  = json_decode( $raw, true );
	$host = preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
	$ok   = is_array( $tok ) && 'valid' === $tok['s'] && $host === $tok['d'] && $m['k'] === $tok['k'];
	return $ok;
}

/**
 * Pro script (runs after the theme script).
 */
function dara_pro_assets() {
	if ( ! dara_pro_active() || ! dara_pro_ok_f() || ! wp_script_is( 'dara', 'registered' ) ) {
		return;
	}
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$rel = file_exists( DARA_PRO_DIR . "assets/js/pro{$min}.js" ) ? "assets/js/pro{$min}.js" : 'assets/js/pro.js';
	wp_enqueue_script(
		'dara-pro',
		DARA_PRO_URL . $rel,
		array( 'dara' ),
		DARA_PRO_VERSION . '-' . dara_pro_build()['b'],
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'dara_pro_assets', 20 );

if ( ! function_exists( 'dara_mortgage_calc' ) ) {
	/**
	 * Mortgage calculation (mirrors the JS in pro.js).
	 *
	 * @param float  $price  Property price.
	 * @param float  $down   Down payment percent.
	 * @param int    $years  Term in years.
	 * @param float  $rate   Annual profit rate percent.
	 * @param string $method amortized|flat.
	 * @return array monthly, loan, down, profit, total
	 */
	function dara_mortgage_calc( $price, $down, $years, $rate, $method = 'amortized' ) {
		$price = max( 0, (float) $price );
		$downv = $price * min( 100, max( 0, (float) $down ) ) / 100;
		$loan  = $price - $downv;
		$n     = max( 1, (int) $years ) * 12;
		$r     = max( 0, (float) $rate ) / 100;
		if ( 'flat' === $method ) {
			$profit  = $loan * $r * ( $n / 12 );
			$monthly = ( $loan + $profit ) / $n;
		} else {
			$m       = $r / 12;
			$monthly = $m > 0 ? $loan * $m / ( 1 - pow( 1 + $m, -$n ) ) : $loan / $n;
			$profit  = $monthly * $n - $loan;
		}
		return array(
			'monthly' => $monthly,
			'loan'    => $loan,
			'down'    => $downv,
			'profit'  => max( 0, $profit ),
			'total'   => $downv + $loan + max( 0, $profit ),
		);
	}
}

/**
 * Render the mortgage calculator.
 *
 * @param array $args { price, title, apply_url }.
 */
function dara_pro_mortgage( $args = array() ) {
	if ( ! function_exists( 'dara_mod' ) || ! dara_pro_active() || ! dara_pro_ok_f() ) {
		return;
	}
	// Second, independent check: the build ID must match the signed manifest.
	$manifest = dara_pro_manifest();
	if ( dara_pro_guarded() && ( ! $manifest || ! hash_equals( (string) $manifest['b'], (string) dara_pro_build()['b'] ) ) ) {
		return;
	}
	include DARA_PRO_DIR . 'templates/mortgage.php';
}

/**
 * Render the comparison table (inside the theme's Compare template).
 */
function dara_pro_compare_page() {
	$t = dara_pro_token();
	if ( ! function_exists( 'dara_mod' ) || ! dara_pro_active() || ! dara_pro_ok_f() || ( dara_pro_guarded() && ( ! $t || dara_pro_host() !== $t['d'] ) ) ) {
		echo '<div class="container section section--top-0"><p class="empty">' . esc_html__( 'Property comparison needs an active Dara Pro license.', 'dara-pro' ) . '</p></div>';
		return;
	}
	include DARA_PRO_DIR . 'templates/compare.php';
}
