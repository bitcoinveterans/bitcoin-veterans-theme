<?php //Template Name: Page Full ?>
<?php get_header(); ?>
<?php if ( has_post_thumbnail($post->ID) ) {

    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'default-header' ); ?>

    <section class="banner-inner" style="background-image: url(<?php echo $image[0]; ?>);">


<?php } else { ?>

    <section class="banner-inner" style="background-image: url(<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/header-default.jpg);">

<?php } ?>
        <div class="module">

            <div class="page-title-wrap">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </div>

        </div><!--module-->
    </section><!-- banner -->

    <section class="group page-full">

        <div class="module">

            <div class="col-1">
                <?php while (have_posts()): the_post(); ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div><!-- entry-content -->

                <?php endwhile; ?>
            </div><!-- col-1 -->

         </div><!-- module -->
    </section><!-- page-cols -->

    <?php get_footer(); ?>
