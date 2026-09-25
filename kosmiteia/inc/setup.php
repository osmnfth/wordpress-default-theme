<?php
/**
 * Theme setup, assets, pattern categories, block styles.
 *
 * @package Kosmiteia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Basic features of the theme.
 */
function kosmiteia_setup() {
	load_child_theme_textdomain( 'kosmiteia', KOSMITEIA_DIR . '/languages' );

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 480,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// The same styles as the front-end inside the editor, so the preview is accurate.
	add_editor_style( 'assets/css/theme.css' );

	// Image sizes for the cards (fixed aspect ratio 3:2 and 16:9 for the hero).
	add_image_size( 'kosmiteia-card', 800, 533, true );
	add_image_size( 'kosmiteia-hero', 1920, 1080, true );
}
add_action( 'after_setup_theme', 'kosmiteia_setup' );

/**
 * Names for the custom image sizes in the UI (Media / block settings).
 *
 * @param array $sizes Existing sizes.
 * @return array
 */
function kosmiteia_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'kosmiteia-card' => __( 'Card (3:2)', 'kosmiteia' ),
			'kosmiteia-hero' => __( 'Hero (16:9)', 'kosmiteia' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'kosmiteia_image_size_names' );

/**
 * Asset version for cache busting.
 *
 * In a development environment (WP_DEBUG) this uses the last modified time,
 * so changes to CSS/JS are visible immediately without a hard refresh.
 * In production, it uses the theme version.
 *
 * @param string $relative Relative path to the asset file.
 * @return string
 */
function kosmiteia_asset_version( $relative ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$path = KOSMITEIA_DIR . '/' . ltrim( $relative, '/' );

		if ( file_exists( $path ) ) {
			return (string) filemtime( $path );
		}
	}

	return KOSMITEIA_VERSION;
}

/**
 * Assets front-end.
 */
function kosmiteia_enqueue_assets() {
	wp_enqueue_style(
		'kosmiteia-theme',
		KOSMITEIA_URI . '/assets/css/theme.css',
		array(),
		kosmiteia_asset_version( 'assets/css/theme.css' )
	);

	wp_enqueue_script(
		'kosmiteia-interactions',
		KOSMITEIA_URI . '/assets/js/interactions.js',
		array(),
		kosmiteia_asset_version( 'assets/js/interactions.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

}
add_action( 'wp_enqueue_scripts', 'kosmiteia_enqueue_assets' );

/**
 * Κατηγορίες patterns ώστε τα έτοιμα μπλοκ να βρίσκονται εύκολα στον inserter.
 */
function kosmiteia_register_pattern_categories() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category(
		'kosmiteia',
		array(
			'label'       => __( 'Κοσμητεία', 'kosmiteia' ),
			'description' => __( 'Ενότητες σελίδας για την ιστοσελίδα της Κοσμητείας.', 'kosmiteia' ),
		)
	);
}
add_action( 'init', 'kosmiteia_register_pattern_categories' );

/**
 * Block styles (εναλλακτικές εμφανίσεις που επιλέγονται από το UI).
 */
function kosmiteia_register_block_styles() {
	register_block_style( 'core/post-template', array(
		'name'  => 'kosmiteia-cards',
		'label' => __( 'Κάρτες Κοσμητείας', 'kosmiteia' ),
	) );

	register_block_style( 'core/group', array(
		'name'  => 'kosmiteia-card',
		'label' => __( 'Κάρτα με σκιά', 'kosmiteia' ),
	) );

	register_block_style( 'core/columns', array(
		'name'  => 'kosmiteia-subfooter',
		'label' => __( 'Subfooter 4 στηλών', 'kosmiteia' ),
	) );

	register_block_style( 'core/buttons', array(
		'name'  => 'kosmiteia-scroll-down',
		'label' => __( 'Κουμπί κύλισης (scroll)', 'kosmiteia' ),
	) );

	register_block_style( 'core/post-featured-image', array(
		'name'  => 'kosmiteia-zoom',
		'label' => __( 'Zoom στο hover', 'kosmiteia' ),
	) );

	register_block_style( 'core/heading', array(
		'name'  => 'kosmiteia-underline',
		'label' => __( 'Με υπογράμμιση', 'kosmiteia' ),
	) );
}
add_action( 'init', 'kosmiteia_register_block_styles' );

/**
 * Προσβασιμότητα: το "Read more" των query loops παίρνει το όνομα του άρθρου.
 *
 * @param string $content Περιεχόμενο μπλοκ.
 * @param array  $block   Δεδομένα μπλοκ.
 * @return string
 */
function kosmiteia_accessible_read_more( $content, $block ) {
	$blocks = array( 'core/read-more', 'core/post-excerpt' );

	if ( ! isset( $block['blockName'] ) || ! in_array( $block['blockName'], $blocks, true ) || ! in_the_loop() ) {
		return $content;
	}

	if ( 'core/post-excerpt' === $block['blockName'] && false === strpos( $content, 'wp-block-post-excerpt__more-link' ) ) {
		return $content;
	}

	$label = sprintf(
		/* translators: %s: τίτλος άρθρου. */
		__( 'Περισσότερα για: %s', 'kosmiteia' ),
		wp_strip_all_tags( get_the_title() )
	);

	$aria = ' aria-label="' . esc_attr( $label ) . '"';

	if ( 'core/post-excerpt' === $block['blockName'] ) {
		return str_replace(
			'<a class="wp-block-post-excerpt__more-link"',
			'<a' . $aria . ' class="wp-block-post-excerpt__more-link"',
			$content
		);
	}

	$position = strpos( $content, '<a ' );

	if ( false === $position ) {
		return $content;
	}

	return substr_replace( $content, '<a' . $aria . ' ', $position, 3 );
}
add_filter( 'render_block', 'kosmiteia_accessible_read_more', 10, 2 );
