<?php
/**
 * Founders section layout
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $founders_section = array(
    'founders_section' => get_sub_field('founders_section'),
);

$args = array(
    'founders_section' => $founders_section,
);


?>
<section class="founders section" aria-labelledby="founders-title">
    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/founders', '', $args ); ?>
    </div>
</section>