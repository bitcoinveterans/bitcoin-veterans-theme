<?php //Template Name: BV Podcast ?>
<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="page section" aria-labelledby="page-title">
        <div class="container container-1360">
            <?php
                $page_title = get_field('page_title');
                $page_description =  get_field('page_description');
                $page_title_icon =  get_field('page_title_icon');
            ?>
            <div class="section-title">
                <?php if ($page_title_icon): ?>
                    <?php echo $page_title_icon; ?>
                <?php endif; ?>
                <?php if ($page_title): ?>
                    <h1 class="section-title__headline" id="page-title"><?php echo esc_html($page_title); ?></h1>
                <?php endif; ?>
                <?php if ($page_description): ?>
                    <p class="section-title__description"><?php echo esc_html($page_description); ?></p>
                <?php endif; ?>
            </div>

            <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                query_posts('post_type=cc_starter_podcast&paged='.$paged);
                $page_var = get_query_var('paged');
            ?>
            <?php if(function_exists('cc_starter_paging_nav')): ?>
            <div class="paginate paginate-top">
                <?php cc_starter_paging_nav(); ?>
            </div>
            <?php endif; ?>
            <?php
                if ( have_posts() ) :
            ?>
            <ul class="podcast-episodes__list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'podcast' ); ?>
            <?php endwhile; ?>
            </ul>
            <?php endif; ?>

            <?php if(function_exists('cc_starter_paging_nav')): ?>
            <div class="paginate paginate-bottom">
                <?php cc_starter_paging_nav(); ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php get_footer(); ?>
