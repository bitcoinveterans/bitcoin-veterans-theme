<?php
/**
 * Accordion layout
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
 // This file should be renamed to layout-accordion_group.php

 $accordion_group = get_sub_field('accordion_group');
 $accordion_section = array(
    'section_heading' => $accordion_group['section_heading'],
    'section_description' => $accordion_group['section_description'],
    'section_icon' => $accordion_group['section_icon'],
    'accordion_list' => $accordion_group['accordion_list'],
);

$args = array(
    'accordion_section' => $accordion_section,
);

?>

<section class="accordion-list section">
    <div class="container container-1360">
        <div class="section-title">
            <?php if (!empty($accordion_section['section_icon'])): ?>
                <?php echo wp_kses_post($accordion_section['section_icon']); ?>
            <?php endif; ?>
            <?php if (!empty($accordion_section['section_heading'])): ?>
                <h2 class="section-title__headline"><?php echo esc_html($accordion_section['section_heading']); ?></h2>
            <?php endif; ?>
            <?php if (!empty($accordion_section['section_description'])): ?>
                <p class="section-title__description"><?php echo esc_html($accordion_section['section_description']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="container container-768">
        <?php get_template_part( 'template-parts/partials/accordion', '', $args ); ?>
    </div>
</section>