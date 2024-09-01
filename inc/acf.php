<?php
/**
 * ACF Setup
 *
 * @package cc_starter
 * @since 1.0.0
 */

 // Save ACF JSON
 add_filter('acf/settings/save_json', 'my_acf_json_save_point');
 function my_acf_json_save_point( $path ) {
     // update path
     $path = get_stylesheet_directory() . '/acf-json';
     // return
     return $path;
 }
 
 // Load ACF JSON
 add_filter('acf/settings/load_json', 'my_acf_json_load_point');
function my_acf_json_load_point( $paths ) {
    // remove original path (optional)
    unset($paths[0]);
    // append path
    $paths[] = get_stylesheet_directory() . '/acf-json';
    // return
    return $paths;
}

// Sort the Flexible Content layouts alphabetically
add_filter('acf/load_field', 'sort_flexible_content_layouts');
function sort_flexible_content_layouts($field) {
    // Define an array of your Flexible Content field names
    $flexible_fields = array(
        'home_page_content_sections',
    );

    // Check if the current field is one of the Flexible Content fields
    if (in_array($field['name'], $flexible_fields)) {
        if (isset($field['layouts']) && is_array($field['layouts'])) {
            usort($field['layouts'], function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        }
    }

    return $field;
}

// Add ACF Button to TinyMCE
function my_custom_tinymce_plugin() {
    // Check if the user can edit posts or pages
    if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
        return;
    }
    // Check if the WYSIWYG is enabled
    if ( get_user_option( 'rich_editing' ) !== 'true' ) {
        return;
    }
    
    // Enqueue the TinyMCE plugin script
    add_filter( 'mce_external_plugins', 'add_my_tinymce_plugin' );
    // Add the button to the TinyMCE editor
    add_filter( 'mce_buttons', 'register_my_tinymce_button' );
}
add_action( 'admin_head', 'my_custom_tinymce_plugin' );

function add_my_tinymce_plugin( $plugin_array ) {
    $plugin_array['my_custom_button'] = get_template_directory_uri() . '/assets/js/custom-tinymce-plugin.js';
    return $plugin_array;
}

function register_my_tinymce_button( $buttons ) {
    array_push( $buttons, 'my_custom_button' );
    return $buttons;
}
