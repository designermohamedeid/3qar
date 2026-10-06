<?php
/**
 * Storage, keys, signing and releases.
 *
 * @package DaraLicenseServer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Products this server sells: license product => packages it unlocks.
 *
 * @return array
 */
function dls_products() {
	return apply_filters(
		'dls_products',
		array(
			'dara' => array(
				'name'     => 'Dara',
				'packages' => array( 'dara', 'dara-core' ),
			),
		)
	);
}

/**
 * Table names.
 *
 * @return object { licenses, activations }
 */
function dls_tables() {
	global $wpdb;
	return (object) array(
		'licenses'    => $wpdb->prefix . 'dls_licenses',
		'activations' => $wpdb->prefix . 'dls_activations',
	);
}

/**
 * Create tables, signing keys and the private releases folder.
 */
function dls_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t       = dls_tables();
	$charset = $wpdb->get_charset_collate();
	dbDelta(
		"CREATE TABLE {$t->licenses} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			license_key varchar(64) NOT NULL,
			product varchar(40) NOT NULL DEFAULT 'dara',
			customer_name varchar(190) NOT NULL DEFAULT '',
			customer_email varchar(190) NOT NULL DEFAULT '',
			max_sites int(11) NOT NULL DEFAULT 1,
			status varchar(20) NOT NULL DEFAULT 'active',
			expires_at datetime NULL DEFAULT NULL,
			source varchar(60) NOT NULL DEFAULT '',
			order_ref varchar(100) NOT NULL DEFAULT '',
			notes text NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY license_key (license_key),
			KEY customer_email (customer_email)
		) $charset;"
	);
	dbDelta(
		"CREATE TABLE {$t->activations} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			license_id bigint(20) unsigned NOT NULL,
			domain varchar(190) NOT NULL,
			is_local tinyint(1) NOT NULL DEFAULT 0,
			version varchar(20) NOT NULL DEFAULT '',
			activated_at datetime NOT NULL,
			last_check datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY license_domain (license_id,domain),
			KEY domain (domain)
		) $charset;"
	);
	if ( ! get_option( 'dls_sign_secret' ) && ! defined( 'DLS_SIGN_SECRET' ) ) {
		dls_generate_keys();
	}
	if ( ! get_option( 'dls_hmac_secret' ) ) {
		add_option( 'dls_hmac_secret', bin2hex( random_bytes( 32 ) ), '', false );
	}
	if ( ! get_option( 'dls_api_secret' ) ) {
		add_option( 'dls_api_secret', bin2hex( random_bytes( 24 ) ), '', false );
	}
	dls_release_dir();
	update_option( 'dls_db_version', DLS_VERSION );
}

/**
 * Re-run the installer after an update.
 */
function dls_maybe_upgrade() {
	if ( get_option( 'dls_db_version' ) !== DLS_VERSION ) {
		dls_install();
	}
}

/**
 * New Ed25519 key pair (stored base64; the secret is never autoloaded).
 */
function dls_generate_keys() {
	$pair = sodium_crypto_sign_keypair();
	update_option( 'dls_sign_secret', base64_encode( sodium_crypto_sign_secretkey( $pair ) ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	update_option( 'dls_sign_public', base64_encode( sodium_crypto_sign_publickey( $pair ) ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
}

/**
 * Public key (base64) to embed in the client.
 *
 * @return string
 */
function dls_public_key() {
	if ( defined( 'DLS_SIGN_SECRET' ) ) {
		return base64_encode( sodium_crypto_sign_publickey_from_secretkey( base64_decode( DLS_SIGN_SECRET ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}
	return (string) get_option( 'dls_sign_public' );
}

/**
 * Sign a payload.
 *
 * @param array $payload Data.
 * @return array { payload: base64 JSON, signature: base64 }
 */
function dls_sign( array $payload ) {
	$secret = defined( 'DLS_SIGN_SECRET' ) ? DLS_SIGN_SECRET : get_option( 'dls_sign_secret' );
	$json   = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES );
	return array(
		'payload'   => base64_encode( $json ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		'signature' => base64_encode( sodium_crypto_sign_detached( $json, base64_decode( $secret ) ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	);
}

/**
 * Normalize a domain: lowercase host, no scheme, port, path or www.
 *
 * @param string $domain Raw domain or URL.
 * @return string
 */
function dls_normalize_domain( $domain ) {
	$domain = strtolower( trim( (string) $domain ) );
	if ( false !== strpos( $domain, '://' ) ) {
		$domain = (string) wp_parse_url( $domain, PHP_URL_HOST );
	}
	$domain = preg_replace( '/[:\/].*$/', '', $domain );
	$domain = preg_replace( '/^www\./', '', $domain );
	return preg_match( '/^[a-z0-9.-]{1,190}$/', $domain ) ? trim( $domain, '.' ) : '';
}

/**
 * Development and staging hosts do not use a site slot.
 *
 * @param string $domain Normalized domain.
 * @return bool
 */
function dls_is_local( $domain ) {
	if ( 'localhost' === $domain ) {
		return true;
	}
	if ( filter_var( $domain, FILTER_VALIDATE_IP ) ) {
		// Private and reserved IPs (127.0.0.1, 192.168.x.x...) are local; public IPs are not.
		return ! filter_var( $domain, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	}
	return (bool) preg_match( '/(^|\.)(staging|stage|dev|test|local)\.|\.(local|test|localhost|invalid|example)$/', $domain );
}

/**
 * New license key: DARA-XXXXX-XXXXX-XXXXX-XXXXX (no ambiguous characters).
 *
 * @param string $prefix Prefix.
 * @return string
 */
function dls_new_key( $prefix = 'DARA' ) {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$parts = array( strtoupper( $prefix ) );
	for ( $p = 0; $p < 4; $p++ ) {
		$s = '';
		for ( $i = 0; $i < 5; $i++ ) {
			$s .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
		}
		$parts[] = $s;
	}
	return implode( '-', $parts );
}

/**
 * Create a license.
 *
 * @param array $data { product, customer_name, customer_email, max_sites, expires_at, source, order_ref, notes }.
 * @return array|WP_Error License row.
 */
function dls_create_license( array $data ) {
	global $wpdb;
	$product = isset( $data['product'] ) ? sanitize_key( $data['product'] ) : 'dara';
	if ( ! isset( dls_products()[ $product ] ) ) {
		return new WP_Error( 'dls_product', 'Unknown product.' );
	}
	$expires = ! empty( $data['expires_at'] ) ? gmdate( 'Y-m-d H:i:s', strtotime( $data['expires_at'] ) ) : null;
	$row     = array(
		'license_key'    => dls_new_key( 'dara' === $product ? 'DARA' : $product ),
		'product'        => $product,
		'customer_name'  => isset( $data['customer_name'] ) ? sanitize_text_field( $data['customer_name'] ) : '',
		'customer_email' => isset( $data['customer_email'] ) ? sanitize_email( $data['customer_email'] ) : '',
		'max_sites'      => isset( $data['max_sites'] ) ? max( 1, (int) $data['max_sites'] ) : 1,
		'status'         => 'active',
		'expires_at'     => $expires,
		'source'         => isset( $data['source'] ) ? sanitize_key( $data['source'] ) : 'manual',
		'order_ref'      => isset( $data['order_ref'] ) ? sanitize_text_field( $data['order_ref'] ) : '',
		'notes'          => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
		'created_at'     => current_time( 'mysql', true ),
	);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	if ( ! $wpdb->insert( dls_tables()->licenses, $row ) ) {
		return new WP_Error( 'dls_db', 'Could not save the license.' );
	}
	$row['id'] = (int) $wpdb->insert_id;
	do_action( 'dls_license_created', $row );
	return $row;
}

/**
 * Get a license by key or ID.
 *
 * @param string|int $key_or_id License key or numeric ID.
 * @return array|null
 */
function dls_get_license( $key_or_id ) {
	global $wpdb;
	$t = dls_tables()->licenses;
	if ( is_int( $key_or_id ) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE id = %d", $key_or_id ), ARRAY_A );
	}
	$key = strtoupper( preg_replace( '/[^A-Za-z0-9-]/', '', (string) $key_or_id ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE license_key = %s", $key ), ARRAY_A );
}

/**
 * Update license fields.
 *
 * @param int   $id     License ID.
 * @param array $fields Fields.
 */
function dls_update_license( $id, array $fields ) {
	global $wpdb;
	$allowed = array_intersect_key( $fields, array_flip( array( 'customer_name', 'customer_email', 'max_sites', 'status', 'expires_at', 'notes' ) ) );
	if ( $allowed ) {
		$wpdb->update( dls_tables()->licenses, $allowed, array( 'id' => (int) $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
}

/**
 * Activations of a license.
 *
 * @param int $license_id License ID.
 * @return array
 */
function dls_activations( $license_id ) {
	global $wpdb;
	$t = dls_tables()->activations;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE license_id = %d ORDER BY activated_at", $license_id ), ARRAY_A );
}

/**
 * Remove one activation.
 *
 * @param int    $license_id License ID.
 * @param string $domain     Domain.
 */
function dls_remove_activation( $license_id, $domain ) {
	global $wpdb;
	$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		dls_tables()->activations,
		array(
			'license_id' => (int) $license_id,
			'domain'     => $domain,
		)
	);
}

/**
 * Why a license cannot be used right now ('' when it can).
 *
 * Expiry only ends updates: an expired license still activates sites and keeps every feature.
 *
 * @param array|null $license     License row.
 * @param bool       $for_updates Also require an unexpired license.
 * @return string Error code.
 */
function dls_license_problem( $license, $for_updates = false ) {
	if ( ! $license ) {
		return 'invalid_key';
	}
	if ( 'active' !== $license['status'] ) {
		return 'suspended';
	}
	if ( $for_updates && $license['expires_at'] && strtotime( $license['expires_at'] . ' UTC' ) < time() ) {
		return 'expired';
	}
	return '';
}

/**
 * Activate (or refresh) a domain on a license.
 *
 * @param array  $license License row.
 * @param string $domain  Normalized domain.
 * @param string $version Client version.
 * @param bool   $create  Create the activation when missing (false = check only).
 * @return true|WP_Error
 */
function dls_activate_domain( array $license, $domain, $version = '', $create = true ) {
	global $wpdb;
	$t     = dls_tables()->activations;
	$now   = current_time( 'mysql', true );
	$found = null;
	$used  = 0;
	foreach ( dls_activations( $license['id'] ) as $a ) {
		if ( $a['domain'] === $domain ) {
			$found = $a;
		} elseif ( ! $a['is_local'] ) {
			++$used;
		}
	}
	if ( $found ) {
		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$t,
			array(
				'last_check' => $now,
				'version'    => substr( sanitize_text_field( $version ), 0, 20 ),
			),
			array( 'id' => $found['id'] )
		);
		return true;
	}
	if ( ! $create ) {
		return new WP_Error( 'not_activated', 'This domain is not activated on this license.' );
	}
	$local = dls_is_local( $domain );
	if ( ! $local && $used >= (int) $license['max_sites'] ) {
		return new WP_Error( 'site_limit', 'This license is already used on another domain. Deactivate it there first, or contact support.' );
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->insert(
		$t,
		array(
			'license_id'   => (int) $license['id'],
			'domain'       => $domain,
			'is_local'     => $local ? 1 : 0,
			'version'      => substr( sanitize_text_field( $version ), 0, 20 ),
			'activated_at' => $now,
			'last_check'   => $now,
		)
	);
	return true;
}

/**
 * Signed license token for a domain.
 *
 * @param array  $license License row.
 * @param string $domain  Domain.
 * @param string $status  valid|invalid.
 * @param string $reason  Error code when invalid.
 * @return array
 */
function dls_token( $license, $domain, $status = 'valid', $reason = '' ) {
	$days = (int) apply_filters( 'dls_token_days', 14 );
	return dls_sign(
		array(
			'k' => $license ? substr( hash( 'sha256', $license['license_key'] ), 0, 16 ) : '',
			'p' => $license ? $license['product'] : '',
			'd' => $domain,
			's' => $status,
			'r' => $reason,
			'e' => $license && $license['expires_at'] ? strtotime( $license['expires_at'] . ' UTC' ) : 0,
			'i' => time(),
			'x' => time() + $days * DAY_IN_SECONDS,
		)
	);
}

/**
 * Private folder for release zips (deny-all for Apache; random file names for Nginx).
 *
 * @return string
 */
function dls_release_dir() {
	$up  = wp_upload_dir( null, false );
	$dir = trailingslashit( $up['basedir'] ) . 'dls-releases';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $dir . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
	return $dir;
}

/**
 * Latest release of every package.
 *
 * @return array package => { version, file, changelog, requires, tested, requires_php, date }
 */
function dls_releases() {
	return (array) get_option( 'dls_releases', array() );
}

/**
 * Save a release from an uploaded zip.
 *
 * @param string $package   Package slug (dara or dara-core).
 * @param string $version   Version.
 * @param string $tmp_file  Uploaded file path.
 * @param array  $meta      changelog, requires, tested, requires_php.
 * @return true|WP_Error
 */
function dls_add_release( $package, $version, $tmp_file, array $meta = array() ) {
	if ( ! preg_match( '/^[a-z0-9-]+$/', $package ) || ! preg_match( '/^\d+(\.\d+){1,3}$/', $version ) ) {
		return new WP_Error( 'dls_release', 'Invalid package or version.' );
	}
	$name = $package . '-' . $version . '-' . wp_generate_password( 12, false ) . '.zip';
	$dest = dls_release_dir() . '/' . $name;
	if ( ! @copy( $tmp_file, $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return new WP_Error( 'dls_release', 'Could not store the file.' );
	}
	$releases = dls_releases();
	if ( ! empty( $releases[ $package ]['file'] ) && $releases[ $package ]['file'] !== $name ) {
		wp_delete_file( dls_release_dir() . '/' . $releases[ $package ]['file'] );
	}
	$releases[ $package ] = array(
		'version'      => $version,
		'file'         => $name,
		'changelog'    => isset( $meta['changelog'] ) ? wp_kses_post( $meta['changelog'] ) : '',
		'requires'     => isset( $meta['requires'] ) ? sanitize_text_field( $meta['requires'] ) : '6.3',
		'tested'       => isset( $meta['tested'] ) ? sanitize_text_field( $meta['tested'] ) : '',
		'requires_php' => isset( $meta['requires_php'] ) ? sanitize_text_field( $meta['requires_php'] ) : '7.4',
		'date'         => gmdate( 'Y-m-d' ),
	);
	update_option( 'dls_releases', $releases, false );
	return true;
}

/**
 * Short-lived signed download URL for a licensed domain.
 *
 * @param array  $license License row.
 * @param string $domain  Domain.
 * @param string $package Package.
 * @return string
 */
function dls_download_url( $license, $domain, $package ) {
	$exp  = time() + HOUR_IN_SECONDS;
	$data = $license['id'] . '|' . $package . '|' . $domain . '|' . $exp;
	return add_query_arg(
		array(
			'l'   => (int) $license['id'],
			'pkg' => $package,
			'd'   => rawurlencode( $domain ),
			'exp' => $exp,
			'sig' => hash_hmac( 'sha256', $data, get_option( 'dls_hmac_secret' ) ),
		),
		rest_url( 'dls/v1/download' )
	);
}

/**
 * Simple per-IP rate limit.
 *
 * @param string $bucket Name.
 * @param int    $max    Requests per minute.
 * @return bool True when allowed.
 */
function dls_rate_ok( $bucket, $max = 20 ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'dls_rl_' . $bucket . '_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return false;
	}
	set_transient( $key, $n + 1, MINUTE_IN_SECONDS );
	return true;
}
