<?php
/**
 * Εντολές WP-CLI: wp kosmiteia ...
 *
 *   wp kosmiteia seed [--force]
 *   wp kosmiteia import-announcements [--feed=<url>] [--pages=<n>] [--limit=<n>]
 *                                     [--faculty=<όνομα>] [--status=<publish|draft>]
 *                                     [--cats=<κατ1|κατ2>] [--dry-run] [--no-media] [--force]
 *   wp kosmiteia info
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Δημιουργία του αρχικού περιεχομένου.
 *
 * ## OPTIONS
 *
 * [--force]
 * : Ξαναδημιουργεί το περιεχόμενο ακόμη κι αν υπάρχει ήδη.
 *
 * @param array $args       Θέσεις (δεν χρησιμοποιούνται).
 * @param array $assoc_args Παράμετροι.
 */
function kosmiteia_cli_seed( $args, $assoc_args ) {
	kosmiteia_install_demo_content( isset( $assoc_args['force'] ) );
}
WP_CLI::add_command( 'kosmiteia seed', 'kosmiteia_cli_seed' );

/**
 * Εισαγωγή ανακοινώσεων από το RSS feed του παλιού ιστότοπου.
 *
 * ## OPTIONS
 *
 * [--feed=<url>]
 * : Το feed. Προεπιλογή: https://health.duth.gr/feed/
 *
 * [--pages=<number>]
 * : Μέγιστες σελίδες feed (10 ανακοινώσεις ανά σελίδα). Προεπιλογή: 60.
 *
 * [--limit=<number>]
 * : Μέγιστες ανακοινώσεις. 0 = όλες.
 *
 * [--faculty=<name>]
 * : Όρος «Σχολή» που θα μπει σε όλες τις ανακοινώσεις.
 *
 * [--status=<status>]
 * : publish (προεπιλογή) ή draft.
 *
 * [--cats=<list>]
 * : Μόνο αυτές οι κατηγορίες του παλιού ιστότοπου, χωρισμένες με «|».
 *
 * [--dry-run]
 * : Μόνο αναφορά, χωρίς εγγραφές.
 *
 * [--no-media]
 * : Χωρίς λήψη PDF/εικόνων.
 *
 * [--force]
 * : Ενημερώνει ανακοινώσεις που έχουν ήδη εισαχθεί.
 *
 * @param array $args       Θέσεις (δεν χρησιμοποιούνται).
 * @param array $assoc_args Παράμετροι.
 */
function kosmiteia_cli_import_announcements( $args, $assoc_args ) {
	$defaults = kosmiteia_import_defaults();

	$result = kosmiteia_import_announcements(
		array(
			'feed'    => isset( $assoc_args['feed'] ) ? $assoc_args['feed'] : $defaults['feed'],
			'pages'   => isset( $assoc_args['pages'] ) ? (int) $assoc_args['pages'] : $defaults['pages'],
			'limit'   => isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : $defaults['limit'],
			'dry_run' => isset( $assoc_args['dry-run'] ),
			'media'   => ! isset( $assoc_args['no-media'] ),
			'force'   => isset( $assoc_args['force'] ),
			'status'  => isset( $assoc_args['status'] ) ? $assoc_args['status'] : $defaults['status'],
			'cats'    => isset( $assoc_args['cats'] ) ? explode( '|', $assoc_args['cats'] ) : array(),
			'faculty' => isset( $assoc_args['faculty'] ) ? $assoc_args['faculty'] : '',
		)
	);

	WP_CLI::success(
		sprintf(
			'Νέες: %d | Ενημερωμένες: %d | Παραλείψεις: %d | Αρχεία: %d',
			$result['created'],
			$result['updated'],
			$result['skipped'],
			$result['files']
		)
	);
}
WP_CLI::add_command( 'kosmiteia import-announcements', 'kosmiteia_cli_import_announcements' );

/**
 * Σύνοψη του περιεχομένου του ιστότοπου.
 */
function kosmiteia_cli_info() {
	$types = array( 'kosm_school', 'kosm_announcement', 'kosm_program', 'kosm_event', 'kosm_person', 'kosm_document' );
	$rows  = array();

	foreach ( $types as $type ) {
		$object = get_post_type_object( $type );

		if ( ! $object ) {
			continue;
		}

		$counts = wp_count_posts( $type );

		$rows[] = array(
			'type'      => $type,
			'label'     => $object->labels->name,
			'published' => isset( $counts->publish ) ? (int) $counts->publish : 0,
			'draft'     => isset( $counts->draft ) ? (int) $counts->draft : 0,
			'archive'   => (string) get_post_type_archive_link( $type ),
		);
	}

	WP_CLI\Utils\format_items( 'table', $rows, array( 'type', 'label', 'published', 'draft', 'archive' ) );
}
WP_CLI::add_command( 'kosmiteia info', 'kosmiteia_cli_info' );
