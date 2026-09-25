<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_breadcrumb_trail() {
	$trail = array();

	if ( is_front_page() ) {
		return $trail;
	}

	$trail[] = array(
		'name' => __( 'Αρχική', 'kosmiteia' ),
		'url'  => home_url( '/' ),
	);

	$add_post_type_archive = static function ( $post_type ) use ( &$trail ) {
		$link = get_post_type_archive_link( $post_type );

		if ( ! $link ) {
			return;
		}

		$object = get_post_type_object( $post_type );

		$trail[] = array(
			'name' => $object ? $object->labels->name : $post_type,
			'url'  => $link,
		);
	};

	if ( is_singular() ) {
		$post_id   = get_queried_object_id();
		$post_type = get_post_type( $post_id );

		if ( 'page' === $post_type ) {
			foreach ( array_reverse( (array) get_post_ancestors( $post_id ) ) as $ancestor ) {
				$trail[] = array(
					'name' => wp_strip_all_tags( get_the_title( $ancestor ) ),
					'url'  => (string) get_permalink( $ancestor ),
				);
			}
		} else {
			$add_post_type_archive( $post_type );

			if ( 'kosm_announcement' === $post_type ) {
				$terms = get_the_terms( $post_id, 'kosm_ann_category' );

				if ( $terms && ! is_wp_error( $terms ) ) {
					$term    = $terms[0];
					$trail[] = array(
						'name' => $term->name,
						'url'  => (string) get_term_link( $term ),
					);
				}
			}
		}

		$trail[] = array(
			'name' => wp_strip_all_tags( get_the_title( $post_id ) ),
			'url'  => '',
		);

		return $trail;
	}

	if ( is_post_type_archive() ) {
		$object = get_queried_object();

		$trail[] = array(
			'name' => ( $object && isset( $object->labels->name ) ) ? $object->labels->name : wp_strip_all_tags( post_type_archive_title( '', false ) ),
			'url'  => '',
		);

		return $trail;
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( $term && isset( $term->taxonomy ) ) {
			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( $taxonomy && ! empty( $taxonomy->object_type ) ) {
				$add_post_type_archive( $taxonomy->object_type[0] );
			}

			$trail[] = array(
				'name' => $term->name,
				'url'  => '',
			);
		}

		return $trail;
	}

	if ( is_search() ) {
		$trail[] = array(
			/* translators: %s: όρος αναζήτησης. */
			'name' => sprintf( __( 'Αναζήτηση: %s', 'kosmiteia' ), get_search_query() ),
			'url'  => '',
		);

		return $trail;
	}

	if ( is_404() ) {
		$trail[] = array(
			'name' => __( 'Η σελίδα δεν βρέθηκε', 'kosmiteia' ),
			'url'  => '',
		);

		return $trail;
	}

	if ( is_archive() ) {
		$trail[] = array(
			'name' => wp_strip_all_tags( get_the_archive_title() ),
			'url'  => '',
		);
	}

	return $trail;
}

function kosmiteia_breadcrumbs_html( $attributes = array() ) {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'showHome'    => true,
			'showCurrent' => true,
		)
	);

	$trail = kosmiteia_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return '';
	}

	if ( ! $attributes['showHome'] ) {
		array_shift( $trail );
	}

	if ( ! $attributes['showCurrent'] ) {
		array_pop( $trail );
	}

	if ( ! $trail ) {
		return '';
	}

	$items = '';
	$last  = count( $trail ) - 1;

	foreach ( array_values( $trail ) as $index => $item ) {
		$name = esc_html( $item['name'] );

		if ( '' !== $item['url'] && $index !== $last ) {
			$content = sprintf(
				'<a class="kosmiteia-breadcrumbs__link" href="%s">%s</a>',
				esc_url( $item['url'] ),
				$name
			);
		} else {
			$content = sprintf( '<span class="kosmiteia-breadcrumbs__current" aria-current="page">%s</span>', $name );
		}

		$items .= sprintf( '<li class="kosmiteia-breadcrumbs__item">%s</li>', $content );
	}

	return sprintf(
		'<nav class="kosmiteia-breadcrumbs" aria-label="%1$s"><ol class="kosmiteia-breadcrumbs__list">%2$s</ol></nav>',
		esc_attr__( 'Διαδρομή πλοήγησης', 'kosmiteia' ),
		$items
	);
}

function kosmiteia_render_breadcrumbs_block( $attributes ) {
	$html = kosmiteia_breadcrumbs_html( $attributes );

	if ( ! $html ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '<p class="kosmiteia-notice">' . esc_html__( 'Η διαδρομή εμφανίζεται στις εσωτερικές σελίδες.', 'kosmiteia' ) . '</p>';
		}

		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		$html
	);
}
