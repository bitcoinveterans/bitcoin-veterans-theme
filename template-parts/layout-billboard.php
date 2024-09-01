<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$billboard = array(
    'content' => get_field('content'),
    'media' => get_field('media'),
    'media_position' => get_field('media_position'),
);

$args = array(
    'billboard' => $billboard,
);
?>
<section class="billboard section">
<div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/billboard', '', $args );  ?>
    </div>
</section>
 