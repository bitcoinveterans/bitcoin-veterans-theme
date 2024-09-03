<?php
/**
 * Layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $text_editor_group = get_sub_field('text_editor_group');
 $text_editor_section = array(
    'section_heading' => $text_editor_group['section_heading'],
    'section_description' => $text_editor_group['section_description'],
    'section_icon' => $text_editor_group['section_icon'],
    'text_editor' => $text_editor_group['text_editor'],
);

$args = array(
    'text_editor_section' => $text_editor_section,
);
// echo '<pre>text_editor_section ';
// 	print_r($text_editor_section);
// echo '</pre>';
?>

<section class="acf-text-editor section" aria-label="Text Content">
	<div class="container container-768">
		<?php get_template_part( 'template-parts/partials/text_editor', '', $args ); ?>
	</div>
</section>