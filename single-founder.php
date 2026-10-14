<?php get_header(); ?>
<main id="content" class="site-main founder-single-content" role="main">

    <section class="founder-single section" aria-labelledby="founder-single-title">
        <div class="container container-996">
            <?php if (have_posts()):  while (have_posts()): the_post(); ?>
                <div class="founder-content-wrapper">
                    <div class="founder-image-column">
                        <?php if (has_post_thumbnail()): ?>
                            <figure class="founder-image">
                                <?php the_post_thumbnail('full', array('class' => 'founder-image__img', 'alt' => get_the_title())); ?>
                            </figure>
                        <?php endif; ?>
                    </div>
                    <div class="founder-info-column">
                        <div class="section-title">
                            <h1 class="section-title__headline" id="founder-single-title"><?php the_title(); ?></h1>
                            <?php if (have_rows('social_media_account')): ?>
                                <ul class="founder-social-links">
                                    <li class="founder-social-links__follow">Follow: </li>
                                    <?php while (have_rows('social_media_account')): the_row(); 
                                        $social_icon = get_sub_field('social_icon');
                                        $social_url = get_sub_field('social_url');
                                        $aria_label = get_sub_field('aria_label');
                                    ?>
                                        <li class="founder-social-links__item">
                                            <a href="<?php echo esc_url($social_url); ?>" class="founder-social-links__link" aria-label="<?php echo esc_attr($aria_label); ?>" target="_blank" rel="noopener noreferrer">
                                                <i class="<?php echo esc_attr($social_icon); ?>" aria-hidden="true"></i>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div><!-- entry-content -->
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
            <?php cc_starter_post_nav(); ?>
            <div class="founder-all-link">
                <a href="<?php echo esc_url(home_url('/founder')); ?>" class="btn btn--secondary btn--icon-right">
                    <span class="btn__icon" aria-hidden="true">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                    <span class="btn-text">View All Founders</span>
                </a>
            </div>
        </div>
    </section>
    <?php get_footer(); ?>
