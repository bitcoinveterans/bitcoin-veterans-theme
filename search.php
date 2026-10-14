<?php
/**
 * Search results template (spec Phase 6).
 *
 * Structure: container-1360 -> section-title ("Search results for ...") ->
 * search form -> recent-posts__grid of content.php cards -> pagination.
 * The empty state shows the form with a no-results message.
 *
 * @package cc_starter
 */

get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="recent-posts section" aria-labelledby="page-title">
        <div class="container container-1360">

            <div class="section-title">
                <h1 class="section-title__headline" id="page-title">
                    <?php printf( esc_html__( 'Search results for: %s', 'cc_starter' ), esc_html( get_search_query() ) ); ?>
                </h1>
            </div>

            <div class="search-results__form">
                <?php get_search_form(); ?>
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

            <?php else : ?>

            <div class="search-no-results">
                <p><?php esc_html_e( 'Nothing found. Try a different search.', 'cc_starter' ); ?></p>
            </div>

            <?php endif; ?>

            <?php if ( function_exists( 'cc_starter_paging_nav' ) ) : ?>
            <div class="paginate paginate-bottom">
                <?php cc_starter_paging_nav(); ?>
            </div>
            <?php endif; ?>

        </div><!-- container -->
    </section>

</main>

<?php get_footer(); ?>
