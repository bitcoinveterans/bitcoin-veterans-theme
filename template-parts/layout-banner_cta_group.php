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
    'hide_overlay' => $banner_cta['hide_overlay'],
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
<?php
$image = $image ? wp_get_attachment_image_src( $image, '1200x300' ) : false;
$hide_overlay = ( $banner_cta['hide_overlay'] ?? 0 ) == 1;
if ( $image ) {
?>
    <section class="banner-cta section<?php if ($hide_overlay) echo ' no-overlay'; ?>" style="background-image: url(<?php echo esc_url( $image[0] ); ?>);">

<?php } else { ?>
    <section class="banner-cta section<?php if ($hide_overlay) echo ' no-overlay'; ?>">
<?php } ?>

    <div class="container container-1360">
        <?php get_template_part( 'template-parts/partials/banner-cta', '', $args );  ?>
    </div>
</section>