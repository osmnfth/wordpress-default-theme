<?php
/**
 * Custom post types, taxonomies και meta fields.
 *
 * Όλο το περιεχόμενο των ενοτήτων της αρχικής (Σχολές, Ανακοινώσεις,
 * Μεταπτυχιακά) προέρχεται από εδώ - τίποτα δεν είναι γραμμένο στατικά
 * μέσα στα templates.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Καταχώριση των custom post types.
 */
function kosmiteia_register_post_types() {

	/* --------------------------------------------------------------------
	 * Σχολές / Schools
	 * ----------------------------------------------------------------- */
	register_post_type(
		'kosm_school',
		array(
			'labels'        => array(
				'name'               => __( 'Σχολές', 'kosmiteia' ),
				'singular_name'      => __( 'Σχολή', 'kosmiteia' ),
				'menu_name'          => __( 'Σχολές', 'kosmiteia' ),
				'add_new'            => __( 'Προσθήκη νέας', 'kosmiteia' ),
				'add_new_item'       => __( 'Προσθήκη νέας Σχολής', 'kosmiteia' ),
				'edit_item'          => __( 'Επεξεργασία Σχολής', 'kosmiteia' ),
				'new_item'           => __( 'Νέα Σχολή', 'kosmiteia' ),
				'view_item'          => __( 'Προβολή Σχολής', 'kosmiteia' ),
				'view_items'         => __( 'Προβολή Σχολών', 'kosmiteia' ),
				'search_items'       => __( 'Αναζήτηση Σχολών', 'kosmiteia' ),
				'not_found'          => __( 'Δεν βρέθηκαν Σχολές', 'kosmiteia' ),
				'not_found_in_trash' => __( 'Δεν βρέθηκαν Σχολές στον κάδο', 'kosmiteia' ),
				'all_items'          => __( 'Όλες οι Σχολές', 'kosmiteia' ),
				'archives'           => __( 'Αρχείο Σχολών', 'kosmiteia' ),
				'featured_image'     => __( 'Φωτογραφία Σχολής', 'kosmiteia' ),
				'set_featured_image' => __( 'Ορισμός φωτογραφίας Σχολής', 'kosmiteia' ),
				'item_updated'       => __( 'Η Σχολή ενημερώθηκε.', 'kosmiteia' ),
				'item_published'     => __( 'Η Σχολή δημοσιεύτηκε.', 'kosmiteia' ),
			),
			'description'   => __( 'Οι Σχολές που υπάγονται στην Κοσμητεία.', 'kosmiteia' ),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'schools',
			'menu_icon'     => 'dashicons-bank',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies'    => array( 'kosm_faculty' ),
			'rewrite'       => array(
				'slug'       => _x( 'schools', 'URL slug για τις Σχολές', 'kosmiteia' ),
				'with_front' => false,
			),
			'template'      => array(
				array( 'core/paragraph', array( 'placeholder' => __( 'Σύντομη περιγραφή της Σχολής...', 'kosmiteia' ) ) ),
				array( 'core/heading', array( 'level' => 2, 'placeholder' => __( 'Τμήματα', 'kosmiteia' ) ) ),
				array( 'core/list', array() ),
			),
		)
	);

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
		array( 'kosm_school', 'kosm_announcement', 'kosm_program' ),
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
		'kosm_school'       => array(
			'kosm_dean'     => __( 'Κοσμήτορας', 'kosmiteia' ),
			'kosm_phone'    => __( 'Τηλέφωνο', 'kosmiteia' ),
			'kosm_email'    => __( 'Email', 'kosmiteia' ),
			'kosm_address'  => __( 'Διεύθυνση', 'kosmiteia' ),
			'kosm_site_url' => __( 'Ιστοσελίδα Σχολής', 'kosmiteia' ),
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
 * Query Loop με κλάση CSS "is-related-to-school" μέσα σε σελίδα Σχολής:
 * φιλτράρεται αυτόματα ώστε να δείχνει μόνο περιεχόμενο της Σχολής αυτής.
 *
 * Η κλάση προστίθεται από το UI: Ρυθμίσεις μπλοκ → Για προχωρημένους →
 * Πρόσθετες κλάσεις CSS. Αν η Σχολή δεν έχει όρο «Σχολή (φίλτρο)», το
 * query μένει ως έχει.
 *
 * @param string|null $pre_render   Προ-αποδοθέν περιεχόμενο (δεν το αλλάζουμε).
 * @param array       $parsed_block Το μπλοκ πριν το render.
 * @return string|null
 */
function kosmiteia_track_query_block( $pre_render, $parsed_block ) {
	if ( isset( $parsed_block['blockName'] ) && 'core/query' === $parsed_block['blockName'] ) {
		$GLOBALS['kosmiteia_query_class'] = isset( $parsed_block['attrs']['className'] )
			? (string) $parsed_block['attrs']['className']
			: '';
	}

	return $pre_render;
}
add_filter( 'pre_render_block', 'kosmiteia_track_query_block', 10, 2 );

/**
 * Προσθέτει το φίλτρο Σχολής στο query του σημειωμένου Query Loop.
 *
 * @param array $query Παράμετροι WP_Query.
 * @return array
 */
function kosmiteia_related_to_school_query( $query ) {
	$classes = isset( $GLOBALS['kosmiteia_query_class'] ) ? $GLOBALS['kosmiteia_query_class'] : '';

	if ( ! is_singular( 'kosm_school' ) || false === strpos( $classes, 'is-related-to-school' ) ) {
		return $query;
	}

	$terms = wp_get_post_terms( get_queried_object_id(), 'kosm_faculty', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $query;
	}

	$tax_query = isset( $query['tax_query'] ) ? $query['tax_query'] : array();

	$tax_query[] = array(
		'taxonomy'         => 'kosm_faculty',
		'field'            => 'term_id',
		'terms'            => array_map( 'intval', $terms ),
		'include_children' => false,
	);

	$query['tax_query'] = $tax_query;

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'kosmiteia_related_to_school_query' );

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
