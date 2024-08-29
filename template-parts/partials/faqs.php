<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
if ( isset( $args['faqs_section'] ) ) :
    [
        'section_heading' => $section_heading,
        'section_description' => $section_description,
        'section_icon' => $section_icon,
        'faqs_section_options' => $faqs_section_options,
    ] = $args['faqs_section'];

    // Setup variables for FAQs section
    $faqs_category = $faqs_section_options['faq_category'];
    $number_of_faqs = $faqs_section_options['number_of_faqs'];
    $show_see_all_faqs = $faqs_section_options['show_see_all_faqs'];

    $args = array(
        'post_type' => 'cc_starter_faqs',
        'posts_per_page' => $number_of_faqs,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );

    if (!empty($faqs_category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'faq_category',
                'field'    => 'term_id',
                'terms'    => $faqs_category,
            ),
        );
    }

    $faqs_query = new WP_Query($args);

?>

<div class="section-title">
    <?php if ($section_icon): ?>
        <?php echo $section_icon; ?>
    <?php endif; ?>
    <?php if ($section_heading): ?>
        <h2 class="section-title__headline"><?php echo esc_html($section_heading); ?></h2>
    <?php endif; ?>
    <?php if ($section_description): ?>
        <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
    <?php endif; ?>
</div>
<?php if ($faqs_query->have_posts()) : ?>
    <div class="accordion" id="faqAccordion">
        <?php while ($faqs_query->have_posts()) : $faqs_query->the_post(); ?>
            <details class="accordion-item details accordion">
                <summary class="accordion-header">
                    <h3 class="heading summary"><?php the_title(); ?></h3>
                </summary>
                <div class="details__content">
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </details>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>
<?php endif; ?>
    <?php if ($show_see_all_faqs): ?>
        <div class="faqs-cta">
            <a href="<?php echo esc_url(get_post_type_archive_link('cc_starter_faqs')); ?>" class="btn btn--secondary btn--icon-right">
                <span class="btn__icon" aria-hidden="true">
                    <i class="fa-solid fa-arrow-right"></i>
                </span>
                <span class="btn-text"><?php echo esc_html__('View All FAQs', 'your-text-domain'); ?></span>
            </a>
        </div>
    <?php endif; ?>

<?php endif; ?>