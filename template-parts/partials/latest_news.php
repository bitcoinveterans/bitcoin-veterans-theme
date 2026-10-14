<?php
/**
 * latest News template
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
if ( isset( $args['latest_news_section'] ) ) :
    $latest_news_section = $args['latest_news_section'];
    $section_title = $latest_news_section['latest_news_section']['section_heading'];
    $section_description = $latest_news_section['latest_news_section']['section_description'];
    $section_icon = $latest_news_section['latest_news_section']['section_icon'];
endif;

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $query = new WP_Query($args);

?>
<?php if ($section_title || $section_description || $section_icon) : ?>
    <div class="section-title">
        <?php if ($section_icon): ?>
            <?php echo $section_icon; ?>
        <?php endif; ?>
        <?php if ($section_title) : ?>
            <h2 class="section-title__headline"><?php echo esc_html($section_title); ?></h2>
        <?php endif; ?>
        <?php if ($section_description) : ?>
            <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($query->have_posts()) : ?>
    <ul class="recent-posts__grid">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <li id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
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
                    <h3 class="post-card__title grit"><?php the_title(); ?></h3>
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