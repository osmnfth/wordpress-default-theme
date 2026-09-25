<?php
/**
 * Check that the "Kosmiteia Core" plugin is active.
 *
 * The theme handles the presentation; the plugin provides the content (content
 * types, blocks, filters, map, gallery). Without it, the templates would request
 * blocks that do not exist, so we provide the administrator with a clear
 * instruction instead of leaving them with blank pages.
 *
 * @package Kosmiteia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the "Kosmiteia Core" plugin active?
 *
 * @return bool
 */
function kosmiteia_core_is_active() {
	return defined( 'KOSMITEIA_CORE_VERSION' );
}

/**
 * Display a notice in the admin area when the plugin is missing.
 */
function kosmiteia_core_missing_notice() {
	if ( kosmiteia_core_is_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$plugins_url = admin_url( 'plugins.php' );

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Kosmiteia:', 'kosmiteia' ),
		esc_html__( 'The theme requires the "Kosmiteia Core" plugin for the content types and blocks.', 'kosmiteia' ),
		esc_url( $plugins_url ),
		esc_html__( 'Activate from Plugins', 'kosmiteia' )
	);
}
add_action( 'admin_notices', 'kosmiteia_core_missing_notice' );
