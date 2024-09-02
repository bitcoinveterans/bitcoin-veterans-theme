<?php
/**
 * Latest news section layout
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 $latest_news_section = array(
    'latest_news_section' => get_sub_field('latest_news_section'),
);

$args = array(
    'latest_news_section' => $latest_news_section,
);

?>

<section class="recent-posts section" aria-labelledby="recent-posts-title">
    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/latest_news', '', $args ); ?>
    </div>
</section>