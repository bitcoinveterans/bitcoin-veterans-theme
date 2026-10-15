<?php
// Get all ACF fields
    $author_content = get_field('author_content', get_the_ID());
    $author_pages = get_field('author_pages', get_the_ID());
?>
<li id="post-<?php the_ID(); ?>" <?php post_class('bvauthor__item'); ?>>
    <header class="post-card__header">
        <h3 class="post-card__title"><?php echo get_the_title(); ?></h3>
    </header>
    <div class="post-card__content">
        <div class="post-card__excerpt entry-content">
            <?php echo $author_content; ?>
        </div>
    </div>
    
    <?php if( !empty($author_pages) ): ?>
        <footer class="post-card__footer">
            <ul class="bv_author_link">
                <?php foreach ($author_pages as $page): ?>
                    <li><a class="btn btn--secondary btn--icon-right" href="<?php echo esc_url($page['bv_author_link']['url']); ?>" target="<?php echo esc_attr($page['bv_author_link']['target']); ?>">
                        <i class="fa-solid fa-link" aria-hidden="true"></i>
                        <?php echo esc_html($page['bv_author_link']['title']); ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>
    </footer>
    <?php endif; ?>
</li>
