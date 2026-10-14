<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="page section" aria-labelledby="page-title">
        <div class="container container-1360">

            <?php if (have_posts()):  while (have_posts()): the_post(); ?>

            <article <?php post_class('single-article'); ?>>

                <header class="post-header">
                    <p class="post-meta">
                        <time class="post-meta__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                        <span class="post-meta__sep" aria-hidden="true">//</span>
                        <span class="post-meta__author"><?php the_author(); ?></span>
                    </p>
                    <div class="section-title-wrap">
                        <h1 class="page-title" id="page-title"><?php the_title(); ?></h1>
                    </div>
                </header>

                <div class="entry-content">

                    <?php the_content(); ?>

                </div><!-- entry-content -->

            </article>

            <?php endwhile; ?>
            <?php endif; ?>

            <?php get_template_part( 'template-parts/partials/post', 'prev-next' ); ?>

        </div><!-- container -->

    </section><!-- page -->

</main>

<?php get_footer(); ?>

