<?php
/**
 * Image layout for card grid
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['card_grid_section'] ) ) :
    [
        'section_heading' => $section_heading,
        'section_description' => $section_description,
        'section_icon' => $section_icon,
        'card_grid_icon_card_group' => $card_grid_icon_card_group,
    ] = $args['card_grid_section'];
?>
    <div class="section-title">
        <?php if (!empty($section_icon)): ?>
            <?php echo wp_kses_post($section_icon); ?>
        <?php endif; ?>
        <?php if (!empty($section_heading)): ?>
            <h2 class="section-title__headline"><?php echo esc_html($section_heading); ?></h2>
        <?php endif; ?>
        <?php if (!empty($section_description)): ?>
            <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($card_grid_icon_card_group) && is_array($card_grid_icon_card_group)) : ?>
        <div class="content-grid__grid">
            <?php foreach ($card_grid_icon_card_group as $card) : ?>
                <article class="content-grid-card">
                    <h3 class="content-grid__card-title grit">
                        <a href="<?php echo esc_url($card['icon_card_link']['url']); ?>" class="content-grid__card-link"<?php echo !empty($card['icon_card_link']['target']) ? ' target="' . esc_attr($card['icon_card_link']['target']) . '"' : ''; ?>>
                            <?php if (!empty($card['icon_card_icon'])) : ?>
                                <?php echo wp_kses_post($card['icon_card_icon']); ?>
                            <?php endif; ?>
                            <?php echo esc_html($card['icon_card_heading']); ?>
                        </a>
                    </h3>
                    <p class="content-grid__card-description"><?php echo esc_html($card['icon_card_description']); ?></p>
                </article>
            <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php endif; ?>