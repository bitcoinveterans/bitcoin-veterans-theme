<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$args = array(
	'text_editor' => get_sub_field( 'text_editor' ),
); ?>

<section class="acf-text-editor section" aria-label="Text Content">
	<div class="container container-768">
		<?php get_template_part( 'template-parts/partials/text_editor', '', $args ); ?>
	</div>
</section>