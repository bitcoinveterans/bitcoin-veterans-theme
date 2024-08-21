<?php //Template Name: Blog ?>

<?php get_header(); ?>
    <section class="banner-inner" style="background-image: url(<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/header-default.jpg);">
        <div class="module">

            <div class="page-title-wrap">
                <h1 class="page-title">What's New</h1>
                <div class="page-sub-title">
                    <?php if (is_category()):  ?>
                        Category: <?php echo single_cat_title( '', false ); ?>
                    <?php elseif (is_tag()): ?>
                        Tag: <?php single_tag_title() ?>

                    <?php elseif (is_archive()): ?>
                        Archive: <?php the_time('F Y') ?>

                    <?php else: ?>
                    <?php endif;?>
                </div>
            </div>

        </div><!--module-->
    </section><!-- banner -->

    <main class="main-content flex-loop group" id="content" role="main">
        <section class="module">
            <div class="blog-grid">

                <?php
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                    query_posts('post_type=post&paged='.$paged);
                    $page_var = get_query_var('paged');
                    if ( have_posts() ) : while (have_posts()) : the_post();
                ?>
                <?php get_template_part( 'template-parts/content' ); ?>

                <?php endwhile; ?>
            </div><!-- blog-grid -->
                <?php endif; ?>

            <?php if(function_exists('cc_non_paging_nav')): ?>
            <div class="paginate">
                <?php cc_non_paging_nav(); ?>
            </div>
            <?php endif; ?>

        </section>
    </main>
	<?php get_footer(); ?>
