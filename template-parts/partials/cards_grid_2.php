<?php
/**
 * Cards Grid 2
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['cards_grid_2'] ) ) :
    $cards_grid_2 = $args['cards_grid_2']['cards_grid_2'];
    
    // Section heading group
    $section_heading = isset($cards_grid_2['section_heading']) ? $cards_grid_2['section_heading'] : '';
    $section_description = isset($cards_grid_2['section_description']) ? $cards_grid_2['section_description'] : '';
    $section_icon = isset($cards_grid_2['section_icon']) ? $cards_grid_2['section_icon'] : '';

    // Function to process card data
    if ( ! function_exists( 'process_card_data_2' ) ) {
        function process_card_data_2($card) {
            $processed = [
                'image' => isset($card['media']) ? $card['media'] : '',
                'headline' => isset($card['headline']) ? $card['headline'] : '',
                'description' => isset($card['description']) ? $card['description'] : '',
                'button' => isset($card['button']) ? $card['button'] : '',
                'icon_position' => 'left'
            ];

            if ($processed['button']) {
                $processed['button_label'] = isset($processed['button']['button_label']) ? $processed['button']['button_label'] : '';
                $processed['button_link'] = isset($processed['button']['button_link']['url']) ? $processed['button']['button_link']['url'] : '';
                $processed['button_target'] = isset($processed['button']['button_link']['target']) ? $processed['button']['button_link']['target'] : '';
                $processed['button_icon'] = isset($processed['button']['button_icon_group']['button_icon']) ? $processed['button']['button_icon_group']['button_icon'] : '';
                $processed['button_icon_position'] = isset($processed['button']['button_icon_group']['icon_position']) ? $processed['button']['button_icon_group']['icon_position'] : '';

                if (isset($processed['button']['button_icon_group']['icon_position'])) {
                    $processed['icon_position'] = $processed['button']['button_icon_group']['icon_position'] === 'icon-right' ? 'right' : 'left';
                }
            }

            return $processed;
        }
    }
    // Process two cards
    $card_1 = isset($cards_grid_2['card_1']) ? process_card_data_2($cards_grid_2['card_1']) : [];
    $card_2 = isset($cards_grid_2['card_2']) ? process_card_data_2($cards_grid_2['card_2']) : [];

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
<div class="cards-grid-2__grid">
    <?php foreach ([$card_1, $card_2] as $card) : ?>
        <article class="cards-grid-2__item">
            <header class="cards-grid-2__header">
                <?php if (!empty($card['image'])) : ?>
                    <figure class="cards-grid-2__figure">
                        <?php if (!empty($card['image'])) : ?>
                            <?php $image = wp_get_attachment_image_src($card['image'], 'full'); ?>
                            <?php if ($image) : ?>
                                <img src="<?php echo esc_url($image[0]); ?>" alt="<?php echo esc_attr(get_post_meta($card['image'], '_wp_attachment_image_alt', true)); ?>" width="<?php echo esc_attr($image[1]); ?>" height="<?php echo esc_attr($image[2]); ?>" class="cards-grid-2__image" loading="lazy">
                            <?php endif; ?>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>
            </header>
            <div class="cards-grid-2__content">
                <?php if (!empty($card['headline'])) : ?>
                    <h3 class="cards-grid-2__title grit"><?php echo esc_html($card['headline']); ?></h3>
                <?php endif; ?>
                <?php if (!empty($card['description'])) : ?>
                    <p class="cards-grid-2__text"><?php echo esc_html($card['description']); ?></p>
                <?php endif; ?>
                <?php if ($card['button']) : ?>
                    <footer class="cards-grid-2__footer">
                        <a href="<?php echo esc_url($card['button_link']); ?>" class="btn btn--primary <?php echo $card['icon_position'] === 'right' ? 'btn--icon-right' : ''; ?>" 
                            <?php echo $card['button_target'] ? 'target="' . esc_attr($card['button_target']) . '"' : ''; ?>>
                            <?php if (!empty($card['button_icon'])): ?>
                                <span class="btn__icon" aria-hidden="true">
                                    <?php echo ($card['button_icon']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="btn__text"><?php echo esc_html($card['button_label']); ?></span>
                        </a>
                    </footer>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>