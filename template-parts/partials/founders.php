<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
if ( isset( $args['founders_section'] ) ) :
    $founders_section = $args['founders_section'];
    $section_heading = $founders_section['founders_section']['section_heading'];
    $section_description = $founders_section['founders_section']['section_description'];
    $section_icon = $founders_section['founders_section']['section_icon'];

    $args = array(
        'post_type' => 'founder',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );

    $founder_query = new WP_Query($args);

?>

<?php if (!empty($section_icon) || !empty($section_heading) || !empty($section_description)): ?>
    <div class="section-title">
        <?php if (!empty($section_icon)): ?>
            <?php echo $section_icon; ?>
        <?php endif; ?>
        <?php if (!empty($section_heading)): ?>
            <h2 class="section-title__headline"><?php echo esc_html($section_heading); ?></h2>
        <?php endif; ?>
        <?php if (!empty($section_description)): ?>
            <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div class="founders-grid" role="list">
    <?php if ($founder_query->have_posts()) : ?>
        <?php while ($founder_query->have_posts()) : $founder_query->the_post(); ?>
            <article class="founder-card" role="listitem">
                <div class="founder-card__image">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('600x560', array('alt' => 'Portrait of ' . get_the_title())); ?>
                    <?php endif; ?>
                </div>                      
                <div class="founder-card__content">
                    <h3 class="founder-card__name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                </div>
            </article>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    <?php endif; ?>
</div>

<?php endif; ?>