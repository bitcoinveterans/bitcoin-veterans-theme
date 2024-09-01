<?php
/**
 * File that handles loading and hooking of scripts and styles
 *
 * @package cc_starter
 * @since 1.0.0
 */

// Enqueue scripts and styles.
function cc_starter_enqueue_styles_and_scripts() {
	
	// Load the stylesheets
	wp_enqueue_style( 'cc_starter-style', get_stylesheet_uri() );

	wp_enqueue_style( 'cc_starter-googlefonts', '//fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,500;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Phudu:wght@500;700;900&display=swap' );

	wp_enqueue_style( 'cc_starter-master', CC_STARTER_THEME_URI . '/assets/css/main.css' );


	// Load JS files

	wp_enqueue_script( 'cc_starter-custom', get_template_directory_uri() . '/assets/js/theme.min.js', array('jquery'), '20140125', true );

	wp_enqueue_script( 'cc_starter-fontsmoothie', get_template_directory_uri() . '/assets/js/fontsmoothie.min.js', array('jquery'), '20140904', true );

	wp_enqueue_script( 'cc_starter-skiplink', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.min.js', array('jquery'), '20140974', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// wp_enqueue_script( 'cc_starter-custom-tinymce-plugin', get_template_directory_uri() . '/assets/js/custom-tinymce-plugin.js', array('jquery'), '20140125', true );
}
add_action( 'wp_enqueue_scripts', 'cc_starter_enqueue_styles_and_scripts', 999 );

// Enqueue admin styles
function cc_starter_enqueue_admin_styles() {
    wp_enqueue_style('cc_starter-admin-styles', CC_STARTER_THEME_URI . '/assets/css/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'cc_starter_enqueue_admin_styles');