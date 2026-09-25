<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_default_languages() {
	$languages = array(
		'el' => array(
			'name'   => 'Ελληνικά',
			'short'  => 'EL',
			'locale' => 'el',
			'hreflang' => 'el',
		),
	);

	if ( kosmiteia_option_raw( 'enable_en', 1 ) ) {
		$languages['en'] = array(
			'name'   => 'English',
			'short'  => 'EN',
			'locale' => 'en_US',
			'hreflang' => 'en',
		);
	}

	return apply_filters( 'kosmiteia_languages', $languages );
}

function kosmiteia_multilingual_plugin() {
	if ( function_exists( 'pll_the_languages' ) ) {
		return 'polylang';
	}

	if ( defined( 'ICL_LANGUAGE_CODE' ) && has_filter( 'wpml_active_languages' ) ) {
		return 'wpml';
	}

	return '';
}

function kosmiteia_current_language() {
	$plugin = kosmiteia_multilingual_plugin();

	if ( 'polylang' === $plugin && function_exists( 'pll_current_language' ) ) {
		$current = pll_current_language( 'slug' );
		if ( $current ) {
			return $current;
		}
	}

	if ( 'wpml' === $plugin ) {
		return apply_filters( 'wpml_current_language', ICL_LANGUAGE_CODE );
	}

	static $fallback = null;

	if ( null !== $fallback ) {
		return $fallback;
	}

	$languages = kosmiteia_default_languages();
	$default   = key( $languages );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$requested = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : '';

	if ( ! $requested && isset( $_COOKIE['kosmiteia_lang'] ) ) {
		$requested = sanitize_key( wp_unslash( $_COOKIE['kosmiteia_lang'] ) );
	}

	$fallback = isset( $languages[ $requested ] ) ? $requested : $default;

	return $fallback;
}

function kosmiteia_get_languages() {
	$plugin = kosmiteia_multilingual_plugin();
	$list   = array();

	if ( 'polylang' === $plugin ) {
		$raw = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );

		if ( is_array( $raw ) ) {
			foreach ( $raw as $language ) {
				$list[] = array(
					'slug'     => $language['slug'],
					'name'     => $language['name'],
					'short'    => strtoupper( $language['slug'] ),
					'url'      => $language['url'],
					'current'  => ! empty( $language['current_lang'] ),
					'hreflang' => $language['slug'],
				);
			}
		}

		return $list;
	}

	if ( 'wpml' === $plugin ) {
		$raw = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );

		if ( is_array( $raw ) ) {
			foreach ( $raw as $language ) {
				$list[] = array(
					'slug'     => $language['language_code'],
					'name'     => $language['native_name'],
					'short'    => strtoupper( $language['language_code'] ),
					'url'      => $language['url'],
					'current'  => ! empty( $language['active'] ),
					'hreflang' => $language['language_code'],
				);
			}
		}

		return $list;
	}

	$current = kosmiteia_current_language();

	foreach ( kosmiteia_default_languages() as $slug => $language ) {
		$list[] = array(
			'slug'     => $slug,
			'name'     => $language['name'],
			'short'    => $language['short'],
			'url'       => kosmiteia_language_url( $slug, true ),
			'canonical' => kosmiteia_language_url( $slug ),
			'current'   => $slug === $current,
			'hreflang'  => $language['hreflang'],
		);
	}

	return $list;
}

function kosmiteia_translate_archive_title( $name ) {
	if ( 'el' === kosmiteia_current_language() || '' === (string) $name ) {
		return $name;
	}

	// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	return translate( $name, 'kosmiteia' );
}
add_filter( 'post_type_archive_title', 'kosmiteia_translate_archive_title' );

function kosmiteia_language_url( $slug, $explicit = false ) {
	$languages = kosmiteia_default_languages();
	$default   = key( $languages );

	$host = isset( $_SERVER['HTTP_HOST'] )
		? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) )
		: (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$url  = esc_url_raw( set_url_scheme( 'http://' . $host . $path ) );

	if ( $slug !== $default || $explicit ) {
		return add_query_arg( 'lang', $slug, $url );
	}

	return remove_query_arg( 'lang', $url );
}

function kosmiteia_filter_locale( $locale ) {
	if ( is_admin() || kosmiteia_multilingual_plugin() ) {
		return $locale;
	}

	$languages = kosmiteia_default_languages();
	$current   = kosmiteia_current_language();

	if ( $current === key( $languages ) ) {
		return $locale;
	}

	return isset( $languages[ $current ]['locale'] ) ? $languages[ $current ]['locale'] : $locale;
}
add_filter( 'locale', 'kosmiteia_filter_locale' );

function kosmiteia_remember_language() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_admin() || kosmiteia_multilingual_plugin() || ! isset( $_GET['lang'] ) || headers_sent() ) {
		return;
	}

	$languages = kosmiteia_default_languages();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$requested = sanitize_key( wp_unslash( $_GET['lang'] ) );

	if ( ! isset( $languages[ $requested ] ) ) {
		return;
	}

	setcookie( 'kosmiteia_lang', $requested, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	$_COOKIE['kosmiteia_lang'] = $requested;

	if ( $requested === key( $languages ) ) {
		wp_safe_redirect( kosmiteia_language_url( $requested ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'kosmiteia_remember_language' );

function kosmiteia_language_body_class( $classes ) {
	$classes[] = 'kosmiteia-lang-' . kosmiteia_current_language();

	return $classes;
}
add_filter( 'body_class', 'kosmiteia_language_body_class' );

function kosmiteia_localize_template_part( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || 'core/template-part' !== $parsed_block['blockName'] ) {
		return $parsed_block;
	}

	$languages = kosmiteia_default_languages();
	$current   = kosmiteia_current_language();

	if ( $current === key( $languages ) || empty( $parsed_block['attrs']['slug'] ) ) {
		return $parsed_block;
	}

	$slug        = $parsed_block['attrs']['slug'];
	$localized   = $slug . '-' . $current;
	$exists      = get_block_template( get_stylesheet() . '//' . $localized, 'wp_template_part' );

	if ( $exists ) {
		$parsed_block['attrs']['slug'] = $localized;
	}

	return $parsed_block;
}
add_filter( 'render_block_data', 'kosmiteia_localize_template_part' );

function kosmiteia_localize_navigation( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || 'core/navigation' !== $parsed_block['blockName'] ) {
		return $parsed_block;
	}

	$languages = kosmiteia_default_languages();
	$current   = kosmiteia_current_language();

	if ( $current === key( $languages ) || empty( $parsed_block['attrs']['ref'] ) ) {
		return $parsed_block;
	}

	$menu = get_post( $parsed_block['attrs']['ref'] );

	if ( ! $menu || 'wp_navigation' !== $menu->post_type ) {
		return $parsed_block;
	}

	$localized = get_page_by_path( $menu->post_name . '-' . $current, OBJECT, 'wp_navigation' );

	if ( $localized ) {
		$parsed_block['attrs']['ref'] = $localized->ID;
	}

	return $parsed_block;
}
add_filter( 'render_block_data', 'kosmiteia_localize_navigation' );

function kosmiteia_language_switcher_html( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'display' => 'short',   // short | full.
			'class'   => '',
		)
	);

	$languages = kosmiteia_get_languages();

	if ( count( $languages ) < 2 ) {
		return '';
	}

	$items = '';

	foreach ( $languages as $language ) {
		$label = 'full' === $args['display'] ? $language['name'] : $language['short'];

		$items .= sprintf(
			'<li class="kosmiteia-language-switcher__item"><a class="kosmiteia-language-switcher__link%1$s" href="%2$s" lang="%3$s" hreflang="%3$s"%4$s>%5$s<span class="screen-reader-text"> %6$s</span></a></li>',
			$language['current'] ? ' is-current' : '',
			esc_url( $language['url'] ),
			esc_attr( $language['hreflang'] ),
			$language['current'] ? ' aria-current="true"' : '',
			esc_html( $label ),
			esc_html( $language['name'] )
		);
	}

	return sprintf(
		'<nav class="kosmiteia-language-switcher%1$s" aria-label="%2$s"><ul class="kosmiteia-language-switcher__list">%3$s</ul></nav>',
		$args['class'] ? ' ' . esc_attr( $args['class'] ) : '',
		esc_attr__( 'Επιλογή γλώσσας', 'kosmiteia' ),
		$items
	);
}

function kosmiteia_hreflang_tags() {
	$languages = kosmiteia_get_languages();

	if ( count( $languages ) < 2 ) {
		return;
	}

	foreach ( $languages as $language ) {
		printf(
			'<link rel="alternate" hreflang="%1$s" href="%2$s" />' . "\n",
			esc_attr( $language['hreflang'] ),
			esc_url( isset( $language['canonical'] ) ? $language['canonical'] : $language['url'] )
		);
	}
}
add_action( 'wp_head', 'kosmiteia_hreflang_tags', 5 );
