<?php
defined( 'ABSPATH' ) || exit;

const KOSMITEIA_ANNOUNCER_ROLE = 'kosmiteia_announcer';

function kosmiteia_announcement_caps( $full = true ) {
	$own = array(
		'edit_kosm_announcements',
		'publish_kosm_announcements',
		'delete_kosm_announcements',
		'edit_published_kosm_announcements',
		'delete_published_kosm_announcements',
	);

	if ( ! $full ) {
		return $own;
	}

	return array_merge(
		$own,
		array(
			'edit_others_kosm_announcements',
			'delete_others_kosm_announcements',
			'read_private_kosm_announcements',
			'edit_private_kosm_announcements',
			'delete_private_kosm_announcements',
		)
	);
}

function kosmiteia_register_roles() {
	$announcer = array(
		'read'         => true,
		'upload_files' => true,
	);

	foreach ( kosmiteia_announcement_caps( false ) as $cap ) {
		$announcer[ $cap ] = true;
	}

	$role = get_role( KOSMITEIA_ANNOUNCER_ROLE );

	if ( ! $role ) {
		add_role( KOSMITEIA_ANNOUNCER_ROLE, __( 'Συντάκτης Ανακοινώσεων', 'kosmiteia' ), $announcer );
	} else {
		foreach ( $announcer as $cap => $grant ) {
			if ( ! $role->has_cap( $cap ) ) {
				$role->add_cap( $cap );
			}
		}

		foreach ( array_keys( $role->capabilities ) as $cap ) {
			if ( ! isset( $announcer[ $cap ] ) ) {
				$role->remove_cap( $cap );
			}
		}
	}

	foreach ( array( 'administrator', 'editor' ) as $slug ) {
		$existing = get_role( $slug );

		if ( ! $existing ) {
			continue;
		}

		foreach ( kosmiteia_announcement_caps( true ) as $cap ) {
			if ( ! $existing->has_cap( $cap ) ) {
				$existing->add_cap( $cap );
			}
		}
	}
}

function kosmiteia_maybe_update_roles() {
	if ( get_option( 'kosmiteia_roles_version' ) === KOSMITEIA_CORE_VERSION ) {
		return;
	}

	kosmiteia_register_roles();
	update_option( 'kosmiteia_roles_version', KOSMITEIA_CORE_VERSION );
}
add_action( 'init', 'kosmiteia_maybe_update_roles', 20 );

function kosmiteia_limit_media_library( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'upload' !== $screen->base ) {
		return;
	}

	if ( ! kosmiteia_user_is_announcer() ) {
		return;
	}

	$query->set( 'author', get_current_user_id() );
}
add_action( 'pre_get_posts', 'kosmiteia_limit_media_library' );

function kosmiteia_limit_media_modal( $args ) {
	if ( kosmiteia_user_is_announcer() ) {
		$args['author'] = get_current_user_id();
	}

	return $args;
}
add_filter( 'ajax_query_attachments_args', 'kosmiteia_limit_media_modal' );

function kosmiteia_user_is_announcer() {
	$user = wp_get_current_user();

	if ( ! $user || ! $user->exists() ) {
		return false;
	}

	return in_array( KOSMITEIA_ANNOUNCER_ROLE, (array) $user->roles, true ) && ! user_can( $user, 'edit_others_posts' );
}

function kosmiteia_announcer_dashboard_redirect() {
	if ( ! kosmiteia_user_is_announcer() ) {
		return;
	}

	$screen = get_current_screen();

	if ( $screen && 'dashboard' === $screen->base ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=kosm_announcement' ) );
		exit;
	}
}
add_action( 'current_screen', 'kosmiteia_announcer_dashboard_redirect' );

function kosmiteia_announcer_admin_cleanup() {
	if ( ! kosmiteia_user_is_announcer() ) {
		return;
	}

	remove_menu_page( 'index.php' );
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'kosmiteia_announcer_admin_cleanup', 999 );
