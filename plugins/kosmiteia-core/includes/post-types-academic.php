<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_register_academic_post_types() {

	register_post_type(
		'kosm_event',
		array(
			'labels'        => array(
				'name'               => __( 'Εκδηλώσεις', 'kosmiteia' ),
				'singular_name'      => __( 'Εκδήλωση', 'kosmiteia' ),
				'menu_name'          => __( 'Εκδηλώσεις', 'kosmiteia' ),
				'add_new'            => __( 'Προσθήκη νέας', 'kosmiteia' ),
				'add_new_item'       => __( 'Προσθήκη νέας Εκδήλωσης', 'kosmiteia' ),
				'edit_item'          => __( 'Επεξεργασία Εκδήλωσης', 'kosmiteia' ),
				'new_item'           => __( 'Νέα Εκδήλωση', 'kosmiteia' ),
				'view_item'          => __( 'Προβολή Εκδήλωσης', 'kosmiteia' ),
				'view_items'         => __( 'Προβολή Εκδηλώσεων', 'kosmiteia' ),
				'search_items'       => __( 'Αναζήτηση Εκδηλώσεων', 'kosmiteia' ),
				'not_found'          => __( 'Δεν βρέθηκαν Εκδηλώσεις', 'kosmiteia' ),
				'not_found_in_trash' => __( 'Δεν βρέθηκαν Εκδηλώσεις στον κάδο', 'kosmiteia' ),
				'all_items'          => __( 'Όλες οι Εκδηλώσεις', 'kosmiteia' ),
				'archives'           => __( 'Ημερολόγιο Εκδηλώσεων', 'kosmiteia' ),
			),
			'description'   => __( 'Ημερίδες, συνέδρια, ορκωμοσίες και άλλες εκδηλώσεις.', 'kosmiteia' ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'events',
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 23,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			'taxonomies'    => array( 'kosm_event_type', 'kosm_faculty' ),
			'rewrite'       => array(
				'slug'       => _x( 'events', 'URL slug για τις Εκδηλώσεις', 'kosmiteia' ),
				'with_front' => false,
			),
			'template'      => array(
				array( 'core/paragraph', array( 'placeholder' => __( 'Σύντομη περιγραφή της εκδήλωσης...', 'kosmiteia' ) ) ),
			),
		)
	);

	register_post_type(
		'kosm_person',
		array(
			'labels'        => array(
				'name'               => __( 'Προσωπικό', 'kosmiteia' ),
				'singular_name'      => __( 'Μέλος', 'kosmiteia' ),
				'menu_name'          => __( 'Προσωπικό', 'kosmiteia' ),
				'add_new'            => __( 'Προσθήκη νέου', 'kosmiteia' ),
				'add_new_item'       => __( 'Προσθήκη νέου μέλους', 'kosmiteia' ),
				'edit_item'          => __( 'Επεξεργασία μέλους', 'kosmiteia' ),
				'new_item'           => __( 'Νέο μέλος', 'kosmiteia' ),
				'view_item'          => __( 'Προβολή μέλους', 'kosmiteia' ),
				'view_items'         => __( 'Προβολή μελών', 'kosmiteia' ),
				'search_items'       => __( 'Αναζήτηση μελών', 'kosmiteia' ),
				'not_found'          => __( 'Δεν βρέθηκαν μέλη', 'kosmiteia' ),
				'not_found_in_trash' => __( 'Δεν βρέθηκαν μέλη στον κάδο', 'kosmiteia' ),
				'all_items'          => __( 'Όλα τα μέλη', 'kosmiteia' ),
				'archives'           => __( 'Κατάλογος προσωπικού', 'kosmiteia' ),
				'featured_image'     => __( 'Φωτογραφία μέλους', 'kosmiteia' ),
				'set_featured_image' => __( 'Ορισμός φωτογραφίας', 'kosmiteia' ),
			),
			'description'   => __( 'Μέλη Δ.Ε.Π., Ε.ΔΙ.Π., Ε.Τ.Ε.Π., διοικητικό προσωπικό και συλλογικά όργανα.', 'kosmiteia' ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'people',
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 24,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies'    => array( 'kosm_person_group', 'kosm_faculty' ),
			'rewrite'       => array(
				'slug'       => _x( 'people', 'URL slug για το Προσωπικό', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'kosm_document',
		array(
			'labels'        => array(
				'name'               => __( 'Έγγραφα', 'kosmiteia' ),
				'singular_name'      => __( 'Έγγραφο', 'kosmiteia' ),
				'menu_name'          => __( 'Έγγραφα', 'kosmiteia' ),
				'add_new'            => __( 'Προσθήκη νέου', 'kosmiteia' ),
				'add_new_item'       => __( 'Προσθήκη νέου Εγγράφου', 'kosmiteia' ),
				'edit_item'          => __( 'Επεξεργασία Εγγράφου', 'kosmiteia' ),
				'new_item'           => __( 'Νέο Έγγραφο', 'kosmiteia' ),
				'view_item'          => __( 'Προβολή Εγγράφου', 'kosmiteia' ),
				'view_items'         => __( 'Προβολή Εγγράφων', 'kosmiteia' ),
				'search_items'       => __( 'Αναζήτηση Εγγράφων', 'kosmiteia' ),
				'not_found'          => __( 'Δεν βρέθηκαν Έγγραφα', 'kosmiteia' ),
				'not_found_in_trash' => __( 'Δεν βρέθηκαν Έγγραφα στον κάδο', 'kosmiteia' ),
				'all_items'          => __( 'Όλα τα Έγγραφα', 'kosmiteia' ),
				'archives'           => __( 'Αρχείο Εγγράφων', 'kosmiteia' ),
			),
			'description'   => __( 'Κανονισμοί, έντυπα, αποφάσεις και οδηγοί σπουδών προς λήψη.', 'kosmiteia' ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'documents',
			'menu_icon'     => 'dashicons-media-document',
			'menu_position' => 25,
			'supports'      => array( 'title', 'editor', 'excerpt', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies'    => array( 'kosm_document_type', 'kosm_faculty' ),
			'rewrite'       => array(
				'slug'       => _x( 'documents', 'URL slug για τα Έγγραφα', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'kosmiteia_register_academic_post_types' );

function kosmiteia_register_academic_taxonomies() {
	register_taxonomy(
		'kosm_event_type',
		array( 'kosm_event' ),
		array(
			'labels'            => array(
				'name'          => __( 'Είδη εκδηλώσεων', 'kosmiteia' ),
				'singular_name' => __( 'Είδος εκδήλωσης', 'kosmiteia' ),
				'menu_name'     => __( 'Είδη', 'kosmiteia' ),
				'add_new_item'  => __( 'Προσθήκη είδους', 'kosmiteia' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => _x( 'event-type', 'URL slug είδους εκδήλωσης', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'kosm_person_group',
		array( 'kosm_person' ),
		array(
			'labels'            => array(
				'name'          => __( 'Κατηγορίες προσωπικού', 'kosmiteia' ),
				'singular_name' => __( 'Κατηγορία προσωπικού', 'kosmiteia' ),
				'menu_name'     => __( 'Κατηγορίες', 'kosmiteia' ),
				'add_new_item'  => __( 'Προσθήκη κατηγορίας', 'kosmiteia' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => _x( 'staff-group', 'URL slug κατηγορίας προσωπικού', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'kosm_document_type',
		array( 'kosm_document' ),
		array(
			'labels'            => array(
				'name'          => __( 'Είδη εγγράφων', 'kosmiteia' ),
				'singular_name' => __( 'Είδος εγγράφου', 'kosmiteia' ),
				'menu_name'     => __( 'Είδη', 'kosmiteia' ),
				'add_new_item'  => __( 'Προσθήκη είδους', 'kosmiteia' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => _x( 'document-type', 'URL slug είδους εγγράφου', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'kosmiteia_register_academic_taxonomies' );

function kosmiteia_register_academic_meta() {
	$fields = array(
		'kosm_event'    => array(
			'kosm_event_start'    => __( 'Έναρξη (ΕΕΕΕ-ΜΜ-ΗΗ ΩΩ:ΛΛ)', 'kosmiteia' ),
			'kosm_event_end'      => __( 'Λήξη (ΕΕΕΕ-ΜΜ-ΗΗ ΩΩ:ΛΛ)', 'kosmiteia' ),
			'kosm_event_location' => __( 'Τόπος διεξαγωγής', 'kosmiteia' ),
			'kosm_event_url'      => __( 'Σύνδεσμος δήλωσης συμμετοχής', 'kosmiteia' ),
			'kosm_event_online'   => __( 'Διαδικτυακή εκδήλωση (ναι/όχι)', 'kosmiteia' ),
		),
		'kosm_person'   => array(
			'kosm_person_role'   => __( 'Ιδιότητα / βαθμίδα', 'kosmiteia' ),
			'kosm_person_email'  => __( 'Email', 'kosmiteia' ),
			'kosm_person_phone'  => __( 'Τηλέφωνο', 'kosmiteia' ),
			'kosm_person_office' => __( 'Γραφείο', 'kosmiteia' ),
			'kosm_person_cv_url' => __( 'Σύνδεσμος βιογραφικού', 'kosmiteia' ),
			'kosm_person_orcid'  => __( 'ORCID', 'kosmiteia' ),
		),
		'kosm_document' => array(
			'kosm_document_file'   => __( 'Αρχείο (URL)', 'kosmiteia' ),
			'kosm_document_number' => __( 'Αριθμός πρωτοκόλλου / ΑΔΑ', 'kosmiteia' ),
			'kosm_document_date'   => __( 'Ημερομηνία εγγράφου', 'kosmiteia' ),
		),
	);

	foreach ( $fields as $post_type => $metas ) {
		foreach ( $metas as $key => $label ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'              => 'string',
					'label'             => $label,
					'single'            => true,
					'default'           => '',
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function ( $allowed, $meta_key, $object_id ) {
						if ( $object_id ) {
							return current_user_can( 'edit_post', $object_id );
						}

						return current_user_can( 'edit_posts' ) || current_user_can( 'edit_kosm_announcements' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'kosmiteia_register_academic_meta' );

function kosmiteia_events_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'kosm_event' ) ) {
		return;
	}

	$query->set( 'posts_per_page', (int) kosmiteia_option( 'events_per_page', 10 ) );
	$query->set( 'meta_key', 'kosm_event_start' );
	$query->set( 'orderby', array( 'meta_value' => 'ASC', 'date' => 'DESC' ) );

	if ( kosmiteia_option( 'hide_past_events', 1 ) ) {
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				array(
					'key'     => 'kosm_event_start',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'CHAR',
				),
				array(
					'key'     => 'kosm_event_start',
					'compare' => 'NOT EXISTS',
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'kosmiteia_events_archive_query' );

function kosmiteia_people_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'kosm_person' ) || $query->is_tax( 'kosm_person_group' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$query->set( 'posts_per_page', 50 );
	}

	if ( $query->is_post_type_archive( 'kosm_document' ) || $query->is_tax( 'kosm_document_type' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'kosmiteia_people_archive_query' );

function kosmiteia_academic_admin_columns( $columns, $post_type = '' ) {
	if ( 'kosm_event' === $post_type ) {
		$columns['kosm_event_start'] = __( 'Ημερομηνία', 'kosmiteia' );
	}

	if ( 'kosm_person' === $post_type ) {
		$columns['kosm_person_role'] = __( 'Ιδιότητα', 'kosmiteia' );
	}

	if ( 'kosm_document' === $post_type ) {
		$columns['kosm_document_file'] = __( 'Αρχείο', 'kosmiteia' );
	}

	return $columns;
}
add_filter( 'manage_posts_columns', 'kosmiteia_academic_admin_columns', 10, 2 );

function kosmiteia_academic_admin_column_content( $column, $post_id ) {
	$keys = array( 'kosm_event_start', 'kosm_person_role', 'kosm_document_file' );

	if ( ! in_array( $column, $keys, true ) ) {
		return;
	}

	$value = get_post_meta( $post_id, $column, true );

	if ( '' === $value ) {
		echo '&mdash;';
		return;
	}

	if ( 'kosm_document_file' === $column ) {
		printf(
			'<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
			esc_url( $value ),
			esc_html( basename( (string) wp_parse_url( $value, PHP_URL_PATH ) ) )
		);
		return;
	}

	echo esc_html( $value );
}
add_action( 'manage_posts_custom_column', 'kosmiteia_academic_admin_column_content', 10, 2 );
