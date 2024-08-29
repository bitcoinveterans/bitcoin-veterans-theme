<?php
/**
 * Image layout for accordian
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
$faqs_section = array(
    'section_heading' => get_sub_field('section_heading'),
    'section_description' => get_sub_field('section_description'),
    'section_icon' => get_sub_field('section_icon'),
	'faqs_section_options' => get_sub_field('faqs_section_options'),
);

$args = array(
    'faqs_section' => $faqs_section,
);

?>

<section class="faqs section" aria-labelledby="faqs-title">
<div class="container container-768">
        <?php get_template_part( 'template-parts/partials/faqs', '', $args ); ?>
    </div>
</section>