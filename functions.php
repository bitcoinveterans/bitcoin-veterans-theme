<?php
/*
 * Disallow direct access
 */
if( !defined( 'ABSPATH' ) ) {
    die( 'Forbidden.' );
}

/**
 * Theme Functions
 *
 * @package cc_starter
 * @since 1.0.0
 */

/**
 * Define constants.
 */
define( 'CC_STARTER_THEME_URI', get_template_directory_uri() );
define( 'CC_STARTER_THEME_DIR', get_template_directory() );
define( 'IMAGES', CC_STARTER_THEME_URI . '/assets/img' );
define( 'SCRIPTS', CC_STARTER_THEME_URI . '/assets/js' );
define( 'STYLES', CC_STARTER_THEME_URI . '/assets/css' );

// Theme setup.
require_once CC_STARTER_THEME_DIR . '/inc/theme-setup.php';

// include scripts-styles.
require_once CC_STARTER_THEME_DIR . '/inc/scripts-styles.php';

// include acf.
require_once CC_STARTER_THEME_DIR . '/inc/acf.php';

// include font awesome kit.
require_once CC_STARTER_THEME_DIR . '/inc/fa-setup-kit.php';

// include template-tags.
require_once CC_STARTER_THEME_DIR . '/inc/template-tags.php';

// include custom excerpt.
require_once CC_STARTER_THEME_DIR . '/inc/custom-excerpt.php';

// include extras.
require_once CC_STARTER_THEME_DIR . '/inc/extras.php';

// include widget areas.
//require_once CC_STARTER_THEME_DIR . '/inc/widget-areas.php';

// include widget areas.
require_once CC_STARTER_THEME_DIR . '/inc/cc-starter-comments.php';

// include podcast RSS feed importer.
require_once CC_STARTER_THEME_DIR . '/inc/podcast-feed.php';

// include ajax.
require_once CC_STARTER_THEME_DIR . '/inc/ajax.php';
