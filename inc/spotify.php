<?php
/**
 * A File for handling Spotify API calls
 *
 * @package cc_starter
 * @since 1.0.0
 */
function get_spotify_access_token() {
    $client_id = 'REDACTED_SPOTIFY_CLIENT_ID';
    $client_secret = 'REDACTED_SPOTIFY_CLIENT_SECRET';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
    ));

    $response = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($response);
    
    return $json->access_token;
}

function test_spotify_access_token() {
    $token = get_spotify_access_token();
    
    if ($token) {
        return 'Spotify Access Token: ' . esc_html($token);
    } else {
        return 'Failed to retrieve access token.';
    }
}
add_shortcode('test_spotify_token', 'test_spotify_access_token');


// Get All Episodes ////////////////////////////////////////////////////////
function get_spotify_show_episodes($show_id) {
    $access_token = get_spotify_access_token();

    $url = 'https://api.spotify.com/v1/shows/' . $show_id . '/episodes';
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return 'Error fetching episodes.';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    return $data->items; // Returns an array of episodes
}


// Shortcode to display all shows ////////////////////////////////////////////////////////
function display_spotify_show_episodes($atts) {
    $atts = shortcode_atts(array(
        'show_id' => '', // Provide the show ID here
        'page' => 1, // Default to first page
    ), $atts, 'spotify_episodes');

    if (empty($atts['show_id'])) {
        return 'Show ID is required.';
    }

    $page = max(1, intval($atts['page']));
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $access_token = get_spotify_access_token();
    $url = 'https://api.spotify.com/v1/shows/' . $atts['show_id'] . '/episodes?limit=' . $per_page . '&offset=' . $offset;
    
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return 'Error fetching episodes.';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (empty($data->items)) {
        return 'No episodes found.';
    }

    $episodes = $data->items;
    $total_episodes = $data->total;
    $total_pages = ceil($total_episodes / $per_page);

    // Function to generate pagination HTML
    function generate_pagination($page, $total_pages) {
        $output = '<div class="spotify-episodes-pagination">';
        if ($page > 1) {
            $output .= '<a href="' . add_query_arg('spotify_page', $page - 1) . '" class="prev-page">Previous</a> ';
        }
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                $output .= '<span class="current-page">' . $i . '</span> ';
            } else {
                $output .= '<a href="' . add_query_arg('spotify_page', $i) . '" class="page-number">' . $i . '</a> ';
            }
        }
        if ($page < $total_pages) {
            $output .= '<a href="' . add_query_arg('spotify_page', $page + 1) . '" class="next-page">Next</a>';
        }
        $output .= '</div>';
        return $output;
    }

    // Start building the output
    $output = '';

    // Add top pagination
    $output .= generate_pagination($page, $total_pages);

    $output .= '<ul class="spotify-episodes__list">';
    foreach ($episodes as $index => $episode) {
        // Calculate the correct episode number
        $episode_number = $total_episodes - ($offset + $index);
        $output .= '<li class="spotify-episodes__item">';
        $output .= '<i class="spotify-episodes__play-icon fa-regular fa-circle-play" aria-hidden="true"></i><span class="sr-only">Play Episode</span>';
        $output .= '<div class="spotify-episodes__details">';
        $output .= '<span class="spotify-episodes__number grit">' . sprintf('%02d', $episode_number) . '</span>';
        $output .= '<small class="spotify-episodes__date">' . date('F j, Y', strtotime($episode->release_date)) . '</small>';
        $output .= '<h3 class="spotify-episodes__title"><a href="' . esc_url($episode->external_urls->spotify) . '" class="spotify-episodes__play-button" target="_blank" aria-label="Play Episode: ' . esc_attr($episode->name) . '">' . esc_html($episode->name) . '</a></h3>';
        $output .= '<p class="spotify-episodes__description">' . esc_html(wp_trim_words($episode->description, 30, '...')) . '</p>';
        $output .= '</div>';
        $output .= '</li>';
    }
    $output .= '</ul>';

    // Add bottom pagination
    $output .= generate_pagination($page, $total_pages);

    return $output;
}
add_shortcode('spotify_episodes', 'display_spotify_show_episodes');

// Add this function to handle the page parameter
function spotify_episodes_query_vars($vars) {
    $vars[] = 'spotify_page';
    return $vars;
}
add_filter('query_vars', 'spotify_episodes_query_vars');