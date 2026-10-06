#!/bin/sh
# Build the sale packages:
#   dist/direct/   your own store: license key per domain (bin/license.conf)
#   dist/market/   marketplaces (ThemeForest): no license checks
#   dist/server/   license server plugin for muhamedeid.com + WHMCS module
# Each edition folder has dara.zip, dara-child.zip, dara-core.zip, dara-pro.zip
# and dara-package.zip (everything + documentation).
#   direct: the theme bundles Dara Core only. Dara Pro is uploaded to the license
#           server (Licenses → Releases) and each buyer gets a copy built for them.
#   market: the theme bundles Dara Core and Dara Pro.
set -e
cd "$(dirname "$0")/.."
ROOT=$(pwd)
. "$ROOT/bin/license.conf"
TMP="$ROOT/dist/.build"
rm -rf "$ROOT/dist/direct" "$ROOT/dist/market" "$ROOT/dist/server" "$TMP"
mkdir -p "$TMP"

copy() { # copy <src> <dest> without development files
	mkdir -p "$2"
	tar -C "$1" --exclude node_modules --exclude './assets/css/src' --exclude ./build.sh \
		--exclude ./build-css.py --exclude ./package.json --exclude ./package-lock.json \
		--exclude ./README.md --exclude .DS_Store --exclude './plugins/*.zip' -cf - . | tar -C "$2" -xf -
}

edition_file() { # edition_file <path> <edition>
	cat > "$1" <<PHP
<?php
/**
 * Build edition and license server (written by bin/package.sh).
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
		'edition' => '$2',
		'api'     => '$LICENSE_API',
		'pubkey'  => '$( [ "$2" = direct ] && echo "$LICENSE_PUBKEY" )',
		'store'   => '$LICENSE_STORE',
	);
}
PHP
}

pro_edition_file() { # pro_edition_file <path> <edition>
	cat > "$1" <<PHP
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
		'edition' => '$2',
		'pubkey'  => '$( [ "$2" = direct ] && echo "$LICENSE_PUBKEY" )',
	);
}
PHP
}

build() { # build <edition>
	OUT="$ROOT/dist/$1"
	B="$TMP/$1"
	mkdir -p "$OUT" "$B"
	copy dara-core "$B/dara-core"
	edition_file "$B/dara-core/includes/edition.php" "$1"
	(cd "$B" && zip -qr "$OUT/dara-core.zip" dara-core)

	copy dara-pro "$B/dara-pro"
	pro_edition_file "$B/dara-pro/includes/edition.php" "$1"
	rm -f "$B/dara-pro/manifest.json" "$B/dara-pro/manifest.sig"
	(cd "$B" && zip -qr "$OUT/dara-pro.zip" dara-pro)

	copy dara "$B/dara"
	mkdir -p "$B/dara/plugins"
	cp "$OUT/dara-core.zip" "$B/dara/plugins/dara-core.zip"
	[ "$1" = market ] && cp "$OUT/dara-pro.zip" "$B/dara/plugins/dara-pro.zip"
	(cd "$B" && zip -qr "$OUT/dara.zip" dara)

	copy dara-child "$B/dara-child"
	(cd "$B" && zip -qr "$OUT/dara-child.zip" dara-child)

	mkdir -p "$B/package/Theme" "$B/package/Plugin"
	cp "$OUT/dara.zip" "$OUT/dara-child.zip" "$B/package/Theme/"
	cp "$OUT/dara-core.zip" "$B/package/Plugin/"
	[ "$1" = market ] && cp "$OUT/dara-pro.zip" "$B/package/Plugin/"
	cp -r documentation "$B/package/Documentation"
	cp documentation/licensing.txt "$B/package/Licensing.txt"
	(cd "$B/package" && zip -qr "$OUT/dara-package.zip" .)
}

if [ -z "$LICENSE_PUBKEY" ]; then
	echo "Note: LICENSE_PUBKEY is empty in bin/license.conf, so the direct build has no license checks yet."
fi
build direct
build market

mkdir -p "$ROOT/dist/server"
(cd license-server && zip -qr "$ROOT/dist/server/dara-license-server.zip" dara-license-server)
(cd license-server/whmcs && zip -qr "$ROOT/dist/server/whmcs-daralicense.zip" modules)

rm -rf "$TMP"
ls -la "$ROOT/dist/direct" "$ROOT/dist/market" "$ROOT/dist/server"
