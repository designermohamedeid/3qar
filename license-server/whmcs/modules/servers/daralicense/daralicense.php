<?php
/**
 * WHMCS server module: Dara License.
 *
 * Install: copy this folder to <whmcs>/modules/servers/daralicense/.
 * Product setup: Products/Services → (your Dara product) → Module Settings → "Dara License",
 * fill the API URL and API secret from the license server (Licenses → Settings) and choose
 * "Automatically setup the product as soon as the first payment is received".
 *
 * Create  → issues a key (saved in the "License Key" field, shown to the client).
 * Suspend / Unsuspend / Terminate → suspends or re-activates the key.
 * Renew   → extends updates and support to the next due date.
 *
 * @package DaraLicenseServer
 */

if ( ! defined( 'WHMCS' ) ) {
	die( 'This file cannot be accessed directly' );
}

/**
 * Module metadata.
 *
 * @return array
 */
function daralicense_MetaData() {
	return array(
		'DisplayName'    => 'Dara License',
		'APIVersion'     => '1.1',
		'RequiresServer' => false,
	);
}

/**
 * Product options.
 *
 * @return array
 */
function daralicense_ConfigOptions() {
	return array(
		'API URL'           => array(
			'Type'        => 'text',
			'Size'        => '60',
			'Default'     => 'https://muhamedeid.com/wp-json/dls/v1/',
			'Description' => 'From the license server: Licenses → Settings → API endpoint',
		),
		'API Secret'        => array(
			'Type'        => 'password',
			'Size'        => '60',
			'Description' => 'Licenses → Settings → Shop API secret',
		),
		'Sites per license' => array(
			'Type'    => 'text',
			'Size'    => '5',
			'Default' => '1',
		),
		'Product'           => array(
			'Type'    => 'text',
			'Size'    => '20',
			'Default' => 'dara',
		),
	);
}

/**
 * Call the license server.
 *
 * @param array  $params Module params.
 * @param string $route  Route.
 * @param array  $body   Body.
 * @param string $method HTTP method.
 * @return array [ http code, decoded body ]
 */
function daralicense_call( array $params, $route, array $body = array(), $method = 'POST' ) {
	$url = rtrim( $params['configoption1'], '/' ) . '/' . $route;
	if ( 'GET' === $method && $body ) {
		$url .= ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $body );
	}
	$ch = curl_init( $url );
	curl_setopt_array(
		$ch,
		array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 20,
			CURLOPT_HTTPHEADER     => array( 'X-DLS-Secret: ' . $params['configoption2'], 'Accept: application/json' ),
			CURLOPT_CUSTOMREQUEST  => $method,
		)
	);
	if ( 'POST' === $method ) {
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $body ) );
	}
	$raw  = curl_exec( $ch );
	$code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$err  = curl_error( $ch );
	curl_close( $ch );
	logModuleCall( 'daralicense', $route, $body, $raw ? $raw : $err, '', array( $params['configoption2'] ) );
	return array( $code, json_decode( (string) $raw, true ) );
}

/**
 * Order reference for a service.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_ref( array $params ) {
	return 'whmcs-' . (int) $params['serviceid'];
}

/**
 * Updates/support end date: the next due date for recurring products, lifetime for one-time.
 *
 * @param array $params Module params.
 * @return string Y-m-d or '' for lifetime.
 */
function daralicense_expiry( array $params ) {
	$due = isset( $params['model'] ) ? (string) $params['model']->nextduedate : '';
	return ( $due && '0000-00-00' !== substr( $due, 0, 10 ) ) ? substr( $due, 0, 10 ) . ' 23:59:59' : '';
}

/**
 * Create: issue the key.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_CreateAccount( array $params ) {
	$c                = $params['clientsdetails'];
	list( $code, $r ) = daralicense_call(
		$params,
		'licenses',
		array(
			'product'        => $params['configoption4'] ? $params['configoption4'] : 'dara',
			'customer_name'  => trim( $c['firstname'] . ' ' . $c['lastname'] ),
			'customer_email' => $c['email'],
			'max_sites'      => max( 1, (int) $params['configoption3'] ),
			'expires_at'     => daralicense_expiry( $params ),
			'order_ref'      => daralicense_ref( $params ),
			'source'         => 'whmcs',
		)
	);
	if ( ( 200 !== $code && 201 !== $code ) || empty( $r['license_key'] ) ) {
		return isset( $r['message'] ) ? $r['message'] : 'License server error (HTTP ' . $code . ')';
	}
	$params['model']->serviceProperties->save( array( 'License Key' => $r['license_key'] ) );
	// Also keep the key in the service Username, so emails can use the standard {$service_username}.
	try {
		\WHMCS\Database\Capsule::table( 'tblhosting' )->where( 'id', (int) $params['serviceid'] )->update( array( 'username' => $r['license_key'] ) );
	} catch ( \Exception $e ) {
		logModuleCall( 'daralicense', 'save username', $params['serviceid'], $e->getMessage() );
	}
	return 'success';
}

/**
 * Set status.
 *
 * @param array  $params Module params.
 * @param string $status active|suspended.
 * @return string
 */
function daralicense_status( array $params, $status ) {
	list( $code, $r ) = daralicense_call(
		$params,
		'licenses/status',
		array(
			'order_ref' => daralicense_ref( $params ),
			'status'    => $status,
		)
	);
	return 200 === $code ? 'success' : ( isset( $r['message'] ) ? $r['message'] : 'License server error (HTTP ' . $code . ')' );
}

/**
 * Suspend.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_SuspendAccount( array $params ) {
	return daralicense_status( $params, 'suspended' );
}

/**
 * Unsuspend.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_UnsuspendAccount( array $params ) {
	return daralicense_status( $params, 'active' );
}

/**
 * Terminate (cancelled / refunded).
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_TerminateAccount( array $params ) {
	return daralicense_status( $params, 'suspended' );
}

/**
 * Renew: extend updates and support to the new due date.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_Renew( array $params ) {
	list( $code, $r ) = daralicense_call(
		$params,
		'licenses/status',
		array(
			'order_ref'  => daralicense_ref( $params ),
			'status'     => 'active',
			'expires_at' => daralicense_expiry( $params ),
		)
	);
	return 200 === $code ? 'success' : ( isset( $r['message'] ) ? $r['message'] : 'License server error (HTTP ' . $code . ')' );
}

/**
 * Admin button: free all domains (customer moved to a new domain).
 *
 * @return array
 */
function daralicense_AdminCustomButtonArray() {
	return array( 'Reset domains' => 'resetDomains' );
}

/**
 * Client button too.
 *
 * @return array
 */
function daralicense_ClientAreaCustomButtonArray() {
	return array( 'Reset domains' => 'resetDomains' );
}

/**
 * Reset domains.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_resetDomains( array $params ) {
	list( $code, $r ) = daralicense_call( $params, 'licenses/reset', array( 'order_ref' => daralicense_ref( $params ) ) );
	return 200 === $code ? 'success' : ( isset( $r['message'] ) ? $r['message'] : 'License server error (HTTP ' . $code . ')' );
}

/**
 * License details from the server.
 *
 * @param array $params Module params.
 * @return array|null
 */
function daralicense_details( array $params ) {
	list( $code, $r ) = daralicense_call( $params, 'licenses', array( 'order_ref' => daralicense_ref( $params ) ), 'GET' );
	return 200 === $code ? $r : null;
}

/**
 * Admin service tab.
 *
 * @param array $params Module params.
 * @return array
 */
function daralicense_AdminServicesTabFields( array $params ) {
	$d = daralicense_details( $params );
	if ( ! $d ) {
		return array( 'License' => 'Not created yet (run Create).' );
	}
	return array(
		'License Key'   => '<code>' . htmlspecialchars( $d['license_key'] ) . '</code>',
		'Status'        => htmlspecialchars( $d['status'] ),
		'Domains'       => $d['sites'] ? htmlspecialchars( implode( ', ', $d['sites'] ) ) : '—',
		'Sites allowed' => (int) $d['max_sites'],
		'Updates until' => $d['expires_at'] ? htmlspecialchars( substr( $d['expires_at'], 0, 10 ) ) : 'Lifetime',
	);
}

/**
 * Client area: key, domains and how to activate.
 *
 * @param array $params Module params.
 * @return string
 */
function daralicense_ClientArea( array $params ) {
	$d = daralicense_details( $params );
	if ( ! $d ) {
		return '<p>Your license is being prepared.</p>';
	}
	$sites = $d['sites'] ? htmlspecialchars( implode( ', ', $d['sites'] ) ) : 'Not activated yet';
	return '<div style="text-align:start">'
		. '<p><strong>License key:</strong><br><code style="font-size:16px;user-select:all">' . htmlspecialchars( $d['license_key'] ) . '</code></p>'
		. '<p><strong>Activated on:</strong> ' . $sites . ' (' . (int) $d['max_sites'] . ' allowed)</p>'
		. '<p><strong>Updates &amp; support until:</strong> ' . ( $d['expires_at'] ? htmlspecialchars( substr( $d['expires_at'], 0, 10 ) ) : 'Lifetime' ) . '</p>'
		. '<p>Activate it in WordPress: <em>Properties → License</em>. Moving to a new domain? Use "Reset domains", then activate on the new site.</p>'
		. '</div>';
}
