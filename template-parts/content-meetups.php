<?php
// Get all ACF fields
    $meetup_url = get_field('url');
?>
<li id="post-<?php the_ID(); ?>" <?php post_class('meetups__item'); ?> role="article">
    <span class="sr-only">Play Episode</span>
    <div class="meetups__details">
        <h3 class="meetups__title grit">
            <?php if ($meetup_url): ?>
                <a href="<?php echo esc_url($meetup_url); ?>" class="" target="_blank" aria-label="<?php the_title(); ?>"><?php the_title(); ?></a>
            <?php else: ?>
                <?php the_title(); ?>
            <?php endif; ?>
        </h3>
    </div>
</li>