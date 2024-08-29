<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
if ( isset( $args['hero_section'] ) ) :
    [
        'headline_is_h1' => $headline_is_h1,
        'headline' => $headline,
        'description' => $description,
        'hero_button_1' => $hero_button_1,
        'hero_button_2' => $hero_button_2,
        'hero_button_3' => $hero_button_3,
    ] = $args['hero_section']; 
    
    if ( $hero_button_1 ) :
        $button_1_label = $hero_button_1['button_label'];
        $button_1_link = $hero_button_1['button_link']['url'];
        $button_1_target = $hero_button_1['button_link']['target'];
        $button_1_icon = $hero_button_1['button_icon_group']['button_icon'];
        $button_1_icon_position = $hero_button_1['button_icon_group']['icon_position'];
    endif;

    if ( $hero_button_2 ) :
        $button_2_label = $hero_button_2['button_label'];
        $button_2_link = $hero_button_2['button_link']['url'];
        $button_2_target = $hero_button_2['button_link']['target'];
        $button_2_icon = $hero_button_2['button_icon_group']['button_icon'];
        $button_2_icon_position = $hero_button_2['button_icon_group']['icon_position'];
    endif;

    if ( $hero_button_3 ) :
        $button_3_label = $hero_button_3['button_label'];
        $button_3_link = $hero_button_3['button_link']['url'];
        $button_3_target = $hero_button_3['button_link']['target'];
        $button_3_icon = $hero_button_3['button_icon_group']['button_icon'];
        $button_3_icon_position = $hero_button_3['button_icon_group']['icon_position'];
    endif;

    $tag = $headline_is_h1 ? 'h1' : 'h2';
    $icon_position = 'left';

    if ($hero_button_1 && isset($hero_button_1['button_icon_group']['icon_position'])) {
        $icon_position = $hero_button_1['button_icon_group']['icon_position'] === 'icon-right' ? 'right' : 'left';
    } elseif ($hero_button_2 && isset($hero_button_2['button_icon_group']['icon_position'])) {
        $icon_position = $hero_button_2['button_icon_group']['icon_position'] === 'icon-right' ? 'right' : 'left';
    } elseif ($hero_button_3 && isset($hero_button_3['button_icon_group']['icon_position'])) {
        $icon_position = $hero_button_3['button_icon_group']['icon_position'] === 'icon-right' ? 'right' : 'left';
    }
?>

    <div class="hero__content">
        <?php if ($headline): ?>
            <<?php echo esc_attr($tag); ?> class="hero__title grit"><?php echo $headline; ?></<?php echo esc_attr($tag); ?>>
        <?php endif; ?>
        <?php if ($description): ?>
            <p class="hero__description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php if ($hero_button_1 || $hero_button_2 || $hero_button_3): ?>
        <div class="hero__actions">
            <nav aria-label="Additional mission actions">
                <ul class="hero__actions-list">
                    <?php if ($hero_button_1): ?>
                    <li class="hero__action-item hero__action-item--primary">
                        <a href="<?php echo esc_url($button_1_link); ?>" class="btn btn--primary <?php echo $icon_position === 'right' ? 'btn--icon-right' : ''; ?>" <?php echo $button_1_target ? 'target="' . esc_attr($button_1_target) . '"' : ''; ?>>
                            <?php if ($button_1_icon): ?>
                                <span class="btn__icon" aria-hidden="true">
                                    <?php echo ($button_1_icon); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($button_1_label): ?>
                                <span class="btn__text"><?php echo esc_html($button_1_label); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($hero_button_2): ?>
                    <li class="hero__action-item hero__action-item--secondary">
                        <a href="<?php echo esc_url($button_2_link); ?>" class="btn btn--secondary <?php echo $button_2_icon_position === 'icon-right' ? 'btn--icon-right' : ''; ?>" <?php echo $button_2_target ? 'target="' . esc_attr($button_2_target) . '"' : ''; ?>>
                            <?php if ($button_2_icon): ?>
                                <span class="btn__icon" aria-hidden="true">
                                    <?php echo ($button_2_icon); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($button_2_label): ?>
                                <span class="btn__text"><?php echo esc_html($button_2_label); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($hero_button_3): ?>
                    <li class="hero__action-item hero__action-item--secondary">
                        <a href="<?php echo esc_url($button_3_link); ?>" class="btn btn--secondary <?php echo $button_3_icon_position === 'icon-right' ? 'btn--icon-right' : ''; ?>" <?php echo $button_3_target ? 'target="' . esc_attr($button_3_target) . '"' : ''; ?>>
                            <?php if ($button_3_icon): ?>
                                <span class="btn__icon" aria-hidden="true">
                                    <?php echo ($button_3_icon); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($button_3_label): ?>
                                <span class="btn__text"><?php echo esc_html($button_3_label); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

<?php endif; ?>