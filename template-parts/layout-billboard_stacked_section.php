<?php
/**
 * Billboard stacked
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
$billboard_stacked = get_sub_field('billboard_stacked');
$billboard_stacked_section = array(
    'section_heading' => $billboard_stacked['section_heading'],
    'section_description' => $billboard_stacked['section_description'],
    'section_icon' => $billboard_stacked['section_icon'],
    'card' => array(
        'headline' => $billboard_stacked['card']['headline'],
        'description' => $billboard_stacked['card']['description'],
        'button' => $billboard_stacked['card']['button'],
    ),
    'card_image' => array(
        'image' => $billboard_stacked['card_image']['image'],
        'image_position' => $billboard_stacked['card_image']['image_position'],
    ),
);

$args = array(
    'billboard_stacked_section' => $billboard_stacked_section,
);
// echo '<pre>';
// 	print_r($billboard_stacked_section);
// echo '</pre>';
?>
<section class="billboard section">
<div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/billboard_stacked', '', $args );  ?>
    </div>
</section>
 