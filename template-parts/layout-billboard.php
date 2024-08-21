<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $billboard = array(
    'headline' => get_sub_field('headline'),
    'description' => get_sub_field('description'),
    'button' => get_sub_field('button'),
    'image' => get_sub_field('image'),
    'image_on_left' => get_sub_field('image_on_left')
);

$args = array(
    'billboard' => $billboard,
);

get_template_part( 'template-parts/partials/billboard', '', $args ); 

?>
 