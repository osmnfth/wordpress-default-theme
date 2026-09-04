<?php
/**
 * Ρυθμίσεις ιστότοπου (Κοσμητεία → Ρυθμίσεις).
 *
 * Ό,τι αλλάζει από ίδρυμα σε ίδρυμα - διεύθυνση, τηλέφωνα, ωράριο, χάρτης,
 * κοινωνικά δίκτυα, αριθμός ανακοινώσεων ανά σελίδα - ζει εδώ και όχι μέσα
 * στα templates ή στα patterns. Τα κείμενα διαβάζονται στη σελίδα μέσω των
 * Block Bindings (δείτε includes/bindings.php), οπότε μια αλλαγή εδώ
 * ενημερώνει αυτόματα κάθε σημείο που τα εμφανίζει.
 *
 * Ανάγνωση από κώδικα:  kosmiteia_option( 'contact_email' )
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

const KOSMITEIA_SETTINGS_OPTION = 'kosmiteia_settings';
const KOSMITEIA_SETTINGS_GROUP  = 'kosmiteia_settings_group';

/**
 * Οι ενότητες και τα πεδία των ρυθμίσεων.
 *
 * Κάθε πεδίο: label, type (text|textarea|email|url|tel|number|checkbox),
 * default και προαιρετικά description / step / min / max.
 *
 * @return array
 */
function kosmiteia_settings_schema() {
	$schema = array(

		'identity' => array(
			'title'  => __( 'Ταυτότητα', 'kosmiteia' ),
			'fields' => array(
				'institution'   => array(
					'label'       => __( 'Ίδρυμα', 'kosmiteia' ),
					'type'        => 'text',
					'default'     => __( 'Δημοκρίτειο Πανεπιστήμιο Θράκης', 'kosmiteia' ),
					'description' => __( 'Εμφανίζεται στο υποσέλιδο και στα structured data.', 'kosmiteia' ),
				),
				'dean_name'     => array(
					'label'   => __( 'Κοσμήτορας', 'kosmiteia' ),
					'type'    => 'text',
					'default' => '',
				),
				'dean_title'    => array(
					'label'   => __( 'Ιδιότητα Κοσμήτορα', 'kosmiteia' ),
					'type'    => 'text',
					'default' => __( 'Καθηγητής', 'kosmiteia' ),
				),
				'tagline'       => array(
					'label'       => __( 'Μότο υποσέλιδου', 'kosmiteia' ),
					'type'        => 'textarea',
					'default'     => __( 'Παιδεία, έρευνα και κοινωνική προσφορά. Η Κοσμητεία συντονίζει τις Σχολές, τα προγράμματα σπουδών και την ακαδημαϊκή κοινότητα.', 'kosmiteia' ),
				),
				'copyright'     => array(
					'label'       => __( 'Κείμενο copyright', 'kosmiteia' ),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Αν μείνει κενό συντίθεται αυτόματα: «© έτος - όνομα ιστότοπου».', 'kosmiteia' ),
				),
			),
		),

		'contact'  => array(
			'title'  => __( 'Επικοινωνία', 'kosmiteia' ),
			'fields' => array(
				'contact_address' => array(
					'label'   => __( 'Διεύθυνση', 'kosmiteia' ),
					'type'    => 'text',
					'default' => '',
				),
				'contact_phone'   => array(
					'label'   => __( 'Τηλέφωνο', 'kosmiteia' ),
					'type'    => 'tel',
					'default' => '',
				),
				'contact_fax'     => array(
					'label'   => __( 'Fax', 'kosmiteia' ),
					'type'    => 'tel',
					'default' => '',
				),
				'contact_email'   => array(
					'label'   => __( 'Email', 'kosmiteia' ),
					'type'    => 'email',
					'default' => '',
				),
				'contact_hours'   => array(
					'label'   => __( 'Ωράριο εξυπηρέτησης', 'kosmiteia' ),
					'type'    => 'text',
					'default' => __( 'Δευτέρα έως Παρασκευή, 09:00-14:00', 'kosmiteia' ),
				),
				'contact_notes'   => array(
					'label'       => __( 'Σημείωση πρόσβασης', 'kosmiteia' ),
					'type'        => 'textarea',
					'default'     => '',
					'description' => __( 'Π.χ. οδηγίες πρόσβασης ή προσβασιμότητα κτηρίου.', 'kosmiteia' ),
				),
			),
		),

		'map'      => array(
			'title'  => __( 'Χάρτης', 'kosmiteia' ),
			'fields' => array(
				'map_lat'  => array(
					'label'   => __( 'Γεωγραφικό πλάτος', 'kosmiteia' ),
					'type'    => 'number',
					'step'    => '0.000001',
					'default' => 41.1226,
				),
				'map_lng'  => array(
					'label'   => __( 'Γεωγραφικό μήκος', 'kosmiteia' ),
					'type'    => 'number',
					'step'    => '0.000001',
					'default' => 25.4064,
				),
				'map_zoom' => array(
					'label'   => __( 'Zoom', 'kosmiteia' ),
					'type'    => 'number',
					'min'     => 1,
					'max'     => 19,
					'default' => 16,
				),
			),
		),

		'social'   => array(
			'title'  => __( 'Κοινωνικά δίκτυα', 'kosmiteia' ),
			'fields' => array(
				'social_facebook' => array(
					'label'   => __( 'Facebook', 'kosmiteia' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_instagram' => array(
					'label'   => __( 'Instagram', 'kosmiteia' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_youtube'  => array(
					'label'   => __( 'YouTube', 'kosmiteia' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_linkedin' => array(
					'label'   => __( 'LinkedIn', 'kosmiteia' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_x'        => array(
					'label'   => __( 'X / Twitter', 'kosmiteia' ),
					'type'    => 'url',
					'default' => '',
				),
			),
		),

		'content'  => array(
			'title'  => __( 'Περιεχόμενο', 'kosmiteia' ),
			'fields' => array(
				'announcements_per_page' => array(
					'label'       => __( 'Ανακοινώσεις ανά σελίδα', 'kosmiteia' ),
					'type'        => 'number',
					'min'         => 1,
					'max'         => 100,
					'default'     => 10,
					'description' => __( 'Ισχύει στο αρχείο Ανακοινώσεων και στα φίλτρα του.', 'kosmiteia' ),
				),
				'events_per_page'        => array(
					'label'   => __( 'Εκδηλώσεις ανά σελίδα', 'kosmiteia' ),
					'type'    => 'number',
					'min'     => 1,
					'max'     => 100,
					'default' => 10,
				),
				'excerpt_length'         => array(
					'label'   => __( 'Λέξεις περίληψης', 'kosmiteia' ),
					'type'    => 'number',
					'min'     => 5,
					'max'     => 120,
					'default' => 24,
				),
				'hide_past_events'       => array(
					'label'       => __( 'Απόκρυψη περασμένων εκδηλώσεων', 'kosmiteia' ),
					'type'        => 'checkbox',
					'default'     => 1,
					'description' => __( 'Στο αρχείο Εκδηλώσεων εμφανίζονται μόνο οι επόμενες.', 'kosmiteia' ),
				),
			),
		),

		'language' => array(
			'title'  => __( 'Γλώσσες', 'kosmiteia' ),
			'fields' => array(
				'enable_en' => array(
					'label'       => __( 'Αγγλική έκδοση', 'kosmiteia' ),
					'type'        => 'checkbox',
					'default'     => 1,
					'description' => __( 'Ενεργοποιεί τον επιλογέα γλώσσας και τα αγγλικά template parts (?lang=en).', 'kosmiteia' ),
				),
			),
		),
	);

	/**
	 * Φίλτρο για προσθήκη/αφαίρεση πεδίων από child theme ή plugin.
	 *
	 * @param array $schema Οι ενότητες και τα πεδία.
	 */
	return apply_filters( 'kosmiteia_settings_schema', $schema );
}

/**
 * Οι προεπιλεγμένες τιμές όλων των πεδίων.
 *
 * @return array
 */
function kosmiteia_settings_defaults() {
	$defaults = array();

	foreach ( kosmiteia_settings_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
		}
	}

	return $defaults;
}

/**
 * Συμπληρώνει όσες προεπιλογές λείπουν (κατά την ενεργοποίηση).
 */
function kosmiteia_settings_add_defaults() {
	$saved = get_option( KOSMITEIA_SETTINGS_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	update_option( KOSMITEIA_SETTINGS_OPTION, array_merge( kosmiteia_settings_defaults(), $saved ) );
}

/**
 * Όλες οι ρυθμίσεις, με τις προεπιλογές συμπληρωμένες.
 *
 * @return array
 */
function kosmiteia_settings() {
	static $cache   = null;
	static $loading = false;

	if ( null !== $cache ) {
		return $cache;
	}

	// Οι προεπιλογές περνούν από __(), δηλαδή από τον μηχανισμό μετάφρασης· αν
	// κάποιο φίλτρο γλώσσας ζητήσει ρύθμιση εκείνη τη στιγμή, θα ξαναμπαίναμε
	// εδώ. Στην περίπτωση αυτή επιστρέφουμε ό,τι είναι αποθηκευμένο, χωρίς
	// προεπιλογές, ώστε να μη δημιουργηθεί ατέρμονη αναδρομή.
	if ( $loading ) {
		$saved = get_option( KOSMITEIA_SETTINGS_OPTION, array() );

		return is_array( $saved ) ? $saved : array();
	}

	$loading = true;

	$saved   = get_option( KOSMITEIA_SETTINGS_OPTION, array() );
	$cache   = array_merge( kosmiteia_settings_defaults(), is_array( $saved ) ? $saved : array() );
	$loading = false;

	return $cache;
}

/**
 * Μία ρύθμιση.
 *
 * @param string $key     Κλειδί πεδίου.
 * @param mixed  $default Τιμή αν λείπει.
 * @return mixed
 */
function kosmiteia_option( $key, $default = '' ) {
	$settings = kosmiteia_settings();

	if ( ! isset( $settings[ $key ] ) || '' === $settings[ $key ] ) {
		return $default;
	}

	/**
	 * Φίλτρο ανά ρύθμιση.
	 *
	 * @param mixed  $value Η τιμή.
	 * @param string $key   Το κλειδί.
	 */
	return apply_filters( 'kosmiteia_option', $settings[ $key ], $key );
}

/**
 * Ανάγνωση ρύθμισης χωρίς να «ξυπνήσει» ο μηχανισμός μεταφράσεων.
 *
 * Οι προεπιλογές των πεδίων περνούν από __(), οπότε δεν πρέπει να ζητούνται πριν
 * το init (το WordPress 6.7+ βγάζει ειδοποίηση «translation triggered too
 * early»). Όποιος χρειάζεται ρύθμιση πολύ νωρίς - π.χ. το φίλτρο γλώσσας -
 * χρησιμοποιεί αυτή τη συνάρτηση, που διαβάζει σκέτα την αποθηκευμένη τιμή.
 *
 * @param string $key     Κλειδί πεδίου.
 * @param mixed  $default Τιμή αν λείπει.
 * @return mixed
 */
function kosmiteia_option_raw( $key, $default = '' ) {
	$saved = get_option( KOSMITEIA_SETTINGS_OPTION, array() );

	if ( ! is_array( $saved ) || ! isset( $saved[ $key ] ) || '' === $saved[ $key ] ) {
		return $default;
	}

	return $saved[ $key ];
}

/**
 * Καθαρισμός των τιμών πριν την αποθήκευση.
 *
 * @param array $input Ό,τι ήρθε από τη φόρμα.
 * @return array
 */
function kosmiteia_settings_sanitize( $input ) {
	$clean = array();

	foreach ( kosmiteia_settings_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$type  = isset( $field['type'] ) ? $field['type'] : 'text';
			$value = isset( $input[ $key ] ) ? $input[ $key ] : '';

			switch ( $type ) {
				case 'checkbox':
					$clean[ $key ] = empty( $value ) ? 0 : 1;
					break;

				case 'email':
					$clean[ $key ] = sanitize_email( $value );
					break;

				case 'url':
					$clean[ $key ] = esc_url_raw( $value );
					break;

				case 'number':
					$clean[ $key ] = ( '' === $value ) ? '' : ( 0 === strpos( (string) ( isset( $field['step'] ) ? $field['step'] : '' ), '0.' ) ? (float) $value : (int) $value );
					break;

				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $value );
					break;

				default:
					$clean[ $key ] = sanitize_text_field( $value );
					break;
			}
		}
	}

	return $clean;
}

/**
 * Καταχώριση της ρύθμισης (Settings API + REST, ώστε να διαβάζεται και από τον editor).
 */
function kosmiteia_settings_register() {
	register_setting(
		KOSMITEIA_SETTINGS_GROUP,
		KOSMITEIA_SETTINGS_OPTION,
		array(
			'type'              => 'object',
			'sanitize_callback' => 'kosmiteia_settings_sanitize',
			'default'           => kosmiteia_settings_defaults(),
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'kosmiteia_settings_register' );

/**
 * Μενού «Κοσμητεία» με τις σελίδες Ρυθμίσεων και Εργαλείων.
 */
function kosmiteia_settings_menu() {
	add_menu_page(
		__( 'Κοσμητεία', 'kosmiteia' ),
		__( 'Κοσμητεία', 'kosmiteia' ),
		'manage_options',
		'kosmiteia-settings',
		'kosmiteia_settings_page',
		'dashicons-welcome-learn-more',
		3
	);

	add_submenu_page(
		'kosmiteia-settings',
		__( 'Ρυθμίσεις Κοσμητείας', 'kosmiteia' ),
		__( 'Ρυθμίσεις', 'kosmiteia' ),
		'manage_options',
		'kosmiteia-settings',
		'kosmiteia_settings_page'
	);
}
add_action( 'admin_menu', 'kosmiteia_settings_menu' );

/**
 * Ένα πεδίο της φόρμας.
 *
 * @param string $key   Κλειδί.
 * @param array  $field Ορισμός πεδίου.
 * @param mixed  $value Τρέχουσα τιμή.
 */
function kosmiteia_settings_field( $key, $field, $value ) {
	$name = KOSMITEIA_SETTINGS_OPTION . '[' . $key . ']';
	$id   = 'kosmiteia-' . str_replace( '_', '-', $key );
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

	if ( 'textarea' === $type ) {
		printf(
			'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( (string) $value )
		);
	} elseif ( 'checkbox' === $type ) {
		printf(
			'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>',
			esc_attr( $id ),
			esc_attr( $name ),
			checked( 1, (int) $value, false ),
			esc_html__( 'Ναι', 'kosmiteia' )
		);
	} else {
		$extra = '';

		foreach ( array( 'step', 'min', 'max' ) as $attribute ) {
			if ( isset( $field[ $attribute ] ) ) {
				$extra .= sprintf( ' %s="%s"', $attribute, esc_attr( $field[ $attribute ] ) );
			}
		}

		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text"%5$s>',
			esc_attr( $type ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( (string) $value ),
			$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Τα μέρη του έχουν ήδη περάσει από esc_attr().
		);
	}

	if ( ! empty( $field['description'] ) ) {
		echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
	}

	echo '</td></tr>';
}

/**
 * Η σελίδα ρυθμίσεων.
 */
function kosmiteia_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = kosmiteia_settings();

	echo '<div class="wrap"><h1>' . esc_html__( 'Ρυθμίσεις Κοσμητείας', 'kosmiteia' ) . '</h1>';
	echo '<p>' . esc_html__( 'Τα στοιχεία αυτά τροφοδοτούν το υποσέλιδο, τη σελίδα επικοινωνίας, τον χάρτη και τα δομημένα δεδομένα. Δεν χρειάζεται επέμβαση σε templates.', 'kosmiteia' ) . '</p>';
	echo '<form method="post" action="options.php">';

	settings_fields( KOSMITEIA_SETTINGS_GROUP );

	foreach ( kosmiteia_settings_schema() as $section_key => $section ) {
		echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ( $section['fields'] as $key => $field ) {
			kosmiteia_settings_field( $key, $field, isset( $settings[ $key ] ) ? $settings[ $key ] : '' );
		}

		echo '</tbody></table>';
	}

	submit_button();

	echo '</form></div>';
}

/**
 * Καθάρισμα της στατικής cache όταν αποθηκεύονται οι ρυθμίσεις.
 */
function kosmiteia_settings_flush_cache() {
	wp_cache_delete( KOSMITEIA_SETTINGS_OPTION, 'options' );
}
add_action( 'update_option_' . KOSMITEIA_SETTINGS_OPTION, 'kosmiteia_settings_flush_cache' );
