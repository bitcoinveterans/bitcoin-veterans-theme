<?php
/**
 * Image layout for latest news
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$section_title = get_sub_field('section_title');
$section_description = get_sub_field('section_description');

// Query for the 3 most recent posts
$query = new WP_Query(array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC'
));

?>

<section class="recent-posts section" aria-labelledby="recent-posts-title">
    <div class="container container-1360">
        <?php if ($section_title) : ?>
        <div class="section-title-wrap">
                <?php if ( $section_title) : ?>
                    <h2 class="section-title"><?php echo esc_html( $section_title ); ?></h2>
                <?php endif; ?>
                <?php if ( $section_description) : ?>
                    <p class="section-desc"><?php echo esc_html( $section_description ); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($query->have_posts()) : ?>
            <ul class="recent-posts__grid">
                <?php
                    while ( $query->have_posts() ) : $query->the_post();
                ?>
                <li id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?> role="article">
                    <header class="post-card__header">
                        <div class="post-card__author">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <?php
                            $author_id = get_the_author_meta('ID');
                            $author_name = get_the_author();
                            $author_url = get_author_posts_url($author_id);
                            ?>
                            <?php echo esc_html($author_name); ?>
                        </div>
                    </header>
                    <div class="post-card__content">
                        <h3 class="post-card__title grit"><?php the_title(); ?>
                        </h3>
                        <div class="post-card__excerpt">
                            <?php echo cc_get_excerpt(); ?>
                        </div>
                    </div>
                    <footer class="post-card__footer">
                        <a href="<?php the_permalink(); ?>" class="post-card__read-more">Read More</a>
                    </footer>
                </li>
                
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</section>