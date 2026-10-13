<?php get_header(); ?>
<main id="content" class="site-main page-content" role="main">

    <section class="page section" aria-labelledby="page-title">
        <div class="container container-1360">
            <?php
                $archive_podcast = get_field('archive_meetups', 'option');
                $page_title = $archive_podcast['page_title'];   
                $page_description = $archive_podcast['page_description'];
                $page_title_icon = $archive_podcast['page_title_icon'];

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
                $selected_state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $args = array(
                    'post_type' => 'cc_starter_meet-up',
                    'paged' => $paged,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'posts_per_page' => -1
                );
                // If a state is selected, add taxonomy query
                if ($selected_state && $selected_state !== 'all') {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'meetup-state',
                            'field' => 'name',
                            'terms' => $selected_state,
                        ),
                    );
                }

                $query = new WP_Query($args);
            ?>
            <!-- State Filter Input -->
            <div class="meetup-filter">
                <label for="state-search">Filter by State:</label>
                <select id="state-select" class="select">
                    <option value="">Select a state...</option>
                </select>
            </div>
            
            <?php
                if ( $query->have_posts() ) :
            ?>
            <ul id="meetup-list" class="meetups__list meetups__grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'meetups' ); ?>
            <?php endwhile; ?>
            </ul>
            <?php endif; ?>

            <?php if(function_exists('cc_starter_paging_nav')): ?>
            <div class="paginate paginate-bottom">
                <?php //cc_starter_paging_nav(); ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php get_footer(); ?>
