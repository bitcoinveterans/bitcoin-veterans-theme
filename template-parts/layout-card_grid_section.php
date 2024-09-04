<?php
/**
 * Card grid section layout
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $card_grid_section = array(
    'section_heading' => get_sub_field('section_heading'),
    'section_description' => get_sub_field('section_description'),
    'section_icon' => get_sub_field('section_icon'),
    'card_grid_icon_card_group' => get_sub_field('card_grid_icon_card_group'),
);

$args = array(
    'card_grid_section' => $card_grid_section,
);

?>
<section class="content-grid section" aria-labelledby="content-grid-title">
<div class="container container-1360 gradient-noise">
        <?php get_template_part( 'template-parts/partials/card_grid', '', $args ); ?>
    </div>
</section>