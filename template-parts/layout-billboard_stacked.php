<?php
/**
 * Image layout for text editor
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$billboard_stacked = array(
    'billboard_stacked' => get_field('billboard_stacked'),
);

$args = array(
    'billboard' => $billboard_stacked,
);
?>
<section class="billboard section">
<div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/billboard_stacked', '', $args );  ?>
    </div>
</section>
 