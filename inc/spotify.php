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


// Import from Spotify to CPT ////////////////////////////////////////////////////////
function import_spotify_episodes() {
    $show_id = '1AgitYhJScVcW4YpQ5wwya'; 
    $access_token = get_spotify_access_token();
    $episodes = [];
    $offset = 0;
    $limit = 50;

    do {
        $url = 'https://api.spotify.com/v1/shows/' . $show_id . '/episodes?limit=' . $limit . '&offset=' . $offset;
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            error_log('Error fetching Spotify episodes: ' . $response->get_error_message());
            break;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (!empty($data->items)) {
            $episodes = array_merge($episodes, $data->items);
            $offset += count($data->items);
        } else {
            break;
        }
    } while (count($data->items) == $limit);

    // Reverse the array to get oldest episodes first
    $episodes = array_reverse($episodes);

    // Import episodes
    foreach ($episodes as $index => $episode) {
        $episode_number = $index + 1;

        // Check if episode already exists
        $existing_post = get_posts(array(
            'post_type' => 'cc_starter_podcast',
            'meta_key' => 'spotify_episode_id',
            'meta_value' => $episode->id,
            'posts_per_page' => 1,
        ));

        if (empty($existing_post)) {
            // Create new post
            $post_id = wp_insert_post(array(
                'post_title' => $episode->name,
                'post_type' => 'cc_starter_podcast',
                'post_status' => 'publish',
            ));

            if (!is_wp_error($post_id)) {
                // Set ACF fields
                update_field('episode_date', $episode->release_date, $post_id);
                update_field('episode_number', $episode_number, $post_id);
                update_field('description', $episode->description, $post_id);
                update_field('spotify_url', $episode->external_urls->spotify, $post_id);
                update_field('spotify_episode_id', $episode->id, $post_id);

                // Set the Spotify episode ID as post meta for future reference
                // update_post_meta($post_id, 'spotify_episode_id', $episode->id);

                // Set the post's published date to match the Spotify episode release date
                $post_date = date('Y-m-d H:i:s', strtotime($episode->release_date));
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_date' => $post_date,
                    'post_date_gmt' => get_gmt_from_date($post_date)
                ));

                error_log("Imported new episode: " . $episode->name);
            } else {
                error_log("Failed to import episode: " . $episode->name);
            }
        } else {
            // Episode already exists, you could update it here if needed
            error_log("Episode already exists: " . $episode->name);
        }
    }
}

// Schedule daily import
function schedule_spotify_episode_import() {
    if (!wp_next_scheduled('import_spotify_episodes_hook')) {
        wp_schedule_event(time(), 'daily', 'import_spotify_episodes_hook');
    }
}
add_action('wp', 'schedule_spotify_episode_import');

// Hook for the scheduled event
add_action('import_spotify_episodes_hook', 'import_spotify_episodes');

// Run import manually (you can remove this after initial import)
add_action('init', 'import_spotify_episodes');






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
// add this  [spotify_episodes show_id="1AgitYhJScVcW4YpQ5wwya"] 

// Add this function to handle the page parameter
function spotify_episodes_query_vars($vars) {
    $vars[] = 'spotify_page';
    return $vars;
}
add_filter('query_vars', 'spotify_episodes_query_vars');