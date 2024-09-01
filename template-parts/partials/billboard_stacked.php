<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['billboard'] ) && isset( $args['billboard']['billboard_stacked'] ) ) :
    $billboard_stacked = $args['billboard']['billboard_stacked'];
    $section_heading = isset($billboard_stacked['section_heading']) ? $billboard_stacked['section_heading'] : '';
    $section_description = isset($billboard_stacked['section_description']) ? $billboard_stacked['section_description'] : '';
    $section_icon = isset($billboard_stacked['section_icon']) ? $billboard_stacked['section_icon'] : '';

    $card_image = isset($billboard_stacked['card_image']) ? $billboard_stacked['card_image'] : [];
    $image_id = isset($card_image['image']) ? $card_image['image'] : '';
    $image_position = isset($card_image['image_position']) ? $card_image['image_position'] : '';
    
    $card = isset($billboard_stacked['card']) ? $billboard_stacked['card'] : [];
    $card_headline = isset($card['headline']) ? $card['headline'] : '';
    $card_description = isset($card['description']) ? $card['description'] : '';
    $card_button = isset($card['button']) ? $card['button'] : '';

    $button_label = '';
    $button_link = '';
    $button_target = '';
    $button_icon = '';
    $button_icon_position = '';

    if ( $card_button ) :
        $button_label = isset($card_button['button_label']) ? $card_button['button_label'] : '';
        $button_link = isset($card_button['button_link']['url']) ? $card_button['button_link']['url'] : '';
        $button_target = isset($card_button['button_link']['target']) ? $card_button['button_link']['target'] : '';
        $button_icon = isset($card_button['button_icon_group']['button_icon']) ? $card_button['button_icon_group']['button_icon'] : '';
        $button_icon_position = isset($card_button['button_icon_group']['icon_position']) ? $card_button['button_icon_group']['icon_position'] : '';
    endif;

    $icon_position = 'left';
    if ($card_button && isset($card_button['button_icon_group']['icon_position'])) {
        $icon_position = $card_button['button_icon_group']['icon_position'] === 'icon-right' ? 'right' : 'left';
    }

    $image_on_left = ($image_position == 1) ? 'media-left' : '';

endif;
?>

<div class="section-title">
    <?php if (!empty($section_icon)) : ?>
        <?php echo $section_icon; ?>
    <?php endif; ?>
    <?php if (!empty($section_heading)) : ?>
        <h2 class="section-title__headline"><?php echo esc_html($section_heading); ?></h2>
    <?php endif; ?>
    <?php if (!empty($section_description)) : ?>
        <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
    <?php endif; ?>
</div>

<div class="billboard-stacked <?php echo $image_on_left ? 'media-left' : ''; ?>">
    <div class="billboard__content">
        <header>
            <?php if (!empty($card_headline)) : ?>
                <h2 class="billboard__title grit"><?php echo esc_html($card_headline); ?></h2>
            <?php endif; ?>
        </header>
        <div class="billboard__body">
            <?php if (!empty($card_description)) : ?>
                <?php echo wp_kses_post($card_description); ?>
            <?php endif; ?>
        </div>
        <?php if ($card_button) : ?>
            <div class="billboard__cta">
                <a href="<?php echo esc_url($button_link); ?>" class="btn btn--primary <?php echo $icon_position === 'right' ? 'btn--icon-right' : ''; ?>" 
                    <?php echo $button_target ? 'target="' . esc_attr($button_target) . '"' : ''; ?>>
                    <?php if (!empty($button_icon)): ?>
                        <span class="btn__icon" aria-hidden="true">
                            <?php echo ($button_icon); ?>
                        </span>
                    <?php endif; ?>
                    <span class="btn__text"><?php echo esc_html($button_label); ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <div class="billboard__media">
        <figure class="billboard__figure">
            <?php if (!empty($image_id)) : ?>
                <?php $image = wp_get_attachment_image_src($image_id, 'full'); ?>
                <?php if ($image) : ?>
                    <img src="<?php echo esc_url($image[0]); ?>" alt="<?php echo esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true)); ?>" width="<?php echo esc_attr($image[1]); ?>" height="<?php echo esc_attr($image[2]); ?>" class="billboard__image" loading="lazy">
                <?php endif; ?>
            <?php endif; ?>
        </figure>
    </div>
</div>