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
 * Κάθε πεδίο: label, type (text|textarea|richtext|email|url|tel|number|
 * checkbox|image|page|select), default και προαιρετικά description / options /
 * step / min / max.
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
				'dean_page'     => array(
					'label'       => __( 'Σελίδα μηνύματος Κοσμήτορα', 'kosmiteia' ),
					'type'        => 'page',
					'default'     => 0,
					'description' => __( 'Εκεί οδηγεί το κουμπί «Διαβάστε περισσότερα» της ενότητας στην αρχική, καθώς και το token {{url_dean}}.', 'kosmiteia' ),
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
				'programs_per_page'      => array(
					'label'       => __( 'Μεταπτυχιακά ανά σελίδα', 'kosmiteia' ),
					'type'        => 'number',
					'min'         => 1,
					'max'         => 100,
					'default'     => 12,
					'description' => __( 'Ισχύει στο αρχείο Μεταπτυχιακών και στα φίλτρα του.', 'kosmiteia' ),
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

		'floating' => array(
			'title'  => __( 'Πλωτό κουμπί', 'kosmiteia' ),
			'fields' => array(
				'floating_enable'     => array(
					'label'       => __( 'Εμφάνιση πλωτού κουμπιού', 'kosmiteia' ),
					'type'        => 'checkbox',
					'default'     => 1,
					'description' => __( 'Στρογγυλό κουμπί κάτω δεξιά. Φαίνεται στην κορυφή της σελίδας, κρύβεται με το σκρολάρισμα και επανεμφανίζεται στο τέλος της σελίδας.', 'kosmiteia' ),
				),
				'floating_image'      => array(
					'label'       => __( 'Εικόνα κουμπιού', 'kosmiteia' ),
					'type'        => 'image',
					'default'     => 0,
					'description' => __( 'Συνήθως το λογότυπο. Αν μείνει κενή, χρησιμοποιείται το λογότυπο του ιστότοπου.', 'kosmiteia' ),
				),
				'floating_label'      => array(
					'label'       => __( 'Περιγραφή κουμπιού', 'kosmiteia' ),
					'type'        => 'text',
					'default'     => __( 'Μήνυμα της Κοσμητείας', 'kosmiteia' ),
					'description' => __( 'Διαβάζεται από τους αναγνώστες οθόνης και εμφανίζεται ως tooltip.', 'kosmiteia' ),
				),
				'floating_title'      => array(
					'label'   => __( 'Τίτλος παραθύρου', 'kosmiteia' ),
					'type'    => 'text',
					'default' => '',
				),
				'floating_text'       => array(
					'label'       => __( 'Κείμενο παραθύρου', 'kosmiteia' ),
					'type'        => 'richtext',
					'default'     => '',
					'description' => __( 'Χωρίς τίτλο και κείμενο το κουμπί δεν εμφανίζεται.', 'kosmiteia' ),
				),
				'floating_link'       => array(
					'label'   => __( 'Σύνδεσμος παραθύρου', 'kosmiteia' ),
					'type'    => 'url',
					'default' => '',
				),
				'floating_link_label' => array(
					'label'   => __( 'Κείμενο συνδέσμου', 'kosmiteia' ),
					'type'    => 'text',
					'default' => __( 'Περισσότερα', 'kosmiteia' ),
				),
			),
		),

		'loader'   => array(
			'title'  => __( 'Οθόνη φόρτωσης', 'kosmiteia' ),
			'fields' => array(
				'loader_enable' => array(
					'label'       => __( 'Εμφάνιση οθόνης φόρτωσης', 'kosmiteia' ),
					'type'        => 'checkbox',
					'default'     => 1,
					'description' => __( 'Λευκή οθόνη με το λογότυπο στο κέντρο, όσο φορτώνει η επόμενη σελίδα σε αργή σύνδεση.', 'kosmiteia' ),
				),
				'loader_scope'  => array(
					'label'   => __( 'Πού εμφανίζεται', 'kosmiteia' ),
					'type'    => 'select',
					'default' => 'mobile',
					'options' => array(
						'mobile' => __( 'Μόνο σε κινητά και tablet', 'kosmiteia' ),
						'all'    => __( 'Σε όλες τις συσκευές', 'kosmiteia' ),
					),
				),
				'loader_image'  => array(
					'label'       => __( 'Λογότυπο οθόνης φόρτωσης', 'kosmiteia' ),
					'type'        => 'image',
					'default'     => 0,
					'description' => __( 'Αν μείνει κενό, χρησιμοποιείται το λογότυπο του ιστότοπου.', 'kosmiteia' ),
				),
				'loader_delay'  => array(
					'label'       => __( 'Καθυστέρηση εμφάνισης (ms)', 'kosmiteia' ),
					'type'        => 'number',
					'min'         => 0,
					'max'         => 3000,
					'default'     => 350,
					'description' => __( 'Σε γρήγορη σύνδεση η σελίδα προλαβαίνει να φορτώσει και η οθόνη δεν εμφανίζεται καθόλου.', 'kosmiteia' ),
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

				case 'richtext':
					// Επιτρέπεται ό,τι και σε ένα άρθρο (σύνδεσμοι, έντονα,
					// λίστες) - τίποτα εκτελέσιμο.
					$clean[ $key ] = wp_kses_post( $value );
					break;

				case 'image':
				case 'page':
					$clean[ $key ] = absint( $value );
					break;

				case 'select':
					$options       = isset( $field['options'] ) ? $field['options'] : array();
					$value         = sanitize_key( $value );
					$clean[ $key ] = isset( $options[ $value ] ) ? $value : (string) $field['default'];
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

	// Ο editor του WordPress δέχεται id μόνο με πεζά και κάτω παύλες.
	$label_for = ( 'richtext' === $type ) ? str_replace( '-', '_', $id ) : $id;

	echo '<tr><th scope="row"><label for="' . esc_attr( $label_for ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

	if ( 'textarea' === $type ) {
		printf(
			'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( (string) $value )
		);
	} elseif ( 'richtext' === $type ) {
		wp_editor(
			(string) $value,
			$label_for,
			array(
				'textarea_name' => $name,
				'textarea_rows' => 6,
				'media_buttons' => false,
				'teeny'         => true,
			)
		);
	} elseif ( 'image' === $type ) {
		kosmiteia_settings_image_field( $id, $name, (int) $value );
	} elseif ( 'select' === $type ) {
		$options = '';

		foreach ( (array) $field['options'] as $option_value => $option_label ) {
			$options .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option_value ),
				selected( (string) $value, (string) $option_value, false ),
				esc_html( $option_label )
			);
		}

		printf(
			'<select id="%1$s" name="%2$s">%3$s</select>',
			esc_attr( $id ),
			esc_attr( $name ),
			$options // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Τα μέρη του έχουν ήδη περάσει από esc_attr()/esc_html().
		);
	} elseif ( 'page' === $type ) {
		wp_dropdown_pages(
			array(
				'name'              => $name,
				'id'                => $id,
				'selected'          => (int) $value,
				'show_option_none'  => __( '— καμία —', 'kosmiteia' ),
				'option_none_value' => 0,
			)
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
 * Πεδίο επιλογής εικόνας από τη Βιβλιοθήκη πολυμέσων.
 *
 * Χωρίς JavaScript παραμένει χρησιμοποιήσιμο: το ID της εικόνας φαίνεται και
 * γράφεται με το χέρι στο πεδίο κειμένου.
 *
 * @param string $id    HTML id.
 * @param string $name  Όνομα πεδίου.
 * @param int    $value Το ID της εικόνας.
 */
function kosmiteia_settings_image_field( $id, $name, $value ) {
	$image = $value ? wp_get_attachment_image( $value, 'medium', false, array( 'style' => 'max-width:180px;height:auto' ) ) : '';

	printf(
		'<div class="kosmiteia-image-field" data-kosmiteia-image-field><div class="kosmiteia-image-field__preview" data-preview>%1$s</div>'
		. '<p><button type="button" class="button" data-action="select">%2$s</button> '
		. '<button type="button" class="button-link" data-action="remove"%3$s>%4$s</button></p>'
		. '<p><label>%5$s <input type="number" min="0" step="1" id="%6$s" name="%7$s" value="%8$d" class="small-text" data-input></label></p></div>',
		$image, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- έξοδος του wp_get_attachment_image().
		esc_html__( 'Επιλογή εικόνας', 'kosmiteia' ),
		$value ? '' : ' hidden',
		esc_html__( 'Αφαίρεση', 'kosmiteia' ),
		esc_html__( 'ID εικόνας', 'kosmiteia' ),
		esc_attr( $id ),
		esc_attr( $name ),
		(int) $value
	);
}

/**
 * Assets της σελίδας ρυθμίσεων: Βιβλιοθήκη πολυμέσων για το πεδίο εικόνας.
 *
 * @param string $hook Το τρέχον admin screen.
 */
function kosmiteia_settings_admin_assets( $hook ) {
	if ( 'toplevel_page_kosmiteia-settings' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'kosmiteia-admin',
		KOSMITEIA_CORE_URL . '/assets/css/admin.css',
		array(),
		kosmiteia_core_asset_version( 'assets/css/admin.css' )
	);

	wp_enqueue_script(
		'kosmiteia-admin-media',
		KOSMITEIA_CORE_URL . '/assets/js/admin-media.js',
		array( 'jquery' ),
		kosmiteia_core_asset_version( 'assets/js/admin-media.js' ),
		true
	);

	wp_localize_script(
		'kosmiteia-admin-media',
		'kosmiteiaMediaFieldL10n',
		array(
			'title'  => __( 'Επιλογή εικόνας', 'kosmiteia' ),
			'button' => __( 'Χρήση αυτής της εικόνας', 'kosmiteia' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'kosmiteia_settings_admin_assets' );

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
