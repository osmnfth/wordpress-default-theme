<?php
defined( 'ABSPATH' ) || exit;

function kosmiteia_webp_source_types() {
	return (array) apply_filters( 'kosmiteia_webp_source_types', array( 'image/jpeg', 'image/png' ) );
}

function kosmiteia_supports_webp() {
	return (bool) wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
}

function kosmiteia_webp_quality( $quality, $mime ) {
	if ( 'image/webp' !== $mime ) {
		return $quality;
	}

	return (int) apply_filters( 'kosmiteia_webp_quality', 82 );
}
add_filter( 'wp_editor_set_quality', 'kosmiteia_webp_quality', 10, 2 );

function kosmiteia_image_output_format( $formats ) {
	if ( ! kosmiteia_supports_webp() ) {
		return $formats;
	}

	foreach ( kosmiteia_webp_source_types() as $type ) {
		$formats[ $type ] = 'image/webp';
	}

	return $formats;
}
add_filter( 'image_editor_output_format', 'kosmiteia_image_output_format' );

function kosmiteia_allowed_mime_types( $mimes ) {
	$mimes['webp'] = 'image/webp';
	$mimes['webm'] = 'video/webm';

	return $mimes;
}
add_filter( 'upload_mimes', 'kosmiteia_allowed_mime_types' );

function kosmiteia_webp_target_path( $file ) {
	$dir  = dirname( $file );
	$name = wp_basename( $file );
	$ext  = pathinfo( $name, PATHINFO_EXTENSION );

	if ( $ext ) {
		$name = substr( $name, 0, - ( strlen( $ext ) + 1 ) );
	}

	return trailingslashit( $dir ) . wp_unique_filename( $dir, $name . '.webp' );
}

function kosmiteia_truecolor_source( $file, $mime ) {
	if ( 'image/png' !== $mime || ! function_exists( 'imagecreatefrompng' ) ) {
		return '';
	}

	$image = @imagecreatefrompng( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	if ( ! $image ) {
		return '';
	}

	if ( imageistruecolor( $image ) ) {
		imagedestroy( $image );
		return '';
	}

	imagepalettetotruecolor( $image );
	imagealphablending( $image, false );
	imagesavealpha( $image, true );

	$temp   = trailingslashit( get_temp_dir() ) . uniqid( 'kosmiteia-', false ) . '.png';
	$stored = imagepng( $image, $temp );

	imagedestroy( $image );

	if ( ! $stored ) {
		wp_delete_file( $temp );
		return '';
	}

	return $temp;
}

function kosmiteia_save_as_webp( $file, $mime ) {
	$source = kosmiteia_truecolor_source( $file, $mime );
	$editor = wp_get_image_editor( $source ? $source : $file );

	if ( is_wp_error( $editor ) ) {
		if ( $source ) {
			wp_delete_file( $source );
		}

		return $editor;
	}

	$saved = $editor->save( kosmiteia_webp_target_path( $file ), 'image/webp' );

	if ( $source ) {
		wp_delete_file( $source );
	}

	if ( is_wp_error( $saved ) ) {
		return $saved;
	}

	$path = isset( $saved['path'] ) ? $saved['path'] : '';

	if ( ! $path || ! file_exists( $path ) || filesize( $path ) < 1 || ! @getimagesize( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $path && file_exists( $path ) ) {
			wp_delete_file( $path );
		}

		return new WP_Error( 'kosmiteia_webp_failed', __( 'Η μετατροπή σε WebP απέτυχε.', 'kosmiteia' ) );
	}

	return $saved;
}

function kosmiteia_convert_upload_to_webp( $upload ) {
	if ( ! apply_filters( 'kosmiteia_convert_uploads_to_webp', true ) ) {
		return $upload;
	}

	if ( empty( $upload['type'] ) || ! in_array( $upload['type'], kosmiteia_webp_source_types(), true ) ) {
		return $upload;
	}

	if ( ! kosmiteia_supports_webp() ) {
		return $upload;
	}

	$saved = kosmiteia_save_as_webp( $upload['file'], $upload['type'] );

	if ( is_wp_error( $saved ) ) {
		return $upload;
	}

	wp_delete_file( $upload['file'] );

	$upload['url']  = str_replace( wp_basename( $upload['file'] ), $saved['file'], $upload['url'] );
	$upload['file'] = $saved['path'];
	$upload['type'] = 'image/webp';

	return $upload;
}
add_filter( 'wp_handle_upload', 'kosmiteia_convert_upload_to_webp' );
add_filter( 'wp_handle_sideload', 'kosmiteia_convert_upload_to_webp' );

function kosmiteia_require_webm_video( $file ) {
	if ( ! apply_filters( 'kosmiteia_require_webm_video', true ) ) {
		return $file;
	}

	if ( ! empty( $file['error'] ) ) {
		return $file;
	}

	$check = wp_check_filetype( isset( $file['name'] ) ? $file['name'] : '' );
	$type  = $check['type'] ? $check['type'] : ( isset( $file['type'] ) ? $file['type'] : '' );

	if ( 0 !== strpos( (string) $type, 'video/' ) || 'video/webm' === $type ) {
		return $file;
	}

	$file['error'] = __( 'Τα βίντεο ανεβαίνουν μόνο σε μορφή WebM. Μετατρέψτε το αρχείο σε .webm και δοκιμάστε ξανά.', 'kosmiteia' );

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'kosmiteia_require_webm_video' );
add_filter( 'wp_handle_sideload_prefilter', 'kosmiteia_require_webm_video' );

function kosmiteia_media_url_map( $old_meta, $new_meta, $base_url ) {
	$map = array();

	if ( ! empty( $old_meta['file'] ) && ! empty( $new_meta['file'] ) ) {
		$map[ $base_url . wp_basename( $old_meta['file'] ) ] = $base_url . wp_basename( $new_meta['file'] );
	}

	$old_sizes = isset( $old_meta['sizes'] ) ? (array) $old_meta['sizes'] : array();
	$new_sizes = isset( $new_meta['sizes'] ) ? (array) $new_meta['sizes'] : array();

	foreach ( $old_sizes as $size => $data ) {
		if ( empty( $data['file'] ) || empty( $new_sizes[ $size ]['file'] ) ) {
			continue;
		}

		$map[ $base_url . $data['file'] ] = $base_url . $new_sizes[ $size ]['file'];
	}

	return $map;
}

function kosmiteia_replace_media_urls( $map ) {
	global $wpdb;

	$updated = 0;

	foreach ( $map as $old => $new ) {
		if ( $old === $new ) {
			continue;
		}

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s",
				'%' . $wpdb->esc_like( $old ) . '%'
			)
		);

		foreach ( $ids as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );
			$replaced = str_replace( $old, $new, $content );

			if ( $replaced === $content ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => (int) $post_id,
					'post_content' => $replaced,
				)
			);

			++$updated;
		}
	}

	return $updated;
}

function kosmiteia_convert_attachment_to_webp( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	$mime          = get_post_mime_type( $attachment_id );

	if ( ! in_array( $mime, kosmiteia_webp_source_types(), true ) ) {
		return new WP_Error( 'kosmiteia_skipped', __( 'Το αρχείο δεν είναι JPEG ή PNG.', 'kosmiteia' ) );
	}

	if ( ! kosmiteia_supports_webp() ) {
		return new WP_Error( 'kosmiteia_no_webp', __( 'Ο server δεν υποστηρίζει WebP.', 'kosmiteia' ) );
	}

	$file = get_attached_file( $attachment_id );

	if ( ! $file || ! file_exists( $file ) ) {
		return new WP_Error( 'kosmiteia_missing', __( 'Το αρχείο δεν βρέθηκε.', 'kosmiteia' ) );
	}

	$actual = @getimagesize( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	if ( ! $actual || empty( $actual['mime'] ) ) {
		return new WP_Error( 'kosmiteia_invalid', __( 'Το αρχείο δεν είναι έγκυρη εικόνα.', 'kosmiteia' ) );
	}

	if ( ! in_array( $actual['mime'], kosmiteia_webp_source_types(), true ) ) {
		return new WP_Error( 'kosmiteia_skipped', __( 'Το αρχείο δεν είναι JPEG ή PNG.', 'kosmiteia' ) );
	}

	$mime     = $actual['mime'];
	$old_meta = (array) wp_get_attachment_metadata( $attachment_id );
	$base_url = trailingslashit( dirname( wp_get_attachment_url( $attachment_id ) ) );

	$saved = kosmiteia_save_as_webp( $file, $mime );

	if ( is_wp_error( $saved ) ) {
		return $saved;
	}

	update_attached_file( $attachment_id, $saved['path'] );

	wp_update_post(
		array(
			'ID'             => $attachment_id,
			'post_mime_type' => 'image/webp',
		)
	);

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$new_meta = wp_generate_attachment_metadata( $attachment_id, $saved['path'] );
	wp_update_attachment_metadata( $attachment_id, $new_meta );

	kosmiteia_replace_media_urls( kosmiteia_media_url_map( $old_meta, (array) $new_meta, $base_url ) );

	return true;
}

function kosmiteia_media_bulk_actions( $actions ) {
	$actions['kosmiteia_webp'] = __( 'Μετατροπή σε WebP', 'kosmiteia' );

	return $actions;
}
add_filter( 'bulk_actions-upload', 'kosmiteia_media_bulk_actions' );

function kosmiteia_handle_media_bulk_actions( $redirect, $action, $ids ) {
	if ( 'kosmiteia_webp' !== $action ) {
		return $redirect;
	}

	$done    = 0;
	$skipped = 0;

	foreach ( (array) $ids as $id ) {
		if ( ! current_user_can( 'edit_post', $id ) ) {
			++$skipped;
			continue;
		}

		if ( is_wp_error( kosmiteia_convert_attachment_to_webp( $id ) ) ) {
			++$skipped;
			continue;
		}

		++$done;
	}

	return add_query_arg(
		array(
			'kosmiteia_webp_done'    => $done,
			'kosmiteia_webp_skipped' => $skipped,
		),
		$redirect
	);
}
add_filter( 'handle_bulk_actions-upload', 'kosmiteia_handle_media_bulk_actions', 10, 3 );

function kosmiteia_media_bulk_notice() {
	if ( ! isset( $_GET['kosmiteia_webp_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$done    = (int) $_GET['kosmiteia_webp_done']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$skipped = isset( $_GET['kosmiteia_webp_skipped'] ) ? (int) $_GET['kosmiteia_webp_skipped'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$message = sprintf(
		/* translators: %d: πλήθος αρχείων. */
		_n( 'Μετατράπηκε %d αρχείο σε WebP.', 'Μετατράπηκαν %d αρχεία σε WebP.', $done, 'kosmiteia' ),
		$done
	);

	if ( $skipped ) {
		$message .= ' ' . sprintf(
			/* translators: %d: πλήθος αρχείων. */
			_n( '%d αρχείο παραλείφθηκε.', '%d αρχεία παραλείφθηκαν.', $skipped, 'kosmiteia' ),
			$skipped
		);
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html( $message )
	);
}
add_action( 'admin_notices', 'kosmiteia_media_bulk_notice' );
