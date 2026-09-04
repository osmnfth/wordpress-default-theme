<?php
/**
 * Πεδίο «Φωτογραφίες (γκαλερί)» στη διαχείριση.
 *
 * Οι φωτογραφίες της γκαλερί επιλέγονται σε δικό τους πεδίο, έξω από το
 * κείμενο του άρθρου: έτσι δεν μπερδεύονται ποτέ με τις εικόνες που μπαίνουν
 * μέσα στο περιεχόμενο ούτε με την επιλεγμένη εικόνα (featured).
 *
 * Αποθηκεύονται στο meta `kosm_gallery` ως λίστα IDs χωρισμένη με κόμμα.
 *
 * @package Kosmiteia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Οι τύποι περιεχομένου που έχουν το πεδίο γκαλερί.
 *
 * Προσθήκη/αφαίρεση από ένα child ή plugin:
 *   add_filter( 'kosmiteia_gallery_post_types', function ( $types ) { ... } );
 *
 * @return string[]
 */
function kosmiteia_gallery_post_types() {
	return (array) apply_filters(
		'kosmiteia_gallery_post_types',
		array( 'kosm_school', 'kosm_program', 'kosm_announcement', 'page', 'post' )
	);
}

/**
 * Καθαρίζει την τιμή του πεδίου: μόνο IDs εικόνων, χωρίς διπλά, με κόμμα.
 *
 * @param string $value Η τιμή όπως ήρθε από τη φόρμα ή το REST API.
 * @return string
 */
function kosmiteia_sanitize_gallery_ids( $value ) {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
	$ids = array_values( array_unique( $ids ) );

	$ids = array_filter(
		$ids,
		function ( $id ) {
			return wp_attachment_is_image( $id );
		}
	);

	return implode( ',', $ids );
}

/**
 * Καταχώριση του meta, ώστε να είναι διαθέσιμο και μέσω REST API.
 */
function kosmiteia_register_gallery_meta() {
	foreach ( kosmiteia_gallery_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			'kosm_gallery',
			array(
				'type'              => 'string',
				'label'             => __( 'Φωτογραφίες (γκαλερί)', 'kosmiteia' ),
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'kosmiteia_sanitize_gallery_ids',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'kosmiteia_register_gallery_meta' );

/**
 * Τα IDs των φωτογραφιών μιας σελίδας, με τη σειρά που τα όρισε ο συντάκτης.
 *
 * @param int $post_id ID σελίδας.
 * @return int[]
 */
function kosmiteia_gallery_field_ids( $post_id ) {
	$value = (string) get_post_meta( (int) $post_id, 'kosm_gallery', true );

	if ( '' === $value ) {
		return array();
	}

	$ids = array_filter( array_map( 'absint', explode( ',', $value ) ) );

	return array_values(
		array_filter(
			$ids,
			function ( $id ) {
				return wp_attachment_is_image( $id );
			}
		)
	);
}

/**
 * Το meta box στην οθόνη επεξεργασίας.
 */
function kosmiteia_add_gallery_meta_box() {
	foreach ( kosmiteia_gallery_post_types() as $post_type ) {
		add_meta_box(
			'kosmiteia-gallery',
			__( 'Φωτογραφίες (γκαλερί)', 'kosmiteia' ),
			'kosmiteia_render_gallery_meta_box',
			$post_type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'kosmiteia_add_gallery_meta_box' );

/**
 * Μία μικρογραφία μέσα στο πεδίο.
 *
 * @param int $id ID συνημμένου.
 * @return string
 */
function kosmiteia_gallery_field_item( $id ) {
	$thumb = wp_get_attachment_image(
		$id,
		'thumbnail',
		false,
		array( 'class' => 'kosmiteia-gallery-field__image' )
	);

	if ( ! $thumb ) {
		return '';
	}

	return sprintf(
		'<li class="kosmiteia-gallery-field__item" data-id="%1$d">%2$s<span class="kosmiteia-gallery-field__buttons">
			<button type="button" class="button-link" data-action="move-up" aria-label="%3$s">&#9664;</button>
			<button type="button" class="button-link" data-action="move-down" aria-label="%4$s">&#9654;</button>
			<button type="button" class="button-link kosmiteia-gallery-field__remove" data-action="remove" aria-label="%5$s">&times;</button>
		</span></li>',
		(int) $id,
		$thumb,
		esc_attr__( 'Μετακίνηση πιο μπροστά', 'kosmiteia' ),
		esc_attr__( 'Μετακίνηση πιο πίσω', 'kosmiteia' ),
		esc_attr__( 'Αφαίρεση φωτογραφίας', 'kosmiteia' )
	);
}

/**
 * Το περιεχόμενο του meta box.
 *
 * @param WP_Post $post Η σελίδα που επεξεργαζόμαστε.
 */
function kosmiteia_render_gallery_meta_box( $post ) {
	wp_nonce_field( 'kosmiteia_gallery_save', 'kosmiteia_gallery_nonce' );

	$ids   = kosmiteia_gallery_field_ids( $post->ID );
	$items = '';

	foreach ( $ids as $id ) {
		$items .= kosmiteia_gallery_field_item( $id );
	}
	?>
	<div class="kosmiteia-gallery-field" data-kosmiteia-gallery-field>
		<ul class="kosmiteia-gallery-field__list" data-list><?php echo $items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></ul>

		<p class="kosmiteia-gallery-field__empty" data-empty <?php echo $ids ? 'hidden' : ''; ?>>
			<?php esc_html_e( 'Δεν έχουν επιλεγεί φωτογραφίες.', 'kosmiteia' ); ?>
		</p>

		<p class="kosmiteia-gallery-field__actions">
			<button type="button" class="button button-primary" data-action="select">
				<?php esc_html_e( 'Επιλογή φωτογραφιών', 'kosmiteia' ); ?>
			</button>
			<button type="button" class="button" data-action="clear" <?php echo $ids ? '' : 'disabled'; ?>>
				<?php esc_html_e( 'Αφαίρεση όλων', 'kosmiteia' ); ?>
			</button>
		</p>

		<input type="hidden" name="kosm_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-input>

		<p class="description">
			<?php esc_html_e( 'Οι φωτογραφίες εμφανίζονται ως μικρογραφίες στη σελίδα και ανοίγουν σε lightbox. Δεν έχουν σχέση με τις εικόνες που βάζετε μέσα στο κείμενο ούτε με την επιλεγμένη εικόνα.', 'kosmiteia' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Αποθήκευση του πεδίου.
 *
 * @param int $post_id ID σελίδας.
 */
function kosmiteia_save_gallery_meta( $post_id ) {
	if ( ! isset( $_POST['kosmiteia_gallery_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['kosmiteia_gallery_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'kosmiteia_gallery_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$value = isset( $_POST['kosm_gallery'] )
		? kosmiteia_sanitize_gallery_ids( wp_unslash( $_POST['kosm_gallery'] ) )
		: '';

	if ( '' === $value ) {
		delete_post_meta( $post_id, 'kosm_gallery' );
		return;
	}

	update_post_meta( $post_id, 'kosm_gallery', $value );
}
add_action( 'save_post', 'kosmiteia_save_gallery_meta' );

/**
 * Assets του πεδίου (media modal + το δικό μας script/στυλ).
 *
 * @param string $hook Το τρέχον admin screen.
 */
function kosmiteia_gallery_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->post_type, kosmiteia_gallery_post_types(), true ) ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'kosmiteia-admin',
		KOSMITEIA_URI . '/assets/css/admin.css',
		array(),
		kosmiteia_asset_version( 'assets/css/admin.css' )
	);

	wp_enqueue_script(
		'kosmiteia-admin-gallery',
		KOSMITEIA_URI . '/assets/js/admin-gallery.js',
		array( 'jquery' ),
		kosmiteia_asset_version( 'assets/js/admin-gallery.js' ),
		true
	);

	wp_localize_script(
		'kosmiteia-admin-gallery',
		'kosmiteiaGalleryFieldL10n',
		array(
			'title'      => __( 'Φωτογραφίες (γκαλερί)', 'kosmiteia' ),
			'button'     => __( 'Χρήση αυτών των φωτογραφιών', 'kosmiteia' ),
			'moveUp'     => __( 'Μετακίνηση πιο μπροστά', 'kosmiteia' ),
			'moveDown'   => __( 'Μετακίνηση πιο πίσω', 'kosmiteia' ),
			'remove'     => __( 'Αφαίρεση φωτογραφίας', 'kosmiteia' ),
			'confirmAll' => __( 'Να αφαιρεθούν όλες οι φωτογραφίες από τη γκαλερί;', 'kosmiteia' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'kosmiteia_gallery_admin_assets' );
