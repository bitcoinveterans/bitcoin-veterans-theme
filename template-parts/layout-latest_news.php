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

<section class="latest-news section">
    <div class="module">
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
            <ul class="posts">
                <?php
                    while ( $query->have_posts() ) : $query->the_post();
                ?>
                <li id="post-<?php the_ID(); ?>" <?php post_class('card-post'); ?> role="article">
                <?php if (has_post_thumbnail()) : ?>
                    <header class="card-header">
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><figure><?php the_post_thumbnail('300x300'); ?></figure></a>
                    </header>
                <?php endif; ?>
                    <div class="card-body">
                        <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                    </div>
                    <footer class="card-footer">
                        <a href="<?php the_permalink(); ?>" role="button" aria-label="Read More"class="button">Read More</a>
                    </footer>
                </li>
                
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <?php wp_reset_postdata(); // Reset the post data ?>
    </div>
</section>