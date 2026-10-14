<?php
/**
 * 404 template (spec Phase 6): "404" in display type btc, the message, the
 * search form, and a primary button back home.
 *
 * @package cc_starter
 */

get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="page section error-404" aria-labelledby="page-title">
        <div class="container container-768">

            <div class="section-title">
                <p class="error-404__code" aria-hidden="true">404</p>
                <h1 class="section-title__headline" id="page-title"><?php esc_html_e( 'Page Not Found', 'cc_starter' ); ?></h1>
                <p class="section-title__description"><?php esc_html_e( 'Please use the site navigation to get where you want to go.', 'cc_starter' ); ?></p>
            </div>

            <div class="error-404__search">
                <?php get_search_form(); ?>
            </div>

            <div class="error-404__actions">
                <a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'cc_starter' ); ?></a>
            </div>

        </div><!-- container -->
    </section>

</main>

<?php get_footer(); ?>
