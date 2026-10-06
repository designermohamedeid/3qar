<?php
/**
 * REST API: /wp-json/dls/v1/...
 *
 * Client routes (activate, check, deactivate, update, download) are public and rate limited.
 * Admin routes (licenses) need the X-DLS-Secret header (for WHMCS or other shops).
 *
 * @package DaraLicenseServer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register routes.
 */
function dls_register_routes() {
	$public = '__return_true';
	register_rest_route(
		'dls/v1',
		'/activate',
		array(
			'methods'             => 'POST',
			'callback'            => 'dls_api_activate',
			'permission_callback' => $public,
		)
	);
	register_rest_route(
		'dls/v1',
		'/check',
		array(
			'methods'             => 'POST',
			'callback'            => 'dls_api_check',
			'permission_callback' => $public,
		)
	);
	register_rest_route(
		'dls/v1',
		'/deactivate',
		array(
			'methods'             => 'POST',
			'callback'            => 'dls_api_deactivate',
			'permission_callback' => $public,
		)
	);
	register_rest_route(
		'dls/v1',
		'/update',
		array(
			'methods'             => 'POST',
			'callback'            => 'dls_api_update',
			'permission_callback' => $public,
		)
	);
	register_rest_route(
		'dls/v1',
		'/download',
		array(
			'methods'             => 'GET',
			'callback'            => 'dls_api_download',
			'permission_callback' => $public,
		)
	);
	register_rest_route(
		'dls/v1',
		'/licenses',
		array(
			array(
				'methods'             => 'POST',
				'callback'            => 'dls_api_create_license',
				'permission_callback' => 'dls_api_secret_ok',
			),
			array(
				'methods'             => 'GET',
				'callback'            => 'dls_api_get_license',
				'permission_callback' => 'dls_api_secret_ok',
			),
		)
	);
	register_rest_route(
		'dls/v1',
		'/licenses/reset',
		array(
			'methods'             => 'POST',
			'callback'            => 'dls_api_reset_sites',
			'permission_callback' => 'dls_api_secret_ok',
		)
	);
	register_rest_route(
		'dls/v1',
		'/licenses/status',
		array(
			'methods'             => 'POST',
			'callback'            => 'dls_api_set_status',
			'permission_callback' => 'dls_api_secret_ok',
		)
	);
}
add_action( 'rest_api_init', 'dls_register_routes' );

/**
 * Admin API secret check (constant-time).
 *
 * @param WP_REST_Request $req Request.
 * @return bool
 */
function dls_api_secret_ok( $req ) {
	$given = (string) $req->get_header( 'x_dls_secret' );
	return '' !== $given && hash_equals( (string) get_option( 'dls_api_secret' ), $given );
}

/**
 * Common input: license + normalized domain.
 *
 * @param WP_REST_Request $req Request.
 * @return array [ license|null, domain ]
 */
function dls_api_input( $req ) {
	return array(
		dls_get_license( (string) $req->get_param( 'license_key' ) ),
		dls_normalize_domain( (string) $req->get_param( 'domain' ) ),
	);
}

/**
 * Error response that still carries a signed "invalid" token, so clients can trust it.
 *
 * @param string     $code    Error code.
 * @param string     $message Message.
 * @param array|null $license License.
 * @param string     $domain  Domain.
 * @param int        $status  HTTP status.
 * @return WP_REST_Response
 */
function dls_api_error( $code, $message, $license, $domain, $status = 403 ) {
	return new WP_REST_Response(
		array_merge(
			array(
				'success' => false,
				'code'    => $code,
				'message' => $message,
			),
			$domain ? dls_token( $license, $domain, 'invalid', $code ) : array()
		),
		$status
	);
}

/**
 * Messages for license problems.
 *
 * @param string $code Code.
 * @return string
 */
function dls_problem_message( $code ) {
	$messages = array(
		'invalid_key' => 'This license key does not exist.',
		'suspended'   => 'This license is suspended. Please contact support.',
		'expired'     => 'Updates for this license have expired. Renew it to get new versions.',
		'bad_domain'  => 'Invalid domain.',
		'bad_product' => 'This key is for a different product.',
	);
	return isset( $messages[ $code ] ) ? $messages[ $code ] : 'License error.';
}

/**
 * POST /activate.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_activate( $req ) {
	return dls_api_activate_or_check( $req, true );
}

/**
 * POST /check (does not take a new site slot).
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_check( $req ) {
	return dls_api_activate_or_check( $req, false );
}

/**
 * Shared activate / check logic.
 *
 * @param WP_REST_Request $req    Request.
 * @param bool            $create Create activation.
 * @return WP_REST_Response
 */
function dls_api_activate_or_check( $req, $create ) {
	if ( ! dls_rate_ok( $create ? 'act' : 'chk', $create ? 10 : 30 ) ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'rate_limited',
				'message' => 'Too many requests. Try again in a minute.',
			),
			429
		);
	}
	list( $license, $domain ) = dls_api_input( $req );
	if ( ! $domain ) {
		return dls_api_error( 'bad_domain', dls_problem_message( 'bad_domain' ), null, '', 400 );
	}
	$problem = dls_license_problem( $license );
	if ( ! $problem && $req->get_param( 'product' ) && sanitize_key( $req->get_param( 'product' ) ) !== $license['product'] ) {
		$problem = 'bad_product';
	}
	if ( $problem ) {
		return dls_api_error( $problem, dls_problem_message( $problem ), $license, $domain );
	}
	$result = dls_activate_domain( $license, $domain, (string) $req->get_param( 'version' ), $create );
	if ( is_wp_error( $result ) ) {
		return dls_api_error( $result->get_error_code(), $result->get_error_message(), $license, $domain );
	}
	return new WP_REST_Response(
		array_merge(
			array(
				'success'  => true,
				'customer' => $license['customer_name'],
				'expires'  => $license['expires_at'],
			),
			dls_token( $license, $domain )
		),
		200
	);
}

/**
 * POST /deactivate.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_deactivate( $req ) {
	if ( ! dls_rate_ok( 'deact', 10 ) ) {
		return new WP_REST_Response( array( 'success' => false ), 429 );
	}
	list( $license, $domain ) = dls_api_input( $req );
	if ( $license && $domain ) {
		dls_remove_activation( $license['id'], $domain );
	}
	return new WP_REST_Response( array( 'success' => true ), 200 );
}

/**
 * POST /update: newer versions, with download links only for licensed domains.
 *
 * Body: license_key, domain, packages { slug: installed version }.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_update( $req ) {
	if ( ! dls_rate_ok( 'upd', 30 ) ) {
		return new WP_REST_Response( array(), 429 );
	}
	list( $license, $domain ) = dls_api_input( $req );
	$licensed                 = $license && $domain && ! dls_license_problem( $license, true ) && ! is_wp_error( dls_activate_domain( $license, $domain, '', false ) );
	$allowed                  = $licensed ? dls_products()[ $license['product'] ]['packages'] : array();
	$installed                = (array) $req->get_param( 'packages' );
	$out                      = array();
	foreach ( dls_releases() as $package => $rel ) {
		if ( ! isset( $installed[ $package ] ) || version_compare( $rel['version'], (string) $installed[ $package ], '<=' ) ) {
			continue;
		}
		$out[ $package ] = array(
			'version'      => $rel['version'],
			'changelog'    => $rel['changelog'],
			'requires'     => $rel['requires'],
			'tested'       => $rel['tested'],
			'requires_php' => $rel['requires_php'],
			'date'         => $rel['date'],
			'package'      => in_array( $package, $allowed, true ) ? dls_download_url( $license, $domain, $package ) : '',
		);
	}
	return new WP_REST_Response(
		array(
			'licensed' => $licensed,
			'packages' => $out,
		),
		200
	);
}

/**
 * GET /download: stream a release zip for a valid signed link.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response|void
 */
function dls_api_download( $req ) {
	$id      = (int) $req->get_param( 'l' );
	$package = sanitize_key( (string) $req->get_param( 'pkg' ) );
	$domain  = dls_normalize_domain( rawurldecode( (string) $req->get_param( 'd' ) ) );
	$exp     = (int) $req->get_param( 'exp' );
	$sig     = (string) $req->get_param( 'sig' );
	$expect  = hash_hmac( 'sha256', $id . '|' . $package . '|' . $domain . '|' . $exp, get_option( 'dls_hmac_secret' ) );
	if ( $exp < time() || ! hash_equals( $expect, $sig ) ) {
		return new WP_REST_Response( array( 'message' => 'Link expired or invalid.' ), 403 );
	}
	$license = dls_get_license( $id );
	if ( dls_license_problem( $license, true ) || ! in_array( $package, dls_products()[ $license['product'] ]['packages'], true ) ) {
		return new WP_REST_Response( array( 'message' => 'License not valid.' ), 403 );
	}
	$releases = dls_releases();
	$file     = isset( $releases[ $package ]['file'] ) ? dls_release_dir() . '/' . $releases[ $package ]['file'] : '';
	if ( ! $file || ! is_readable( $file ) ) {
		return new WP_REST_Response( array( 'message' => 'File not found.' ), 404 );
	}
	$built = '';
	if ( in_array( $package, dls_watermarked_packages(), true ) ) {
		$built = dls_build_watermarked( $file, $package, $license, $domain, $releases[ $package ]['version'] );
		if ( is_wp_error( $built ) ) {
			return new WP_REST_Response( array( 'message' => $built->get_error_message() ), 500 );
		}
		$file = $built;
	}
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . $package . '-' . $releases[ $package ]['version'] . '.zip"' );
	header( 'Content-Length: ' . filesize( $file ) );
	readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	if ( $built ) {
		wp_delete_file( $built );
	}
	exit;
}

/**
 * POST /licenses: create a license (shops / WHMCS).
 *
 * Body: product, customer_name, customer_email, max_sites, expires_at, order_ref, source.
 * An existing order_ref returns the same license (safe to retry).
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_create_license( $req ) {
	global $wpdb;
	$ref = sanitize_text_field( (string) $req->get_param( 'order_ref' ) );
	if ( $ref ) {
		$t = dls_tables()->licenses;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE order_ref = %s", $ref ), ARRAY_A );
		if ( $existing ) {
			return new WP_REST_Response( dls_public_license( $existing ), 200 );
		}
	}
	$row = dls_create_license( $req->get_params() );
	if ( is_wp_error( $row ) ) {
		return new WP_REST_Response( array( 'message' => $row->get_error_message() ), 400 );
	}
	return new WP_REST_Response( dls_public_license( $row ), 201 );
}

/**
 * GET /licenses?license_key= or ?order_ref=.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_get_license( $req ) {
	$license = dls_find_license( $req );
	return $license ? new WP_REST_Response( dls_public_license( $license ), 200 ) : new WP_REST_Response( array( 'message' => 'Not found.' ), 404 );
}

/**
 * POST /licenses/status: suspend, reactivate or renew (by license_key or order_ref).
 *
 * Body: status (active|suspended), expires_at, max_sites.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_set_status( $req ) {
	$license = dls_find_license( $req );
	if ( ! $license ) {
		return new WP_REST_Response( array( 'message' => 'Not found.' ), 404 );
	}
	$fields = array();
	if ( in_array( $req->get_param( 'status' ), array( 'active', 'suspended' ), true ) ) {
		$fields['status'] = $req->get_param( 'status' );
	}
	if ( null !== $req->get_param( 'expires_at' ) ) {
		$fields['expires_at'] = $req->get_param( 'expires_at' ) ? gmdate( 'Y-m-d H:i:s', strtotime( $req->get_param( 'expires_at' ) ) ) : null;
	}
	if ( $req->get_param( 'max_sites' ) ) {
		$fields['max_sites'] = max( 1, (int) $req->get_param( 'max_sites' ) );
	}
	dls_update_license( $license['id'], $fields );
	return new WP_REST_Response( dls_public_license( dls_get_license( (int) $license['id'] ) ), 200 );
}

/**
 * POST /licenses/reset: free all domains of a license (support / moving hosts).
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function dls_api_reset_sites( $req ) {
	$license = dls_find_license( $req );
	if ( ! $license ) {
		return new WP_REST_Response( array( 'message' => 'Not found.' ), 404 );
	}
	foreach ( dls_activations( (int) $license['id'] ) as $a ) {
		dls_remove_activation( (int) $license['id'], $a['domain'] );
	}
	return new WP_REST_Response( dls_public_license( $license ), 200 );
}

/**
 * Find a license by license_key or order_ref param.
 *
 * @param WP_REST_Request $req Request.
 * @return array|null
 */
function dls_find_license( $req ) {
	global $wpdb;
	if ( $req->get_param( 'license_key' ) ) {
		return dls_get_license( (string) $req->get_param( 'license_key' ) );
	}
	$ref = sanitize_text_field( (string) $req->get_param( 'order_ref' ) );
	if ( ! $ref ) {
		return null;
	}
	$t = dls_tables()->licenses;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE order_ref = %s", $ref ), ARRAY_A );
}

/**
 * License fields returned to shops.
 *
 * @param array $l License row.
 * @return array
 */
function dls_public_license( $l ) {
	return array(
		'license_key'    => $l['license_key'],
		'product'        => $l['product'],
		'status'         => $l['status'],
		'max_sites'      => (int) $l['max_sites'],
		'expires_at'     => $l['expires_at'],
		'customer_email' => $l['customer_email'],
		'order_ref'      => $l['order_ref'],
		'sites'          => wp_list_pluck( dls_activations( (int) $l['id'] ), 'domain' ),
	);
}
