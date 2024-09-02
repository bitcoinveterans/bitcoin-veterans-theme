<?php
/**
 * Layout for Biillboard
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['billboard_section'] ) ) :
    $billboard = $args['billboard_section'];
    $content = $billboard['content'];
    $media = $billboard['media'];
    $media_position = $billboard['media_position'];

    $headline = $content['headline'];
    $sub_headline = $content['sub_headline'];
    $description = $content['description'];
    $button = $content['button'];

    if ( $button ) :
        $button_label = isset($button['button_label']) ? $button['button_label'] : '';
        $button_link = isset($button['button_link']['url']) ? $button['button_link']['url'] : '';
        $button_target = isset($button['button_link']['target']) ? $button['button_link']['target'] : '';
        $button_icon = isset($button['button_icon_group']['button_icon']) ? $button['button_icon_group']['button_icon'] : '';
        $button_icon_position = isset($button['button_icon_group']['icon_position']) ? $button['button_icon_group']['icon_position'] : '';
    endif;
    
    $icon_position = 'left';

    $image_on_left = ($media_position == 1) ? 'media-left' : '';
endif;
?>

<section class="billboard-traditional <?php echo $image_on_left ? 'media-left' : ''; ?>">
    <div class="billboard__media">
        <figure class="billboard__figure">
            <?php if ( $media ) : 
                echo wp_get_attachment_image(
                $media,
                'full',
                false,
                [
                    'class' => 'billboard__img',
                    'sizes' => '(min-width: 768px) 540px, 100vw',
                ]
            );   
        ?>
        <?php endif; ?>
        </figure>
    </div>

    <div class="billboard__content">
    <?php if ( $headline ) : ?> 
        <header>
            <h2 class="billboard__title grit"><?php echo esc_html( $headline ); ?></h2>
            <?php if ( $sub_headline ) : ?>
                <p class="billboard__subtitle"><?php echo esc_html( $sub_headline ); ?></p>
            <?php endif; ?>
        </header>
    <?php endif; ?>
        <?php if ( $description ) : ?>
            <div class="billboard__body">
                <?php echo wp_kses_post( $description ); ?>
            </div>
        <?php endif; ?>
        <?php if ( $button && isset($button['button_link']['url']) && isset($button['button_label']) ) : ?>
            <div class="billboard__cta">
                <a href="<?php echo esc_url($button['button_link']['url']); ?>" 
                   class="btn btn--primary <?php echo isset($button['button_icon_group']['icon_position']) && $button['button_icon_group']['icon_position'] === 'icon-right' ? 'btn--icon-right' : ''; ?>" 
                   <?php echo isset($button['button_link']['target']) ? 'target="' . esc_attr($button['button_link']['target']) . '"' : ''; ?>>
                    <?php if (isset($button['button_icon_group']['button_icon'])): ?>
                        <span class="btn__icon" aria-hidden="true">
                            <?php echo $button['button_icon_group']['button_icon']; ?>
                        </span>
                    <?php endif; ?>
                    <span class="btn__text"><?php echo esc_html($button['button_label']); ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>