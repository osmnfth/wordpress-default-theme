<?php
/**
 * Kosmiteia — Child theme του Twenty Twenty-Five για Κοσμητείες Σχολών.
 *
 * @package Kosmiteia
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'KOSMITEIA_VERSION', '1.0.0' );
define( 'KOSMITEIA_DIR', get_stylesheet_directory() );
define( 'KOSMITEIA_URI', get_stylesheet_directory_uri() );

require_once KOSMITEIA_DIR . '/inc/setup.php';
require_once KOSMITEIA_DIR . '/inc/post-types.php';
require_once KOSMITEIA_DIR . '/inc/multilingual.php';
require_once KOSMITEIA_DIR . '/inc/announcements.php';
require_once KOSMITEIA_DIR . '/inc/breadcrumbs.php';
require_once KOSMITEIA_DIR . '/inc/blocks.php';
require_once KOSMITEIA_DIR . '/inc/gallery.php';
require_once KOSMITEIA_DIR . '/inc/media.php';
require_once KOSMITEIA_DIR . '/inc/seo.php';
