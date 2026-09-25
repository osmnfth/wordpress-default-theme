<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_add_attachment_meta_box() {
	add_meta_box(
		'kosmiteia-attachment',
		__( 'Συνημμένο αρχείο', 'kosmiteia' ),
		'kosmiteia_render_attachment_meta_box',
		'kosm_announcement',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'kosmiteia_add_attachment_meta_box' );

function kosmiteia_render_attachment_meta_box( $post ) {
	wp_nonce_field( 'kosmiteia_attachment_save', 'kosmiteia_attachment_nonce' );

	$url  = (string) get_post_meta( $post->ID, 'kosm_file_url', true );
	$name = $url ? wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ) : '';
	?>
	<div data-kosmiteia-file-field>
		<p data-name <?php echo $url ? '' : 'hidden'; ?>>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" data-link><?php echo esc_html( rawurldecode( $name ) ); ?></a>
		</p>

		<p>
			<button type="button" class="button" data-action="select"><?php esc_html_e( 'Επιλογή ή ανέβασμα αρχείου', 'kosmiteia' ); ?></button>
			<button type="button" class="button-link button-link-delete" data-action="remove" <?php echo $url ? '' : 'hidden'; ?>><?php esc_html_e( 'Αφαίρεση', 'kosmiteia' ); ?></button>
		</p>

		<input type="hidden" name="kosm_file_url" value="<?php echo esc_attr( $url ); ?>" data-input>

		<p class="description"><?php esc_html_e( 'Εμφανίζεται ως κουμπί «Λήψη συνημμένου αρχείου» στη σελίδα της ανακοίνωσης. Χωρίς αρχείο, το κουμπί δεν εμφανίζεται.', 'kosmiteia' ); ?></p>
	</div>
	<?php
}

function kosmiteia_save_attachment_meta( $post_id ) {
	if ( ! isset( $_POST['kosmiteia_attachment_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['kosmiteia_attachment_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'kosmiteia_attachment_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$url = isset( $_POST['kosm_file_url'] ) ? esc_url_raw( wp_unslash( $_POST['kosm_file_url'] ) ) : '';

	if ( '' === $url ) {
		delete_post_meta( $post_id, 'kosm_file_url' );
		return;
	}

	update_post_meta( $post_id, 'kosm_file_url', $url );
}
add_action( 'save_post_kosm_announcement', 'kosmiteia_save_attachment_meta' );

function kosmiteia_attachment_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'kosm_announcement' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'kosmiteia-admin-file',
		KOSMITEIA_CORE_URL . '/assets/js/admin-file.js',
		array( 'jquery' ),
		kosmiteia_core_asset_version( 'assets/js/admin-file.js' ),
		true
	);

	wp_localize_script(
		'kosmiteia-admin-file',
		'kosmiteiaFileFieldL10n',
		array(
			'title'  => __( 'Συνημμένο αρχείο', 'kosmiteia' ),
			'button' => __( 'Χρήση αυτού του αρχείου', 'kosmiteia' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'kosmiteia_attachment_admin_assets' );

// A button bound to an empty post-meta URL (attachment, application link, school site) is not rendered.
function kosmiteia_hide_empty_bound_button( $content, $block, $instance ) {
	$key = $block['attrs']['metadata']['bindings']['url']['args']['key'] ?? '';

	if ( '' === $key || 'core/post-meta' !== ( $block['attrs']['metadata']['bindings']['url']['source'] ?? '' ) ) {
		return $content;
	}

	$post_id = $instance->context['postId'] ?? get_the_ID();

	if ( ! $post_id || '' !== trim( (string) get_post_meta( $post_id, $key, true ) ) ) {
		return $content;
	}

	return '';
}
add_filter( 'render_block_core/button', 'kosmiteia_hide_empty_bound_button', 10, 3 );

function kosmiteia_hide_empty_buttons( $content ) {
	return preg_match( '/\bwp-block-button(?![\w-])/', $content ) ? $content : '';
}
add_filter( 'render_block_core/buttons', 'kosmiteia_hide_empty_buttons' );
