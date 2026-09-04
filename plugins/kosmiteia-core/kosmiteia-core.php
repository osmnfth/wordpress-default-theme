<?php
/**
 * Plugin Name:       Κοσμητεία Core
 * Plugin URI:        https://health.duth.gr/
 * Description:       Το «μηχανοστάσιο» του ιστότοπου της Κοσμητείας: τύποι περιεχομένου (Σχολές, Ανακοινώσεις, Μεταπτυχιακά, Εκδηλώσεις, Προσωπικό, Έγγραφα), ταξινομίες, πεδία, μπλοκ, φίλτρα, χάρτης, γκαλερί, δίγλωσση λειτουργία, SEO και εργαλεία εισαγωγής περιεχομένου. Δουλεύει ανεξάρτητα από το ενεργό θέμα.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Κοσμητεία Σχολής Επιστημών Υγείας
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kosmiteia
 * Domain Path:       /languages
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'KOSMITEIA_CORE_VERSION', '1.0.0' );
define( 'KOSMITEIA_CORE_FILE', __FILE__ );
define( 'KOSMITEIA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'KOSMITEIA_CORE_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Έκδοση αρχείου για cache busting.
 *
 * Σε περιβάλλον ανάπτυξης (WP_DEBUG) χρησιμοποιεί την ώρα τελευταίας
 * τροποποίησης, ώστε οι αλλαγές σε CSS/JS να φαίνονται αμέσως χωρίς σκληρό
 * refresh. Στην παραγωγή χρησιμοποιεί την έκδοση του plugin.
 *
 * @param string $relative Διαδρομή σχετική με τον φάκελο του plugin.
 * @return string
 */
function kosmiteia_core_asset_version( $relative ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$path = KOSMITEIA_CORE_DIR . ltrim( $relative, '/' );

		if ( file_exists( $path ) ) {
			return (string) filemtime( $path );
		}
	}

	return KOSMITEIA_CORE_VERSION;
}

/**
 * Μεταφράσεις του plugin.
 */
function kosmiteia_core_load_textdomain() {
	load_plugin_textdomain( 'kosmiteia', false, dirname( plugin_basename( KOSMITEIA_CORE_FILE ) ) . '/languages' );
}
add_action( 'init', 'kosmiteia_core_load_textdomain' );

require_once KOSMITEIA_CORE_DIR . 'includes/settings.php';
require_once KOSMITEIA_CORE_DIR . 'includes/post-types.php';
require_once KOSMITEIA_CORE_DIR . 'includes/post-types-academic.php';
require_once KOSMITEIA_CORE_DIR . 'includes/roles.php';
require_once KOSMITEIA_CORE_DIR . 'includes/bindings.php';
require_once KOSMITEIA_CORE_DIR . 'includes/multilingual.php';
require_once KOSMITEIA_CORE_DIR . 'includes/announcements.php';
require_once KOSMITEIA_CORE_DIR . 'includes/breadcrumbs.php';
require_once KOSMITEIA_CORE_DIR . 'includes/blocks.php';
require_once KOSMITEIA_CORE_DIR . 'includes/gallery.php';
require_once KOSMITEIA_CORE_DIR . 'includes/media.php';
require_once KOSMITEIA_CORE_DIR . 'includes/seo.php';
require_once KOSMITEIA_CORE_DIR . 'includes/importer-announcements.php';
require_once KOSMITEIA_CORE_DIR . 'includes/demo-content.php';
require_once KOSMITEIA_CORE_DIR . 'includes/admin.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once KOSMITEIA_CORE_DIR . 'includes/cli.php';
}

/**
 * Ενεργοποίηση: καταχώριση των τύπων περιεχομένου και ανανέωση των permalinks,
 * ώστε τα αρχεία (/schools, /announcements, ...) να δουλεύουν αμέσως.
 */
function kosmiteia_core_activate() {
	kosmiteia_register_post_types();
	kosmiteia_register_academic_post_types();
	kosmiteia_register_taxonomies();
	kosmiteia_register_academic_taxonomies();
	kosmiteia_settings_add_defaults();
	kosmiteia_register_roles();

	flush_rewrite_rules();

	update_option( 'kosmiteia_core_version', KOSMITEIA_CORE_VERSION );
}
register_activation_hook( __FILE__, 'kosmiteia_core_activate' );

/**
 * Απενεργοποίηση: καθαρίζουμε μόνο τα rewrite rules - το περιεχόμενο μένει.
 */
function kosmiteia_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'kosmiteia_core_deactivate' );

/**
 * Σύνδεσμοι «Ρυθμίσεις» / «Εργαλεία» στη λίστα των Προσθέτων.
 *
 * @param array $links Υπάρχοντες σύνδεσμοι.
 * @return array
 */
function kosmiteia_core_action_links( $links ) {
	$own = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=kosmiteia-settings' ) ) . '">' . esc_html__( 'Ρυθμίσεις', 'kosmiteia' ) . '</a>',
		'<a href="' . esc_url( admin_url( 'admin.php?page=kosmiteia-tools' ) ) . '">' . esc_html__( 'Εργαλεία', 'kosmiteia' ) . '</a>',
	);

	return array_merge( $own, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kosmiteia_core_action_links' );
