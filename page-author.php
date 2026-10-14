<?php //Template Name: BV Authors ?>
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
                    <h1 class="section-title__headline" id="page-title"><?php echo esc_html($page_title); ?></h1>
                <?php endif; ?>
                <?php if ($page_description): ?>
                    <p class="section-title__description"><?php echo esc_html($page_description); ?></p>
                <?php endif; ?>
            </div>

            <?php 
                    $args = array(
                        'post_type'      => 'bv-author',
                        'posts_per_page' => -1, 
                        'orderby'       => 'menu_order',
                        'order'         => 'ASC'
						);
					$query = new WP_Query( $args );
					if ( $query->have_posts() ) :
                    
            ?>
        
            <ul class="bvauthor__list">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php get_template_part( 'template-parts/content', 'bvauthor' ); ?>
                <?php endwhile; ?>
            </ul>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
