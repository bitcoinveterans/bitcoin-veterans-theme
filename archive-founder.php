<?php get_header(); ?>
<main id="content" class="site-main founder-archive-content" role="main">
    <section class="founder-archive section" aria-labelledby="founder-archive-title">
        <div class="container container-1024">
            <?php
                $archive_founders = get_field('archive_founders', 'option');
                $page_title = $archive_founders['page_title'];   
                $page_description = $archive_founders['page_description'];
                $page_title_icon = $archive_founders['page_title_icon'];

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
                    $args = array(
                        'post_type'      => 'founder',
                        'posts_per_page' => -1, 
                        'orderby'       => 'menu_order',
                        'order'         => 'ASC'
						);
					$query = new WP_Query( $args );

					if ( $query->have_posts() ) :
                    
            ?>
        
            <div class="founders-grid" role="list">
                <?php while ($query->have_posts()) : $query->the_post();  ?>
                    <article class="founder-card" role="listitem">
                        <div class="founder-card__image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('full', array('class' => 'founder-image__img', 'alt' => get_the_title())); ?>
                            <?php endif; ?>
                        </div>
                        <div class="founder-card__content">
                            <h2 class="founder-card__name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <?php the_posts_pagination(); ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
</main>
<?php get_footer(); ?>
