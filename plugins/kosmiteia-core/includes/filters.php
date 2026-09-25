<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_filters_lang_arg() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET['lang'] ) || ! is_scalar( $_GET['lang'] ) ) {
		return array();
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lang = sanitize_key( wp_unslash( $_GET['lang'] ) );

	return $lang ? array( 'lang' => $lang ) : array();
}

function kosmiteia_filters_hidden_fields( &$action ) {
	$carry = array();
	$parts = wp_parse_url( $action );

	if ( ! empty( $parts['query'] ) ) {
		parse_str( $parts['query'], $carry );
		$action = strtok( $action, '?' );
	}

	$carry = array_merge( $carry, kosmiteia_filters_lang_arg() );

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

function kosmiteia_filters_terms( $taxonomy, $post_type ) {
	$key    = 'kosmiteia_terms_' . md5( $taxonomy . '|' . $post_type );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$terms = array();

	if ( $post_ids ) {
		$found = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'object_ids' => $post_ids,
			)
		);

		if ( ! is_wp_error( $found ) ) {
			foreach ( $found as $term ) {
				$terms[ $term->term_id ] = $term;
			}

			$terms = array_values( $terms );
		}
	}

	set_transient( $key, $terms, HOUR_IN_SECONDS );

	return $terms;
}

function kosmiteia_filters_flush_terms() {
	foreach ( array(
		array( 'kosm_faculty', 'kosm_announcement' ),
		array( 'kosm_faculty', 'kosm_program' ),
		array( 'kosm_ann_category', 'kosm_announcement' ),
		array( 'kosm_program_type', 'kosm_program' ),
	) as $pair ) {
		delete_transient( 'kosmiteia_terms_' . md5( $pair[0] . '|' . $pair[1] ) );
	}
}
add_action( 'save_post', 'kosmiteia_filters_flush_terms' );
add_action( 'deleted_post', 'kosmiteia_filters_flush_terms' );
add_action( 'set_object_terms', 'kosmiteia_filters_flush_terms' );
add_action( 'edited_term', 'kosmiteia_filters_flush_terms' );
add_action( 'delete_term', 'kosmiteia_filters_flush_terms' );

function kosmiteia_filters_term_select( $id, $name, $label, $taxonomy, $all, $current, $post_type ) {
	$terms = kosmiteia_filters_terms( $taxonomy, $post_type );

	if ( empty( $terms ) ) {
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

	return kosmiteia_filters_select( $id, $name, $label, $options );
}

function kosmiteia_filters_select( $id, $name, $label, $options ) {
	return sprintf(
		'<p class="kosmiteia-filters__field"><label for="%1$s">%2$s</label><select id="%1$s" name="%3$s">%4$s</select></p>',
		esc_attr( $id ),
		esc_html( $label ),
		esc_attr( $name ),
		$options // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

function kosmiteia_filters_search_field( $id, $name, $value, $placeholder ) {
	return sprintf(
		'<p class="kosmiteia-filters__field kosmiteia-filters__field--search"><label for="%1$s">%2$s</label><input type="search" id="%1$s" name="%3$s" value="%4$s" placeholder="%5$s" /></p>',
		esc_attr( $id ),
		esc_html__( 'Αναζήτηση', 'kosmiteia' ),
		esc_attr( $name ),
		esc_attr( $value ),
		esc_attr( $placeholder )
	);
}

function kosmiteia_filters_actions( $archive, $active ) {
	$html = sprintf(
		'<button type="submit" class="wp-element-button kosmiteia-filters__submit">%s</button>',
		esc_html__( 'Φιλτράρισμα', 'kosmiteia' )
	);

	if ( $active ) {
		$html .= sprintf(
			'<a class="kosmiteia-filters__reset" href="%s">%s</a>',
			esc_url( add_query_arg( kosmiteia_filters_lang_arg(), $archive ) ),
			esc_html__( 'Καθαρισμός φίλτρων', 'kosmiteia' )
		);
	}

	return $html;
}

function kosmiteia_filters_form( $action, $label, $hidden, $fields, $actions, $count ) {
	return sprintf(
		'<form class="kosmiteia-filters" method="get" action="%1$s" role="search" aria-label="%2$s">%3$s<div class="kosmiteia-filters__fields">%4$s<p class="kosmiteia-filters__actions">%5$s</p></div>%6$s</form>',
		esc_url( $action ),
		esc_attr( $label ),
		$hidden,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$fields,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$actions, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$count    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
