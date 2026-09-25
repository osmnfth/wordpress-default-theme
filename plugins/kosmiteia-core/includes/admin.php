<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_tools_menu() {
	add_submenu_page(
		'kosmiteia-settings',
		__( 'Εργαλεία Κοσμητείας', 'kosmiteia' ),
		__( 'Εργαλεία', 'kosmiteia' ),
		'manage_options',
		'kosmiteia-tools',
		'kosmiteia_tools_page'
	);
}
add_action( 'admin_menu', 'kosmiteia_tools_menu' );

function kosmiteia_tools_render_log( $messages ) {
	if ( ! $messages ) {
		return;
	}

	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Αποτέλεσμα', 'kosmiteia' ) . '</strong></p><ul style="margin:0 0 1em 1.5em;list-style:disc">';

	foreach ( $messages as $entry ) {
		$prefix = '';

		if ( 'warning' === $entry['type'] ) {
			$prefix = '⚠ ';
		} elseif ( 'success' === $entry['type'] ) {
			$prefix = '✔ ';
		}

		echo '<li>' . esc_html( $prefix . $entry['message'] ) . '</li>';
	}

	echo '</ul></div>';
}

function kosmiteia_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$messages = array();
	$action   = isset( $_POST['kosmiteia_action'] ) ? sanitize_key( wp_unslash( $_POST['kosmiteia_action'] ) ) : '';

	if ( 'seed' === $action && check_admin_referer( 'kosmiteia_tools' ) ) {
		$messages = kosmiteia_install_demo_content( ! empty( $_POST['kosmiteia_force'] ) );
	}

	if ( 'import' === $action && check_admin_referer( 'kosmiteia_tools' ) ) {
		$result = kosmiteia_import_announcements(
			array(
				'feed'    => isset( $_POST['kosmiteia_feed'] ) ? esc_url_raw( wp_unslash( $_POST['kosmiteia_feed'] ) ) : '',
				'pages'   => isset( $_POST['kosmiteia_pages'] ) ? (int) $_POST['kosmiteia_pages'] : 5,
				'limit'   => isset( $_POST['kosmiteia_limit'] ) ? (int) $_POST['kosmiteia_limit'] : 0,
				'dry_run' => ! empty( $_POST['kosmiteia_dry_run'] ),
				'media'   => ! empty( $_POST['kosmiteia_media'] ),
				'faculty' => isset( $_POST['kosmiteia_faculty'] ) ? sanitize_text_field( wp_unslash( $_POST['kosmiteia_faculty'] ) ) : '',
				'status'  => isset( $_POST['kosmiteia_status'] ) ? sanitize_key( wp_unslash( $_POST['kosmiteia_status'] ) ) : 'publish',
			)
		);

		$messages = isset( $result['messages'] ) ? $result['messages'] : array();
	}

	$defaults   = kosmiteia_import_defaults();
	$faculties  = get_terms(
		array(
			'taxonomy'   => 'kosm_faculty',
			'hide_empty' => false,
		)
	);
	$faculties  = is_wp_error( $faculties ) ? array() : $faculties;

	echo '<div class="wrap"><h1>' . esc_html__( 'Εργαλεία Κοσμητείας', 'kosmiteia' ) . '</h1>';

	kosmiteia_tools_render_log( $messages );

	echo '<div class="card" style="max-width:46rem"><h2>' . esc_html__( 'Αρχικό περιεχόμενο', 'kosmiteia' ) . '</h2>';
	echo '<p>' . esc_html__( 'Δημιουργεί Τμήματα, Ανακοινώσεις, Μεταπτυχιακά, σελίδες, μενού (EL/EN) και τα template parts της αρχικής, αντλώντας τα patterns από το ενεργό θέμα. Δεν αγγίζει ό,τι έχετε ήδη επεξεργαστεί.', 'kosmiteia' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'kosmiteia_tools' );
	echo '<input type="hidden" name="kosmiteia_action" value="seed">';
	echo '<p><label><input type="checkbox" name="kosmiteia_force" value="1"> ' . esc_html__( 'Ξαναδημιουργία ακόμη κι αν υπάρχει ήδη', 'kosmiteia' ) . '</label></p>';
	submit_button( __( 'Δημιουργία αρχικού περιεχομένου', 'kosmiteia' ), 'primary', 'submit', false );
	echo '</form></div>';

	echo '<div class="card" style="max-width:46rem;margin-top:1.5rem"><h2>' . esc_html__( 'Εισαγωγή ανακοινώσεων από παλιό ιστότοπο', 'kosmiteia' ) . '</h2>';
	echo '<p>' . esc_html__( 'Διαβάζει το RSS feed του παλιού ιστότοπου και δημιουργεί Ανακοινώσεις με τις αρχικές ημερομηνίες, τις κατηγορίες τους και τα συνημμένα PDF. Ξανατρέξιμο δεν δημιουργεί διπλότυπα.', 'kosmiteia' ) . '</p>';
	echo '<form method="post"><table class="form-table" role="presentation"><tbody>';

	printf(
		'<tr><th scope="row"><label for="kosmiteia-feed">%1$s</label></th><td><input type="url" class="regular-text" id="kosmiteia-feed" name="kosmiteia_feed" value="%2$s"></td></tr>',
		esc_html__( 'RSS feed', 'kosmiteia' ),
		esc_attr( $defaults['feed'] )
	);

	printf(
		'<tr><th scope="row"><label for="kosmiteia-pages">%1$s</label></th><td><input type="number" min="1" max="200" id="kosmiteia-pages" name="kosmiteia_pages" value="5"> <p class="description">%2$s</p></td></tr>',
		esc_html__( 'Σελίδες feed', 'kosmiteia' ),
		esc_html__( '10 ανακοινώσεις ανά σελίδα. Για πλήρη εισαγωγή προτιμήστε τη γραμμή εντολών: wp kosmiteia import-announcements', 'kosmiteia' )
	);

	printf(
		'<tr><th scope="row"><label for="kosmiteia-limit">%1$s</label></th><td><input type="number" min="0" id="kosmiteia-limit" name="kosmiteia_limit" value="0"> <p class="description">%2$s</p></td></tr>',
		esc_html__( 'Όριο ανακοινώσεων', 'kosmiteia' ),
		esc_html__( '0 = χωρίς όριο.', 'kosmiteia' )
	);

	echo '<tr><th scope="row">' . esc_html__( 'Σχολή', 'kosmiteia' ) . '</th><td><select name="kosmiteia_faculty"><option value="">' . esc_html__( '— καμία —', 'kosmiteia' ) . '</option>';

	foreach ( $faculties as $term ) {
		printf( '<option value="%1$s">%1$s</option>', esc_attr( $term->name ) );
	}

	echo '</select></td></tr>';

	echo '<tr><th scope="row">' . esc_html__( 'Κατάσταση', 'kosmiteia' ) . '</th><td><select name="kosmiteia_status">';
	echo '<option value="publish">' . esc_html__( 'Δημοσιευμένες', 'kosmiteia' ) . '</option>';
	echo '<option value="draft">' . esc_html__( 'Πρόχειρα', 'kosmiteia' ) . '</option>';
	echo '</select></td></tr>';

	echo '<tr><th scope="row">' . esc_html__( 'Επιλογές', 'kosmiteia' ) . '</th><td>';
	echo '<p><label><input type="checkbox" name="kosmiteia_media" value="1" checked> ' . esc_html__( 'Λήψη συνημμένων (PDF, εικόνες) στη Βιβλιοθήκη', 'kosmiteia' ) . '</label></p>';
	echo '<p><label><input type="checkbox" name="kosmiteia_dry_run" value="1"> ' . esc_html__( 'Δοκιμή χωρίς εγγραφή στη βάση', 'kosmiteia' ) . '</label></p>';
	echo '</td></tr>';

	echo '</tbody></table>';
	wp_nonce_field( 'kosmiteia_tools' );
	echo '<input type="hidden" name="kosmiteia_action" value="import">';
	submit_button( __( 'Έναρξη εισαγωγής', 'kosmiteia' ), 'primary', 'submit', false );
	echo '</form></div>';

	echo '</div>';
}
