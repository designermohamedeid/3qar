<?php
/**
 * Dara Real Estate theme.
 *
 * @package Dara
 */

defined( 'ABSPATH' ) || exit;

define( 'DARA_VERSION', '1.3.0' );
define( 'DARA_DIR', get_template_directory() );
define( 'DARA_URI', get_template_directory_uri() );

require DARA_DIR . '/inc/setup.php';
require DARA_DIR . '/inc/icons.php';
require DARA_DIR . '/inc/template-tags.php';
require DARA_DIR . '/inc/customizer.php';
require DARA_DIR . '/inc/assets.php';
require DARA_DIR . '/inc/performance.php';
require DARA_DIR . '/inc/menus.php';
require DARA_DIR . '/inc/compat.php';
require DARA_DIR . '/inc/blocks.php';
