<?php //Template Name: About ?>

<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">
    <section class="page section" aria-labelledby="page-title">
        <div class="container container-1360">
            <?php
            $page_title = get_field('page_title');
            $page_description =  get_field('page_description');
            $page_title_icon =  get_field('page_title_icon');

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
        </div>
    </section>
    <?php get_template_part( 'template-parts/loop', 'flex' ); ?>
</main>   
<?php get_footer(); ?>