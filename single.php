<?php get_header(); ?>
<section class="banner-inner" style="background-image: url(<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/header-default.jpg);">
    <div class="module">

        <div class="page-title-wrap">
            <h2 class="page-title">What's New</h2>
            <p>Discover the newest happenings and developments.</p>
        </div>

    </div><!--module-->
</section><!-- banner -->
    <section class="page-one-cols">

        <div class="module">

            <div class="col-1">
                <?php if (have_posts()):  while (have_posts()): the_post(); ?>
                <div class="section-title-wrap">
                    <h1 class="page-title"><?php the_title(); ?></h1>
                </div>
                

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- entry-content -->
                <?php endwhile; ?>
                <?php endif; ?>
            </div><!-- col-1 -->

            <?php coplan_2023_post_nav(); ?>
        </div><!-- module -->

    </section><!-- group -->
    <?php get_footer(); ?>
