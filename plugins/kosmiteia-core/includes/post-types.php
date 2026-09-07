<?php
/**
 * Custom post types, taxonomies και meta fields.
 *
 * Το περιεχόμενο των ενοτήτων της αρχικής που αλλάζει συχνά (Ανακοινώσεις,
 * Μεταπτυχιακά) προέρχεται από εδώ - τίποτα δεν είναι γραμμένο στατικά
 * μέσα στα templates. Οι Σχολές δεν είναι τύπος περιεχομένου: είναι λίγες
 * και σταθερές, οπότε η ενότητά τους στην αρχική συντάσσεται από τον
 * Site Editor σαν κανονικές κάρτες.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Καταχώριση των custom post types.
 */
function kosmiteia_register_post_types() {

	/* --------------------------------------------------------------------
	 * Ανακοινώσεις / Announcements
	 * ----------------------------------------------------------------- */
	register_post_type(
		'kosm_announcement',
		array(
			'labels'        => array(
				'name'               => __( 'Ανακοινώσεις', 'kosmiteia' ),
				'singular_name'      => __( 'Ανακοίνωση', 'kosmiteia' ),
				'menu_name'          => __( 'Ανακοινώσεις', 'kosmiteia' ),
				'add_new'            => __( 'Προσθήκη νέας', 'kosmiteia' ),
				'add_new_item'       => __( 'Προσθήκη νέας Ανακοίνωσης', 'kosmiteia' ),
				'edit_item'          => __( 'Επεξεργασία Ανακοίνωσης', 'kosmiteia' ),
				'new_item'           => __( 'Νέα Ανακοίνωση', 'kosmiteia' ),
				'view_item'          => __( 'Προβολή Ανακοίνωσης', 'kosmiteia' ),
				'view_items'         => __( 'Προβολή Ανακοινώσεων', 'kosmiteia' ),
				'search_items'       => __( 'Αναζήτηση Ανακοινώσεων', 'kosmiteia' ),
				'not_found'          => __( 'Δεν βρέθηκαν Ανακοινώσεις', 'kosmiteia' ),
				'not_found_in_trash' => __( 'Δεν βρέθηκαν Ανακοινώσεις στον κάδο', 'kosmiteia' ),
				'all_items'          => __( 'Όλες οι Ανακοινώσεις', 'kosmiteia' ),
				'archives'           => __( 'Αρχείο Ανακοινώσεων', 'kosmiteia' ),
			),
			'description'   => __( 'Ανακοινώσεις, νέα και προκηρύξεις της Κοσμητείας.', 'kosmiteia' ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'announcements',
			'menu_icon'     => 'dashicons-megaphone',
			'menu_position' => 21,
			// Ξεχωριστά δικαιώματα: επιτρέπουν τον ρόλο «Συντάκτης Ανακοινώσεων»
			// που δημοσιεύει μόνο ανακοινώσεις (δείτε includes/roles.php).
			'capability_type' => array( 'kosm_announcement', 'kosm_announcements' ),
			'map_meta_cap'    => true,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'custom-fields', 'comments' ),
			'taxonomies'    => array( 'kosm_ann_category', 'kosm_faculty' ),
			'rewrite'       => array(
				'slug'       => _x( 'announcements', 'URL slug για τις Ανακοινώσεις', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);

	/* --------------------------------------------------------------------
	 * Μεταπτυχιακά Προγράμματα / Postgraduate programmes
	 * ----------------------------------------------------------------- */
	register_post_type(
		'kosm_program',
		array(
			'labels'        => array(
				'name'               => __( 'Μεταπτυχιακά', 'kosmiteia' ),
				'singular_name'      => __( 'Μεταπτυχιακό Πρόγραμμα', 'kosmiteia' ),
				'menu_name'          => __( 'Μεταπτυχιακά', 'kosmiteia' ),
				'add_new'            => __( 'Προσθήκη νέου', 'kosmiteia' ),
				'add_new_item'       => __( 'Προσθήκη νέου Προγράμματος', 'kosmiteia' ),
				'edit_item'          => __( 'Επεξεργασία Προγράμματος', 'kosmiteia' ),
				'new_item'           => __( 'Νέο Πρόγραμμα', 'kosmiteia' ),
				'view_item'          => __( 'Προβολή Προγράμματος', 'kosmiteia' ),
				'view_items'         => __( 'Προβολή Προγραμμάτων', 'kosmiteia' ),
				'search_items'       => __( 'Αναζήτηση Προγραμμάτων', 'kosmiteia' ),
				'not_found'          => __( 'Δεν βρέθηκαν Προγράμματα', 'kosmiteia' ),
				'not_found_in_trash' => __( 'Δεν βρέθηκαν Προγράμματα στον κάδο', 'kosmiteia' ),
				'all_items'          => __( 'Όλα τα Προγράμματα', 'kosmiteia' ),
				'archives'           => __( 'Αρχείο Μεταπτυχιακών', 'kosmiteia' ),
			),
			'description'   => __( 'Προγράμματα Μεταπτυχιακών Σπουδών (Π.Μ.Σ.).', 'kosmiteia' ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'programs',
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies'    => array( 'kosm_program_type', 'kosm_faculty' ),
			'rewrite'       => array(
				'slug'       => _x( 'programs', 'URL slug για τα Μεταπτυχιακά', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'kosmiteia_register_post_types' );

/**
 * Καταχώριση taxonomies.
 */
function kosmiteia_register_taxonomies() {

	// Σχολή: κοινή ταξινομία για φιλτράρισμα ανακοινώσεων και προγραμμάτων.
	register_taxonomy(
		'kosm_faculty',
		array( 'kosm_announcement', 'kosm_program' ),
		array(
			'labels'            => array(
				'name'          => __( 'Σχολές (φίλτρο)', 'kosmiteia' ),
				'singular_name' => __( 'Σχολή (φίλτρο)', 'kosmiteia' ),
				'menu_name'     => __( 'Φίλτρο Σχολής', 'kosmiteia' ),
				'all_items'     => __( 'Όλες οι Σχολές', 'kosmiteia' ),
				'add_new_item'  => __( 'Προσθήκη Σχολής', 'kosmiteia' ),
			),
			'description'       => __( 'Συνδέει Ανακοινώσεις και Μεταπτυχιακά με μια Σχολή.', 'kosmiteia' ),
			'capabilities'      => array(
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_kosm_announcements',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => _x( 'faculty', 'URL slug για το φίλτρο Σχολής', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);

	// Κατηγορίες ανακοινώσεων.
	register_taxonomy(
		'kosm_ann_category',
		array( 'kosm_announcement' ),
		array(
			'labels'            => array(
				'name'          => __( 'Κατηγορίες Ανακοινώσεων', 'kosmiteia' ),
				'singular_name' => __( 'Κατηγορία Ανακοίνωσης', 'kosmiteia' ),
				'menu_name'     => __( 'Κατηγορίες', 'kosmiteia' ),
				'add_new_item'  => __( 'Προσθήκη κατηγορίας', 'kosmiteia' ),
			),
			'capabilities'      => array(
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_kosm_announcements',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => _x( 'announcement-category', 'URL slug κατηγορίας ανακοινώσεων', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);

	// Τύπος προγράμματος (Π.Μ.Σ., Διιδρυματικό, Διδακτορικό).
	register_taxonomy(
		'kosm_program_type',
		array( 'kosm_program' ),
		array(
			'labels'            => array(
				'name'          => __( 'Τύποι Προγραμμάτων', 'kosmiteia' ),
				'singular_name' => __( 'Τύπος Προγράμματος', 'kosmiteia' ),
				'menu_name'     => __( 'Τύποι', 'kosmiteia' ),
				'add_new_item'  => __( 'Προσθήκη τύπου', 'kosmiteia' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => _x( 'program-type', 'URL slug τύπου προγράμματος', 'kosmiteia' ),
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'kosmiteia_register_taxonomies' );

/**
 * Πεδία (post meta) που συνδέονται με μπλοκ μέσω Block Bindings,
 * ώστε να συμπληρώνονται απευθείας μέσα από τον editor.
 */
function kosmiteia_register_meta() {
	$fields = array(
		'kosm_program'      => array(
			'kosm_duration'  => __( 'Διάρκεια', 'kosmiteia' ),
			'kosm_ects'      => __( 'Πιστωτικές μονάδες (ECTS)', 'kosmiteia' ),
			'kosm_fees'      => __( 'Δίδακτρα', 'kosmiteia' ),
			'kosm_deadline'  => __( 'Προθεσμία αιτήσεων', 'kosmiteia' ),
			'kosm_director'  => __( 'Διευθυντής/-τρια Προγράμματος', 'kosmiteia' ),
			'kosm_apply_url' => __( 'Σύνδεσμος αίτησης', 'kosmiteia' ),
		),
		'kosm_announcement' => array(
			'kosm_deadline' => __( 'Προθεσμία', 'kosmiteia' ),
			'kosm_file_url' => __( 'Συνημμένο αρχείο (URL)', 'kosmiteia' ),
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
add_action( 'init', 'kosmiteia_register_meta' );

/**
 * Flush rewrite rules μία φορά ανά έκδοση, ώστε να δουλέψουν
 * αμέσως τα permalinks των custom post types.
 */
function kosmiteia_flush_rewrites() {
	if ( get_option( 'kosmiteia_rewrites_version' ) === KOSMITEIA_CORE_VERSION ) {
		return;
	}

	flush_rewrite_rules();
	update_option( 'kosmiteia_rewrites_version', KOSMITEIA_CORE_VERSION );
}
add_action( 'init', 'kosmiteia_flush_rewrites', 99 );
