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

function cc_get_excerpt( $post_id = null, $length_callback = 'cc_excerpt_length_long', $more_callback = 'cc_excerpt_more' ) {
    // If no post ID is provided, use the current post in the loop
    if ( $post_id === null ) {
        $post_id = get_the_ID();
    }

    // Get the post object
    $post = get_post( $post_id );

    if ( ! $post ) {
        return '';
    }

    // Set up the excerpt
    $excerpt = $post->post_excerpt;
    if ( '' === $excerpt ) {
        $excerpt = $post->post_content;
    }

    // Apply excerpt_length filter
    if ( function_exists( $length_callback ) ) {
        add_filter( 'excerpt_length', $length_callback );
    }

    // Apply excerpt_more filter
    if ( function_exists( $more_callback ) ) {
        add_filter( 'excerpt_more', $more_callback );
    }

    // Generate and trim the excerpt
    $excerpt = wp_trim_words( $excerpt, apply_filters( 'excerpt_length', 55 ), apply_filters( 'excerpt_more', ' [&hellip;]' ) );

    // Remove filters after use to prevent affecting other parts of the theme or plugins
    remove_filter( 'excerpt_length', $length_callback );
    remove_filter( 'excerpt_more', $more_callback );

    return $excerpt;
}