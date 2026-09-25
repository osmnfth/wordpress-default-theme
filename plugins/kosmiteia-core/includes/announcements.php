<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_announcements_per_page() {
	return (int) apply_filters( 'kosmiteia_announcements_per_page', (int) kosmiteia_option( 'announcements_per_page', 10 ) );
}

function kosmiteia_announcement_filters_state() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$read = function ( $key ) {
		return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? (string) wp_unslash( $_GET[ $key ] ) : '';
	};

	$year = (int) $read( 'kosm_year' );

	return array(
		'q'      => sanitize_text_field( $read( 'kosm_q' ) ),
		'cat'    => sanitize_title_for_query( $read( 'kosm_cat' ) ),
		'school' => sanitize_title_for_query( $read( 'kosm_fac' ) ),
		'year'   => ( $year >= 1970 && $year <= 2200 ) ? $year : 0,
	);
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

function kosmiteia_announcement_filters_active( $state ) {
	return ( '' !== $state['q'] || '' !== $state['cat'] || '' !== $state['school'] || 0 !== $state['year'] );
}

function kosmiteia_filter_announcement_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'kosm_announcement' ) ) {
		return;
	}

	$query->set( 'posts_per_page', kosmiteia_announcements_per_page() );

	$state = kosmiteia_announcement_filters_state();

	if ( '' !== $state['q'] ) {
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

function kosmiteia_announcement_years() {
	$cached = get_transient( 'kosmiteia_announcement_years' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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

function kosmiteia_flush_announcement_years( $post_id ) {
	if ( 'kosm_announcement' === get_post_type( $post_id ) ) {
		delete_transient( 'kosmiteia_announcement_years' );
	}
}
add_action( 'save_post', 'kosmiteia_flush_announcement_years' );
add_action( 'deleted_post', 'kosmiteia_flush_announcement_years' );

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
	$hidden = kosmiteia_filters_hidden_fields( $action );
	$prefix = 'kosmiteia-filter-' . $instance;
	$fields = '';

	if ( $attributes['showSearch'] ) {
		$fields .= kosmiteia_filters_search_field(
			$prefix . '-q',
			'kosm_q',
			$state['q'],
			__( 'Λέξη-κλειδί, π.χ. υποτροφίες', 'kosmiteia' )
		);
	}

	if ( $attributes['showCategory'] ) {
		$fields .= kosmiteia_filters_term_select(
			$prefix . '-cat',
			'kosm_cat',
			__( 'Κατηγορία', 'kosmiteia' ),
			'kosm_ann_category',
			__( 'Όλες οι κατηγορίες', 'kosmiteia' ),
			$state['cat'],
			'kosm_announcement'
		);
	}

	if ( $attributes['showFaculty'] ) {
		$fields .= kosmiteia_filters_term_select(
			$prefix . '-school',
			'kosm_fac',
			__( 'Σχολή', 'kosmiteia' ),
			'kosm_faculty',
			__( 'Όλες οι Σχολές', 'kosmiteia' ),
			$state['school'],
			'kosm_announcement'
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

			$fields .= kosmiteia_filters_select(
				$prefix . '-year',
				'kosm_year',
				__( 'Έτος', 'kosmiteia' ),
				$options
			);
		}
	}

	$actions = kosmiteia_filters_actions( $archive, kosmiteia_announcement_filters_active( $state ) );

	$count = '';

	if ( $attributes['showCount'] ) {
		$count = sprintf(
			'<p class="kosmiteia-filters__count" role="status">%s</p>',
			esc_html( kosmiteia_announcement_filters_count_text( $state ) )
		);
	}

	return kosmiteia_filters_form(
		$action,
		__( 'Αναζήτηση και φίλτρα ανακοινώσεων', 'kosmiteia' ),
		$hidden,
		$fields,
		$actions,
		$count
	);
}

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
