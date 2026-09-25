<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_programs_per_page() {
	return (int) apply_filters( 'kosmiteia_programs_per_page', (int) kosmiteia_option( 'programs_per_page', 12 ) );
}

function kosmiteia_program_sort_options() {
	return array(
		''           => __( 'Προεπιλεγμένη σειρά', 'kosmiteia' ),
		'title'      => __( 'Αλφαβητικά (Α → Ω)', 'kosmiteia' ),
		'title-desc' => __( 'Αλφαβητικά (Ω → Α)', 'kosmiteia' ),
		'newest'     => __( 'Νεότερα πρώτα', 'kosmiteia' ),
		'oldest'     => __( 'Παλαιότερα πρώτα', 'kosmiteia' ),
	);
}

function kosmiteia_program_filters_state() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$read = function ( $key ) {
		return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? (string) wp_unslash( $_GET[ $key ] ) : '';
	};

	$sort = sanitize_key( $read( 'kosm_sort' ) );

	return array(
		'q'      => sanitize_text_field( $read( 'kosm_q' ) ),
		'school' => sanitize_title_for_query( $read( 'kosm_fac' ) ),
		'type'   => sanitize_title_for_query( $read( 'kosm_ptype' ) ),
		'sort'   => isset( kosmiteia_program_sort_options()[ $sort ] ) ? $sort : '',
	);
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

function kosmiteia_program_filters_active( $state ) {
	return ( '' !== $state['q'] || '' !== $state['school'] || '' !== $state['type'] || '' !== $state['sort'] );
}

function kosmiteia_filter_program_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'kosm_program' ) ) {
		return;
	}

	$query->set( 'posts_per_page', kosmiteia_programs_per_page() );

	$state = kosmiteia_program_filters_state();

	if ( '' !== $state['q'] ) {
		$query->set( 's', $state['q'] );
	}

	$tax_query = array();

	if ( '' !== $state['school'] ) {
		$tax_query[] = array(
			'taxonomy' => 'kosm_faculty',
			'field'    => 'slug',
			'terms'    => $state['school'],
		);
	}

	if ( '' !== $state['type'] ) {
		$tax_query[] = array(
			'taxonomy' => 'kosm_program_type',
			'field'    => 'slug',
			'terms'    => $state['type'],
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	switch ( $state['sort'] ) {
		case 'title':
			$query->set( 'orderby', array( 'title' => 'ASC' ) );
			break;

		case 'title-desc':
			$query->set( 'orderby', array( 'title' => 'DESC' ) );
			break;

		case 'newest':
			$query->set( 'orderby', array( 'date' => 'DESC' ) );
			break;

		case 'oldest':
			$query->set( 'orderby', array( 'date' => 'ASC' ) );
			break;

		default:
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
			break;
	}
}
add_action( 'pre_get_posts', 'kosmiteia_filter_program_archive' );

function kosmiteia_program_filters_html( $attributes = array() ) {
	static $instance = 0;
	++$instance;

	$attributes = wp_parse_args(
		$attributes,
		array(
			'showSearch'  => true,
			'showFaculty' => true,
			'showType'    => true,
			'showSort'    => true,
			'showCount'   => true,
		)
	);

	$archive = get_post_type_archive_link( 'kosm_program' );

	if ( ! $archive ) {
		return '';
	}

	$state  = kosmiteia_program_filters_state();
	$action = $archive;
	$hidden = kosmiteia_filters_hidden_fields( $action );
	$prefix = 'kosmiteia-program-filter-' . $instance;
	$fields = '';

	if ( $attributes['showSearch'] ) {
		$fields .= kosmiteia_filters_search_field(
			$prefix . '-q',
			'kosm_q',
			$state['q'],
			__( 'Λέξη-κλειδί, π.χ. βιοηθική', 'kosmiteia' )
		);
	}

	if ( $attributes['showFaculty'] ) {
		$fields .= kosmiteia_filters_term_select(
			$prefix . '-school',
			'kosm_fac',
			__( 'Τμήμα', 'kosmiteia' ),
			'kosm_faculty',
			__( 'Όλα τα Τμήματα', 'kosmiteia' ),
			$state['school'],
			'kosm_program'
		);
	}

	if ( $attributes['showType'] ) {
		$fields .= kosmiteia_filters_term_select(
			$prefix . '-type',
			'kosm_ptype',
			__( 'Τύπος', 'kosmiteia' ),
			'kosm_program_type',
			__( 'Όλοι οι τύποι', 'kosmiteia' ),
			$state['type'],
			'kosm_program'
		);
	}

	if ( $attributes['showSort'] ) {
		$options = '';

		foreach ( kosmiteia_program_sort_options() as $value => $label ) {
			$options .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $value ),
				selected( $state['sort'], $value, false ),
				esc_html( $label )
			);
		}

		$fields .= kosmiteia_filters_select(
			$prefix . '-sort',
			'kosm_sort',
			__( 'Ταξινόμηση', 'kosmiteia' ),
			$options
		);
	}

	$count = '';

	if ( $attributes['showCount'] ) {
		$count = sprintf(
			'<p class="kosmiteia-filters__count" role="status">%s</p>',
			esc_html( kosmiteia_program_filters_count_text( $state ) )
		);
	}

	return kosmiteia_filters_form(
		$action,
		__( 'Αναζήτηση και φίλτρα μεταπτυχιακών', 'kosmiteia' ),
		$hidden,
		$fields,
		kosmiteia_filters_actions( $archive, kosmiteia_program_filters_active( $state ) ),
		$count
	);
}

function kosmiteia_program_filters_count_text( $state ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! is_post_type_archive( 'kosm_program' ) ) {
		return __( 'Ο αριθμός αποτελεσμάτων εμφανίζεται στο front-end.', 'kosmiteia' );
	}

	global $wp_query;

	$found = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;

	if ( 0 === $found ) {
		return __( 'Κανένα πρόγραμμα δεν ταιριάζει με τα φίλτρα.', 'kosmiteia' );
	}

	if ( 1 === $found ) {
		return __( 'Βρέθηκε 1 πρόγραμμα.', 'kosmiteia' );
	}

	if ( kosmiteia_program_filters_active( $state ) ) {
		return sprintf(
			/* translators: %d: αριθμός αποτελεσμάτων. */
			__( 'Βρέθηκαν %d προγράμματα.', 'kosmiteia' ),
			$found
		);
	}

	return sprintf(
		/* translators: %d: συνολικός αριθμός προγραμμάτων. */
		__( 'Σύνολο: %d προγράμματα.', 'kosmiteia' ),
		$found
	);
}

function kosmiteia_render_program_filters_block( $attributes ) {
	$html = kosmiteia_program_filters_html( $attributes );

	if ( ! $html ) {
		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		$html
	);
}
