<?php
/**
 * Layout for Banner CTA
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['banner_cta_group'] ) ) :
    $banner_cta = $args['banner_cta_group'];
    
    // Extracting data from the $banner_cta_group array
    $headline = $banner_cta['headline'];
    $description = $banner_cta['description'];
    $background_image = $banner_cta['background_image'];
    $button = $banner_cta['button'];

    if ( $button ) :
        $button_label = isset($button['label']) ? $button['label'] : '';
        $button_link = isset($button['link']['url']) ? $button['link']['url'] : '';
        $button_target = isset($button['link']['target']) ? $button['link']['target'] : '';
        $button_icon = isset($button['icon']['icon']) ? $button['icon']['icon'] : '';
        $button_icon_position = isset($button['icon']['position']) ? $button['icon']['position'] : '';
    endif;
    
    $icon_position = 'left';
endif;
?>
<div class="banner-cta__content">
    <?php if ( $headline ) : ?>
        <h2 class="banner-cta__headline grit"><?php echo esc_html( $headline ); ?></h2>
    <?php endif; ?>
    <?php if ( $description ) : ?>
        <p class="banner-cta__description">
            <?php echo wp_kses_post( $description ); ?>
        </p>
    <?php endif; ?>
    <?php if ( $button ) : ?>
        <div class="banner-cta__cta">
            <a href="<?php echo esc_url($button_link); ?>" class="btn btn--primary <?php echo $button_icon_position === 'icon-right' ? 'btn--icon-right' : ''; ?>" 
               <?php echo $button_target ? 'target="' . esc_attr($button_target) . '"' : ''; ?>>
                <?php if (!empty($button_icon)): ?>
                    <span class="btn__icon" aria-hidden="true">
                        <?php echo $button_icon; ?>
                    </span>
                <?php endif; ?>
                <span class="btn__text"><?php echo esc_html($button_label); ?></span>
            </a>
        </div>
    <?php endif; ?>
</div>