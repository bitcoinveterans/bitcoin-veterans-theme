<?php
/**
 * Editor content template partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

$entry_content = isset( $args['text_editor'] ) ? $args['text_editor'] : false;

if ( $entry_content ) : ?>
	<div class="entry-content">
		<?php echo apply_filters( 'the_content', $entry_content ); ?>
	</div>
<?php endif; ?>
