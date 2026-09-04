<?php
/**
 * Block Bindings: δυναμικά κείμενα από τις Ρυθμίσεις Κοσμητείας.
 *
 * Οποιοδήποτε μπλοκ παραγράφου, επικεφαλίδας, κουμπιού ή εικόνας μπορεί να
 * «δείχνει» σε μια ρύθμιση αντί να έχει σταθερό κείμενο:
 *
 *   <!-- wp:paragraph {"metadata":{"bindings":{"content":{
 *          "source":"kosmiteia/option","args":{"key":"contact_phone"}}}}} -->
 *   <p>Τηλέφωνο</p>
 *   <!-- /wp:paragraph -->
 *
 * Έτσι η αλλαγή γίνεται μία φορά στις Ρυθμίσεις και ενημερώνονται όλα τα
 * σημεία του ιστότοπου - χωρίς επεξεργασία templates ή patterns.
 *
 * Πέρα από τα κλειδιά των ρυθμίσεων υποστηρίζονται και υπολογιζόμενες τιμές:
 *
 *   year        Τρέχον έτος
 *   site_name   Όνομα ιστότοπου
 *   copyright   «© έτος - όνομα» (ή το κείμενο των ρυθμίσεων)
 *   contact_*   Τα στοιχεία επικοινωνίας με ετικέτα, π.χ. «Τηλ. 25510 30953»
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Οι υπολογιζόμενες τιμές που δεν αποθηκεύονται ως ρυθμίσεις.
 *
 * @return array
 */
function kosmiteia_binding_computed() {
	$copyright = kosmiteia_option( 'copyright' );

	if ( '' === $copyright ) {
		$copyright = sprintf(
			/* translators: 1: έτος, 2: όνομα ιστότοπου. */
			__( '© %1$s %2$s', 'kosmiteia' ),
			wp_date( 'Y' ),
			get_bloginfo( 'name' )
		);
	}

	$phone = kosmiteia_option( 'contact_phone' );
	$email = kosmiteia_option( 'contact_email' );
	$hours = kosmiteia_option( 'contact_hours' );

	return array(
		'year'                 => wp_date( 'Y' ),
		'site_name'            => get_bloginfo( 'name' ),
		'site_description'     => get_bloginfo( 'description' ),
		'copyright'            => $copyright,
		/* translators: %s: τηλέφωνο. */
		'contact_phone_label'  => $phone ? sprintf( __( 'Τηλ. %s', 'kosmiteia' ), $phone ) : '',
		/* translators: %s: email. */
		'contact_email_label'  => $email ? sprintf( __( 'Email: %s', 'kosmiteia' ), $email ) : '',
		/* translators: %s: ωράριο. */
		'contact_hours_label'  => $hours ? sprintf( __( 'Ωράριο: %s', 'kosmiteia' ), $hours ) : '',
		'contact_email_mailto' => $email ? 'mailto:' . $email : '',
		'contact_phone_tel'    => $phone ? 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) : '',
	);
}

/**
 * Η τιμή που θα μπει στο μπλοκ.
 *
 * @param array $source_args    Τα args του binding (περιμένουμε «key»).
 * @param mixed $block_instance Το μπλοκ (δεν χρησιμοποιείται).
 * @param string $attribute     Το attribute που δένεται (δεν χρησιμοποιείται).
 * @return string|null
 */
function kosmiteia_binding_value( $source_args, $block_instance = null, $attribute = '' ) {
	if ( empty( $source_args['key'] ) ) {
		return null;
	}

	$key      = (string) $source_args['key'];
	$computed = kosmiteia_binding_computed();

	if ( isset( $computed[ $key ] ) ) {
		$value = $computed[ $key ];
	} else {
		$value = kosmiteia_option( $key );
	}

	if ( '' === $value || null === $value ) {
		// Κενή ρύθμιση: επιστρέφουμε το fallback του binding, αλλιώς κενό
		// κείμενο, ώστε να μη φαίνεται το placeholder του pattern.
		return isset( $source_args['fallback'] ) ? (string) $source_args['fallback'] : '';
	}

	return (string) $value;
}

/**
 * Καταχώριση της πηγής.
 */
function kosmiteia_register_bindings() {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}

	register_block_bindings_source(
		'kosmiteia/option',
		array(
			'label'              => __( 'Ρυθμίσεις Κοσμητείας', 'kosmiteia' ),
			'get_value_callback' => 'kosmiteia_binding_value',
		)
	);
}
add_action( 'init', 'kosmiteia_register_bindings' );

/**
 * Δυναμικά tokens σε κείμενα και συνδέσμους.
 *
 * Γράψτε το token μέσα σε οποιοδήποτε μπλοκ κειμένου ή στο πεδίο URL ενός
 * κουμπιού / στοιχείου μενού:
 *
 *   {{year}} {{site}} {{institution}} {{phone}} {{email}} {{address}} {{hours}}
 *   {{url_home}} {{url_schools}} {{url_announcements}} {{url_programs}}
 *   {{url_events}} {{url_people}} {{url_documents}}
 *
 * Έτσι οι σύνδεσμοι παραμένουν σωστοί ακόμη κι αν αλλάξουν τα permalinks.
 *
 * @param string $block_content Το HTML του μπλοκ.
 * @return string
 */
function kosmiteia_render_dynamic_tokens( $block_content ) {
	if ( false === strpos( $block_content, '{{' ) ) {
		return $block_content;
	}

	$archive = static function ( $post_type ) {
		$link = get_post_type_archive_link( $post_type );

		return $link ? $link : home_url( '/' );
	};

	return strtr(
		$block_content,
		array(
			'{{year}}'              => esc_html( wp_date( 'Y' ) ),
			'{{site}}'              => esc_html( get_bloginfo( 'name' ) ),
			'{{institution}}'       => esc_html( kosmiteia_option( 'institution' ) ),
			'{{phone}}'             => esc_html( kosmiteia_option( 'contact_phone' ) ),
			'{{email}}'             => esc_html( kosmiteia_option( 'contact_email' ) ),
			'{{address}}'           => esc_html( kosmiteia_option( 'contact_address' ) ),
			'{{hours}}'             => esc_html( kosmiteia_option( 'contact_hours' ) ),
			'{{url_home}}'          => esc_url( home_url( '/' ) ),
			'{{url_schools}}'       => esc_url( $archive( 'kosm_school' ) ),
			'{{url_announcements}}' => esc_url( $archive( 'kosm_announcement' ) ),
			'{{url_programs}}'      => esc_url( $archive( 'kosm_program' ) ),
			'{{url_events}}'        => esc_url( $archive( 'kosm_event' ) ),
			'{{url_people}}'        => esc_url( $archive( 'kosm_person' ) ),
			'{{url_documents}}'     => esc_url( $archive( 'kosm_document' ) ),
		)
	);
}
add_filter( 'render_block', 'kosmiteia_render_dynamic_tokens' );

/**
 * Μήκος περίληψης από τις ρυθμίσεις.
 *
 * @param int $length Προεπιλογή του WordPress.
 * @return int
 */
function kosmiteia_excerpt_length( $length ) {
	return (int) kosmiteia_option( 'excerpt_length', 24 );
}
add_filter( 'excerpt_length', 'kosmiteia_excerpt_length' );
