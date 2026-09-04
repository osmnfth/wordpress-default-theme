<?php
/**
 * Εισαγωγή ανακοινώσεων από τον παλιό ιστότοπο (RSS feed).
 *
 * Ο παλιός ιστότοπος (π.χ. health.duth.gr) είναι classic WordPress: οι
 * ανακοινώσεις είναι κανονικά posts (permalink /YYYY/MM/DD/slug/). Το REST API
 * συνήθως είναι κλειστό από το firewall, οπότε η πηγή είναι το RSS feed με
 * σελιδοποίηση:  https://.../feed/?paged=1 ... ?paged=N
 *
 * Κάθε <item> δίνει τίτλο, μόνιμο σύνδεσμο, ημερομηνία, κατηγορίες και
 * ολόκληρο το περιεχόμενο (content:encoded).
 *
 * Εκτέλεση:
 *   - Διαχείριση:  Κοσμητεία → Εργαλεία → «Εισαγωγή από παλιό ιστότοπο»
 *   - Γραμμή εντολών:
 *       wp kosmiteia import-announcements --dry-run
 *       wp kosmiteia import-announcements --faculty="Σχολή Επιστημών Υγείας"
 *
 * Η αντιστοίχιση με το παλιό post γίνεται με το meta «kosm_source_url», ώστε
 * η εισαγωγή να είναι idempotent: ξανατρέξιμο δεν δημιουργεί διπλότυπα.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Καταγραφή προόδου (WP-CLI ή σελίδα Εργαλείων).
 *
 * @param string $message Το μήνυμα.
 * @param string $type    log | warning | success.
 * @return array Όλα τα μηνύματα.
 */
function kosmiteia_import_log( $message = null, $type = 'log' ) {
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

/**
 * Προειδοποίηση.
 *
 * @param string $message Το μήνυμα.
 */
function kosmiteia_import_warn( $message ) {
	kosmiteia_import_log( $message, 'warning' );
}

/**
 * Οι προεπιλεγμένες παράμετροι της εισαγωγής.
 *
 * @return array
 */
function kosmiteia_import_defaults() {
	return array(
		'feed'    => 'https://health.duth.gr/feed/',
		'pages'   => 60,
		'limit'   => 0,
		'dry_run' => false,
		'media'   => true,
		'force'   => false,
		'status'  => 'publish',
		'cats'    => array(),
		'faculty' => '',
	);
}

/* =========================================================================
 * Βοηθητικές συναρτήσεις
 * ====================================================================== */

/**
 * Κατεβάζει μία σελίδα του feed και επιστρέφει τα items ως πίνακες.
 */
function kosm_feed_items( $feed_url, $page ) {
	$url = add_query_arg( 'paged', $page, $feed_url );

	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 45,
			'user-agent' => 'Kosmiteia importer (WordPress)',
		)
	);

	if ( is_wp_error( $response ) ) {
		kosmiteia_import_warn( sprintf( 'Σελίδα %d: %s', $page, $response->get_error_message() ) );
		return array();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );

	if ( 200 !== $code ) {
		kosmiteia_import_warn( sprintf( 'Σελίδα %d: HTTP %d', $page, $code ) );
		return array();
	}

	$previous = libxml_use_internal_errors( true );
	$xml      = simplexml_load_string( wp_remote_retrieve_body( $response ), 'SimpleXMLElement', LIBXML_NOCDATA );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( false === $xml || ! isset( $xml->channel->item ) ) {
		return array();
	}

	$items = array();

	foreach ( $xml->channel->item as $item ) {
		$content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
		$dc      = $item->children( 'http://purl.org/dc/elements/1.1/' );

		$categories = array();

		foreach ( $item->category as $category ) {
			$name = trim( (string) $category );

			if ( '' !== $name ) {
				$categories[] = $name;
			}
		}

		$items[] = array(
			'title'      => trim( html_entity_decode( (string) $item->title, ENT_QUOTES, 'UTF-8' ) ),
			'link'       => trim( (string) $item->link ),
			'date'       => trim( (string) $item->pubDate ),
			'author'     => isset( $dc->creator ) ? trim( (string) $dc->creator ) : '',
			'excerpt'    => trim( (string) $item->description ),
			'content'    => isset( $content->encoded ) ? (string) $content->encoded : '',
			'categories' => array_values( array_unique( $categories ) ),
		);
	}

	return $items;
}

/**
 * Το slug του παλιού permalink (/2025/10/08/slug/) σε μορφή για post_name.
 */
function kosm_slug_from_link( $link, $fallback ) {
	$path  = wp_parse_url( $link, PHP_URL_PATH );
	$parts = array_values( array_filter( explode( '/', (string) $path ) ) );
	$slug  = $parts ? rawurldecode( end( $parts ) ) : '';

	if ( '' === $slug ) {
		$slug = $fallback;
	}

	return sanitize_title( $slug );
}

/**
 * Βρίσκει το περιεχόμενο που προήλθε από το συγκεκριμένο παλιό URL.
 */
function kosm_existing_by_source( $source_url, $post_type = 'kosm_announcement' ) {
	$found = get_posts(
		array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_query'  => array(
				array(
					'key'   => 'kosm_source_url',
					'value' => $source_url,
				),
			),
		)
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Κωδικοποιεί το path ενός URL (τα PDF του παλιού site έχουν ελληνικά ονόματα).
 */
function kosm_encode_url( $url ) {
	$parts = wp_parse_url( $url );

	if ( empty( $parts['path'] ) || empty( $parts['host'] ) ) {
		return $url;
	}

	$path = implode(
		'/',
		array_map(
			function ( $segment ) {
				return rawurlencode( rawurldecode( $segment ) );
			},
			explode( '/', $parts['path'] )
		)
	);

	$rebuilt = ( empty( $parts['scheme'] ) ? 'https' : $parts['scheme'] ) . '://' . $parts['host'] . $path;

	if ( ! empty( $parts['query'] ) ) {
		$rebuilt .= '?' . $parts['query'];
	}

	return $rebuilt;
}

/**
 * Κατεβάζει ένα αρχείο του παλιού site στη Βιβλιοθήκη πολυμέσων και
 * επιστρέφει το attachment ID (0 σε αποτυχία). Αρχείο που έχει ήδη
 * εισαχθεί δεν ξανακατεβαίνει.
 */
function kosm_sideload( $url, $post_id = 0 ) {
	static $cache = array();

	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$existing = kosm_existing_by_source( $url, 'attachment' );

	if ( $existing ) {
		$cache[ $url ] = $existing;
		return $existing;
	}

	$tmp = download_url( kosm_encode_url( $url ), 90 );

	if ( is_wp_error( $tmp ) ) {
		kosmiteia_import_warn( sprintf( 'Δεν κατέβηκε: %s (%s)', $url, $tmp->get_error_message() ) );
		$cache[ $url ] = 0;
		return 0;
	}

	$name = rawurldecode( basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $name,
			'tmp_name' => $tmp,
		),
		$post_id
	);

	if ( is_wp_error( $attachment_id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}

		kosmiteia_import_warn( sprintf( 'Δεν εισήχθη: %s (%s)', $url, $attachment_id->get_error_message() ) );
		$cache[ $url ] = 0;
		return 0;
	}

	update_post_meta( $attachment_id, 'kosm_source_url', $url );

	$cache[ $url ] = (int) $attachment_id;

	return (int) $attachment_id;
}

/**
 * Το εσωτερικό HTML ενός κόμβου.
 */
function kosm_inner_html( DOMNode $node ) {
	$html = '';

	foreach ( $node->childNodes as $child ) {
		$html .= $node->ownerDocument->saveHTML( $child );
	}

	return trim( $html );
}

/**
 * Μπλοκ εικόνας, με το ID της νέας εικόνας όταν αυτή έχει εισαχθεί.
 */
function kosm_image_block( DOMElement $img, $images ) {
	$src = $img->getAttribute( 'src' );
	$alt = $img->getAttribute( 'alt' );

	if ( '' === $src ) {
		return '';
	}

	$id    = isset( $images[ $src ] ) ? (int) $images[ $src ] : 0;
	$class = $id ? ' class="wp-image-' . $id . '"' : '';
	$attrs = $id ? ' {"id":' . $id . ',"sizeSlug":"large"}' : '';

	return sprintf(
		"<!-- wp:image%1\$s -->\n<figure class=\"wp-block-image size-large\"><img src=\"%2\$s\" alt=\"%3\$s\"%4\$s/></figure>\n<!-- /wp:image -->\n\n",
		$attrs,
		esc_url( $src ),
		esc_attr( $alt ),
		$class
	);
}

/**
 * Διατρέχει τα παιδιά ενός κόμβου και τα μεταφράζει σε blocks.
 */
function kosm_nodes_to_blocks( DOMNode $parent, $images = array() ) {
	$blocks = '';

	foreach ( $parent->childNodes as $node ) {

		if ( XML_TEXT_NODE === $node->nodeType ) {
			$text = trim( $node->textContent );

			if ( '' !== $text ) {
				$blocks .= "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->\n\n";
			}

			continue;
		}

		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			continue;
		}

		$tag   = strtolower( $node->nodeName );
		$inner = kosm_inner_html( $node );

		switch ( $tag ) {

			case 'p':
				if ( $node->getElementsByTagName( 'img' )->length ) {
					$blocks .= kosm_nodes_to_blocks( $node, $images );
					break;
				}

				if ( '' === trim( wp_strip_all_tags( $inner ) ) ) {
					break;
				}

				$blocks .= "<!-- wp:paragraph -->\n<p>" . $inner . "</p>\n<!-- /wp:paragraph -->\n\n";
				break;

			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
				$level = max( 2, (int) substr( $tag, 1 ) );

				$blocks .= sprintf(
					"<!-- wp:heading {\"level\":%1\$d} -->\n<h%1\$d class=\"wp-block-heading\">%2\$s</h%1\$d>\n<!-- /wp:heading -->\n\n",
					$level,
					$inner
				);
				break;

			case 'ul':
			case 'ol':
				$items = '';

				foreach ( $node->getElementsByTagName( 'li' ) as $li ) {
					$items .= "<!-- wp:list-item -->\n<li>" . kosm_inner_html( $li ) . "</li>\n<!-- /wp:list-item -->\n";
				}

				if ( '' === $items ) {
					break;
				}

				$blocks .= sprintf(
					"<!-- wp:list%1\$s -->\n<%2\$s class=\"wp-block-list\">%3\$s</%2\$s>\n<!-- /wp:list -->\n\n",
					( 'ol' === $tag ) ? ' {"ordered":true}' : '',
					$tag,
					$items
				);
				break;

			case 'img':
				$blocks .= kosm_image_block( $node, $images );
				break;

			case 'figure':
			case 'div':
			case 'section':
			case 'article':
			case 'span':
				$blocks .= kosm_nodes_to_blocks( $node, $images );
				break;

			case 'br':
			case 'script':
			case 'style':
				break;

			case 'a':
				$blocks .= "<!-- wp:paragraph -->\n<p>" . $node->ownerDocument->saveHTML( $node ) . "</p>\n<!-- /wp:paragraph -->\n\n";
				break;

			default:
				$blocks .= "<!-- wp:html -->\n" . trim( $node->ownerDocument->saveHTML( $node ) ) . "\n<!-- /wp:html -->\n\n";
				break;
		}
	}

	return $blocks;
}

/**
 * Μετατρέπει το HTML του παλιού site σε block markup του editor.
 * Ό,τι δεν αναγνωρίζεται μπαίνει σε core/html, ώστε να μη χαθεί τίποτα.
 */
function kosm_html_to_blocks( $html, $images = array() ) {
	$html = preg_replace( '/\[\/?vc_[^\]]*\]/u', '', (string) $html );
	$html = trim( (string) $html );

	if ( '' === $html ) {
		return '';
	}

	$dom      = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="kosm-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	$root = $dom->getElementById( 'kosm-root' );

	if ( ! $root ) {
		return "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->\n\n";
	}

	return kosm_nodes_to_blocks( $root, $images );
}

/**
 * Εκτελεί την εισαγωγή.
 *
 * @param array $args feed, pages, limit, dry_run, media, force, status, cats, faculty.
 * @return array Σύνοψη: total, created, updated, skipped, files, messages.
 */
function kosmiteia_import_announcements( $args = array() ) {
	$args = wp_parse_args( $args, kosmiteia_import_defaults() );

	$feed_url  = rtrim( (string) $args['feed'], '?&' );
	$max_pages = max( 1, (int) $args['pages'] );
	$limit     = max( 0, (int) $args['limit'] );
	$dry_run   = (bool) $args['dry_run'];
	$do_media  = (bool) $args['media'] && ! $dry_run;
	$force     = (bool) $args['force'];
	$status    = in_array( $args['status'], array( 'publish', 'draft', 'pending', 'private' ), true ) ? $args['status'] : 'publish';
	$only_cats = array_filter( array_map( 'trim', (array) $args['cats'] ) );
	$faculty   = (string) $args['faculty'];

	if ( ! function_exists( 'media_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	if ( ! is_user_logged_in() ) {
		wp_set_current_user( 1 );
	}

	if ( $dry_run ) {
		kosmiteia_import_log( __( 'ΔΟΚΙΜΗ (dry run): δεν γράφεται τίποτα στη βάση.', 'kosmiteia' ) );
	}

	/* =========================================================================
	 * 1. Κατέβασμα του feed
	 * ====================================================================== */

	kosmiteia_import_log( sprintf( '  Πηγή: %s', $feed_url ) );

	$items = array();
	$seen  = array();

	for ( $page = 1; $page <= $max_pages; $page++ ) {
		$batch = kosm_feed_items( $feed_url, $page );

		if ( ! $batch ) {
			break;
		}

		$new = 0;

		foreach ( $batch as $item ) {
			if ( '' === $item['link'] || isset( $seen[ $item['link'] ] ) ) {
				continue;
			}

			$seen[ $item['link'] ] = true;
			$items[]               = $item;
			++$new;
		}

		kosmiteia_import_log( sprintf( '  Σελίδα %d: %d νέα (σύνολο %d)', $page, $new, count( $items ) ) );

		if ( 0 === $new ) {
			break;
		}

		usleep( 300000 );
	}

	if ( ! $items ) {
		kosmiteia_import_warn( __( 'Δεν βρέθηκαν ανακοινώσεις στο feed.', 'kosmiteia' ) );

		return array(
			'total'    => 0,
			'created'  => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'files'    => 0,
			'messages' => kosmiteia_import_log(),
		);
	}

	/* =========================================================================
	 * 2. Εισαγωγή
	 * ====================================================================== */

	$created  = 0;
	$updated  = 0;
	$skipped  = 0;
	$files    = 0;
	$examined = 0;

	foreach ( $items as $item ) {

		if ( $only_cats && ! array_intersect( $only_cats, $item['categories'] ) ) {
			continue;
		}

		if ( $limit && $examined >= $limit ) {
			break;
		}

		++$examined;

		$existing = kosm_existing_by_source( $item['link'] );

		if ( $existing && ! $force ) {
			++$skipped;
			continue;
		}

		$timestamp = strtotime( $item['date'] );
		$timestamp = $timestamp ? $timestamp : time();
		$date_gmt  = gmdate( 'Y-m-d H:i:s', $timestamp );
		$slug      = kosm_slug_from_link( $item['link'], sanitize_title( $item['title'] ) );
		$content   = $item['content'];

		/* Συνημμένα: PDF και εικόνες του παλιού site. */
		preg_match_all( '#https?://[^"\'\s<>]+#u', $content, $matches );

		$uploads = array_values(
			array_unique(
				array_filter(
					$matches[0],
					function ( $url ) {
						return false !== strpos( $url, '/wp-content/uploads/' );
					}
				)
			)
		);

		$file_url = '';
		$images   = array();
		$replace  = array();

		foreach ( $uploads as $url ) {
			$clean  = html_entity_decode( $url, ENT_QUOTES, 'UTF-8' );
			$is_pdf = (bool) preg_match( '/\.pdf$/i', $clean );

			if ( $do_media ) {
				$attachment_id = kosm_sideload( $clean );

				if ( $attachment_id ) {
					++$files;

					$new_url         = wp_get_attachment_url( $attachment_id );
					$replace[ $url ] = $new_url;
					$clean           = $new_url;

					if ( wp_attachment_is_image( $attachment_id ) ) {
						$images[ $new_url ] = $attachment_id;
					}
				}
			}

			if ( $is_pdf && '' === $file_url ) {
				$file_url = $clean;
			}
		}

		if ( $replace ) {
			$content = strtr( $content, $replace );
		}

		$blocks = kosm_html_to_blocks( $content, $images );

		if ( $dry_run ) {
			kosmiteia_import_log(
				sprintf(
					'  [%s] %s | %s | %s | αρχεία: %d',
					$existing ? 'ΥΠΑΡΧΕΙ' : 'ΝΕΑ',
					gmdate( 'Y-m-d', $timestamp ),
					mb_substr( $item['title'], 0, 70 ),
					$item['categories'] ? implode( ', ', $item['categories'] ) : '-',
					count( $uploads )
				)
			);
			continue;
		}

		$postarr = array(
			'post_type'     => 'kosm_announcement',
			'post_status'   => $status,
			'post_title'    => $item['title'],
			'post_name'     => $slug,
			'post_content'  => $blocks,
			'post_excerpt'  => wp_trim_words( wp_strip_all_tags( $item['excerpt'] ), 40, '…' ),
			'post_date_gmt' => $date_gmt,
			'post_date'     => get_date_from_gmt( $date_gmt ),
		);

		if ( $existing ) {
			$postarr['ID'] = $existing;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) ) {
			kosmiteia_import_warn( sprintf( '%s: %s', $item['title'], $post_id->get_error_message() ) );
			continue;
		}

		$post_id = (int) $post_id;

		update_post_meta( $post_id, 'kosm_source_url', $item['link'] );

		if ( '' !== $file_url ) {
			update_post_meta( $post_id, 'kosm_file_url', $file_url );
		}

		if ( $item['categories'] ) {
			wp_set_object_terms( $post_id, $item['categories'], 'kosm_ann_category' );
		}

		if ( '' !== $faculty ) {
			wp_set_object_terms( $post_id, array( $faculty ), 'kosm_faculty' );
		}

		if ( $images && ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, (int) reset( $images ) );
		}

		if ( $existing ) {
			++$updated;
		} else {
			++$created;
		}
	}

	kosmiteia_import_log( '' );
	kosmiteia_import_log( sprintf( '  Ανακοινώσεις στο feed: %d', count( $items ) ) );
	kosmiteia_import_log( sprintf( '  Νέες: %d | Ενημερωμένες: %d | Υπάρχουσες (παράλειψη): %d', $created, $updated, $skipped ) );
	kosmiteia_import_log( sprintf( '  Αρχεία στη Βιβλιοθήκη: %d', $files ) );

	return array(
		'total'    => count( $items ),
		'created'  => $created,
		'updated'  => $updated,
		'skipped'  => $skipped,
		'files'    => $files,
		'messages' => kosmiteia_import_log(),
	);
}
