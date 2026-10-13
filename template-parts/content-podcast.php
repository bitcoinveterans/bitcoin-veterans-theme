<?php
// Get all ACF fields
    $episode_date = get_field('episode_date');
    if ($episode_date) {
        $episode_date = DateTime::createFromFormat('d/m/Y', $episode_date);
        $episode_date = $episode_date->format('F j, Y');
    }
    $episode_description = get_field('description');
    $episode_number = get_field('episode_number');
    $spotify_url = get_field('spotify_url');
    // Episodes imported from the RSS feed have no Spotify URL; fall back to the feed's episode link.
    $listen_url = $spotify_url ? $spotify_url : get_post_meta(get_the_ID(), 'episode_url', true);
?>
<li id="post-<?php the_ID(); ?>" <?php post_class('podcast-episodes__item'); ?> role="article">
    <i class="podcast-episodes__play-icon fa-regular fa-circle-play" aria-hidden="true"></i>
    <span class="sr-only">Play Episode</span>
    <div class="podcast-episodes__details">
        <?php if ($episode_number): ?>
            <span class="podcast-episodes__number grit"><?php echo esc_html($episode_number); ?></span>
        <?php endif; ?>
        <?php if ($episode_date): ?>
            <small class="podcast-episodes__date"><?php echo esc_html($episode_date); ?></small>
        <?php endif; ?>
        <h3 class="podcast-episodes__title grit">
            <?php if ($listen_url): ?>
                <a href="<?php echo esc_url($listen_url); ?>" class="podcast-episodes__play-button" target="_blank" aria-label="Play Episode: <?php the_title(); ?>"><?php the_title(); ?></a>
            <?php else: ?>
                <?php the_title(); ?>
            <?php endif; ?>
        </h3>
        <?php if ($episode_description): ?>
            <p class="podcast-episodes__description">
                
                <?php echo esc_html(wp_trim_words($episode_description, 30, '...')); ?>
            </p>
        <?php endif; ?>
        <?php if (function_exists('get_founders_images_list')): ?>
            <?php echo get_founders_images_list(); ?>
        <?php endif; ?>
    </div>
</li>