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
	wp_enqueue_style( 'cc_starter-style', get_stylesheet_uri(), array(), filemtime( get_stylesheet_directory() . '/style.css' ) );

	// Fonts are self-hosted (assets/fonts/*.woff2 via @font-face in main.css); no Google Fonts request.
	wp_enqueue_style( 'cc_starter-master', CC_STARTER_THEME_URI . '/assets/css/main.css', array(), filemtime( CC_STARTER_THEME_DIR . '/assets/css/main.css' ) );



	// Load JS files

	wp_enqueue_script( 'cc_starter-custom', get_template_directory_uri() . '/assets/js/theme.min.js', array('jquery'), filemtime( get_template_directory() . '/assets/js/theme.min.js' ), true );

	wp_localize_script( 'cc_starter-custom', 'ajax_url', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_script( 'cc_starter-fontsmoothie', get_template_directory_uri() . '/assets/js/fontsmoothie.min.js', array('jquery'), '20140904', true );

	wp_enqueue_script( 'cc_starter-skiplink', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.min.js', array('jquery'), '20140974', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cc_starter_enqueue_styles_and_scripts', 999 );

// Preload the critical self-hosted webfonts early on every page.
function cc_starter_preload_fonts() {
	$fonts = array( 'barlow-400', 'black-ops-one-400' );
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" as="font" type="font/woff2" crossorigin href="%s">' . "\n",
			esc_url( get_template_directory_uri() . '/assets/fonts/' . $font . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'cc_starter_preload_fonts', 2 );

// Enqueue admin styles
function cc_starter_enqueue_admin_styles() {
    wp_enqueue_style('cc_starter-admin-styles', CC_STARTER_THEME_URI . '/assets/css/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'cc_starter_enqueue_admin_styles');