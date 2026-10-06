<?php
/**
 * Build edition (written by bin/package.sh).
 *
 * @package DaraPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Edition settings.
 *
 * @return array { edition, pubkey }
 */
function dara_pro_edition() {
	return array(
		'edition' => 'direct',
		'pubkey'  => '',
	);
}
