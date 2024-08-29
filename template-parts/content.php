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
        <h3 class="post-card__title"><?php the_title(); ?></h3>
        <div class="post-card__excerpt">
            <?php echo cc_get_excerpt(); ?>
        </div>
    </div>
    <footer class="post-card__footer">
        <a href="<?php the_permalink(); ?>" class="post-card__read-more">Read More</a>
    </footer>
</li>