<?php
/**
 * Εισαγωγή ανακοινώσεων από το παλιό site (health.duth.gr) στο νέο theme.
 *
 * Το παλιό site είναι classic WordPress: οι ανακοινώσεις είναι κανονικά posts
 * (permalink /YYYY/MM/DD/slug/) και η σελίδα /anakoinoseis/ απλώς τα εμφανίζει.
 * Το REST API (/wp-json, ?rest_route=) επιστρέφει 403 από το firewall του
 * server, οπότε η πηγή εδώ είναι το RSS feed με σελιδοποίηση:
 *
 *     https://health.duth.gr/feed/?paged=1 ... ?paged=40
 *
 * Κάθε <item> δίνει τίτλο, μόνιμο σύνδεσμο, ημερομηνία, κατηγορίες και
 * ολόκληρο το περιεχόμενο (content:encoded).
 *
 * Εκτέλεση (το site πρέπει να τρέχει: docker compose up -d):
 *
 *     docker compose run --rm --entrypoint wp \
 *         -e KOSM_IMPORT_DRYRUN=1 \
 *         provision eval-file /provision/import-announcements.php
 *
 * Ρυθμίσεις μέσω μεταβλητών περιβάλλοντος:
 *
 *   KOSM_IMPORT_FEED     Το feed (default https://health.duth.gr/feed/)
 *   KOSM_IMPORT_PAGES    Μέγιστες σελίδες feed (default 60)
 *   KOSM_IMPORT_LIMIT    Μέγιστες ανακοινώσεις (default 0 = όλες)
 *   KOSM_IMPORT_DRYRUN   1 = μόνο αναφορά, καμία εγγραφή στη βάση
 *   KOSM_IMPORT_MEDIA    0 = χωρίς κατέβασμα PDF/εικόνων (default 1)
 *   KOSM_IMPORT_FORCE    1 = ξαναγράφει ανακοινώσεις που έχουν ήδη εισαχθεί
 *   KOSM_IMPORT_STATUS   publish (default) ή draft
 *   KOSM_IMPORT_CATS     Μόνο αυτές οι κατηγορίες του παλιού site,
 *                        χωρισμένες με «|» (default: όλες)
 *   KOSM_IMPORT_FACULTY  Όρος kosm_faculty για όλες τις ανακοινώσεις
 *                        (default: κανένας)
 *
 * Η αντιστοίχιση με το παλιό post γίνεται με το meta «kosm_source_url», ώστε
 * το script να είναι idempotent: ξανατρέξιμο δεν δημιουργεί διπλότυπα.
 *
 * @package Kosmiteia
 */

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

wp_set_current_user( 1 );

/* =========================================================================
 * Ρυθμίσεις
 * ====================================================================== */

/**
 * Τιμή μεταβλητής περιβάλλοντος με προεπιλογή.
 */
function kosm_import_env( $name, $default = '' ) {
	$value = getenv( $name );

	return ( false === $value || '' === $value ) ? $default : $value;
}

$feed_url  = rtrim( kosm_import_env( 'KOSM_IMPORT_FEED', 'https://health.duth.gr/feed/' ), '?&' );
$max_pages = (int) kosm_import_env( 'KOSM_IMPORT_PAGES', '60' );
$limit     = (int) kosm_import_env( 'KOSM_IMPORT_LIMIT', '0' );
$dry_run   = '1' === kosm_import_env( 'KOSM_IMPORT_DRYRUN', '0' );
$do_media  = '0' !== kosm_import_env( 'KOSM_IMPORT_MEDIA', '1' );
$force     = '1' === kosm_import_env( 'KOSM_IMPORT_FORCE', '0' );
$status    = kosm_import_env( 'KOSM_IMPORT_STATUS', 'publish' );
$only_cats = array_filter( array_map( 'trim', explode( '|', kosm_import_env( 'KOSM_IMPORT_CATS', '' ) ) ) );
$faculty   = kosm_import_env( 'KOSM_IMPORT_FACULTY', '' );

if ( $dry_run ) {
	WP_CLI::log( '  ΔΟΚΙΜΗ (dry run): δεν γράφεται τίποτα στη βάση.' );
	$do_media = false;
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
		WP_CLI::warning( sprintf( 'Σελίδα %d: %s', $page, $response->get_error_message() ) );
		return array();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );

	if ( 200 !== $code ) {
		WP_CLI::warning( sprintf( 'Σελίδα %d: HTTP %d', $page, $code ) );
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
		WP_CLI::warning( sprintf( 'Δεν κατέβηκε: %s (%s)', $url, $tmp->get_error_message() ) );
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

		WP_CLI::warning( sprintf( 'Δεν εισήχθη: %s (%s)', $url, $attachment_id->get_error_message() ) );
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

/* =========================================================================
 * 1. Κατέβασμα του feed
 * ====================================================================== */

WP_CLI::log( sprintf( '  Πηγή: %s', $feed_url ) );

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

	WP_CLI::log( sprintf( '  Σελίδα %d: %d νέα (σύνολο %d)', $page, $new, count( $items ) ) );

	if ( 0 === $new ) {
		break;
	}

	usleep( 300000 );
}

if ( ! $items ) {
	WP_CLI::error( 'Δεν βρέθηκαν ανακοινώσεις στο feed.' );
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
		WP_CLI::log(
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
		WP_CLI::warning( sprintf( '%s: %s', $item['title'], $post_id->get_error_message() ) );
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

WP_CLI::log( '' );
WP_CLI::log( sprintf( '  Ανακοινώσεις στο feed: %d', count( $items ) ) );
WP_CLI::log( sprintf( '  Νέες: %d | Ενημερωμένες: %d | Υπάρχουσες (παράλειψη): %d', $created, $updated, $skipped ) );
WP_CLI::log( sprintf( '  Αρχεία στη Βιβλιοθήκη: %d', $files ) );
