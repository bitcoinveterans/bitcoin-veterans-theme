<?php
/**
 * Ajax
 *
 * @package cc_starter
 * @since 1.0.0
 */

// Register AJAX actions
add_action('wp_ajax_get_all_meetup_state_terms', 'retrieve_meetup_state_terms');
add_action('wp_ajax_nopriv_get_all_meetup_state_terms', 'retrieve_meetup_state_terms');


function retrieve_meetup_state_terms() {
    // Define the taxonomy name
    $taxonomy = 'meetup-state'; // Ensure this matches the registered taxonomy
    
    // Retrieve terms
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    // Check for errors
    if (is_wp_error($terms)) {
        wp_send_json_error($terms->get_error_message());
        return;
    }
    
    // Prepare terms for response
    $state_names = array();
    foreach ($terms as $term) {
        $state_names[] = $term->name;
    }

    // Send JSON response
    wp_send_json_success($state_names);
}


