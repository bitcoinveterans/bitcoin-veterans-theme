<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['billboard_stacked_section'] ) ) :
    $billboard_stacked_section = $args['billboard_stacked_section'];
    
    $section_heading = $billboard_stacked_section['section_heading'];
    $section_description = $billboard_stacked_section['section_description'];
    $section_icon = $billboard_stacked_section['section_icon'];
    
    $card = $billboard_stacked_section['card'];
    $card_image = $billboard_stacked_section['card_image'];
    
    $image_id = $card_image['image'];
    $image_position = $card_image['image_position'];
    
    $card_headline = $card['headline'];
    $card_description = $card['description'];
    $card_button = $card['button'];
    
    if ($card_button) {
        $button_label = isset($card_button['button_label']) ? $card_button['button_label'] : '';
        $button_link = isset($card_button['button_link']['url']) ? $card_button['button_link']['url'] : '';
        $button_target = isset($card_button['button_link']['target']) ? $card_button['button_link']['target'] : '';
        $button_icon = isset($card_button['button_icon_group']['button_icon']) ? $card_button['button_icon_group']['button_icon'] : '';
        $button_icon_position = isset($card_button['button_icon_group']['icon_position']) ? $card_button['button_icon_group']['icon_position'] : '';
    }
    
    $icon_position = 'left';
    
    $image_on_left = ($image_position == 1) ? 'media-left' : '';    
endif;
?>

<?php if (!empty($section_icon) || !empty($section_heading) || !empty($section_description)) : ?>
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
<?php endif; ?>


<div class="billboard-stacked <?php echo $image_on_left ? 'media-left' : ''; ?>">
    <div class="billboard__content">
        <header>
            <?php if (!empty($card_headline)) : ?>
                <h2 class="billboard__title grit"><?php echo esc_html($card_headline); ?></h2>
            <?php endif; ?>
        </header>
        <div class="billboard__body">
            <?php if (!empty($card_description)) : ?>
               <div class="entry-content">
               <?php echo wp_kses_post($card_description); ?>
               </div>
            <?php endif; ?>
        </div>
        <?php if ($card_button && !empty($button_label) && !empty($button_link)) : ?>
            <div class="billboard__cta">
                <a href="<?php echo esc_url($button_link); ?>" class="btn btn--primary <?php echo $button_icon_position === 'icon-right' ? 'btn--icon-right' : ''; ?>" 
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