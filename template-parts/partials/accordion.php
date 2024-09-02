<?php
/**
 * Accordion partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['accordion_section'] ) ) :
    $accordion_section = $args['accordion_section'];
    $accordion_list = $accordion_section['accordion_list'];
?>
<div class="accordion" id="faqAccordion">
    <?php if ( !empty( $accordion_list ) && is_array( $accordion_list ) ) : ?>
        <?php foreach ( $accordion_list as $index => $item ) : ?>
            <details class="accordion-item details accordion"<?php echo $index === 0 ? ' open=""' : ''; ?>>
                <summary class="accordion-header">
                    <h3 class="heading summary"><?php echo esc_html( $item['summary'] ); ?></h3>
                </summary>
                <div class="details__content">
                    <div class="entry-content">
                        <p><?php echo esc_html( $item['details'] ); ?></p>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
