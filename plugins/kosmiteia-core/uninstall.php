<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$kosmiteia_options = array(
	'kosmiteia_settings',
	'kosmiteia_core_version',
	'kosmiteia_rewrites_version',
	'kosmiteia_roles_version',
	'kosmiteia_demo_version',
	'kosmiteia_announcement_years',
);

foreach ( $kosmiteia_options as $kosmiteia_option ) {
	delete_option( $kosmiteia_option );
}

delete_transient( 'kosmiteia_announcement_years' );

remove_role( 'kosmiteia_announcer' );
