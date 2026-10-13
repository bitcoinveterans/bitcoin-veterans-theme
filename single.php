<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="page" aria-labelledby="page-title">
        <div class="container container-1360">

            <!-- <div class="section-title">
                <i class="fa-classic fa-regular fa-comments" aria-hidden="true"></i>     
                <h2 class="section-title__headline">What's New</h2>
            </div> -->
            
            <?php if (have_posts()):  while (have_posts()): the_post(); ?>

            <div class="entry-content">

                <div class="section-title-wrap">
                    <h1 class="page-title"><?php the_title(); ?></h1>
                </div>

                <?php the_content(); ?>

            </div><!-- entry-content -->

            <?php endwhile; ?>
            <?php endif; ?>

        </div><!-- container -->

    </section><!-- page -->

<?php get_footer(); ?>


 