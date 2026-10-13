<?php
// Get all ACF fields
    $meetup_url = get_field('url');
    $location_details = get_field('location_details');
    $city = $location_details['city'];
    $country = $location_details['country'];

    $meetup_state = get_the_terms($post->ID, 'meetup-state');
    $state_term = $meetup_state ? $meetup_state[0]->name : '';
    $location_output = $city . ', ' . $state_term;
    if ($country && $country !== 'United States') {
        $location_output .= ', ' . $country;
    }

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
        <?php if ($location_output): ?>
            <small class="meetup__location"><?php echo esc_html($location_output); ?></small>
        <?php endif; ?>
    </div>
</li>