<?php
defined( 'ABSPATH' ) || exit;

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
		return isset( $source_args['fallback'] ) ? (string) $source_args['fallback'] : '';
	}

	return (string) $value;
}

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

function kosmiteia_dean_page_url() {
	$page_id = (int) kosmiteia_option( 'dean_page', 0 );

	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return (string) get_permalink( $page_id );
	}

	return home_url( '/' );
}

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
			'{{url_dean}}'          => esc_url( kosmiteia_dean_page_url() ),
			'{{url_announcements}}' => esc_url( $archive( 'kosm_announcement' ) ),
			'{{url_programs}}'      => esc_url( $archive( 'kosm_program' ) ),
			'{{url_events}}'        => esc_url( $archive( 'kosm_event' ) ),
			'{{url_people}}'        => esc_url( $archive( 'kosm_person' ) ),
			'{{url_documents}}'     => esc_url( $archive( 'kosm_document' ) ),
		)
	);
}
add_filter( 'render_block', 'kosmiteia_render_dynamic_tokens' );

function kosmiteia_excerpt_length( $length ) {
	return (int) kosmiteia_option( 'excerpt_length', 24 );
}
add_filter( 'excerpt_length', 'kosmiteia_excerpt_length' );
