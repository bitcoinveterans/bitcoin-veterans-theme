<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$billboard_group = get_sub_field('billboard_traditional_group');
$billboard_section = array(
    'content' => array(
        'headline' => $billboard_group['headline'],
        'sub_headline' => $billboard_group['sub_headline'],
        'description' => $billboard_group['description'],
        'button' => $billboard_group['button'],
    ),
    'media' => $billboard_group['card_media']['media'],
    'media_position' => $billboard_group['card_media']['image_position'],
);

$args = array(
    'billboard_section' => $billboard_section,
);
// echo '<pre>billboard_group ';
// 	print_r($billboard_section);
// echo '</pre>';
?>
<section class="billboard section">
<div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/billboard', '', $args );  ?>
    </div>
</section>
 