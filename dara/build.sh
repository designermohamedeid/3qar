#!/bin/sh
# Build the production assets: split CSS per template, then minify CSS & JS.
set -e
cd "$(dirname "$0")"
python3 build-css.py
for f in core home listing property project content; do
	npx --no-install cleancss -O1 -o "assets/css/$f.min.css" "assets/css/$f.css"
done
npx --no-install terser assets/js/main.js -c -m -o assets/js/main.min.js
