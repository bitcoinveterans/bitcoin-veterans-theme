<?php
/**
 * Custom Excerpt 
 *
 * @package cc_starter
 * @since 1.0.0
 */

function cc_excerpt_length_long( $length ) {
    return 45;
}

function cc_excerpt_length_short( $length ) {
    return 12;
}

function cc_excerpt_more( $more ) {
    return ' <a href="' . get_permalink() . '">Read More</a>';
}

function cc_get_excerpt( $length_callback = 'cc_excerpt_length_short', $more_callback = 'cc_excerpt_more' ) {
    ob_start();

    if ( function_exists( $length_callback ) ) {
        add_filter( 'excerpt_length', $length_callback );
    }
	
    if ( function_exists( $more_callback ) ) {
        add_filter( 'excerpt_more', $more_callback );
    }

    the_excerpt();

    // Remove filters after use to prevent affecting other parts of the theme or plugins
    remove_filter( 'excerpt_length', $length_callback );
    remove_filter( 'excerpt_more', $more_callback );

    return ob_get_clean();
}