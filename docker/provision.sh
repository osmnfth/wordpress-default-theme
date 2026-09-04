#!/bin/sh
# Αυτόματο στήσιμο του δοκιμαστικού site της Κοσμητείας.
# Τρέχει μέσα στο container "provision" (wordpress:cli) σε κάθε `docker compose up`.
# Είναι idempotent: αν το site υπάρχει ήδη, δεν ξαναγράφει περιεχόμενο.

set -e

cd /var/www/html

say() { echo "[kosmiteia] $1"; }

say "Αναμονή για τα αρχεία του WordPress..."
tries=0
while [ ! -f wp-settings.php ] || [ ! -f wp-config.php ]; do
	tries=$((tries + 1))
	if [ "$tries" -gt 120 ]; then
		echo "[kosmiteia] Τα αρχεία του WordPress δεν βρέθηκαν." >&2
		exit 1
	fi
	sleep 2
done

say "Αναμονή για τη βάση δεδομένων..."
tries=0
until wp db check --quiet >/dev/null 2>&1; do
	tries=$((tries + 1))
	if [ "$tries" -gt 60 ]; then
		echo "[kosmiteia] Η βάση δεν απάντησε." >&2
		exit 1
	fi
	sleep 2
done

if wp core is-installed >/dev/null 2>&1; then
	say "Το WordPress είναι ήδη εγκατεστημένο."
else
	say "Εγκατάσταση WordPress..."
	wp core install \
		--url="$KOSMITEIA_URL" \
		--title="Κοσμητεία Σχολών" \
		--admin_user="$KOSMITEIA_ADMIN_USER" \
		--admin_password="$KOSMITEIA_ADMIN_PASSWORD" \
		--admin_email="$KOSMITEIA_ADMIN_EMAIL" \
		--skip-email
fi

say "Ελληνικά της διεπαφής..."
wp language core install el --activate 2>/dev/null || say "  (χωρίς δίκτυο - μένει στα αγγλικά)"

say "Twenty Twenty-Five (parent theme)..."
if ! wp theme is-installed twentytwentyfive 2>/dev/null; then
	wp theme install twentytwentyfive 2>/dev/null || say "  (δεν κατέβηκε - χρειάζεται δίκτυο)"
fi

say "Ενεργοποίηση του child theme..."
wp theme activate kosmiteia

say "Ενεργοποίηση του προσθέτου «Κοσμητεία Core»..."
wp plugin activate kosmiteia-core

say "FileBird (φάκελοι στη Βιβλιοθήκη πολυμέσων)..."
if wp plugin is-installed filebird 2>/dev/null; then
	wp plugin activate filebird >/dev/null 2>&1 || true
else
	wp plugin install filebird --activate 2>/dev/null || say "  (δεν κατέβηκε - χρειάζεται δίκτυο)"
fi

say "Μόνιμοι σύνδεσμοι..."
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard

say "Αρχικό περιεχόμενο, μενού και αγγλικά parts..."
if [ "${KOSMITEIA_RESEED:-0}" = "1" ]; then
	wp kosmiteia seed --force
else
	wp kosmiteia seed
fi

say "Καθαρισμός cache..."
wp cache flush 2>/dev/null || true

say "-------------------------------------------------------------"
say "Έτοιμο: $KOSMITEIA_URL"
say "Διαχείριση: $KOSMITEIA_URL/wp-admin  ($KOSMITEIA_ADMIN_USER / $KOSMITEIA_ADMIN_PASSWORD)"
say "Αγγλικά: $KOSMITEIA_URL/?lang=en"
say "-------------------------------------------------------------"
