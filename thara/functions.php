<?php
/**
 * Thara Real Estate theme bootstrap.
 *
 * @package Thara
 */

defined( 'ABSPATH' ) || exit;

define( 'THARA_VERSION', '1.0.0' );
define( 'THARA_DIR', get_template_directory() );
define( 'THARA_URI', get_template_directory_uri() );

require THARA_DIR . '/inc/setup.php';
require THARA_DIR . '/inc/template-tags.php';
require THARA_DIR . '/inc/enqueue.php';
require THARA_DIR . '/inc/menus.php';
require THARA_DIR . '/inc/post-types.php';
require THARA_DIR . '/inc/customizer.php';
require THARA_DIR . '/inc/newsletter.php';
