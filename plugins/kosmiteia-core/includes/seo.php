<?php
/**
 * SEO: meta description, Open Graph, Twitter cards και structured data (JSON-LD).
 *
 * Όλα παράγονται δυναμικά από το περιεχόμενο. Αν υπάρχει ενεργό SEO plugin
 * (Yoast, Rank Math, SEOPress, AIOSEO) το theme κάνει στην άκρη και δεν
 * τυπώνει διπλά meta tags.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Υπάρχει ενεργό SEO plugin;
 *
 * @return bool
 */
function kosmiteia_seo_plugin_active() {
	return (
		defined( 'WPSEO_VERSION' ) ||
		defined( 'RANK_MATH_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' ) ||
		defined( 'AIOSEO_VERSION' )
	);
}

/**
 * Περιγραφή της τρέχουσας σελίδας.
 *
 * @return string
 */
function kosmiteia_meta_description() {
	$description = '';

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 32, '' );
		}
	} elseif ( is_post_type_archive() ) {
		$object      = get_queried_object();
		$description = ( $object && ! empty( $object->description ) ) ? $object->description : post_type_archive_title( '', false );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$description = term_description();
	} elseif ( is_search() ) {
		$description = sprintf(
			/* translators: %s: όρος αναζήτησης. */
			__( 'Αποτελέσματα αναζήτησης για: %s', 'kosmiteia' ),
			get_search_query()
		);
	}

	if ( ! $description ) {
		$description = get_bloginfo( 'description', 'display' );
	}

	return trim( wp_strip_all_tags( $description ) );
}

/**
 * Η εικόνα που αντιπροσωπεύει τη σελίδα (για OG / Twitter).
 *
 * @return string URL ή κενό.
 */
function kosmiteia_share_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

		if ( $image ) {
			return $image[0];
		}
	}

	$custom_logo_id = get_theme_mod( 'custom_logo' );

	if ( $custom_logo_id ) {
		$logo = wp_get_attachment_image_src( $custom_logo_id, 'full' );

		if ( $logo ) {
			return $logo[0];
		}
	}

	return '';
}

/**
 * Meta tags στο <head>.
 */
function kosmiteia_head_meta() {
	if ( kosmiteia_seo_plugin_active() ) {
		return;
	}

	$description = kosmiteia_meta_description();
	$image       = kosmiteia_share_image();
	$title       = wp_get_document_title();
	$url         = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$locale      = get_locale();

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( is_singular() ? 'article' : 'website' ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( $locale ) );

	if ( $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	if ( $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
		printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
	} else {
		printf( '<meta name="twitter:card" content="summary" />' . "\n" );
	}

	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );

	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	if ( is_singular() ) {
		printf( '<meta property="article:published_time" content="%s" />' . "\n", esc_attr( get_the_date( DATE_W3C ) ) );
		printf( '<meta property="article:modified_time" content="%s" />' . "\n", esc_attr( get_the_modified_date( DATE_W3C ) ) );
	}
}
add_action( 'wp_head', 'kosmiteia_head_meta', 6 );

/**
 * Structured data (schema.org) σε JSON-LD.
 */
function kosmiteia_json_ld() {
	if ( kosmiteia_seo_plugin_active() ) {
		return;
	}

	$graph = array();
	$home  = home_url( '/' );
	$logo  = kosmiteia_share_image();

	// Ο οργανισμός (Κοσμητεία / Σχολή).
	$organization = array(
		'@type'  => 'CollegeOrUniversity',
		'@id'    => $home . '#organization',
		'name'   => get_bloginfo( 'name' ),
		'url'    => $home,
	);

	if ( get_bloginfo( 'description' ) ) {
		$organization['description'] = get_bloginfo( 'description' );
	}

	if ( $logo ) {
		$organization['logo'] = $logo;
	}

	// Στοιχεία επικοινωνίας από τις Ρυθμίσεις Κοσμητείας.
	$address = kosmiteia_option( 'contact_address' );
	$phone   = kosmiteia_option( 'contact_phone' );
	$email   = kosmiteia_option( 'contact_email' );

	if ( $address ) {
		$organization['address'] = array(
			'@type'         => 'PostalAddress',
			'streetAddress' => $address,
		);
	}

	if ( $phone ) {
		$organization['telephone'] = $phone;
	}

	if ( $email ) {
		$organization['email'] = $email;
	}

	if ( kosmiteia_option( 'institution' ) ) {
		$organization['parentOrganization'] = array(
			'@type' => 'CollegeOrUniversity',
			'name'  => kosmiteia_option( 'institution' ),
		);
	}

	$social = array_filter(
		array(
			kosmiteia_option( 'social_facebook' ),
			kosmiteia_option( 'social_instagram' ),
			kosmiteia_option( 'social_youtube' ),
			kosmiteia_option( 'social_linkedin' ),
			kosmiteia_option( 'social_x' ),
		)
	);

	if ( $social ) {
		$organization['sameAs'] = array_values( $social );
	}

	$graph[] = $organization;

	// Το website + εσωτερική αναζήτηση.
	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => $home . '#website',
		'url'             => $home,
		'name'            => get_bloginfo( 'name' ),
		'inLanguage'      => get_bloginfo( 'language' ),
		'publisher'       => array( '@id' => $home . '#organization' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	if ( is_singular() ) {
		$post_id = get_the_ID();
		$type    = get_post_type();
		$image   = kosmiteia_share_image();

		$node = array(
			'@id'           => get_permalink(),
			'url'           => get_permalink(),
			'name'          => wp_strip_all_tags( get_the_title() ),
			'headline'      => wp_strip_all_tags( get_the_title() ),
			'description'   => kosmiteia_meta_description(),
			'inLanguage'    => get_bloginfo( 'language' ),
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'isPartOf'      => array( '@id' => $home . '#website' ),
		);

		if ( $image ) {
			$node['image'] = $image;
		}

		if ( 'kosm_announcement' === $type ) {
			$node['@type']     = 'NewsArticle';
			$node['author']    = array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			);
			$node['publisher'] = array( '@id' => $home . '#organization' );
		} elseif ( 'kosm_program' === $type ) {
			$node['@type']    = 'Course';
			$node['provider'] = array( '@id' => $home . '#organization' );

			$duration = get_post_meta( $post_id, 'kosm_duration', true );
			$ects     = get_post_meta( $post_id, 'kosm_ects', true );

			if ( $duration || $ects ) {
				$instance = array(
					'@type'        => 'CourseInstance',
					'courseMode'   => 'onsite',
					'courseWorkload' => $ects ? $ects . ' ECTS' : '',
				);

				if ( $duration ) {
					$instance['name'] = $duration;
				}

				$node['hasCourseInstance'] = array( array_filter( $instance ) );
			}
		} elseif ( 'kosm_event' === $type ) {
			$node['@type']     = 'Event';
			$node['organizer'] = array( '@id' => $home . '#organization' );

			$start    = get_post_meta( $post_id, 'kosm_event_start', true );
			$end      = get_post_meta( $post_id, 'kosm_event_end', true );
			$location = get_post_meta( $post_id, 'kosm_event_location', true );

			if ( $start ) {
				$node['startDate'] = $start;
			}
			if ( $end ) {
				$node['endDate'] = $end;
			}
			if ( $location ) {
				$node['location'] = array(
					'@type'   => 'Place',
					'name'    => $location,
					'address' => $location,
				);
			}
		} elseif ( 'kosm_person' === $type ) {
			$node['@type']            = 'Person';
			$node['worksFor']         = array( '@id' => $home . '#organization' );
			$node['jobTitle']         = get_post_meta( $post_id, 'kosm_person_role', true );
			$node['email']            = get_post_meta( $post_id, 'kosm_person_email', true );
			$node['telephone']        = get_post_meta( $post_id, 'kosm_person_phone', true );
			$node                     = array_filter( $node );
		} elseif ( 'kosm_document' === $type ) {
			$node['@type']     = 'DigitalDocument';
			$node['publisher'] = array( '@id' => $home . '#organization' );

			$file = get_post_meta( $post_id, 'kosm_document_file', true );

			if ( $file ) {
				$node['url'] = $file;
			}
		} elseif ( 'kosm_school' === $type ) {
			$node['@type']         = 'EducationalOrganization';
			$node['parentOrganization'] = array( '@id' => $home . '#organization' );

			$email   = get_post_meta( $post_id, 'kosm_email', true );
			$phone   = get_post_meta( $post_id, 'kosm_phone', true );
			$address = get_post_meta( $post_id, 'kosm_address', true );

			if ( $email ) {
				$node['email'] = $email;
			}
			if ( $phone ) {
				$node['telephone'] = $phone;
			}
			if ( $address ) {
				$node['address'] = $address;
			}
		} else {
			$node['@type'] = is_page() ? 'WebPage' : 'Article';
		}

		$graph[] = $node;

		// Breadcrumbs.
		$items = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => __( 'Αρχική', 'kosmiteia' ),
				'item'     => $home,
			),
		);

		$archive_link = get_post_type_archive_link( $type );

		if ( $archive_link ) {
			$object  = get_post_type_object( $type );
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $object ? $object->labels->name : $type,
				'item'     => $archive_link,
			);
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			'name'     => wp_strip_all_tags( get_the_title() ),
			'item'     => get_permalink(),
		);

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink() . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'kosmiteia_json_ld', 7 );

/**
 * Καθαρά, περιγραφικά titles για τα αρχεία των CPT.
 *
 * @param array $parts Τμήματα του τίτλου.
 * @return array
 */
function kosmiteia_document_title_parts( $parts ) {
	if ( is_post_type_archive( array( 'kosm_school', 'kosm_announcement', 'kosm_program', 'kosm_event', 'kosm_person', 'kosm_document' ) ) ) {
		$object = get_queried_object();

		if ( $object && isset( $object->labels->name ) ) {
			$parts['title'] = $object->labels->name;
		}
	}

	return $parts;
}
add_filter( 'document_title_parts', 'kosmiteia_document_title_parts' );
