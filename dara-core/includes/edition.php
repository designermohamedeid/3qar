<?php
/**
 * Build edition and license server (written by bin/package.sh).
 *
 * "direct": sold on your own store, needs a license key.
 * "market": marketplace build (ThemeForest), no license checks.
 *
 * @package DaraCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Edition settings.
 *
 * @return array { edition, api, pubkey, store }
 */
function dara_core_edition() {
	return array(
		'edition' => 'direct',
		'api'     => 'https://muhamedeid.com/wp-json/dls/v1/',
		'pubkey'  => '',
		'store'   => 'https://muhamedeid.com/',
	);
}
