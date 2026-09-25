<?php
defined( 'ABSPATH' ) || exit;

define( 'KOSM_ASSETS', KOSMITEIA_CORE_DIR . 'assets/demo' );
define( 'KOSM_DEMO_VERSION', '3.1.0' );

function kosmiteia_demo_log( $message = null, $type = 'log' ) {
	static $messages = array();

	if ( null === $message ) {
		return $messages;
	}

	$messages[] = array(
		'type'    => $type,
		'message' => (string) $message,
	);

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		if ( 'warning' === $type ) {
			WP_CLI::warning( $message );
		} elseif ( 'success' === $type ) {
			WP_CLI::success( $message );
		} else {
			WP_CLI::log( $message );
		}
	}

	return $messages;
}

function kosmiteia_demo_warn( $message ) {
	kosmiteia_demo_log( $message, 'warning' );
}

function kosmiteia_demo_success( $message ) {
	kosmiteia_demo_log( $message, 'success' );
}

function kosm_image( $filename, $title, $alt ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'meta_key'    => '_kosm_asset',
			'meta_value'  => $filename,
			'fields'      => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$path = KOSM_ASSETS . '/' . $filename;

	if ( ! file_exists( $path ) ) {
		kosmiteia_demo_warn( "Λείπει η εικόνα $filename" );
		return 0;
	}

	$upload = wp_upload_bits( $filename, null, file_get_contents( $path ) );

	if ( ! empty( $upload['error'] ) ) {
		kosmiteia_demo_warn( 'Αποτυχία μεταφόρτωσης ' . $filename . ': ' . $upload['error'] );
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $attachment_id, '_kosm_asset', $filename );

	return (int) $attachment_id;
}

function kosm_post( $post_type, $slug, $args ) {
	$existing = get_posts(
		array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'name'        => $slug,
			'numberposts' => 1,
		)
	);

	$data = wp_parse_args(
		$args,
		array(
			'post_type'   => $post_type,
			'post_name'   => $slug,
			'post_status' => 'publish',
		)
	);

	if ( $existing ) {
		$post_id = (int) $existing[0]->ID;

		$seeded = get_post_meta( $post_id, '_kosmiteia_seeded_hash', true );

		if ( ! $seeded || md5( (string) $existing[0]->post_content ) !== $seeded ) {
			kosmiteia_demo_log( sprintf( '    Το «%s» υπάρχει και έχει δικό του περιεχόμενο - παραλείπεται.', $slug ) );

			return $post_id;
		}

		$data['ID'] = $post_id;
		wp_update_post( $data );
	} else {
		$post_id = wp_insert_post( $data, true );
	}

	if ( is_wp_error( $post_id ) ) {
		kosmiteia_demo_warn( $post_id->get_error_message() );
		return 0;
	}

	$post_id = (int) $post_id;

	if ( isset( $data['post_content'] ) ) {
		update_post_meta( $post_id, '_kosmiteia_seeded_hash', md5( (string) $data['post_content'] ) );
	}

	return $post_id;
}

function kosm_setting( $key, $value ) {
	$settings = get_option( KOSMITEIA_SETTINGS_OPTION, array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$settings[ $key ] = $value;

	update_option( KOSMITEIA_SETTINGS_OPTION, $settings );
}

function kosm_template_part( $slug, $title, $area, $content ) {
	$existing = get_posts(
		array(
			'post_type'   => 'wp_template_part',
			'post_status' => 'any',
			'name'        => $slug,
			'numberposts' => 1,
		)
	);

	if ( $existing ) {
		$seeded = get_post_meta( $existing[0]->ID, '_kosmiteia_seeded_hash', true );

		if ( $seeded && md5( $existing[0]->post_content ) !== $seeded ) {
			kosmiteia_demo_log( sprintf( '    Το part «%s» έχει τροποποιηθεί - παραλείπεται.', $slug ) );

			return (int) $existing[0]->ID;
		}
	}

	$data = array(
		'post_type'    => 'wp_template_part',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => '',
	);

	if ( $existing ) {
		$data['ID'] = $existing[0]->ID;
		$part_id    = wp_update_post( $data );
	} else {
		$part_id = wp_insert_post( $data, true );
	}

	if ( is_wp_error( $part_id ) ) {
		kosmiteia_demo_warn( $part_id->get_error_message() );
		return 0;
	}

	wp_set_object_terms( $part_id, get_stylesheet(), 'wp_theme' );
	wp_set_object_terms( $part_id, $area, 'wp_template_part_area' );
	update_post_meta( $part_id, '_kosmiteia_seeded_hash', md5( $content ) );

	return (int) $part_id;
}

function kosm_front_page_add_dean() {
	$existing = get_posts(
		array(
			'post_type'   => 'wp_template',
			'post_status' => 'any',
			'name'        => 'front-page',
			'numberposts' => 1,
		)
	);

	if ( ! $existing ) {
		return;
	}

	$template = $existing[0];
	$content  = (string) $template->post_content;

	if ( false !== strpos( $content, '"home-dean"' ) || false === strpos( $content, '"home-hero"' ) ) {
		return;
	}

	$updated = preg_replace(
		'/(<!-- wp:template-part \{"slug":"home-hero"[^}]*\} \/-->)/',
		'$1' . "\n\n" . '<!-- wp:template-part {"slug":"home-dean","theme":"' . get_stylesheet() . '"} /-->',
		$content,
		1
	);

	if ( ! $updated || $updated === $content ) {
		return;
	}

	wp_update_post(
		array(
			'ID'           => $template->ID,
			'post_content' => $updated,
		)
	);

	kosmiteia_demo_log( '    Η ενότητα «Μήνυμα Κοσμήτορα» προστέθηκε στο αποθηκευμένο πρότυπο της αρχικής.' );
}

function kosm_navigation( $slug, $title, $links ) {
	$blocks = '';

	foreach ( $links as $link ) {
		$blocks .= kosm_navigation_item( $link );
	}

	$existing = get_posts(
		array(
			'post_type'   => 'wp_navigation',
			'post_status' => 'any',
			'name'        => $slug,
			'numberposts' => 1,
		)
	);

	if ( $existing ) {
		kosmiteia_demo_log( sprintf( '    Το μενού «%s» υπάρχει - παραλείπεται.', $slug ) );

		return (int) $existing[0]->ID;
	}

	$data = array(
		'post_type'    => 'wp_navigation',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $blocks,
	);

	if ( $existing ) {
		$data['ID'] = $existing[0]->ID;
		wp_update_post( $data );
		return (int) $existing[0]->ID;
	}

	$menu_id = wp_insert_post( $data, true );

	return is_wp_error( $menu_id ) ? 0 : (int) $menu_id;
}

function kosm_navigation_item( $link ) {
	$children = isset( $link['children'] ) ? (array) $link['children'] : array();

	if ( ! $children ) {
		return sprintf(
			'<!-- wp:navigation-link {"label":"%s","url":"%s","kind":"custom","isTopLevelLink":true} /-->' . "\n",
			esc_attr( $link['label'] ),
			esc_url( $link['url'] )
		);
	}

	$html = sprintf(
		'<!-- wp:navigation-submenu {"label":"%s","url":"%s","kind":"custom","isTopLevelItem":true} -->' . "\n",
		esc_attr( $link['label'] ),
		esc_url( $link['url'] )
	);

	foreach ( $children as $child ) {
		$html .= sprintf(
			'<!-- wp:navigation-link {"label":"%s","url":"%s","kind":"custom"} /-->' . "\n",
			esc_attr( $child['label'] ),
			esc_url( $child['url'] )
		);
	}

	return $html . '<!-- /wp:navigation-submenu -->' . "\n";
}

function kosm_pattern( $file, $locale = null ) {
	$path = get_stylesheet_directory() . '/patterns/' . $file;

	if ( ! file_exists( $path ) ) {
		kosmiteia_demo_warn( "Λείπει το pattern $file" );
		return '';
	}

	$switched = false;

	if ( $locale && get_locale() !== $locale ) {
		$switched = switch_to_locale( $locale );
	}

	ob_start();
	include $path;
	$html = ob_get_clean();

	if ( $switched ) {
		restore_previous_locale();
	}

	return trim( $html );
}

function kosm_hero_with_images( $markup, $image_ids, $dim = 60 ) {
	$dim = max( 10, min( 100, 10 * (int) round( $dim / 10 ) ) );

	if ( 50 === $dim ) {
		$dim = 60;
	}

	$index = 0;

	$markup = preg_replace_callback(
		'/<!-- wp:cover \{"overlayColor":"([a-z0-9-]+)","dimRatio":100,/',
		function ( $matches ) use ( $image_ids, $dim, &$index ) {
			$id  = isset( $image_ids[ $index ] ) ? $image_ids[ $index ] : 0;
			$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
			++$index;

			if ( ! $url ) {
				return $matches[0];
			}

			return sprintf(
				'<!-- wp:cover {"url":"%s","id":%d,"alt":"","overlayColor":"%s","dimRatio":%d,',
				esc_url( $url ),
				$id,
				$matches[1],
				$dim
			);
		},
		$markup
	);

	$index  = 0;
	$markup = preg_replace_callback(
		'/<span aria-hidden="true" class="wp-block-cover__background has-([a-z0-9-]+)-background-color has-background-dim-100 has-background-dim"><\/span>/',
		function ( $matches ) use ( $image_ids, $dim, &$index ) {
			$id  = isset( $image_ids[ $index ] ) ? $image_ids[ $index ] : 0;
			$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
			++$index;

			if ( ! $url ) {
				return $matches[0];
			}

			return sprintf(
				'<span aria-hidden="true" class="wp-block-cover__background has-%1$s-background-color has-background-dim-%4$d has-background-dim"></span><img class="wp-block-cover__image-background wp-image-%2$d" alt="" src="%3$s" data-object-fit="cover"/>',
				$matches[1],
				$id,
				esc_url( $url ),
				$dim
			);
		},
		$markup
	);

	return $markup;
}

function kosm_navigation_ref( $markup, $menu_id ) {
	if ( ! $menu_id ) {
		return $markup;
	}

	return preg_replace(
		'/<!-- wp:navigation \{"overlayMenu"/',
		'<!-- wp:navigation {"ref":' . (int) $menu_id . ',"overlayMenu"',
		$markup,
		1
	);
}

function kosm_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
}

function kosm_h( $text, $level = 2 ) {
	return sprintf(
		"<!-- wp:heading {\"level\":%1\$d} -->\n<h%1\$d class=\"wp-block-heading\">%2\$s</h%1\$d>\n<!-- /wp:heading -->\n\n",
		$level,
		$text
	);
}

function kosm_map( $lat, $lng, $title, $address, $zoom = 16 ) {
	$attrs = wp_json_encode(
		array(
			'lat'           => $lat,
			'lng'           => $lng,
			'zoom'          => $zoom,
			'height'        => 420,
			'markerTitle'   => $title,
			'markerAddress' => $address,
		),
		JSON_UNESCAPED_UNICODE
	);

	return "<!-- wp:kosmiteia/map " . $attrs . " /-->

";
}

function kosm_list( $items ) {
	$html = "<!-- wp:list -->\n<ul class=\"wp-block-list\">";

	foreach ( $items as $item ) {
		$html .= "<!-- wp:list-item -->\n<li>" . $item . "</li>\n<!-- /wp:list-item -->\n";
	}

	return $html . "</ul>\n<!-- /wp:list -->\n\n";
}

function kosmiteia_install_demo_content( $force = false ) {
	if ( get_option( 'kosmiteia_demo_version' ) === KOSM_DEMO_VERSION && ! $force ) {
		kosmiteia_demo_log( __( 'Το αρχικό περιεχόμενο υπάρχει ήδη - δεν έγινε καμία αλλαγή.', 'kosmiteia' ) );

		return kosmiteia_demo_log();
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	if ( ! is_user_logged_in() ) {
		wp_set_current_user( 1 );
	}

	update_option( 'blogname', 'Κοσμητεία Σχολής Επιστημών Υγείας' );
	update_option( 'blogdescription', 'Δημοκρίτειο Πανεπιστήμιο Θράκης' );
	update_option( 'timezone_string', 'Europe/Athens' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'posts_per_page', 10 );
	update_option( 'show_on_front', 'posts' );

	update_option(
		KOSMITEIA_SETTINGS_OPTION,
		array_merge(
			kosmiteia_settings_defaults(),
			get_option( KOSMITEIA_SETTINGS_OPTION, array() ),
			array(
				'institution'     => 'Δημοκρίτειο Πανεπιστήμιο Θράκης',
				'dean_name'       => 'Θεόδωρος Κωνσταντινίδης',
				'dean_title'      => 'Κοσμήτορας, Καθηγητής Ιατρικής',
				'floating_enable' => 1,
				'floating_label'  => 'Μήνυμα της Κοσμητείας',
				'floating_title'  => 'Καλώς ήρθατε στην Κοσμητεία',
				'floating_text'   => '<p>Η Κοσμητεία της Σχολής Επιστημών Υγείας στηρίζει τους φοιτητές και τα μέλη της ακαδημαϊκής κοινότητας. Για κάθε ερώτημα ή αίτημα, η Γραμματεία είναι στη διάθεσή σας.</p>',
				'contact_address' => 'Πανεπιστημιούπολη Αλεξανδρούπολης, Δραγάνα, Τ.Κ. 68100',
				'contact_phone'   => '25510 30953',
				'contact_email'   => 'secr@health.duth.gr',
				'contact_hours'   => 'Δευτέρα έως Παρασκευή, 09:00-14:00',
				'map_lat'         => 40.8640,
				'map_lng'         => 25.8040,
				'map_zoom'        => 16,
			)
		)
	);

	kosmiteia_demo_log( '  Ρυθμίσεις ιστότοπου: ok' );

	$images = array(
		'hero1'    => kosm_image( 'hero-1.png', 'Πανεπιστημιούπολη', 'Άποψη της πανεπιστημιούπολης' ),
		'hero2'    => kosm_image( 'hero-2.png', 'Ερευνητικά εργαστήρια', 'Ερευνητικά εργαστήρια' ),
		'hero3'    => kosm_image( 'hero-3.png', 'Ακαδημαϊκή κοινότητα', 'Ακαδημαϊκή κοινότητα' ),
		'school1'  => kosm_image( 'school-1.png', 'Τμήμα Ιατρικής', 'Κτίριο του Τμήματος Ιατρικής' ),
		'school2'  => kosm_image( 'school-2.png', 'Τμήμα Μοριακής Βιολογίας και Γενετικής', 'Κτίριο του Τμήματος Μοριακής Βιολογίας και Γενετικής' ),
		'school3'  => kosm_image( 'school-3.png', 'Τμήμα Νοσηλευτικής', 'Κτίριο του Τμήματος Νοσηλευτικής' ),
		'program1' => kosm_image( 'program-1.png', 'Επιστήμη Δεδομένων', 'Εργαστήριο επιστήμης δεδομένων' ),
		'program2' => kosm_image( 'program-2.png', 'Ψηφιακός Πολιτισμός', 'Ψηφιακός πολιτισμός' ),
		'program3' => kosm_image( 'program-3.png', 'Βιώσιμος Σχεδιασμός', 'Βιώσιμος σχεδιασμός' ),
		'program4' => kosm_image( 'program-4.png', 'Βιοϊατρική Μηχανική', 'Εργαστήριο βιοϊατρικής μηχανικής' ),
		'ann1'     => kosm_image( 'announcement-1.png', 'Προκήρυξη', 'Προκήρυξη μεταπτυχιακών σπουδών' ),
		'ann2'     => kosm_image( 'announcement-2.png', 'Ορκωμοσία', 'Τελετή ορκωμοσίας' ),
		'dean'     => kosm_image( 'dean.png', 'Ο Κοσμήτορας', 'Φωτογραφία του Κοσμήτορα' ),
		'logo'     => kosm_image( 'logo.png', 'Λογότυπο Κοσμητείας', 'Λογότυπο Κοσμητείας Σχολών' ),
	);

	$current_logo = (int) get_theme_mod( 'custom_logo' );

	if ( $images['logo'] && ( ! $current_logo || ! wp_attachment_is_image( $current_logo ) ) ) {
		set_theme_mod( 'custom_logo', $images['logo'] );
		update_option( 'site_logo', $images['logo'] );
	} elseif ( $current_logo ) {
		kosmiteia_demo_log( '  Λογότυπο: διατηρήθηκε το υπάρχον (δεν αντικαταστάθηκε).' );
	}

	kosmiteia_demo_log( '  Εικόνες: ' . count( array_filter( $images ) ) );

	$schools = array(
		array(
			'slug'        => 'tmima-iatrikis',
			'title'       => 'Τμήμα Ιατρικής',
			'excerpt'     => 'Ιδρύθηκε το 1977 και λειτουργεί από το ακαδημαϊκό έτος 1984-1985 στην Αλεξανδρούπολη, με κλινικές και εργαστήρια στο Πανεπιστημιακό Γενικό Νοσοκομείο Έβρου.',
			'image'       => $images['school1'],
			'departments' => array(
				'Προπτυχιακό Πρόγραμμα Σπουδών Ιατρικής, εξαετούς φοίτησης',
				'Προγράμματα Μεταπτυχιακών Σπουδών και εκπόνηση διδακτορικών διατριβών',
				'Κλινικές και εργαστήρια στο Πανεπιστημιακό Γενικό Νοσοκομείο Έβρου',
			),
			'meta'        => array(
				'kosm_dean'     => 'Πρόεδρος: Καθηγητής Κωνσταντίνος Βαδικόλιας',
				'kosm_phone'    => '25510 30953',
				'kosm_email'    => 'secr@health.duth.gr',
				'kosm_address'  => 'Πανεπιστημιούπολη Αλεξανδρούπολης, Δραγάνα, Τ.Κ. 68100',
				'kosm_site_url' => 'https://www.med.duth.gr/',
			),
			'intro'       => 'Το Τμήμα Ιατρικής συνδυάζει την προπτυχιακή εκπαίδευση με τη μεταπτυχιακή εξειδίκευση και τη διεθνή ερευνητική παρουσία, σε στενή σύνδεση με το Πανεπιστημιακό Γενικό Νοσοκομείο Έβρου.',
		),
		array(
			'slug'        => 'tmima-moriakis-viologias-genetikis',
			'title'       => 'Τμήμα Μοριακής Βιολογίας και Γενετικής',
			'excerpt'     => 'Ιδρύθηκε το 1999 και είναι το μοναδικό Τμήμα του είδους του στην Ελλάδα. Έγινε αυτοδύναμο το 2012 και σήμερα αριθμεί 21 μέλη ΔΕΠ.',
			'image'       => $images['school2'],
			'departments' => array(
				'Προπτυχιακό Πρόγραμμα Σπουδών στη Μοριακή Βιολογία και Γενετική',
				'Ερευνητικά εργαστήρια μοριακής βιολογίας, γενετικής και βιοπληροφορικής',
				'Συμμετοχή σε Π.Μ.Σ. της Σχολής και σε διεθνή ερευνητικά δίκτυα',
			),
			'meta'        => array(
				'kosm_dean'     => 'Πρόεδρος: Αναπληρωτής Καθηγητής Νικόλαος Γλυκός',
				'kosm_phone'    => '25510 30953',
				'kosm_email'    => 'secr@health.duth.gr',
				'kosm_address'  => 'Πανεπιστημιούπολη Αλεξανδρούπολης, Δραγάνα, Τ.Κ. 68100',
				'kosm_site_url' => 'https://mbg.duth.gr/',
			),
			'intro'       => 'Το Τμήμα Μοριακής Βιολογίας και Γενετικής καλύπτει ένα γνωστικό αντικείμενο αιχμής, με ισχυρή εργαστηριακή υποδομή και συμμετοχή σε εθνικά και ευρωπαϊκά ερευνητικά προγράμματα.',
		),
		array(
			'slug'        => 'tmima-nosileftikis',
			'title'       => 'Τμήμα Νοσηλευτικής',
			'excerpt'     => 'Το τρίτο Τμήμα της Σχολής Επιστημών Υγείας, με έμφαση στην κλινική άσκηση και στη φροντίδα υγείας στην Περιφέρεια Ανατολικής Μακεδονίας και Θράκης.',
			'image'       => $images['school3'],
			'departments' => array(
				'Προπτυχιακό Πρόγραμμα Σπουδών Νοσηλευτικής',
				'Κλινική άσκηση σε νοσοκομεία και δομές υγείας της Περιφέρειας',
				'Συμμετοχή στα διατμηματικά Π.Μ.Σ. της Σχολής',
			),
			'meta'        => array(
				'kosm_dean'     => 'Πρόεδρος: Καθηγητής Νικόλαος Πολύζος',
				'kosm_phone'    => '25510 30953',
				'kosm_email'    => 'secr@health.duth.gr',
				'kosm_address'  => 'Πανεπιστημιούπολη Αλεξανδρούπολης, Δραγάνα, Τ.Κ. 68100',
				'kosm_site_url' => 'https://health.duth.gr/',
			),
			'intro'       => 'Το Τμήμα Νοσηλευτικής εκπαιδεύει νοσηλευτές και νοσηλεύτριες με σύγχρονο πρόγραμμα σπουδών και εκτεταμένη κλινική άσκηση σε συνεργασία με τις δομές υγείας της περιοχής.',
		),
	);

	$school_ids = array();

	foreach ( $schools as $school ) {
		$content  = kosm_p( $school['intro'] );
		$content .= kosm_h( 'Σπουδές και δομές', 2 );
		$content .= kosm_list( $school['departments'] );
		$content .= kosm_h( 'Σπουδές και έρευνα', 2 );
		$content .= kosm_p( 'Το Τμήμα προσφέρει προπτυχιακές και μεταπτυχιακές σπουδές, εκπονεί ερευνητικά έργα με εθνική και ευρωπαϊκή χρηματοδότηση και συμμετέχει σε προγράμματα κινητικότητας φοιτητών.' );

		$post_id = kosm_post(
			'kosm_school',
			$school['slug'],
			array(
				'post_title'   => $school['title'],
				'post_excerpt' => $school['excerpt'],
				'post_content' => $content,
			)
		);

		if ( ! $post_id ) {
			continue;
		}

		$school_ids[ $school['slug'] ] = $post_id;

		if ( $school['image'] && ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, $school['image'] );
		}

		foreach ( $school['meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		wp_set_object_terms( $post_id, array( $school['title'] ), 'kosm_faculty' );
	}

	kosmiteia_demo_log( '  Τμήματα: ' . count( $school_ids ) );

	$announcements = array(
		array(
			'slug'     => 'diapistotiki-praxi-eklogis-edip-etep',
			'title'    => 'Διαπιστωτική Πράξη Εκλογής Εκπροσώπων Ε.ΔΙ.Π. και Ε.Τ.Ε.Π. στην Κοσμητεία',
			'excerpt'  => 'Διαπιστωτική πράξη για την εκλογή των εκπροσώπων των μελών Ε.ΔΙ.Π. και Ε.Τ.Ε.Π. στην Κοσμητεία της Σχολής Επιστημών Υγείας.',
			'category' => 'Εκλογές',
			'faculty'  => 'Σχολή Επιστημών Υγείας',
			'date'     => '2026-07-03',
			'image'    => $images['ann1'],
			'meta'     => array(),
			'body'     => 'Εκδόθηκε η διαπιστωτική πράξη εκλογής των εκπροσώπων των μελών Ε.ΔΙ.Π. και Ε.Τ.Ε.Π. στην Κοσμητεία της Σχολής Επιστημών Υγείας του Δ.Π.Θ., ύστερα από την ολοκλήρωση της εκλογικής διαδικασίας.',
		),
		array(
			'slug'     => 'eforeftiki-epitropi-etep',
			'title'    => 'Εφορευτική Επιτροπή για την ανάδειξη εκπροσώπων των μελών Ε.Τ.Ε.Π.',
			'excerpt'  => 'Ορισμός της εφορευτικής επιτροπής για την ανάδειξη εκπροσώπων των μελών Ε.Τ.Ε.Π. στην Κοσμητεία.',
			'category' => 'Εκλογές',
			'faculty'  => 'Σχολή Επιστημών Υγείας',
			'date'     => '2026-06-15',
			'image'    => 0,
			'meta'     => array(),
			'body'     => 'Ορίστηκε η εφορευτική επιτροπή που θα διενεργήσει την εκλογική διαδικασία για την ανάδειξη εκπροσώπων των μελών Ε.Τ.Ε.Π. στην Κοσμητεία της Σχολής.',
		),
		array(
			'slug'     => 'sygkrotisi-organon-eklogon',
			'title'    => 'Συγκρότηση οργάνων διενέργειας εκλογών',
			'excerpt'  => 'Συγκρότηση των οργάνων που είναι αρμόδια για τη διενέργεια των εκλογών εκπροσώπων στα συλλογικά όργανα της Σχολής.',
			'category' => 'Εκλογές',
			'faculty'  => 'Σχολή Επιστημών Υγείας',
			'date'     => '2026-06-15',
			'image'    => 0,
			'meta'     => array(),
			'body'     => 'Συγκροτήθηκαν τα όργανα διενέργειας εκλογών για την ανάδειξη εκπροσώπων στα συλλογικά όργανα της Σχολής Επιστημών Υγείας, σύμφωνα με την κείμενη νομοθεσία.',
		),
		array(
			'slug'     => 'prokiryxi-eklogon-2026-2027',
			'title'    => 'Προκήρυξη διενέργειας εκλογών για το ακαδημαϊκό έτος 2026-2027',
			'excerpt'  => 'Προκήρυξη εκλογών για την ανάδειξη εκπροσώπων στα συλλογικά όργανα της Σχολής για το ακαδημαϊκό έτος 2026-2027.',
			'category' => 'Προκηρύξεις',
			'faculty'  => 'Σχολή Επιστημών Υγείας',
			'date'     => '2026-04-28',
			'image'    => $images['ann2'],
			'meta'     => array( 'kosm_deadline' => 'Υποβολή υποψηφιοτήτων σύμφωνα με το χρονοδιάγραμμα της προκήρυξης' ),
			'body'     => 'Η Κοσμητεία προκηρύσσει τη διενέργεια εκλογών για την ανάδειξη εκπροσώπων στα συλλογικά όργανα της Σχολής για το ακαδημαϊκό έτος 2026-2027. Οι ενδιαφερόμενοι υποβάλλουν υποψηφιότητα σύμφωνα με το χρονοδιάγραμμα της προκήρυξης.',
		),
		array(
			'slug'     => 'anasygkrotisi-kosmiteias-2025-2026',
			'title'    => 'Διαπιστωτική Πράξη Ανασυγκρότησης Κοσμητείας 2025-2026',
			'excerpt'  => 'Ανασυγκρότηση της Κοσμητείας της Σχολής Επιστημών Υγείας για το ακαδημαϊκό έτος 2025-2026.',
			'category' => 'Διοικητικά',
			'faculty'  => 'Σχολή Επιστημών Υγείας',
			'date'     => '2026-01-08',
			'image'    => 0,
			'meta'     => array(),
			'body'     => 'Με διαπιστωτική πράξη ανασυγκροτήθηκε η Κοσμητεία της Σχολής Επιστημών Υγείας για το ακαδημαϊκό έτος 2025-2026, με τη συμμετοχή του Κοσμήτορα, των Προέδρων των Τμημάτων και των εκπροσώπων των μελών Ε.ΔΙ.Π. και Ε.Τ.Ε.Π.',
		),
		array(
			'slug'     => 'diapistotiki-praxi-ekprosopou-foititon',
			'title'    => 'Διαπιστωτική Πράξη Εκλογής Εκπροσώπου Φοιτητών',
			'excerpt'  => 'Διαπιστωτική πράξη για την εκλογή εκπροσώπου των φοιτητών στα συλλογικά όργανα της Σχολής.',
			'category' => 'Εκλογές',
			'faculty'  => 'Σχολή Επιστημών Υγείας',
			'date'     => '2025-12-17',
			'image'    => 0,
			'meta'     => array(),
			'body'     => 'Εκδόθηκε η διαπιστωτική πράξη εκλογής εκπροσώπου των φοιτητών στα συλλογικά όργανα της Σχολής Επιστημών Υγείας.',
		),
	);

	$announcement_count = 0;

	foreach ( $announcements as $item ) {
		$content = kosm_p( $item['body'] );
		$content .= kosm_h( 'Πληροφορίες', 2 );
		$content .= kosm_p( 'Για διευκρινίσεις μπορείτε να επικοινωνείτε με τη Γραμματεία της Κοσμητείας, καθημερινά 09:00-14:00.' );

		$date = gmdate( 'Y-m-d H:i:s', strtotime( $item['date'] . ' 09:00:00' ) );

		$post_id = kosm_post(
			'kosm_announcement',
			$item['slug'],
			array(
				'post_title'    => $item['title'],
				'post_excerpt'  => $item['excerpt'],
				'post_content'  => $content,
				'post_date'     => get_date_from_gmt( $date ),
				'post_date_gmt' => $date,
			)
		);

		if ( ! $post_id ) {
			continue;
		}

		++$announcement_count;

		if ( $item['image'] && ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, $item['image'] );
		}

		foreach ( $item['meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		wp_set_object_terms( $post_id, array( $item['category'] ), 'kosm_ann_category' );
		wp_set_object_terms( $post_id, array( $item['faculty'] ), 'kosm_faculty' );
	}

	kosmiteia_demo_log( '  Ανακοινώσεις: ' . $announcement_count );

	$programs = array(
		array(
			'slug'    => 'pms-vioithiki',
			'title'   => 'Π.Μ.Σ. «Βιοηθική»',
			'excerpt' => 'Μεταπτυχιακό πρόγραμμα του Τμήματος Ιατρικής στη Βιοηθική.',
			'type'    => 'Π.Μ.Σ.',
			'faculty' => 'Τμήμα Ιατρικής',
			'image'   => $images['program1'],
			'meta'    => array(
				'kosm_apply_url' => 'https://www.med.duth.gr/',
			),
		),
		array(
			'slug'    => 'pms-iatriki-tou-ypnou',
			'title'   => 'Π.Μ.Σ. «Ιατρική του Ύπνου»',
			'excerpt' => 'Μεταπτυχιακό πρόγραμμα του Τμήματος Ιατρικής με αντικείμενο τη διαγνωστική και θεραπευτική προσέγγιση των διαταραχών του ύπνου.',
			'type'    => 'Π.Μ.Σ.',
			'faculty' => 'Τμήμα Ιατρικής',
			'image'   => $images['program2'],
			'meta'    => array(
				'kosm_apply_url' => 'https://www.med.duth.gr/',
			),
		),
		array(
			'slug'    => 'pms-metafrastiki-erevna-vioiatriki',
			'title'   => 'Π.Μ.Σ. «Μεταφραστική Έρευνα στη Βιοϊατρική»',
			'excerpt' => 'Μεταπτυχιακό πρόγραμμα του Τμήματος Μοριακής Βιολογίας και Γενετικής, με έμφαση στη μεταφορά της βασικής έρευνας στην κλινική πράξη.',
			'type'    => 'Π.Μ.Σ.',
			'faculty' => 'Τμήμα Μοριακής Βιολογίας και Γενετικής',
			'image'   => $images['program3'],
			'meta'    => array(
				'kosm_apply_url' => 'https://mbg.duth.gr/',
			),
		),
		array(
			'slug'    => 'pms-didaktiki-ton-vioepistimon',
			'title'   => 'Π.Μ.Σ. «Η Διδακτική των Βιοεπιστημών»',
			'excerpt' => 'Μεταπτυχιακό πρόγραμμα του Τμήματος Μοριακής Βιολογίας και Γενετικής για τη διδασκαλία των βιοεπιστημών.',
			'type'    => 'Π.Μ.Σ.',
			'faculty' => 'Τμήμα Μοριακής Βιολογίας και Γενετικής',
			'image'   => $images['program4'],
			'meta'    => array(
				'kosm_apply_url' => 'https://mbg.duth.gr/',
			),
		),
		array(
			'slug'    => 'diatmimatiko-pms-iatriki-fysiki',
			'title'   => 'Διατμηματικό Π.Μ.Σ. «Ιατρική Φυσική - Ακτινοφυσική»',
			'excerpt' => 'Διατμηματικό πρόγραμμα μεταπτυχιακών σπουδών στην ιατρική φυσική και την ακτινοφυσική.',
			'type'    => 'Διατμηματικό',
			'faculty' => 'Σχολή Επιστημών Υγείας',
			'image'   => 0,
			'meta'    => array(
				'kosm_apply_url' => 'https://health.duth.gr/',
			),
		),
		array(
			'slug'    => 'diatmimatiko-pms-ygieini-asfaleia-ergasias',
			'title'   => 'Διατμηματικό Π.Μ.Σ. «Υγιεινή και Ασφάλεια Εργασίας»',
			'excerpt' => 'Διατμηματικό πρόγραμμα μεταπτυχιακών σπουδών στην υγιεινή και ασφάλεια της εργασίας.',
			'type'    => 'Διατμηματικό',
			'faculty' => 'Σχολή Επιστημών Υγείας',
			'image'   => 0,
			'meta'    => array(
				'kosm_apply_url' => 'https://health.duth.gr/',
			),
		),
	);

	$program_count = 0;

	foreach ( $programs as $program ) {
		$content  = kosm_p( $program['excerpt'] );
		$content .= kosm_h( 'Δομή του προγράμματος', 2 );
		$content .= kosm_list(
			array(
				'Υποχρεωτικά μαθήματα κορμού στο α΄ εξάμηνο',
				'Μαθήματα επιλογής και εργαστήρια στο β΄ εξάμηνο',
				'Εκπόνηση διπλωματικής εργασίας',
			)
		);
		$content .= kosm_h( 'Προϋποθέσεις εισαγωγής', 2 );
		$content .= kosm_p( 'Πτυχίο ΑΕΙ συναφούς γνωστικού αντικειμένου, καλή γνώση αγγλικής γλώσσας και συνέντευξη με την επιτροπή επιλογής.' );

		$post_id = kosm_post(
			'kosm_program',
			$program['slug'],
			array(
				'post_title'   => $program['title'],
				'post_excerpt' => $program['excerpt'],
				'post_content' => $content,
			)
		);

		if ( ! $post_id ) {
			continue;
		}

		++$program_count;

		if ( $program['image'] && ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, $program['image'] );
		}

		foreach ( $program['meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		wp_set_object_terms( $post_id, array( $program['type'] ), 'kosm_program_type' );
		wp_set_object_terms( $post_id, array( $program['faculty'] ), 'kosm_faculty' );
	}

	kosmiteia_demo_log( '  Μεταπτυχιακά: ' . $program_count );

	$about  = kosm_p( 'Η Σχολή Επιστημών Υγείας (Σ.Ε.Υ.) ιδρύθηκε στο Δημοκρίτειο Πανεπιστήμιο Θράκης τον Ιούνιο του 2013 και απαρτίζεται από το Τμήμα Ιατρικής, το Τμήμα Μοριακής Βιολογίας και Γενετικής και το Τμήμα Νοσηλευτικής.' );
	$about .= kosm_h( 'Ιστορικό', 2 );
	$about .= kosm_list(
		array(
			'Τμήμα Ιατρικής: ιδρύθηκε το 1977 και λειτουργεί από το ακαδημαϊκό έτος 1984-1985 στην Αλεξανδρούπολη',
			'Τμήμα Μοριακής Βιολογίας και Γενετικής: ιδρύθηκε το 1999, έγινε αυτοδύναμο το 2012 και αριθμεί 21 μέλη ΔΕΠ',
			'Τμήμα Νοσηλευτικής: το τρίτο Τμήμα της Σχολής, με έμφαση στην κλινική άσκηση',
		)
	);
	$about .= kosm_h( 'Στόχοι της Κοσμητείας', 2 );
	$about .= kosm_list(
		array(
			'Συνεχής αναβάθμιση των προγραμμάτων σπουδών',
			'Ανάπτυξη εθνικών και διεθνών συνεργασιών',
			'Εξασφάλιση πόρων για υποδομές και εξοπλισμό',
			'Προσέλκυση νέων επιστημόνων',
			'Σύνδεση εκπαίδευσης και έρευνας με την παραγωγή και την επιχειρηματικότητα',
			'Αξιοποίηση της τεχνητής νοημοσύνης στη βιοϊατρική έρευνα και στην πανεπιστημιακή εκπαίδευση',
		)
	);
	$about .= kosm_h( 'Αρμοδιότητες της Κοσμητείας', 2 );
	$about .= kosm_list(
		array(
			'Ασκεί τη γενική εποπτεία της λειτουργίας της Σχολής και των Τμημάτων της',
			'Χαράσσει τη γενική εκπαιδευτική και ερευνητική πολιτική της Σχολής',
			'Λαμβάνει μέτρα για την ενίσχυση της εξωστρέφειας',
			'Εγκρίνει, μετά από εισήγηση των Συνελεύσεων των Τμημάτων, τον ετήσιο προγραμματισμό προσλήψεων',
			'Εισηγείται προς τη Σύγκλητο για διοικητικό προσωπικό και ανάγκες χρηματοδότησης',
		)
	);
	$about .= kosm_h( 'Αρμοδιότητες του Κοσμήτορα', 2 );
	$about .= kosm_list(
		array(
			'Προΐσταται της Σχολής και εποπτεύει την εύρυθμη λειτουργία της',
			'Συμμετέχει στη Σύγκλητο και εισηγείται θέματα της Σχολής',
			'Συγκαλεί την Κοσμητεία και προεδρεύει των εργασιών της',
			'Μεριμνά για την εφαρμογή των αποφάσεων και την τήρηση της νομοθεσίας',
		)
	);
	$about .= kosm_h( 'Συγκρότηση Κοσμητείας', 2 );
	$about .= kosm_list(
		array(
			'Κοσμήτορας: Καθηγητής Θεόδωρος Κωνσταντινίδης',
			'Πρόεδρος Τμήματος Ιατρικής: Καθηγητής Κωνσταντίνος Βαδικόλιας',
			'Πρόεδρος Τμήματος Μοριακής Βιολογίας και Γενετικής: Αναπληρωτής Καθηγητής Νικόλαος Γλυκός',
			'Πρόεδρος Τμήματος Νοσηλευτικής: Καθηγητής Νικόλαος Πολύζος',
			'Εκπρόσωπος Ε.ΔΙ.Π.: Αθανάσιος Τσελεμπόνης',
			'Εκπρόσωπος Ε.Τ.Ε.Π.: Γεώργιος Ταρσούδης',
		)
	);

	$about_id = kosm_post(
		'page',
		'i-kosmiteia',
		array(
			'post_title'   => 'Η Κοσμητεία',
			'post_content' => $about,
			'post_excerpt' => 'Ιστορικό, στόχοι, αρμοδιότητες και συγκρότηση της Κοσμητείας της Σχολής Επιστημών Υγείας.',
		)
	);

	$dean  = kosm_p( 'Η Σχολή Επιστημών Υγείας του Δημοκριτείου Πανεπιστημίου Θράκης έχει ως στόχο την προαγωγή της εκπαίδευσης και της έρευνας στην υγεία, την αναβάθμιση της πρόληψης και της θεραπείας των ανθρώπινων νόσων, καθώς και την αγωγή και προαγωγή της υγείας και της ευεξίας.' );
	$dean .= kosm_p( 'Η Κοσμητεία λειτουργεί ως συλλογικό όργανο που συντονίζει το έργο των Τμημάτων της Σχολής, με στόχο την ποιοτική αναβάθμιση της εκπαίδευσης σε προπτυχιακό και μεταπτυχιακό επίπεδο, τη στήριξη της επιστημονικής έρευνας και της ανάπτυξης τεχνολογίας, και τη σύνδεση της Σχολής με την κοινωνία.' );
	$dean .= kosm_h( 'Ο Κοσμήτορας', 2 );
	$dean .= kosm_p( 'Θ. Κ. Κωνσταντινίδης, Καθηγητής Ιατρικής, ειδικός Ιατρικής της Εργασίας και του Περιβάλλοντος.' );

	$dean_id = kosm_post(
		'page',
		'minyma-kosmitora',
		array(
			'post_title'   => 'Μήνυμα Κοσμήτορα',
			'post_content' => $dean,
			'post_excerpt' => 'Χαιρετισμός του Κοσμήτορα της Σχολής Επιστημών Υγείας του Δ.Π.Θ.',
		)
	);

	$access  = kosm_p( 'Η Κοσμητεία στεγάζεται στην Πανεπιστημιούπολη, στο Κτίριο Διοίκησης, στον 1ο όροφο, στην περιοχή Δραγάνα (6ο χλμ. Αλεξανδρούπολης - Μάκρης), στη δυτική είσοδο της Αλεξανδρούπολης.' );
	$access .= kosm_h( 'Μέσα μεταφοράς', 2 );
	$access .= kosm_list(
		array(
			'Αστικές γραμμές λεωφορείων προς Δημοκρίτειο Πανεπιστήμιο Θράκης, Δραγάνα και Πανεπιστημιακό Γενικό Νοσοκομείο Αλεξανδρούπολης',
			'Δωρεάν δρομολόγια του Πανεπιστημίου μεταξύ Αλεξανδρούπολης και Δραγάνας για τους φοιτητές με φοιτητικό πάσο, κατά τη διάρκεια των ακαδημαϊκών εξαμήνων',
		)
	);
	$access .= kosm_h( 'Χάρτης', 2 );
	$access .= kosm_map(
		(float) kosmiteia_option( 'map_lat', 40.8640 ),
		(float) kosmiteia_option( 'map_lng', 25.8040 ),
		'Πανεπιστημιούπολη Δραγάνας',
		kosmiteia_option( 'contact_address', 'Κτίριο Διοίκησης, 1ος όροφος - 6ο χλμ. Αλεξανδρούπολης - Μάκρης, Τ.Κ. 68100' ),
		(int) kosmiteia_option( 'map_zoom', 16 )
	);

	$access_id = kosm_post(
		'page',
		'prosvasi',
		array(
			'post_title'   => 'Πρόσβαση',
			'post_content' => $access,
			'post_excerpt' => 'Πώς θα φτάσετε στην Πανεπιστημιούπολη Δραγάνας και στο Κτίριο Διοίκησης.',
		)
	);

	$contact  = kosm_p( 'Γραμματεία Κοσμητείας Σχολής Επιστημών Υγείας, Πανεπιστημιούπολη Αλεξανδρούπολης, Κτίριο Διοίκησης, 1ος όροφος.' );
	$contact .= kosm_h( 'Στοιχεία επικοινωνίας', 2 );
	$contact .= kosm_list(
		array(
			'Διεύθυνση: 6ο χλμ. Αλεξανδρούπολης - Μάκρης, Δραγάνα, Τ.Κ. 68100, Αλεξανδρούπολη',
			'Τηλέφωνο Κοσμητείας: 25510 30953',
			'Γραμματεία: 25510 30974 (εσωτ. 77974)',
			'Γραφείο Κοσμήτορα: 25510 30521 / 77521',
			'Email: secr@health.duth.gr',
		)
	);
	$contact .= kosm_h( 'Πρόσωπα επικοινωνίας', 2 );
	$contact .= kosm_list(
		array(
			'Κοσμήτορας: Καθηγητής Θεόδωρος Κωνσταντινίδης - tconstan@med.duth.gr',
			'Γραμματέας Σχολής: Πασχάλης Τερζάκης - pterzaki@admin.duth.gr',
		)
	);
	$contact .= kosm_h( 'Πού θα μας βρείτε', 2 );
	$contact .= kosm_p( 'Ο χάρτης δείχνει την Πανεπιστημιούπολη Δραγάνας, όπου βρίσκεται το Κτίριο Διοίκησης της Σχολής.' );
	$contact .= kosm_map(
		(float) kosmiteia_option( 'map_lat', 40.8640 ),
		(float) kosmiteia_option( 'map_lng', 25.8040 ),
		get_bloginfo( 'name' ),
		kosmiteia_option( 'contact_address', '6ο χλμ. Αλεξανδρούπολης - Μάκρης, Δραγάνα, Τ.Κ. 68100' ),
		(int) kosmiteia_option( 'map_zoom', 16 )
	);

	$contact_id = kosm_post(
		'page',
		'epikoinonia',
		array(
			'post_title'   => 'Επικοινωνία',
			'post_content' => $contact,
			'post_excerpt' => 'Τηλέφωνα, email και διεύθυνση της Γραμματείας της Κοσμητείας.',
		)
	);

	foreach ( array(
		$about_id   => $images['hero3'],
		$dean_id    => $images['hero2'],
		$access_id  => $images['hero1'],
		$contact_id => $images['school1'],
	) as $page_id => $image_id ) {
		if ( $page_id && $image_id ) {
			if ( ! has_post_thumbnail( $page_id ) ) {
				set_post_thumbnail( $page_id, $image_id );
			}
		}
	}

	if ( $dean_id ) {
		kosm_setting( 'dean_page', $dean_id );
	}

	if ( $contact_id ) {
		kosm_setting( 'floating_link', get_permalink( $contact_id ) );
		kosm_setting( 'floating_link_label', 'Επικοινωνία' );
	}

	kosmiteia_demo_log( '  Σελίδες: 4' );

	$home       = home_url( '/' );
	$url_school = get_post_type_archive_link( 'kosm_school' );
	$url_ann    = get_post_type_archive_link( 'kosm_announcement' );
	$url_prog   = get_post_type_archive_link( 'kosm_program' );
	$url_dean   = $dean_id ? get_permalink( $dean_id ) : $home;
	$url_about  = $about_id ? get_permalink( $about_id ) : $home;

	$menu_el = kosm_navigation(
		'main',
		'Κύριο μενού',
		array(
			array( 'label' => 'Αρχική', 'url' => $home ),
			array(
				'label'    => 'Η Κοσμητεία',
				'url'      => $url_about,
				'children' => array(
					array( 'label' => 'Η Κοσμητεία', 'url' => $url_about ),
					array( 'label' => 'Μήνυμα Κοσμήτορα', 'url' => $url_dean ),
				),
			),
			array( 'label' => 'Τμήματα', 'url' => $url_school ),
			array( 'label' => 'Μεταπτυχιακά', 'url' => $url_prog ),
			array( 'label' => 'Ανακοινώσεις', 'url' => $url_ann ),
			array( 'label' => 'Πρόσβαση', 'url' => $access_id ? get_permalink( $access_id ) : $home ),
			array( 'label' => 'Επικοινωνία', 'url' => $contact_id ? get_permalink( $contact_id ) : $home ),
		)
	);

	$menu_en = kosm_navigation(
		'main-en',
		'Main menu (EN)',
		array(
			array( 'label' => 'Home', 'url' => add_query_arg( 'lang', 'en', $home ) ),
			array(
				'label'    => 'The Deanery',
				'url'      => add_query_arg( 'lang', 'en', $url_about ),
				'children' => array(
					array( 'label' => 'The Deanery', 'url' => add_query_arg( 'lang', 'en', $url_about ) ),
					array( 'label' => "Dean's message", 'url' => add_query_arg( 'lang', 'en', $url_dean ) ),
				),
			),
			array( 'label' => 'Departments', 'url' => add_query_arg( 'lang', 'en', $url_school ) ),
			array( 'label' => 'Postgraduate', 'url' => add_query_arg( 'lang', 'en', $url_prog ) ),
			array( 'label' => 'Announcements', 'url' => add_query_arg( 'lang', 'en', $url_ann ) ),
			array( 'label' => 'Access', 'url' => $access_id ? add_query_arg( 'lang', 'en', get_permalink( $access_id ) ) : $home ),
			array( 'label' => 'Contact', 'url' => $contact_id ? add_query_arg( 'lang', 'en', get_permalink( $contact_id ) ) : $home ),
		)
	);

	kosmiteia_demo_log( '  Μενού: 2 (main, main-en)' );

	$theme_dir = get_stylesheet_directory();

	$header_el = kosm_navigation_ref( file_get_contents( $theme_dir . '/parts/header.html' ), $menu_el );
	kosm_template_part( 'header', 'Κεφαλίδα (EL)', 'header', $header_el );

	$to_departments = static function ( $markup ) {
		return str_replace(
			array(
				'Κοσμητεία Σχολών',
				'Σπουδές, έρευνα και καινοτομία σε τρεις Σχολές. Ανακαλύψτε τα προγράμματα και την ακαδημαϊκή μας κοινότητα.',
				'Οι Σχολές μας',
				'Τρεις Σχολές με διακριτή ταυτότητα, κοινό στόχο την ποιοτική εκπαίδευση και την έρευνα.',
				'Δείτε τη Σχολή',
				'Σύντομο απόσπασμα από τον χαιρετισμό του Κοσμήτορα προς τη φοιτητική και την ακαδημαϊκή κοινότητα. Το πλήρες κείμενο βρίσκεται στη σελίδα «Μήνυμα Κοσμήτορα».',
				'Ονοματεπώνυμο Κοσμήτορα',
				'Πανεπιστημιούπολη, Κτίριο Διοίκησης',
				'Τ.Κ. 000 00, Πόλη',
				'+30 210 000 0000',
				'info@example.edu',
				'Παιδεία, έρευνα και κοινωνική προσφορά. Η Κοσμητεία συντονίζει τις Σχολές, τα προγράμματα σπουδών και την ακαδημαϊκή κοινότητα.',
			),
			array(
				'Κοσμητεία Σχολής Επιστημών Υγείας',
				'Η Κοσμητεία καλωσορίζει τους/τις φοιτητές/φοιτήτριες στην ιστοσελίδα μας.',
				'Τα Τμήματα της Σχολής',
				'Τρία Τμήματα με κοινό στόχο την εκπαίδευση, την έρευνα και τη φροντίδα υγείας.',
				'Δείτε το Τμήμα',
				'Η Σχολή Επιστημών Υγείας του Δ.Π.Θ. έχει ως στόχο την προαγωγή της εκπαίδευσης και της έρευνας στην υγεία, την πρόληψη και τη θεραπεία των νόσων και την προαγωγή της υγείας και της ευεξίας.',
				'Θεόδωρος Κωνσταντινίδης',
				'Πανεπιστημιούπολη Αλεξανδρούπολης, Κτίριο Διοίκησης',
				'6ο χλμ. Αλεξανδρούπολης - Μάκρης, Τ.Κ. 68100',
				'25510 30953',
				'secr@health.duth.gr',
				'Η Κοσμητεία της Σχολής Επιστημών Υγείας του Δ.Π.Θ. συντονίζει τα Τμήματα Ιατρικής, Μοριακής Βιολογίας και Γενετικής και Νοσηλευτικής.',
			),
			$markup
		);
	};

	$to_departments_en = static function ( $markup ) {
		return str_replace(
			array(
				'Our schools',
				'Three schools with a distinct identity and a shared commitment to quality education and research.',
				'Visit the school',
				'A short extract from the address of the Dean to students and to the academic community. The full text is on the page "Message from the Dean".',
				'Name of the Dean',
			),
			array(
				'The departments of the School',
				'Three departments with a shared commitment to education, research and health care.',
				'Visit the department',
				'The School of Health Sciences of the Democritus University of Thrace works to advance education and research in health, the prevention and treatment of disease, and the promotion of health and well-being.',
				'Theodoros Konstantinidis',
			),
			$markup
		);
	};

	$hero_el = kosm_hero_with_images(
		$to_departments( file_get_contents( $theme_dir . '/parts/home-hero.html' ) ),
		array( $images['hero1'], $images['hero2'], $images['hero3'] )
	);
	kosm_template_part( 'home-hero', 'Αρχική: Hero slider', 'uncategorized', $hero_el );

	kosm_template_part(
		'home-dean',
		'Αρχική: Μήνυμα Κοσμήτορα',
		'uncategorized',
		kosm_hero_with_images(
			$to_departments( file_get_contents( $theme_dir . '/parts/home-dean.html' ) ),
			array( $images['dean'] ),
			20
		)
	);

	kosm_template_part(
		'home-schools',
		'Αρχική: Σχολές',
		'uncategorized',
		$to_departments( file_get_contents( $theme_dir . '/parts/home-schools.html' ) )
	);

	kosm_template_part(
		'footer',
		'Υποσέλιδο (EL)',
		'footer',
		$to_departments( file_get_contents( $theme_dir . '/parts/footer.html' ) )
	);

	$header_en = kosm_navigation_ref( kosm_pattern( 'header-main.php', 'en_US' ), $menu_en );
	kosm_template_part( 'header-en', 'Header (EN)', 'header', $header_en );

	kosm_template_part( 'footer-en', 'Footer (EN)', 'footer', kosm_pattern( 'subfooter-columns.php', 'en_US' ) );

	$hero_en = kosm_hero_with_images(
		kosm_pattern( 'hero-slider.php', 'en_US' ),
		array( $images['hero1'], $images['hero2'], $images['hero3'] )
	);
	kosm_template_part( 'home-hero-en', 'Home: Hero slider (EN)', 'uncategorized', $hero_en );

	kosm_template_part(
		'home-dean-en',
		"Home: Dean's message (EN)",
		'uncategorized',
		kosm_hero_with_images( $to_departments_en( kosm_pattern( 'dean-message.php', 'en_US' ) ), array( $images['dean'] ), 20 )
	);

	kosm_template_part( 'home-schools-en', 'Home: Schools (EN)', 'uncategorized', kosm_pattern( 'schools-cards.php', 'en_US' ) );
	kosm_template_part( 'home-announcements-en', 'Home: Announcements (EN)', 'uncategorized', kosm_pattern( 'announcements-latest.php', 'en_US' ) );
	kosm_template_part( 'home-programs-en', 'Home: Programmes (EN)', 'uncategorized', kosm_pattern( 'programs-grid.php', 'en_US' ) );

	kosm_front_page_add_dean();

	kosmiteia_demo_log( '  Template parts: header, footer, home-hero, home-dean, home-schools + 7 αγγλικά' );

	flush_rewrite_rules( false );
	update_option( 'kosmiteia_demo_version', KOSM_DEMO_VERSION );

	kosmiteia_demo_success( 'Το δοκιμαστικό περιεχόμενο δημιουργήθηκε.' );

	return kosmiteia_demo_log();
}
