<?php
/**
 * Layout for partners section
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $partners_section = array(
    'section_description' => get_sub_field('section_description'),
    'partner_button' => get_sub_field('partner_button'),
);

$args = array(
    'partners_section' => $partners_section,
);


?>
<section class="partners section" aria-labelledby="partners-title">
<div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/partners', '', $args ); ?>
    </div>
</section>