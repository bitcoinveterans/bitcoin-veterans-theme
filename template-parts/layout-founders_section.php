<?php
/**
 * Founders section layout
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $founders_section = array(
    'container_width' => get_sub_field('container_width'),
    'section_heading' => get_sub_field('section_heading'),
    'section_description' => get_sub_field('section_description'),
    'section_icon' => get_sub_field('section_icon'),
);

$args = array(
    'founders_section' => $founders_section,
);
$container_width = $founders_section['container_width'];

?>
<section class="founders section" aria-labelledby="founders-title">
    <div class="container <?php echo esc_attr($container_width); ?>">
        <?php get_template_part( 'template-parts/partials/founders', '', $args ); ?>
    </div>
</section>