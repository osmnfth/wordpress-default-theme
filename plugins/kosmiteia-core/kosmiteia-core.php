<?php
/**
 * Plugin Name:       Κοσμητεία Core
 * Plugin URI:        https://health.duth.gr/
 * Description:       Το «μηχανοστάσιο» του ιστότοπου της Κοσμητείας: τύποι περιεχομένου (Σχολές, Ανακοινώσεις, Μεταπτυχιακά, Εκδηλώσεις, Προσωπικό, Έγγραφα), ταξινομίες, πεδία, μπλοκ, φίλτρα, χάρτης, γκαλερί, πλωτό κουμπί με μήνυμα, οθόνη φόρτωσης, δίγλωσση λειτουργία, SEO και εργαλεία εισαγωγής περιεχομένου. Δουλεύει ανεξάρτητα από το ενεργό θέμα.
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

function kosmiteia_core_asset_version( $relative ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$path = KOSMITEIA_CORE_DIR . ltrim( $relative, '/' );

		if ( file_exists( $path ) ) {
			return (string) filemtime( $path );
		}
	}

	return KOSMITEIA_CORE_VERSION;
}

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
require_once KOSMITEIA_CORE_DIR . 'includes/filters.php';
require_once KOSMITEIA_CORE_DIR . 'includes/announcements.php';
require_once KOSMITEIA_CORE_DIR . 'includes/programs.php';
require_once KOSMITEIA_CORE_DIR . 'includes/breadcrumbs.php';
require_once KOSMITEIA_CORE_DIR . 'includes/blocks.php';
require_once KOSMITEIA_CORE_DIR . 'includes/gallery.php';
require_once KOSMITEIA_CORE_DIR . 'includes/floating-button.php';
require_once KOSMITEIA_CORE_DIR . 'includes/page-loader.php';
require_once KOSMITEIA_CORE_DIR . 'includes/media.php';
require_once KOSMITEIA_CORE_DIR . 'includes/greeklish.php';
require_once KOSMITEIA_CORE_DIR . 'includes/seo.php';
require_once KOSMITEIA_CORE_DIR . 'includes/importer-announcements.php';
require_once KOSMITEIA_CORE_DIR . 'includes/demo-content.php';
require_once KOSMITEIA_CORE_DIR . 'includes/admin.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once KOSMITEIA_CORE_DIR . 'includes/cli.php';
}

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

function kosmiteia_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'kosmiteia_core_deactivate' );

function kosmiteia_core_action_links( $links ) {
	$own = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=kosmiteia-settings' ) ) . '">' . esc_html__( 'Ρυθμίσεις', 'kosmiteia' ) . '</a>',
		'<a href="' . esc_url( admin_url( 'admin.php?page=kosmiteia-tools' ) ) . '">' . esc_html__( 'Εργαλεία', 'kosmiteia' ) . '</a>',
	);

	return array_merge( $own, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kosmiteia_core_action_links' );
