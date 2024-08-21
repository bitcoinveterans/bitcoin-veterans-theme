<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

 if ( isset( $args['billboard'] ) ) :
	[
		'headline' => $headline,
		'description' => $description,
		'button' => $button,
        'image' => $image,
        'image_on_left' => $image_on_left,
	] = $args['billboard']; 
    
    if ( $button) :
        $button_link = $button['url'];
        $button_text = $button['title'];
        $button_target = $button['target'];
    endif;
endif;
?>

<section class="billboard section <?php echo $image_on_left ? 'media_left' : ''; ?> ">
    <div class="module">
        
        <div class="billboard__content">
            <?php if ( $headline) : ?>
                <h2 class="billboard__title"><?php echo esc_html( $headline ); ?></h2>
            <?php endif; ?>
            <?php if ( $description) : ?>
                <p><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>
            <?php if ( $button) : ?>
                <div class="button-wrap">
                    <a class="button__link" href="<?php echo esc_url( $button_link ); ?>" target="<?php echo esc_attr( $button_target ); ?>"><?php echo esc_html( $button_text ); ?></a>
                </div>
            <?php endif; ?>
            
            
        </div>
        <div class="billboard__media">
            <?php if ( $image) : 
                echo wp_get_attachment_image(
                    $image,
                    '540x560',
                    false,
                    [
                        'class' => 'billboard__img',
                        'sizes' => '(min-width: 768px) 540px, 100vw',
                    ]
                );   
            ?>
            <?php endif; ?>
        </div>

    </div>
    
</section>