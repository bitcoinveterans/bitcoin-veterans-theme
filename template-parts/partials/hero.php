<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
 if ( isset( $args['hero_section'] ) ) :
	[
		'headline' => $headline,
		'description' => $description,
		'button' => $button,
		'image_1' => $image_1_id,
		'image_2' => $image_2_id,
		'image_3' => $image_3_id,
	] = $args['hero_section']; 
    
    if ( $button) :
        $button_link = $button['url'];
        $button_text = $button['title'];
        $button_target = $button['target'];
    endif;
    ?>
        <div class="hero__content">
        <?php if ( $headline) : ?>
            <h2 class="hero__title"><?php echo esc_html( $headline ); ?></h2>
        <?php endif; ?>
        <?php if ( $description) : ?>
            <p class="hero__text"><?php echo esc_html( $description ); ?></p>
        <?php endif; ?>
        <?php if ( $button) : ?>
            <a class="button" href="<?php echo esc_url( $button_link ); ?>" target="<?php echo esc_attr( $button_target ); ?>"><?php echo esc_html( $button_text ); ?></a>
        <?php endif; ?>
            
            
        </div>
        <div class="hero__image-grid">
        <?php if ( $image_1_id) : 
            echo wp_get_attachment_image(
                $image_1_id,
                '540x560',
                false,
                [
                    'class' => 'hero__img hero__img--1',
                    'sizes' => '(min-width: 748px) 100%, 100vw',
                ]
            );   
        ?>
        <?php endif; ?>
        <?php if ( $image_2_id) : 
            echo wp_get_attachment_image(
                $image_2_id,
                '540x560',
                false,
                [
                    'class' => 'hero__img hero__img--2',
                    'sizes' => '(min-width: 748px) 100%, 100vw',
                ]
            );   
        ?>
        <?php endif; ?>
        <?php if ( $image_3_id) : 
            echo wp_get_attachment_image(
                $image_3_id,
                '540x560',
                false,
                [
                    'class' => 'hero__img hero__img--3',
                    'sizes' => '(min-width: 748px) 100%, 100vw',
                ]
            );   
        ?>
        <?php endif; ?>
        </div>
<?php endif; ?>