<?php
/**
 * WHMCS hook: adds {$dara_license_key} to emails about a Dara License service.
 *
 * Install: copy to <whmcs>/includes/hooks/daralicense.php
 * Use in any Product/Service email template: {$dara_license_key}
 *
 * @package DaraLicenseServer
 */

use WHMCS\Database\Capsule;

if ( ! defined( 'WHMCS' ) ) {
	die( 'This file cannot be accessed directly' );
}

add_hook(
	'EmailPreSend',
	1,
	function ( $vars ) {
		$service_id = isset( $vars['relid'] ) ? (int) $vars['relid'] : 0;
		if ( ! $service_id ) {
			return array();
		}
		try {
			$key = Capsule::table( 'tblcustomfieldsvalues as v' )
				->join( 'tblcustomfields as f', 'f.id', '=', 'v.fieldid' )
				->where( 'f.type', 'product' )
				->where( 'f.fieldname', 'like', 'License Key%' )
				->where( 'v.relid', $service_id )
				->value( 'v.value' );
		} catch ( \Exception $e ) {
			return array();
		}
		return $key ? array( 'dara_license_key' => $key ) : array();
	}
);
