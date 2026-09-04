<?php
/**
 * Kosmiteia — Child theme του Twenty Twenty-Five για Κοσμητείες Σχολών.
 *
 * Το θέμα κρατά μόνο την εμφάνιση: templates, patterns, στυλ και block styles.
 * Το περιεχόμενο και η λειτουργικότητα (τύποι περιεχομένου, μπλοκ, φίλτρα,
 * χάρτης, γκαλερί, δίγλωσσο, SEO, εργαλεία εισαγωγής) ζουν στο πρόσθετο
 * «Κοσμητεία Core», ώστε να επιβιώνουν σε αλλαγή θέματος.
 *
 * @package Kosmiteia
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'KOSMITEIA_VERSION', '1.0.0' );
define( 'KOSMITEIA_DIR', get_stylesheet_directory() );
define( 'KOSMITEIA_URI', get_stylesheet_directory_uri() );

require_once KOSMITEIA_DIR . '/inc/setup.php';
require_once KOSMITEIA_DIR . '/inc/dependency.php';
