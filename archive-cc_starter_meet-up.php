<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="page section" aria-labelledby="page-title">
        <div class="container container-1360">
            <?php
                $archive_podcast = get_field('archive_meetups', 'option');
                $page_title = $archive_podcast['page_title'];   
                $page_description = $archive_podcast['page_description'];
                $page_title_icon = $archive_podcast['page_title_icon'];

            ?>
            <div class="section-title">
                <?php if ($page_title_icon): ?>
                    <?php echo $page_title_icon; ?>
                <?php endif; ?>
                <?php if ($page_title): ?>
                    <h1 class="section-title__headline"><?php echo esc_html($page_title); ?></h1>
                <?php endif; ?>
                <?php if ($page_description): ?>
                    <p class="section-title__description"><?php echo esc_html($page_description); ?></p>
                <?php endif; ?>
            </div>

            <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $args = array(
                    'post_type' => 'cc_starter_meet-up',
                    'paged' => $paged,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'posts_per_page' => 20
                );
                query_posts($args);
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
            <ul class="meetups__list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'meetups' ); ?>
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
