<?php
/**
 * Ρυθμίσεις theme, assets, pattern categories, block styles.
 *
 * @package Kosmiteia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Βασικές δυνατότητες του theme.
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

	// Τα ίδια στυλ με το front-end μέσα στον editor, ώστε το preview να είναι πιστό.
	add_editor_style( 'assets/css/theme.css' );

	// Μεγέθη εικόνων για τα cards (σταθερή αναλογία 3:2 και 16:9 για το hero).
	add_image_size( 'kosmiteia-card', 800, 533, true );
	add_image_size( 'kosmiteia-hero', 1920, 1080, true );
}
add_action( 'after_setup_theme', 'kosmiteia_setup' );

/**
 * Ονόματα για τα custom image sizes στο UI (Media / block settings).
 *
 * @param array $sizes Υπάρχοντα μεγέθη.
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
 * Έκδοση αρχείου για cache busting.
 *
 * Σε περιβάλλον ανάπτυξης (WP_DEBUG) χρησιμοποιεί την ώρα τελευταίας
 * τροποποίησης, ώστε οι αλλαγές σε CSS/JS να φαίνονται αμέσως χωρίς
 * σκληρό refresh. Στην παραγωγή χρησιμοποιεί την έκδοση του theme.
 *
 * @param string $relative Διαδρομή σχετική με τον φάκελο του theme.
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

	wp_enqueue_script(
		'kosmiteia-slider',
		KOSMITEIA_URI . '/assets/js/slider.js',
		array(),
		kosmiteia_asset_version( 'assets/js/slider.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Μεταφρασμένα labels για την προσβασιμότητα του slider (aria-label κ.λπ.).
	wp_localize_script(
		'kosmiteia-slider',
		'kosmiteiaSliderL10n',
		array(
			'carousel'   => __( 'Παρουσίαση διαφανειών', 'kosmiteia' ),
			'previous'   => __( 'Προηγούμενη διαφάνεια', 'kosmiteia' ),
			'next'       => __( 'Επόμενη διαφάνεια', 'kosmiteia' ),
			'play'       => __( 'Έναρξη αυτόματης εναλλαγής', 'kosmiteia' ),
			'pause'      => __( 'Παύση αυτόματης εναλλαγής', 'kosmiteia' ),
			/* translators: 1: αριθμός διαφάνειας, 2: σύνολο διαφανειών. */
			'slideLabel' => __( 'Διαφάνεια %1$s από %2$s', 'kosmiteia' ),
			/* translators: %s: αριθμός διαφάνειας. */
			'goToSlide'  => __( 'Μετάβαση στη διαφάνεια %s', 'kosmiteia' ),
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
 * Δυναμικά tokens σε κείμενα και συνδέσμους.
 *
 * Γράψτε το token μέσα σε οποιοδήποτε μπλοκ κειμένου ή στο πεδίο URL ενός
 * κουμπιού / στοιχείου μενού:
 *
 *   {{year}}               - τρέχον έτος
 *   {{site}}               - όνομα ιστότοπου
 *   {{url_home}}           - αρχική σελίδα
 *   {{url_schools}}        - αρχείο Σχολών
 *   {{url_announcements}}  - αρχείο Ανακοινώσεων
 *   {{url_programs}}       - αρχείο Μεταπτυχιακών
 *
 * Έτσι οι σύνδεσμοι παραμένουν σωστοί ακόμη κι αν αλλάξουν τα permalinks.
 *
 * @param string $block_content Το HTML του μπλοκ.
 * @return string
 */
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
			'{{url_home}}'          => esc_url( home_url( '/' ) ),
			'{{url_schools}}'       => esc_url( $archive( 'kosm_school' ) ),
			'{{url_announcements}}' => esc_url( $archive( 'kosm_announcement' ) ),
			'{{url_programs}}'      => esc_url( $archive( 'kosm_program' ) ),
		)
	);
}
add_filter( 'render_block', 'kosmiteia_render_dynamic_tokens' );

/**
 * Excerpt: μήκος και κατάληξη, μεταφράσιμα.
 *
 * @param int $length Αριθμός λέξεων.
 * @return int
 */
function kosmiteia_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'kosmiteia_excerpt_length' );

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
