<?php
/**
 * Theme setup
 *
 * @package cc_starter
 * @since 1.0.0
 */


if ( ! function_exists( 'cc_starter_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function cc_starter_setup() {
		add_theme_support( 'title-tag' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails on posts and pages.
		// @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		add_theme_support( 'post-thumbnails' );
		add_image_size( '300x300', 300, 300, true );
		add_image_size( '600x560', 600, 560, true );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary'		=>	__( 'Primary Menu', 'cc_starter' )
		) );

		// Switch default core markup for search form, comment form, and comments to output valid HTML5.
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
	}
endif;
// cc_starter_setup.
add_action( 'after_setup_theme', 'cc_starter_setup' );

// Stop images getting wrapped up in p tags when they get dumped out with the_content() for easier theme styling
function cc_starter_remove_img_ptags($content){
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}
add_filter('the_content', 'cc_starter_remove_img_ptags');

/**
 * Removes specific submenu items from the Appearance menu in the WordPress admin.
 */


// Disable the theme editor functionality
function disable_theme_editor() {
    define('DISALLOW_FILE_EDIT', true);
}
add_action('init', 'disable_theme_editor');
function remove_menus_appearance_patterns(){  
    // Appearance > Patterns  
    remove_submenu_page('themes.php', 'site-editor.php?path=/patterns');
	remove_submenu_page('themes.php', 'customize.php');
	remove_action('admin_menu', '_add_themes_utility_last', 101);
}  
add_action('admin_menu', 'remove_menus_appearance_patterns');

