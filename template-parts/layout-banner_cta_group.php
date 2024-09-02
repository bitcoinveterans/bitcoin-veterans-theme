<?php
/**
 * Banner CTA
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
$banner_cta = get_sub_field('banner_cta');
$banner_cta_group = array(
    'headline' => $banner_cta['headline'],
    'description' => $banner_cta['description'],
    'button' => array(
        'label' => $banner_cta['button_label'],
        'link' => array(
            'title' => $banner_cta['button_link']['title'],
            'url' => $banner_cta['button_link']['url'],
            'target' => $banner_cta['button_link']['target'],
        ),
        'icon' => array(
            'icon' => $banner_cta['button_icon_group']['button_icon'],
            'position' => $banner_cta['button_icon_group']['icon_position'],
        ),
    ),
    'background_image' => $banner_cta['background_image'],
);

$args = array(
    'banner_cta_group' => $banner_cta_group,
);
if ( isset( $args['banner_cta_group'] ) ) :
	[
        'background_image' => $image,
	] = $args['banner_cta_group']; 
endif;
?>
<?php if ( $image ) {

    $image = wp_get_attachment_image_src( 
        $image, 
        '1200x300' 
    );
?>
    <section class="banner-cta section" style="background-image: url(<?php echo $image[0]; ?>);">

<?php } else { ?>
    <section class="banner-cta section">
<?php } ?>

    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/banner-cta', '', $args );  ?>
    </div>
</section>