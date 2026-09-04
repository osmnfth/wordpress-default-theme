<?php
/**
 * Αναζήτηση και φιλτράρισμα Ανακοινώσεων.
 *
 * Το αρχείο Ανακοινώσεων (archive-kosm_announcement) δέχεται φίλτρα μέσω
 * απλών GET παραμέτρων, ώστε κάθε συνδυασμός να έχει δικό του URL που
 * μοιράζεται και μπαίνει στους σελιδοδείκτες:
 *
 *   ?kosm_q=erasmus            - ελεύθερη αναζήτηση κειμένου
 *   ?kosm_cat=prokiryxeis      - κατηγορία ανακοίνωσης (slug)
 *   ?kosm_fac=politexniki      - Σχολή (slug του kosm_faculty)
 *   ?kosm_year=2026            - έτος δημοσίευσης
 *
 * Τα ονόματα των παραμέτρων είναι επίτηδες «δικά μας»: το ?kosm_school θα το
 * ερμήνευε το WordPress ως μεμονωμένη Σχολή (query var του post type) και το
 * ?kosm_faculty ως αρχείο ταξινομίας - και στις δύο περιπτώσεις θα άλλαζε το
 * πρότυπο της σελίδας.
 *
 * Τα φίλτρα εφαρμόζονται στο κύριο query (pre_get_posts), οπότε το Query Loop
 * του template δουλεύει με «Κληρονομιά ερωτήματος» και η σελιδοποίηση του
 * WordPress (/page/2/) κρατά αυτόματα τις παραμέτρους.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ανακοινώσεις ανά σελίδα στο αρχείο.
 *
 * Ορίζεται από τις Ρυθμίσεις Κοσμητείας (Περιεχόμενο → Ανακοινώσεις ανά
 * σελίδα). Προγραμματιστικά:
 *   add_filter( 'kosmiteia_announcements_per_page', function () { return 12; } );
 *
 * @return int
 */
function kosmiteia_announcements_per_page() {
	return (int) apply_filters( 'kosmiteia_announcements_per_page', (int) kosmiteia_option( 'announcements_per_page', 10 ) );
}

/**
 * Οι τιμές των φίλτρων όπως ήρθαν από το URL, καθαρισμένες.
 *
 * @return array Πίνακας με κλειδιά q, cat, school, year.
 */
function kosmiteia_announcement_filters_state() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Δημόσια φόρμα φίλτρων (GET): δεν αλλάζει δεδομένα.
	$read = function ( $key ) {
		return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? (string) wp_unslash( $_GET[ $key ] ) : '';
	};

	$year = (int) $read( 'kosm_year' );

	return array(
		'q'      => sanitize_text_field( $read( 'kosm_q' ) ),
		'cat'    => sanitize_title( $read( 'kosm_cat' ) ),
		'school' => sanitize_title( $read( 'kosm_fac' ) ),
		'year'   => ( $year >= 1970 && $year <= 2200 ) ? $year : 0,
	);
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

/**
 * Υπάρχει έστω ένα ενεργό φίλτρο;
 *
 * @param array $state Κατάσταση φίλτρων.
 * @return bool
 */
function kosmiteia_announcement_filters_active( $state ) {
	return ( '' !== $state['q'] || '' !== $state['cat'] || '' !== $state['school'] || 0 !== $state['year'] );
}

/**
 * Η τρέχουσα γλώσσα ως παράμετρος, ώστε τα φίλτρα να μη «ρίχνουν» το ?lang=en.
 *
 * @return array Κενός πίνακας ή array( 'lang' => 'en' ).
 */
function kosmiteia_announcement_filters_lang_arg() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ανάγνωση δημόσιας παραμέτρου γλώσσας.
	if ( ! isset( $_GET['lang'] ) || ! is_scalar( $_GET['lang'] ) ) {
		return array();
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lang = sanitize_key( wp_unslash( $_GET['lang'] ) );

	return $lang ? array( 'lang' => $lang ) : array();
}

/**
 * Εφαρμογή των φίλτρων στο κύριο query του αρχείου Ανακοινώσεων.
 *
 * @param WP_Query $query Το query.
 */
function kosmiteia_filter_announcement_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'kosm_announcement' ) ) {
		return;
	}

	$query->set( 'posts_per_page', kosmiteia_announcements_per_page() );

	$state = kosmiteia_announcement_filters_state();

	if ( '' !== $state['q'] ) {
		// Δεν αγγίζουμε τα is_search()/is_archive() flags: το template παραμένει
		// το αρχείο των Ανακοινώσεων, απλώς με φιλτραρισμένα αποτελέσματα.
		$query->set( 's', $state['q'] );
	}

	$tax_query = array();

	if ( '' !== $state['cat'] ) {
		$tax_query[] = array(
			'taxonomy' => 'kosm_ann_category',
			'field'    => 'slug',
			'terms'    => $state['cat'],
		);
	}

	if ( '' !== $state['school'] ) {
		$tax_query[] = array(
			'taxonomy' => 'kosm_faculty',
			'field'    => 'slug',
			'terms'    => $state['school'],
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	if ( $state['year'] ) {
		$query->set(
			'date_query',
			array(
				array( 'year' => $state['year'] ),
			)
		);
	}
}
add_action( 'pre_get_posts', 'kosmiteia_filter_announcement_archive' );

/**
 * Τα έτη που έχουν δημοσιευμένες ανακοινώσεις (νεότερο πρώτο).
 *
 * @return array Λίστα ετών ως ακέραιοι.
 */
function kosmiteia_announcement_years() {
	$cached = get_transient( 'kosmiteia_announcement_years' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Το αποτέλεσμα μπαίνει σε transient αμέσως παρακάτω.
	$years = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT YEAR(post_date) AS post_year FROM {$wpdb->posts}
			WHERE post_type = %s AND post_status = 'publish'
			ORDER BY post_year DESC",
			'kosm_announcement'
		)
	);

	$years = array_map( 'intval', (array) $years );

	set_transient( 'kosmiteia_announcement_years', $years, HOUR_IN_SECONDS );

	return $years;
}

/**
 * Καθαρισμός του cache των ετών όταν αλλάζει μια ανακοίνωση.
 *
 * @param int $post_id ID άρθρου.
 */
function kosmiteia_flush_announcement_years( $post_id ) {
	if ( 'kosm_announcement' === get_post_type( $post_id ) ) {
		delete_transient( 'kosmiteia_announcement_years' );
	}
}
add_action( 'save_post', 'kosmiteia_flush_announcement_years' );
add_action( 'deleted_post', 'kosmiteia_flush_announcement_years' );

/**
 * Κρυφά πεδία ώστε η φόρμα (method="get") να μη χάνει παραμέτρους που δεν
 * ελέγχει η ίδια: το ?lang=en της δίγλωσσης λειτουργίας και το ?post_type=...
 * όταν ο ιστότοπος τρέχει με απλούς (μη «όμορφους») μόνιμους συνδέσμους.
 *
 * @param string $action URL δράσης της φόρμας. Επιστρέφεται χωρίς query string.
 * @return string HTML με τα hidden inputs.
 */
function kosmiteia_announcement_filters_hidden_fields( &$action ) {
	$carry = array();
	$parts = wp_parse_url( $action );

	if ( ! empty( $parts['query'] ) ) {
		parse_str( $parts['query'], $carry );
		$action = strtok( $action, '?' );
	}

	$carry = array_merge( $carry, kosmiteia_announcement_filters_lang_arg() );

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
 * Ένα <select> με τους όρους μιας ταξινομίας.
 *
 * @param string $id       ID στοιχείου.
 * @param string $name     Όνομα παραμέτρου.
 * @param string $label    Ετικέτα.
 * @param string $taxonomy Ταξινομία.
 * @param string $all      Κείμενο της επιλογής «όλα».
 * @param string $current  Τρέχουσα τιμή (slug).
 * @return string HTML, ή κενό αν δεν υπάρχουν όροι.
 */
function kosmiteia_announcement_filters_term_select( $id, $name, $label, $taxonomy, $all, $current ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
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

	return sprintf(
		'<p class="kosmiteia-filters__field"><label for="%1$s">%2$s</label><select id="%1$s" name="%3$s">%4$s</select></p>',
		esc_attr( $id ),
		esc_html( $label ),
		esc_attr( $name ),
		$options
	);
}

/**
 * Η φόρμα φίλτρων σε HTML.
 *
 * @param array $attributes Attributes του μπλοκ.
 * @return string
 */
function kosmiteia_announcement_filters_html( $attributes = array() ) {
	static $instance = 0;
	++$instance;

	$attributes = wp_parse_args(
		$attributes,
		array(
			'showSearch'   => true,
			'showCategory' => true,
			'showFaculty'  => true,
			'showYear'     => true,
			'showCount'    => true,
		)
	);

	$archive = get_post_type_archive_link( 'kosm_announcement' );

	if ( ! $archive ) {
		return '';
	}

	$state  = kosmiteia_announcement_filters_state();
	$action = $archive;
	$hidden = kosmiteia_announcement_filters_hidden_fields( $action );
	$prefix = 'kosmiteia-filter-' . $instance;
	$fields = '';

	if ( $attributes['showSearch'] ) {
		$fields .= sprintf(
			'<p class="kosmiteia-filters__field kosmiteia-filters__field--search"><label for="%1$s-q">%2$s</label><input type="search" id="%1$s-q" name="kosm_q" value="%3$s" placeholder="%4$s" /></p>',
			esc_attr( $prefix ),
			esc_html__( 'Αναζήτηση', 'kosmiteia' ),
			esc_attr( $state['q'] ),
			esc_attr__( 'Λέξη-κλειδί, π.χ. υποτροφίες', 'kosmiteia' )
		);
	}

	if ( $attributes['showCategory'] ) {
		$fields .= kosmiteia_announcement_filters_term_select(
			$prefix . '-cat',
			'kosm_cat',
			__( 'Κατηγορία', 'kosmiteia' ),
			'kosm_ann_category',
			__( 'Όλες οι κατηγορίες', 'kosmiteia' ),
			$state['cat']
		);
	}

	if ( $attributes['showFaculty'] ) {
		$fields .= kosmiteia_announcement_filters_term_select(
			$prefix . '-school',
			'kosm_fac',
			__( 'Σχολή', 'kosmiteia' ),
			'kosm_faculty',
			__( 'Όλες οι Σχολές', 'kosmiteia' ),
			$state['school']
		);
	}

	if ( $attributes['showYear'] ) {
		$years = kosmiteia_announcement_years();

		if ( $years ) {
			$options = sprintf( '<option value="">%s</option>', esc_html__( 'Όλα τα έτη', 'kosmiteia' ) );

			foreach ( $years as $year ) {
				$options .= sprintf(
					'<option value="%1$d"%2$s>%1$d</option>',
					$year,
					selected( $state['year'], $year, false )
				);
			}

			$fields .= sprintf(
				'<p class="kosmiteia-filters__field"><label for="%1$s-year">%2$s</label><select id="%1$s-year" name="kosm_year">%3$s</select></p>',
				esc_attr( $prefix ),
				esc_html__( 'Έτος', 'kosmiteia' ),
				$options
			);
		}
	}

	$actions = sprintf(
		'<button type="submit" class="wp-element-button kosmiteia-filters__submit">%s</button>',
		esc_html__( 'Φιλτράρισμα', 'kosmiteia' )
	);

	if ( kosmiteia_announcement_filters_active( $state ) ) {
		$reset     = add_query_arg( kosmiteia_announcement_filters_lang_arg(), $archive );
		$actions .= sprintf(
			'<a class="kosmiteia-filters__reset" href="%s">%s</a>',
			esc_url( $reset ),
			esc_html__( 'Καθαρισμός φίλτρων', 'kosmiteia' )
		);
	}

	$count = '';

	if ( $attributes['showCount'] ) {
		$count = sprintf(
			'<p class="kosmiteia-filters__count" role="status">%s</p>',
			esc_html( kosmiteia_announcement_filters_count_text( $state ) )
		);
	}

	return sprintf(
		'<form class="kosmiteia-filters" method="get" action="%1$s" role="search" aria-label="%2$s">%3$s<div class="kosmiteia-filters__fields">%4$s<p class="kosmiteia-filters__actions">%5$s</p></div>%6$s</form>',
		esc_url( $action ),
		esc_attr__( 'Αναζήτηση και φίλτρα ανακοινώσεων', 'kosmiteia' ),
		$hidden,
		$fields,
		$actions,
		$count
	);
}

/**
 * Το κείμενο του μετρητή αποτελεσμάτων.
 *
 * @param array $state Κατάσταση φίλτρων.
 * @return string
 */
function kosmiteia_announcement_filters_count_text( $state ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! is_post_type_archive( 'kosm_announcement' ) ) {
		return __( 'Ο αριθμός αποτελεσμάτων εμφανίζεται στο front-end.', 'kosmiteia' );
	}

	global $wp_query;

	$found = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;

	if ( 0 === $found ) {
		return __( 'Καμία ανακοίνωση δεν ταιριάζει με τα φίλτρα.', 'kosmiteia' );
	}

	if ( 1 === $found ) {
		return __( 'Βρέθηκε 1 ανακοίνωση.', 'kosmiteia' );
	}

	if ( kosmiteia_announcement_filters_active( $state ) ) {
		return sprintf(
			/* translators: %d: αριθμός αποτελεσμάτων. */
			__( 'Βρέθηκαν %d ανακοινώσεις.', 'kosmiteia' ),
			$found
		);
	}

	return sprintf(
		/* translators: %d: συνολικός αριθμός ανακοινώσεων. */
		__( 'Σύνολο: %d ανακοινώσεις.', 'kosmiteia' ),
		$found
	);
}

/**
 * Render callback του μπλοκ «Φίλτρα ανακοινώσεων».
 *
 * @param array $attributes Attributes του μπλοκ.
 * @return string
 */
function kosmiteia_render_announcement_filters_block( $attributes ) {
	$html = kosmiteia_announcement_filters_html( $attributes );

	if ( ! $html ) {
		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		$html
	);
}
