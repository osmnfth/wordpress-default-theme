<?php
/**
 * Κοινά εξαρτήματα για τις φόρμες φίλτρων των αρχείων.
 *
 * Τα αρχεία Ανακοινώσεων (includes/announcements.php) και Μεταπτυχιακών
 * (includes/programs.php) δουλεύουν με τον ίδιο τρόπο: απλές GET παράμετροι,
 * φόρμα χωρίς JavaScript και φιλτράρισμα στο κύριο query. Ό,τι είναι κοινό -
 * τα κρυφά πεδία της φόρμας, η διατήρηση της γλώσσας και τα <select> των
 * ταξινομιών - ζει εδώ.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Η τρέχουσα γλώσσα ως παράμετρος, ώστε τα φίλτρα να μη «ρίχνουν» το ?lang=en.
 *
 * @return array Κενός πίνακας ή array( 'lang' => 'en' ).
 */
function kosmiteia_filters_lang_arg() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ανάγνωση δημόσιας παραμέτρου γλώσσας.
	if ( ! isset( $_GET['lang'] ) || ! is_scalar( $_GET['lang'] ) ) {
		return array();
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lang = sanitize_key( wp_unslash( $_GET['lang'] ) );

	return $lang ? array( 'lang' => $lang ) : array();
}

/**
 * Κρυφά πεδία ώστε η φόρμα (method="get") να μη χάνει παραμέτρους που δεν
 * ελέγχει η ίδια: το ?lang=en της δίγλωσσης λειτουργίας και το ?post_type=...
 * όταν ο ιστότοπος τρέχει με απλούς (μη «όμορφους») μόνιμους συνδέσμους.
 *
 * @param string $action URL δράσης της φόρμας. Επιστρέφεται χωρίς query string.
 * @return string HTML με τα hidden inputs.
 */
function kosmiteia_filters_hidden_fields( &$action ) {
	$carry = array();
	$parts = wp_parse_url( $action );

	if ( ! empty( $parts['query'] ) ) {
		parse_str( $parts['query'], $carry );
		$action = strtok( $action, '?' );
	}

	$carry = array_merge( $carry, kosmiteia_filters_lang_arg() );

	$html = '';

	foreach ( $carry as $key => $value ) {
		if ( ! is_scalar( $value ) ) {
			continue;
		}

		$html .= sprintf(
			'<input type="hidden" name="%1$s" value="%2$s" />',
			esc_attr( $key ),
			esc_attr( (string) $value )
		);
	}

	return $html;
}

/**
 * Οι όροι μιας ταξινομίας που χρησιμοποιούνται όντως από έναν τύπο περιεχομένου.
 *
 * Η ταξινομία «Σχολή (φίλτρο)» είναι κοινή σε Ανακοινώσεις και Μεταπτυχιακά:
 * χωρίς αυτόν τον περιορισμό, το φίλτρο των Μεταπτυχιακών θα εμφάνιζε και
 * Τμήματα που έχουν μόνο ανακοινώσεις - επιλογές με μηδέν αποτελέσματα.
 *
 * @param string $taxonomy  Ταξινομία.
 * @param string $post_type Τύπος περιεχομένου.
 * @return WP_Term[]
 */
function kosmiteia_filters_terms( $taxonomy, $post_type ) {
	$key    = 'kosmiteia_terms_' . md5( $taxonomy . '|' . $post_type );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$terms = array();

	if ( $post_ids ) {
		$found = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'object_ids' => $post_ids,
			)
		);

		if ( ! is_wp_error( $found ) ) {
			// Με object_ids ένας όρος μπορεί να επιστραφεί πολλές φορές.
			foreach ( $found as $term ) {
				$terms[ $term->term_id ] = $term;
			}

			$terms = array_values( $terms );
		}
	}

	set_transient( $key, $terms, HOUR_IN_SECONDS );

	return $terms;
}

/**
 * Καθαρισμός του cache των όρων όταν αλλάζει περιεχόμενο ή ταξινομίες.
 */
function kosmiteia_filters_flush_terms() {
	foreach ( array(
		array( 'kosm_faculty', 'kosm_announcement' ),
		array( 'kosm_faculty', 'kosm_program' ),
		array( 'kosm_ann_category', 'kosm_announcement' ),
		array( 'kosm_program_type', 'kosm_program' ),
	) as $pair ) {
		delete_transient( 'kosmiteia_terms_' . md5( $pair[0] . '|' . $pair[1] ) );
	}
}
add_action( 'save_post', 'kosmiteia_filters_flush_terms' );
add_action( 'deleted_post', 'kosmiteia_filters_flush_terms' );
add_action( 'set_object_terms', 'kosmiteia_filters_flush_terms' );
add_action( 'edited_term', 'kosmiteia_filters_flush_terms' );
add_action( 'delete_term', 'kosmiteia_filters_flush_terms' );

/**
 * Ένα <select> με τους όρους μιας ταξινομίας.
 *
 * @param string $id        ID στοιχείου.
 * @param string $name      Όνομα παραμέτρου.
 * @param string $label     Ετικέτα.
 * @param string $taxonomy  Ταξινομία.
 * @param string $all       Κείμενο της επιλογής «όλα».
 * @param string $current   Τρέχουσα τιμή (slug).
 * @param string $post_type Τύπος περιεχομένου του αρχείου.
 * @return string HTML, ή κενό αν δεν υπάρχουν όροι.
 */
function kosmiteia_filters_term_select( $id, $name, $label, $taxonomy, $all, $current, $post_type ) {
	$terms = kosmiteia_filters_terms( $taxonomy, $post_type );

	if ( empty( $terms ) ) {
		return '';
	}

	$options = sprintf( '<option value="">%s</option>', esc_html( $all ) );

	foreach ( $terms as $term ) {
		$options .= sprintf(
			'<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( $term->slug ),
			selected( $current, $term->slug, false ),
			esc_html( $term->name )
		);
	}

	return kosmiteia_filters_select( $id, $name, $label, $options );
}

/**
 * Ένα <select> με έτοιμες επιλογές.
 *
 * @param string $id      ID στοιχείου.
 * @param string $name    Όνομα παραμέτρου.
 * @param string $label   Ετικέτα.
 * @param string $options HTML των <option> (ήδη escaped).
 * @return string
 */
function kosmiteia_filters_select( $id, $name, $label, $options ) {
	return sprintf(
		'<p class="kosmiteia-filters__field"><label for="%1$s">%2$s</label><select id="%1$s" name="%3$s">%4$s</select></p>',
		esc_attr( $id ),
		esc_html( $label ),
		esc_attr( $name ),
		$options // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Τα options έχουν ήδη περάσει από esc_attr()/esc_html().
	);
}

/**
 * Πεδίο αναζήτησης.
 *
 * @param string $id          ID στοιχείου.
 * @param string $name        Όνομα παραμέτρου.
 * @param string $value       Τρέχουσα τιμή.
 * @param string $placeholder Κείμενο-υπόδειξη.
 * @return string
 */
function kosmiteia_filters_search_field( $id, $name, $value, $placeholder ) {
	return sprintf(
		'<p class="kosmiteia-filters__field kosmiteia-filters__field--search"><label for="%1$s">%2$s</label><input type="search" id="%1$s" name="%3$s" value="%4$s" placeholder="%5$s" /></p>',
		esc_attr( $id ),
		esc_html__( 'Αναζήτηση', 'kosmiteia' ),
		esc_attr( $name ),
		esc_attr( $value ),
		esc_attr( $placeholder )
	);
}

/**
 * Τα κουμπιά της φόρμας (υποβολή και, όταν χρειάζεται, καθαρισμός).
 *
 * @param string $archive URL του αρχείου.
 * @param bool   $active  Υπάρχει ενεργό φίλτρο;
 * @return string
 */
function kosmiteia_filters_actions( $archive, $active ) {
	$html = sprintf(
		'<button type="submit" class="wp-element-button kosmiteia-filters__submit">%s</button>',
		esc_html__( 'Φιλτράρισμα', 'kosmiteia' )
	);

	if ( $active ) {
		$html .= sprintf(
			'<a class="kosmiteia-filters__reset" href="%s">%s</a>',
			esc_url( add_query_arg( kosmiteia_filters_lang_arg(), $archive ) ),
			esc_html__( 'Καθαρισμός φίλτρων', 'kosmiteia' )
		);
	}

	return $html;
}

/**
 * Το περίβλημα της φόρμας.
 *
 * @param string $action  URL δράσης (χωρίς query string).
 * @param string $label   aria-label της φόρμας.
 * @param string $hidden  Κρυφά πεδία.
 * @param string $fields  Τα πεδία.
 * @param string $actions Τα κουμπιά.
 * @param string $count   Ο μετρητής αποτελεσμάτων.
 * @return string
 */
function kosmiteia_filters_form( $action, $label, $hidden, $fields, $actions, $count ) {
	return sprintf(
		'<form class="kosmiteia-filters" method="get" action="%1$s" role="search" aria-label="%2$s">%3$s<div class="kosmiteia-filters__fields">%4$s<p class="kosmiteia-filters__actions">%5$s</p></div>%6$s</form>',
		esc_url( $action ),
		esc_attr( $label ),
		$hidden,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Παράγεται από kosmiteia_filters_hidden_fields().
		$fields,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Παράγεται από τους helpers παραπάνω.
		$actions, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Παράγεται από kosmiteia_filters_actions().
		$count    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Παράγεται από τα modules, με esc_html().
	);
}
