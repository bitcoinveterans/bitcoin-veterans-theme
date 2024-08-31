<?php //Template Name: Blog ?>

<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="recent-posts section" aria-labelledby="hero-title">
        <div class="container container-1360">
            <div class="section-title">
                <div class="section-title__icon">
                    <i class="fa-classic fa-regular fa-comments" aria-hidden="true"></i>
                </div>
                <h1 class="section-title__headline">What's New</h1>
                <div class="section-subtitle"> 
                    <?php if (is_category()):  ?>
                        Category: <?php echo single_cat_title( '', false ); ?>
                    <?php elseif (is_tag()): ?>
                        Tag: <?php single_tag_title() ?>
                    <?php elseif (is_archive()): ?>
                        Archive: <?php the_time('F Y') ?>
                    <?php endif;?>
                </div>
            </div>
            <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                query_posts('post_type=post&paged='.$paged);
            ?>
            <ul class="recent-posts__grid">
                <?php 
                if ( have_posts() ) : 
                    while (have_posts()) : the_post();
                        get_template_part( 'template-parts/content' );
                    endwhile; 
                endif; 
                ?>
            </ul><!-- recent-posts__grid -->

            <?php if(function_exists('cc_starter_paging_nav')): ?>
            <div class="paginate paginate-bottom">
                <?php cc_starter_paging_nav(); ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
