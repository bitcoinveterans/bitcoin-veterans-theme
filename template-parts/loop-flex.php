<?php
/**
 * ACF flexible content loop
 *
 * Include this file in page and post templates where
 * content is rendered via ACF Flexible Content fields.
 *
 * @package cc_starter
 * @since 1.0.0
 */

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		
		$classic_content = get_the_content();

		if ( !empty( $classic_content ) ) {
            // Display the classic editor content
            echo '<section class="classic-editor-content group"><div class="module">';
			echo '<div class="entry-content">';
            the_content();
            echo '</div>';
			echo '</div></section>';
        } else {
			if ( function_exists( 'have_rows' ) && have_rows( 'home_page_content_sections' ) ) :
				while ( have_rows( 'home_page_content_sections' ) ) :
					the_row();
					echo "<!-- Loading layouts in loop flex -->";
					get_template_part( 'template-parts/layout', get_row_layout() );
				endwhile;
			endif;
		}

	endwhile;
endif;


?>