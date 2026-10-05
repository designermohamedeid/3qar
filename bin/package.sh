#!/bin/sh
# Build the sale package in dist/:
#   dist/dara.zip            installable theme (Dara Core bundled in plugins/)
#   dist/dara-child.zip      installable child theme
#   dist/dara-core.zip       installable plugin
#   dist/dara-package.zip    everything above + documentation + licensing
set -e
cd "$(dirname "$0")/.."
ROOT=$(pwd)
OUT=$ROOT/dist
TMP=$OUT/.build
rm -rf "$OUT"
mkdir -p "$TMP"

copy() { # copy <src> <dest> without development files
	mkdir -p "$2"
	tar -C "$1" --exclude node_modules --exclude './assets/css/src' --exclude ./build.sh \
		--exclude ./build-css.py --exclude ./package.json --exclude ./package-lock.json \
		--exclude ./README.md --exclude .DS_Store --exclude './plugins/*.zip' -cf - . | tar -C "$2" -xf -
}

copy dara-core "$TMP/dara-core"
(cd "$TMP" && zip -qr "$OUT/dara-core.zip" dara-core)

copy dara "$TMP/dara"
mkdir -p "$TMP/dara/plugins"
cp "$OUT/dara-core.zip" "$TMP/dara/plugins/dara-core.zip"
(cd "$TMP" && zip -qr "$OUT/dara.zip" dara)

copy dara-child "$TMP/dara-child"
(cd "$TMP" && zip -qr "$OUT/dara-child.zip" dara-child)

mkdir -p "$TMP/package/Theme" "$TMP/package/Plugin"
cp "$OUT/dara.zip" "$OUT/dara-child.zip" "$TMP/package/Theme/"
cp "$OUT/dara-core.zip" "$TMP/package/Plugin/"
cp -r documentation "$TMP/package/Documentation"
cp documentation/licensing.txt "$TMP/package/Licensing.txt"
(cd "$TMP/package" && zip -qr "$OUT/dara-package.zip" .)

rm -rf "$TMP"
ls -la "$OUT"
