<?php get_header(); ?>
<main id="content" class="site-main founder-archive-content" role="main">
    <section class="founder-archive section" aria-labelledby="founder-archive-title">
        <div class="container container-1024">
            <div class="section-title">
                <i class="fa-classic fa-solid fa-flag-usa" aria-hidden="true"></i>
                <h1 id="founder-archive-title" class="section-title__headline">Meet Our Founders</h1>
                <p class="section-title__description">Get to know the visionaries behind it all—meet our founders and their inspiring stories.</p>
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
