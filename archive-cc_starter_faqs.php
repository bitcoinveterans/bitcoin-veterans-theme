<?php get_header(); ?>
<main id="content" class="site-main faqs-archive-content" role="main">

    <section class="page section" aria-labelledby="page-title">
        <div class="container container-1360">
            <?php
                $archive_faqs = get_field('archive_faqs', 'option');
                $page_title = $archive_faqs['page_title'];   
                $page_description = $archive_faqs['page_description'];
                $page_title_icon = $archive_faqs['page_title_icon'];
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

            <?php
                $faq_categories = get_terms(array(
                    'taxonomy' => 'faq_category',
                    'hide_empty' => true,
                    'orderby' => 'name', 
                    'order' => 'ASC'
                ));

                foreach ($faq_categories as $category) {
                    // Skip the "Home Page" category
                    if ($category->slug === 'home-page' || $category->slug === '101-page') {
                        continue;
                    }

                    $args = array(
                        'post_type' => 'cc_starter_faqs',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'faq_category',
                                'field' => 'term_id',
                                'terms' => $category->term_id,
                            ),
                        ),
                        'orderby' => 'menu_order',
                        'order' => 'ASC',
                        'posts_per_page' => -1
                    );

                    $faq_query = new WP_Query($args);

                    if ($faq_query->have_posts()) :
            ?>
                <div class="faq-category-wrapper">
                    <h2 class="faq-category-title grit"><?php echo esc_html($category->name); ?></h2>
                    <div class="accordion" id="faqAccordion-<?php echo esc_attr($category->slug); ?>">
                    <?php while ($faq_query->have_posts()) : $faq_query->the_post(); ?>
                        <details class="accordion-item details accordion">
                            <summary class="accordion-header">
                                <h3 class="heading summary"><?php the_title(); ?></h3>
                            </summary>
                            <div class="details__content">
                                <div class="entry-content">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </details>
                    <?php endwhile; ?>
                    </div>
                </div>
            <?php
                    endif;
                    wp_reset_postdata();
                }
            ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
