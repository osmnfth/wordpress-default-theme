#!/bin/sh
# Automatic setup of the Kosmiteia demo site.
# Runs inside the "provision" container (wordpress:cli) on every `docker compose up`.
# It is idempotent: if the site already exists, it does not overwrite the existing content.

set -e

cd /var/www/html

say() { echo "[kosmiteia] $1"; }

say "Waiting for WordPress files..."
tries=0
while [ ! -f wp-settings.php ] || [ ! -f wp-config.php ]; do
	tries=$((tries + 1))
	if [ "$tries" -gt 120 ]; then
		echo "[kosmiteia] The WordPress files were not found." >&2
		exit 1
	fi
	sleep 2
done

say "Waiting for the database..."
tries=0
until wp db check --quiet >/dev/null 2>&1; do
	tries=$((tries + 1))
	if [ "$tries" -gt 60 ]; then
		echo "[kosmiteia] The database did not respond." >&2
		exit 1
	fi
	sleep 2
done

if wp core is-installed >/dev/null 2>&1; then
	say "The WordPress is already installed."
else
	say "Installing WordPress..."
	wp core install \
		--url="$KOSMITEIA_URL" \
		--title="Κοσμητεία Σχολών" \
		--admin_user="$KOSMITEIA_ADMIN_USER" \
		--admin_password="$KOSMITEIA_ADMIN_PASSWORD" \
		--admin_email="$KOSMITEIA_ADMIN_EMAIL" \
		--skip-email
fi

say "Greek translation for the interface..."
wp language core install el --activate 2>/dev/null || say "  (offline - keeping English)"

say "Twenty Twenty-Five (parent theme)..."
if ! wp theme is-installed twentytwentyfive 2>/dev/null; then
	wp theme install twentytwentyfive 2>/dev/null || say "  (failed to download - requires internet)"
fi

say "Activating the child theme..."
wp theme activate kosmiteia

say "Activating the «Kosmiteia Core» plugin..."
wp plugin activate kosmiteia-core

say "FileBird (folders in the media library)..."
if wp plugin is-installed filebird 2>/dev/null; then
	wp plugin activate filebird >/dev/null 2>&1 || true
else
	wp plugin install filebird --activate 2>/dev/null || say "  (failed to download - requires internet)"
fi

say "Permanent links..."
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard

say "Initial content, menu and English parts..."
if [ "${KOSMITEIA_RESEED:-0}" = "1" ]; then
	wp kosmiteia seed --force
else
	wp kosmiteia seed
fi

say "Cleaning cache..."
wp cache flush 2>/dev/null || true

say "-------------------------------------------------------------"
say "Ready: $KOSMITEIA_URL"
say "Management: $KOSMITEIA_URL/wp-admin  ($KOSMITEIA_ADMIN_USER / $KOSMITEIA_ADMIN_PASSWORD)"
say "English: $KOSMITEIA_URL/?lang=en"
say "-------------------------------------------------------------"
