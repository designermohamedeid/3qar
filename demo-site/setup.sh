#!/bin/sh
# Set up one Dara live demo site on a server with WP-CLI.
# Run it once per language, inside a fresh WordPress install:
#
#   sh setup.sh /home/USER/public_html/dara      ar https://dara.mansourahost.com/en  English
#   sh setup.sh /home/USER/public_html/dara/en   en https://dara.mansourahost.com     العربية
#
# Arguments: <wordpress path> <ar|en> <other demo URL> <switch label> [buy URL]
# Needs dist/dara.zip (sh bin/package.sh) next to this script's parent folder.
set -e
WP_PATH=$1
LANG_CODE=$2
OTHER_URL=$3
LABEL=$4
BUY_URL=${5:-}
HERE=$(cd "$(dirname "$0")" && pwd)
ZIP="$HERE/../dist/dara.zip"
wp() { command wp --path="$WP_PATH" "$@"; }

[ -f "$ZIP" ] || { echo "Missing $ZIP. Run: sh bin/package.sh"; exit 1; }
[ "$LANG_CODE" = "ar" ] || [ "$LANG_CODE" = "en" ] || { echo "Language must be ar or en"; exit 1; }

# Theme + bundled plugin.
wp theme install "$ZIP" --activate --force
wp plugin install "$WP_PATH/wp-content/themes/dara/plugins/dara-core.zip" --activate --force

# Language and permalinks.
if [ "$LANG_CODE" = "ar" ]; then
	wp language core install ar --activate
fi
wp rewrite structure '/%postname%/' --hard

# Demo content (home page, menus, images).
wp dara demo remove >/dev/null 2>&1 || true
wp dara demo import --lang="$LANG_CODE"
if [ "$LANG_CODE" = "en" ]; then
	wp option update blogname "Dara"
	wp eval '$s = (array) get_option( "dara_core_settings" ); $s["currency"] = "SAR"; update_option( "dara_core_settings", $s );'
else
	wp option update blogname "دارة"
fi

# Demo helpers (floating buy / language bar, forms not stored or emailed).
mkdir -p "$WP_PATH/wp-content/mu-plugins"
cp "$HERE/dara-demo-site.php" "$WP_PATH/wp-content/mu-plugins/"
wp config set DARA_DEMO_SWITCH_URL "$OTHER_URL"
wp config set DARA_DEMO_SWITCH_LABEL "$LABEL"
[ -n "$BUY_URL" ] && wp config set DARA_DEMO_BUY_URL "$BUY_URL"

# Visitors cannot register; comments closed on the demo.
wp option update users_can_register 0
wp option update default_comment_status closed

wp cache flush || true
echo "Done: $(wp option get home)"
