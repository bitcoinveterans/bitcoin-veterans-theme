<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $hero_section = array(
    'headline' => get_sub_field('headline'),
    'description' => get_sub_field('description'),
    'button' => get_sub_field('button'),
    'image_1' => get_sub_field('image_1'),
    'image_2' => get_sub_field('image_2'),
    'image_3' => get_sub_field('image_3'),
);

$args = array(
    'hero_section' => $hero_section,
);
?>

<section class="hero section">
    <div class="module">
        <?php get_template_part( 'template-parts/partials/hero', '', $args ); ?>
    </div>
</section>