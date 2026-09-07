<?php
/**
 * Custom blocks του theme.
 *
 * - kosmiteia/slider              : προσβάσιμο hero slider με InnerBlocks
 *                                   (κάθε διαφάνεια είναι κανονικό Cover/Group,
 *                                   άρα φωτογραφία ή βίντεο και κουμπιά μπαίνουν
 *                                   από το UI).
 * - kosmiteia/language-switcher   : δυναμικός επιλογέας γλώσσας.
 * - kosmiteia/announcement-filters: αναζήτηση και φίλτρα στο αρχείο Ανακοινώσεων.
 * - kosmiteia/program-filters     : αναζήτηση, φίλτρα και ταξινόμηση στο αρχείο
 *                                   Μεταπτυχιακών.
 * - kosmiteia/breadcrumbs        : διαδρομή πλοήγησης (breadcrumbs).
 * - kosmiteia/map                 : χάρτης Leaflet / OpenStreetMap.
 * - kosmiteia/gallery             : μικρογραφίες σε responsive γραμμή με
 *                                   lightbox (χωρίς jQuery/Colorbox).
 *
 * Τα scripts του editor είναι γραμμένα σε καθαρή JavaScript (χωρίς JSX),
 * οπότε το theme δεν χρειάζεται build step (npm/webpack).
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Καταχώριση των editor scripts με τα σωστά dependencies.
 */
function kosmiteia_register_block_scripts() {
	wp_register_script(
		'kosmiteia-slider-editor',
		KOSMITEIA_CORE_URL . '/blocks/slider/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		kosmiteia_core_asset_version( 'blocks/slider/index.js' ),
		true
	);

	wp_register_script(
		'kosmiteia-language-switcher-editor',
		KOSMITEIA_CORE_URL . '/blocks/language-switcher/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		kosmiteia_core_asset_version( 'blocks/language-switcher/index.js' ),
		true
	);

	wp_register_script(
		'kosmiteia-announcement-filters-editor',
		KOSMITEIA_CORE_URL . '/blocks/announcement-filters/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		kosmiteia_core_asset_version( 'blocks/announcement-filters/index.js' ),
		true
	);

	wp_register_script(
		'kosmiteia-program-filters-editor',
		KOSMITEIA_CORE_URL . '/blocks/program-filters/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		kosmiteia_core_asset_version( 'blocks/program-filters/index.js' ),
		true
	);

	wp_register_script(
		'kosmiteia-breadcrumbs-editor',
		KOSMITEIA_CORE_URL . '/blocks/breadcrumbs/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		kosmiteia_core_asset_version( 'blocks/breadcrumbs/index.js' ),
		true
	);

	wp_register_script(
		'kosmiteia-map-editor',
		KOSMITEIA_CORE_URL . '/blocks/map/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		kosmiteia_core_asset_version( 'blocks/map/index.js' ),
		true
	);

	wp_register_script(
		'kosmiteia-gallery-editor',
		KOSMITEIA_CORE_URL . '/blocks/gallery/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		kosmiteia_core_asset_version( 'blocks/gallery/index.js' ),
		true
	);

	kosmiteia_register_map_assets();
	kosmiteia_register_lightbox_assets();
	kosmiteia_register_slider_assets();

	if ( function_exists( 'wp_set_script_translations' ) ) {
		$handles = array(
			'kosmiteia-slider-editor',
			'kosmiteia-language-switcher-editor',
			'kosmiteia-announcement-filters-editor',
			'kosmiteia-program-filters-editor',
			'kosmiteia-breadcrumbs-editor',
			'kosmiteia-map-editor',
			'kosmiteia-gallery-editor',
		);

		foreach ( $handles as $handle ) {
			wp_set_script_translations( $handle, 'kosmiteia', KOSMITEIA_CORE_DIR . '/languages' );
		}
	}
}
add_action( 'init', 'kosmiteia_register_block_scripts', 5 );

/**
 * Leaflet + το δικό μας script/στυλ χάρτη.
 *
 * Η Leaflet συνοδεύει το theme (assets/vendor/leaflet), οπότε δεν φορτώνεται
 * τίποτα από CDN. Τα αρχεία μπαίνουν στη σελίδα μόνο όταν υπάρχει μπλοκ χάρτη,
 * μέσω των πεδίων "style"/"viewScript" του block.json.
 */
function kosmiteia_register_map_assets() {
	wp_register_style(
		'kosmiteia-leaflet',
		KOSMITEIA_CORE_URL . '/assets/vendor/leaflet/leaflet.css',
		array(),
		'1.9.4'
	);

	wp_register_script(
		'kosmiteia-leaflet',
		KOSMITEIA_CORE_URL . '/assets/vendor/leaflet/leaflet.js',
		array(),
		'1.9.4',
		true
	);

	wp_register_style(
		'kosmiteia-map',
		false,
		array( 'kosmiteia-leaflet' ),
		KOSMITEIA_CORE_VERSION
	);

	wp_register_script(
		'kosmiteia-map-view',
		KOSMITEIA_CORE_URL . '/assets/js/map.js',
		array( 'kosmiteia-leaflet' ),
		kosmiteia_core_asset_version( 'assets/js/map.js' ),
		true
	);

	wp_localize_script(
		'kosmiteia-map-view',
		'kosmiteiaMapL10n',
		array(
			'tileUrl'     => kosmiteia_map_tile_url(),
			'attribution' => kosmiteia_map_attribution(),
			'imagePath'   => KOSMITEIA_CORE_URL . '/assets/vendor/leaflet/images/',
			'markerAlt'   => __( 'Σημείο στον χάρτη', 'kosmiteia' ),
		)
	);
}

/**
 * Το script του slider.
 *
 * Φορτώνεται μόνο όπου υπάρχει μπλοκ slider (πεδίο "viewScript" του
 * block.json) - όχι σε κάθε σελίδα. Τα στυλ του είναι στο θέμα.
 */
function kosmiteia_register_slider_assets() {
	wp_register_script(
		'kosmiteia-slider-view',
		KOSMITEIA_CORE_URL . '/assets/js/slider.js',
		array(),
		kosmiteia_core_asset_version( 'assets/js/slider.js' ),
		true
	);

	// Μεταφρασμένα labels για την προσβασιμότητα του slider (aria-label κ.λπ.).
	wp_localize_script(
		'kosmiteia-slider-view',
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

/**
 * Το script του lightbox.
 *
 * Φορτώνεται μόνο στις σελίδες που έχουν μπλοκ γκαλερί (πεδίο "viewScript"
 * του block.json). Τα στυλ του βρίσκονται στο assets/css/theme.css.
 */
function kosmiteia_register_lightbox_assets() {
	wp_register_script(
		'kosmiteia-lightbox',
		KOSMITEIA_CORE_URL . '/assets/js/lightbox.js',
		array(),
		kosmiteia_core_asset_version( 'assets/js/lightbox.js' ),
		true
	);

	wp_localize_script(
		'kosmiteia-lightbox',
		'kosmiteiaLightboxL10n',
		array(
			'dialog'   => __( 'Προβολή φωτογραφίας', 'kosmiteia' ),
			'previous' => __( 'Προηγούμενη φωτογραφία', 'kosmiteia' ),
			'next'     => __( 'Επόμενη φωτογραφία', 'kosmiteia' ),
			'close'    => __( 'Κλείσιμο', 'kosmiteia' ),
			/* translators: 1: αύξων αριθμός φωτογραφίας, 2: σύνολο φωτογραφιών. */
			'counter'  => __( '%1$s από %2$s', 'kosmiteia' ),
		)
	);
}

/**
 * Το URL των πλακιδίων (tiles).
 *
 * Αλλάξτε το αν το Ίδρυμα διαθέτει δικό του tile server:
 *   add_filter( 'kosmiteia_map_tile_url', function () { return 'https://.../{z}/{x}/{y}.png'; } );
 *
 * @return string
 */
function kosmiteia_map_tile_url() {
	return (string) apply_filters( 'kosmiteia_map_tile_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png' );
}

/**
 * Η απαιτούμενη απόδοση πηγής των πλακιδίων.
 *
 * @return string
 */
function kosmiteia_map_attribution() {
	return (string) apply_filters(
		'kosmiteia_map_attribution',
		'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	);
}

/**
 * Καταχώριση των μπλοκ από τα block.json.
 */
function kosmiteia_register_blocks() {
	register_block_type( KOSMITEIA_CORE_DIR . '/blocks/slider' );

	register_block_type(
		KOSMITEIA_CORE_DIR . '/blocks/language-switcher',
		array(
			'render_callback' => 'kosmiteia_render_language_switcher_block',
		)
	);

	register_block_type(
		KOSMITEIA_CORE_DIR . '/blocks/announcement-filters',
		array(
			'render_callback' => 'kosmiteia_render_announcement_filters_block',
		)
	);

	register_block_type(
		KOSMITEIA_CORE_DIR . '/blocks/program-filters',
		array(
			'render_callback' => 'kosmiteia_render_program_filters_block',
		)
	);

	register_block_type(
		KOSMITEIA_CORE_DIR . '/blocks/breadcrumbs',
		array(
			'render_callback' => 'kosmiteia_render_breadcrumbs_block',
		)
	);

	register_block_type(
		KOSMITEIA_CORE_DIR . '/blocks/map',
		array(
			'render_callback' => 'kosmiteia_render_map_block',
		)
	);

	register_block_type(
		KOSMITEIA_CORE_DIR . '/blocks/gallery',
		array(
			'render_callback' => 'kosmiteia_render_gallery_block',
		)
	);
}
add_action( 'init', 'kosmiteia_register_blocks' );

/**
 * Render callback του language switcher.
 *
 * @param array $attributes Attributes του μπλοκ.
 * @return string
 */
function kosmiteia_render_language_switcher_block( $attributes ) {
	$html = kosmiteia_language_switcher_html(
		array(
			'display' => isset( $attributes['display'] ) ? $attributes['display'] : 'short',
		)
	);

	if ( ! $html ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '<p class="kosmiteia-notice">' . esc_html__( 'Δεν βρέθηκαν γλώσσες προς εμφάνιση.', 'kosmiteia' ) . '</p>';
		}

		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(),
		$html
	);
}

/**
 * Render callback του χάρτη.
 *
 * Τυπώνει τα δεδομένα ως data attributes και μια πλήρη εναλλακτική εμφάνιση
 * (διεύθυνση + σύνδεσμοι). Το JavaScript απλώς «ανεβάζει» τον χάρτη από πάνω,
 * οπότε χωρίς JS ή χωρίς δίκτυο η πληροφορία παραμένει προσβάσιμη.
 *
 * @param array $attributes Attributes του μπλοκ.
 * @return string
 */
function kosmiteia_render_map_block( $attributes ) {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'lat'             => 37.9682,
			'lng'             => 23.783,
			'zoom'            => 16,
			'height'          => 420,
			'markerTitle'     => '',
			'markerAddress'   => '',
			'showMarker'      => true,
			'scrollWheelZoom' => false,
			'showDirections'  => true,
		)
	);

	$lat    = (float) $attributes['lat'];
	$lng    = (float) $attributes['lng'];
	$zoom   = min( 19, max( 3, (int) $attributes['zoom'] ) );
	$height = min( 900, max( 200, (int) $attributes['height'] ) );

	if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
		return '';
	}

	$title   = sanitize_text_field( (string) $attributes['markerTitle'] );
	$address = sanitize_text_field( (string) $attributes['markerAddress'] );
	$label   = $title ? $title : __( 'Χάρτης τοποθεσίας', 'kosmiteia' );

	$coords = $lat . ',' . $lng;

	$view_url = sprintf(
		'https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=%3$d/%1$s/%2$s',
		$lat,
		$lng,
		$zoom
	);

	$fallback = '';

	if ( $title ) {
		$fallback .= sprintf( '<strong class="kosmiteia-map__title">%s</strong>', esc_html( $title ) );
	}

	if ( $address ) {
		$fallback .= sprintf( '<span class="kosmiteia-map__address">%s</span>', esc_html( $address ) );
	}

	$fallback .= sprintf(
		'<a class="kosmiteia-map__link" href="%s" rel="noopener">%s</a>',
		esc_url( $view_url ),
		esc_html__( 'Άνοιγμα στον χάρτη (OpenStreetMap)', 'kosmiteia' )
	);

	if ( $attributes['showDirections'] ) {
		$fallback .= sprintf(
			'<a class="kosmiteia-map__link" href="%s" rel="noopener">%s</a>',
			esc_url( 'https://www.openstreetmap.org/directions?to=' . rawurlencode( $coords ) ),
			esc_html__( 'Οδηγίες πρόσβασης', 'kosmiteia' )
		);
	}

	$wrapper = get_block_wrapper_attributes(
		array(
			'class'                => 'kosmiteia-map',
			'style'                => sprintf( '--kosmiteia-map-height:%dpx', $height ),
			'data-lat'             => (string) $lat,
			'data-lng'             => (string) $lng,
			'data-zoom'            => (string) $zoom,
			'data-marker'          => $attributes['showMarker'] ? 'true' : 'false',
			'data-marker-title'    => $title,
			'data-marker-address'  => $address,
			'data-scroll-wheel-zoom' => $attributes['scrollWheelZoom'] ? 'true' : 'false',
		)
	);

	return sprintf(
		'<div %1$s><div class="kosmiteia-map__canvas" aria-label="%2$s"></div><div class="kosmiteia-map__fallback">%3$s</div></div>',
		$wrapper,
		esc_attr( $label ),
		$fallback
	);
}

/**
 * Οι εικόνες που θα δείξει η γκαλερί.
 *
 * Πηγή είναι αποκλειστικά το πεδίο «Φωτογραφίες (γκαλερί)» της σελίδας
 * (inc/gallery.php) - ποτέ οι εικόνες του κειμένου ή η επιλεγμένη εικόνα.
 *
 * @param array $attributes Attributes του μπλοκ.
 * @param int   $post_id    Η σελίδα στην οποία ανήκει το μπλοκ.
 * @return int[] IDs συνημμένων, με τη σειρά που όρισε ο συντάκτης.
 */
function kosmiteia_gallery_image_ids( $attributes, $post_id ) {
	if ( ! $post_id ) {
		return array();
	}

	$ids   = kosmiteia_gallery_field_ids( $post_id );
	$limit = max( 0, (int) $attributes['limit'] );

	if ( $limit ) {
		$ids = array_slice( $ids, 0, $limit );
	}

	return $ids;
}

/**
 * Render callback της γκαλερί.
 *
 * Κάθε μικρογραφία είναι σύνδεσμος προς το πλήρες αρχείο: χωρίς JavaScript η
 * εικόνα ανοίγει κανονικά σε νέα προβολή, ενώ με JavaScript την αναλαμβάνει το
 * lightbox (assets/js/lightbox.js).
 *
 * @param array    $attributes Attributes του μπλοκ.
 * @param string   $content    Περιεχόμενο (δεν χρησιμοποιείται).
 * @param WP_Block $block      Το instance του μπλοκ, για το context της σελίδας.
 * @return string
 */
function kosmiteia_render_gallery_block( $attributes, $content = '', $block = null ) {
	static $instance = 0;

	$attributes = wp_parse_args(
		$attributes,
		array(
			'heading'      => '',
			'columns'      => 4,
			'gap'          => 16,
			'minWidth'     => 160,
			'aspectRatio'  => '1/1',
			'showCaptions' => false,
			'limit'        => 0,
		)
	);

	$post_id = 0;

	if ( $block instanceof WP_Block && isset( $block->context['postId'] ) ) {
		$post_id = (int) $block->context['postId'];
	}

	if ( ! $post_id ) {
		$post_id = (int) get_the_ID();
	}

	$ids = kosmiteia_gallery_image_ids( $attributes, $post_id );

	if ( empty( $ids ) ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '<p class="kosmiteia-notice">' . esc_html__( 'Δεν έχουν επιλεγεί φωτογραφίες στο πεδίο «Φωτογραφίες (γκαλερί)» της σελίδας.', 'kosmiteia' ) . '</p>';
		}

		return '';
	}

	++$instance;

	$group   = 'kosmiteia-gallery-' . $post_id . '-' . $instance;
	$columns = min( 8, max( 1, (int) $attributes['columns'] ) );
	$gap     = min( 48, max( 0, (int) $attributes['gap'] ) );
	$min     = min( 320, max( 80, (int) $attributes['minWidth'] ) );

	$ratios = array( '1/1', '4/3', '3/2', '16/9', '3/4', 'auto' );
	$ratio  = in_array( $attributes['aspectRatio'], $ratios, true ) ? $attributes['aspectRatio'] : '1/1';

	$items = '';

	foreach ( $ids as $id ) {
		$full = wp_get_attachment_image_src( $id, 'full' );

		if ( ! $full ) {
			continue;
		}

		$alt     = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		$caption = trim( wp_strip_all_tags( (string) wp_get_attachment_caption( $id ) ) );
		$label   = $caption ? $caption : $alt;

		$thumb = wp_get_attachment_image(
			$id,
			'kosmiteia-card',
			false,
			array(
				'class'    => 'kosmiteia-gallery__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		$figcaption = '';

		if ( $attributes['showCaptions'] && $caption ) {
			$figcaption = '<figcaption class="kosmiteia-gallery__caption">' . esc_html( $caption ) . '</figcaption>';
		}

		$items .= sprintf(
			'<li class="kosmiteia-gallery__cell"><figure class="kosmiteia-gallery__figure"><a class="kosmiteia-gallery__item" href="%1$s" data-kosmiteia-lightbox="%2$s" data-width="%3$d" data-height="%4$d" data-alt="%5$s" data-caption="%6$s" aria-label="%7$s">%8$s<span class="kosmiteia-gallery__zoom" aria-hidden="true"></span></a>%9$s</figure></li>',
			esc_url( $full[0] ),
			esc_attr( $group ),
			(int) $full[1],
			(int) $full[2],
			esc_attr( $alt ),
			esc_attr( $label ),
			esc_attr(
				$label
					? sprintf(
						/* translators: %s: λεζάντα ή εναλλακτικό κείμενο της φωτογραφίας. */
						__( 'Άνοιγμα φωτογραφίας σε μεγέθυνση: %s', 'kosmiteia' ),
						$label
					)
					: __( 'Άνοιγμα φωτογραφίας σε μεγέθυνση', 'kosmiteia' )
			),
			$thumb,
			$figcaption
		);
	}

	if ( ! $items ) {
		return '';
	}

	$heading = '';

	if ( $attributes['heading'] ) {
		$heading = '<h2 class="kosmiteia-gallery__heading is-style-kosmiteia-underline">' . esc_html( $attributes['heading'] ) . '</h2>';
	}

	$wrapper = get_block_wrapper_attributes(
		array(
			'class' => 'kosmiteia-gallery',
			'style' => sprintf(
				'--kosmiteia-gallery-columns:%1$d;--kosmiteia-gallery-gap:%2$dpx;--kosmiteia-gallery-min:%3$dpx;--kosmiteia-gallery-ratio:%4$s',
				$columns,
				$gap,
				$min,
				$ratio
			),
		)
	);

	return sprintf(
		'<div %1$s>%2$s<ul class="kosmiteia-gallery__list">%3$s</ul></div>',
		$wrapper,
		$heading,
		$items
	);
}
