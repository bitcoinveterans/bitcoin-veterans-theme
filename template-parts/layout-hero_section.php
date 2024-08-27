<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $hero_section = array(
    'headline_is_h1' => get_sub_field('headline_is_h1'),
    'headline' => get_sub_field('headline'),
    'description' => get_sub_field('description'),
    'hero_button_1' => get_sub_field('hero_button_1'),
    'hero_button_2' => get_sub_field('hero_button_2'),
    'hero_button_3' => get_sub_field('hero_button_3'),
);

$args = array(
    'hero_section' => $hero_section,
);

?>
<section class="hero section" aria-labelledby="hero-title">
    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/hero', '', $args ); ?>
    </div>
</section>