<?php
/**
 * Archive template (categories/tags/dates), rebuilt per spec Phase 6 with the
 * same structure as search.php: container-1360 -> section-title (from
 * get_the_archive_title()) -> recent-posts__grid of content.php cards ->
 * pagination.
 *
 * @package cc_starter
 */

get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="recent-posts section" aria-labelledby="page-title">
        <div class="container container-1360">

            <div class="section-title">
                <h1 class="section-title__headline" id="page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
            </div>

            <?php if ( have_posts() ) : ?>

            <ul class="recent-posts__grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content' );
                endwhile;
                ?>
            </ul><!-- recent-posts__grid -->

            <div class="paginate paginate-bottom">
                <?php
                if ( function_exists( 'wp_pagenavi' ) ) {
                    wp_pagenavi();
                } elseif ( function_exists( 'cc_starter_paging_nav' ) ) {
                    cc_starter_paging_nav();
                }
                ?>
            </div>

            <?php endif; ?>

        </div><!-- container -->
    </section>

</main>

<?php get_footer(); ?>
