<?php
/**
 * Image layout for accordian
 *
 * @package adaptdev/base
 * @since 1.0.0
 */
$section_title = get_sub_field('section_title');
$section_description = get_sub_field('section_description');
?>

<section class="accordion-list section">
    <div class="module">
		<div class="section-title-wrap">
			<?php if ( $section_title) : ?>
				<h2 class="section-title"><?php echo esc_html( $section_title ); ?></h2>
            <?php endif; ?>
			<?php if ( $section_description) : ?>
				<p class="section-desc"><?php echo esc_html( $section_description ); ?></p>
            <?php endif; ?>
		</div>
		<?php
			if ( have_rows( 'accordion' ) ) :
				while ( have_rows( 'accordion' ) ) :
					the_row();
					get_template_part(
						'template-parts/partials/accordion',
						'',
						[
							'accordion' => [
								'summary'    => get_sub_field( 'summary' ),
								'details'    => get_sub_field( 'details' ),
								'classnames' => [ 'accordion-item' ],
							],
						]
					);
				endwhile;
			endif;
			?>
    </div>
</section>