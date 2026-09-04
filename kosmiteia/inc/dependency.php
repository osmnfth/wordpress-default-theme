<?php
/**
 * Έλεγχος ότι το πρόσθετο «Κοσμητεία Core» είναι ενεργό.
 *
 * Το θέμα δείχνει· το πρόσθετο παρέχει το περιεχόμενο (τύπους περιεχομένου,
 * μπλοκ, φίλτρα, χάρτη, γκαλερί). Χωρίς αυτό τα templates θα ζητούσαν μπλοκ
 * που δεν υπάρχουν, οπότε ενημερώνουμε τον διαχειριστή με σαφή οδηγία αντί να
 * τον αφήσουμε μπροστά σε κενές σελίδες.
 *
 * @package Kosmiteia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Είναι ενεργό το πρόσθετο;
 *
 * @return bool
 */
function kosmiteia_core_is_active() {
	return defined( 'KOSMITEIA_CORE_VERSION' );
}

/**
 * Ειδοποίηση στη διαχείριση όταν λείπει το πρόσθετο.
 */
function kosmiteia_core_missing_notice() {
	if ( kosmiteia_core_is_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$plugins_url = admin_url( 'plugins.php' );

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Κοσμητεία:', 'kosmiteia' ),
		esc_html__( 'το θέμα χρειάζεται το πρόσθετο «Κοσμητεία Core» για τους τύπους περιεχομένου και τα μπλοκ του.', 'kosmiteia' ),
		esc_url( $plugins_url ),
		esc_html__( 'Ενεργοποίηση από τα Πρόσθετα', 'kosmiteia' )
	);
}
add_action( 'admin_notices', 'kosmiteia_core_missing_notice' );
