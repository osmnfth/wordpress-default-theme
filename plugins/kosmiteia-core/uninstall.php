<?php
/**
 * Καθαρισμός κατά τη διαγραφή του προσθέτου.
 *
 * Σβήνουμε μόνο τις δικές μας ρυθμίσεις. Το περιεχόμενο (Ανακοινώσεις,
 * Μεταπτυχιακά, Εκδηλώσεις, Προσωπικό, Έγγραφα) και τα αρχεία
 * της Βιβλιοθήκης ΔΕΝ διαγράφονται: ανήκουν στο ίδρυμα, όχι στο πρόσθετο.
 * Αν επανεγκατασταθεί, όλα ξαναεμφανίζονται.
 *
 * @package Kosmiteia_Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$kosmiteia_options = array(
	'kosmiteia_settings',
	'kosmiteia_core_version',
	'kosmiteia_rewrites_version',
	'kosmiteia_roles_version',
	'kosmiteia_demo_version',
	'kosmiteia_announcement_years',
);

foreach ( $kosmiteia_options as $kosmiteia_option ) {
	delete_option( $kosmiteia_option );
}

delete_transient( 'kosmiteia_announcement_years' );

// Ο ρόλος «Συντάκτης Ανακοινώσεων» φεύγει μαζί με το πρόσθετο· οι χρήστες
// παραμένουν (χωρίς ρόλο μέχρι να τους δοθεί άλλος από τη διαχείριση).
remove_role( 'kosmiteia_announcer' );
