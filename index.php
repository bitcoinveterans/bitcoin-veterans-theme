
<?php get_header(); ?>
<?php
$image = has_post_thumbnail($post->ID) ? wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'default-header' ) : false;
if ( $image ) { ?>

    <section class="banner-inner" style="background-image: url(<?php echo esc_url( $image[0] ); ?>);">


<?php } else { ?>

    <section class="banner-inner" style="background-image: url(<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/header-default.jpg);">

<?php } ?>
        <div class="module">

            <div class="page-title-wrap">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </div>

        </div><!--module-->
    </section><!-- banner -->

    <main class="main-content flex-loop" id="content" role="main">
        <?php get_template_part( 'template-parts/loop', 'flex' ); ?>
    </main>

    <?php get_footer(); ?>
