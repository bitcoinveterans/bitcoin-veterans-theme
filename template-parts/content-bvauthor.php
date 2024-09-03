<?php
// Get all ACF fields
    $author_content = get_field('author_content', get_the_ID());
    $author_pages = get_field('author_pages', get_the_ID());
?>
<li id="post-<?php the_ID(); ?>" <?php post_class('bvauthor__item'); ?> role="article">
    <header class="post-card__header">
        <h3 class="post-card__title grit"><?php echo get_the_title(); ?></h3>
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
                    <li><a class="btn btn--icon-right" href="<?php echo $page['bv_author_link']['url']; ?>" target="<?php echo $page['bv_author_link']['target']; ?>">
                        <i class="fa-solid fa-link"></i>
                        <?php echo $page['bv_author_link']['title']; ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>
    </footer>
    <?php endif; ?>
</li>