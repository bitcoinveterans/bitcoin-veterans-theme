<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
if ( isset( $args['partners_section'] ) ) :
    [
        'section_description' => $section_description,
        'partner_button' => $partner_button
    ] = $args['partners_section'];

    if ( $partner_button && isset($partner_button['button_label']) && isset($partner_button['button_link']) && isset($partner_button['button_icon_group']) ) :
        $partner_button_label = $partner_button['button_label'];
        $partner_button_link = $partner_button['button_link']['url'];
        $partner_button_target = $partner_button['button_link']['target'];
        $partner_button_icon = $partner_button['button_icon_group']['button_icon'];
        $partner_button_icon_position = $partner_button['button_icon_group']['icon_position'];
    endif;

    $icon_position = 'left';

    if ($partner_button && isset($partner_button['button_icon_group']['icon_position'])) {
        $icon_position = $partner_button['button_icon_group']['icon_position'] === 'icon-right' ? 'right' : 'left';
    }


    $args = array(
        'post_type' => 'partner',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );

    $partner_query = new WP_Query($args);

?>

<?php if ($section_description): ?>
    <div class="section-title">
        <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
    </div>
<?php endif; ?>

<?php if ($partner_query->have_posts()) : ?>
    <ul class="partners-logo-list">
    <?php while ($partner_query->have_posts()) : $partner_query->the_post(); ?>
        <?php
        $partner_logo = get_field('partner_logo');
        $partner_url = get_field('partner_url');
        if ($partner_logo) :
            $logo_url = $partner_logo['url'];
            $logo_alt = $partner_logo['alt'];
        ?>
        <li class="logo-plate">
            <?php if ($partner_url) : ?>
                <a href="<?php echo esc_url($partner_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $logo_alt ? $logo_alt : wp_parse_url( $partner_url, PHP_URL_HOST ) ); ?>">
            <?php endif; ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" class="partner-logo">
            <?php if ($partner_url) : ?>
                </a>
            <?php endif; ?>
        </li>
        <?php endif; ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>
</ul>
<?php endif; ?>

<?php if (isset($partner_button) && !empty($partner_button) && isset($partner_button['button_label']) && isset($partner_button['button_link'])) : ?>
    <?php if ($partner_button_link && $partner_button_label): ?>
    <div class="partners-cta">
        <a href="<?php echo esc_url($partner_button_link); ?>" 
           class="btn btn--secondary <?php echo $icon_position === 'right' ? 'btn--icon-right' : ''; ?>" 
           <?php echo $partner_button_target ? 'target="' . esc_attr($partner_button_target) . '"' : ''; ?>>
            <?php if ($partner_button_icon): ?>
                <span class="btn__icon" aria-hidden="true">
                    <?php echo ($partner_button_icon); ?>
                </span>
            <?php endif; ?>
            <span class="btn__text"><?php echo esc_html($partner_button_label); ?></span>
        </a>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php endif; ?>