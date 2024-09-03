<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( isset( $args['text_editor_section'] ) ) :
    $text_editor_section = $args['text_editor_section'];
    $entry_content = $text_editor_section['text_editor'];
    $section_heading = $text_editor_section['section_heading'];
    $section_description = $text_editor_section['section_description'];
    $section_icon = $text_editor_section['section_icon'];
endif;
?>
<?php if (isset($section_heading) && $section_heading || isset($section_description) && $section_description || isset($section_icon) && $section_icon) : ?>
    <div class="section-title">
    	<?php if (isset($section_icon) && $section_icon): ?>
            <?php echo $section_icon; ?>
        <?php endif; ?>
        <?php if (isset($section_heading) && $section_heading) : ?>
            <h2 class="section-title__headline"><?php echo esc_html($section_heading); ?></h2>
        <?php endif; ?>
        <?php if (isset($section_description) && $section_description) : ?>
            <p class="section-title__description"><?php echo esc_html($section_description); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($entry_content) && $entry_content) : ?>
	<div class="entry-content">
		<?php echo apply_filters( 'the_content', $entry_content ); ?>
	</div>
<?php endif; ?>
