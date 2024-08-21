<?php
/**
 * Accordian partial
 *
 * @package adaptdev/base
 * @since 1.0.0
 */

if ( ! empty( array_filter( $args['accordion'] ) ) ) :
	[
        'summary' => $summary,
		'details' => $details,
		'classnames' => $classnames
	] = $args['accordion'];

	$summary_id = uniqid( 'summary-' );
	$details_id = uniqid( 'details-' );
	$classnames = trim( implode( ' ', array_unique( array_merge( $classnames, [ 'details', 'accordion' ] ) ) ) ); ?>

	<details class="<?php echo esc_attr( $classnames ); ?>">
		<?php if ( ! empty( $summary ) ) : ?>
			<summary><h3 id="<?php echo esc_attr( $summary_id ); ?>" role="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $details_id ); ?>" data-target="<?php echo esc_attr( $details_id ); ?>" class="heading summary"><?php echo esc_html( $summary ); ?></h3></summary>
		<?php endif; ?>

		<?php if ( ! empty( $details ) ) : ?>
			<div id="<?php echo esc_attr( $details_id ); ?>" aria-labelledby="<?php echo esc_attr( $summary_id ); ?>" class="details__content">
				<div class="entry-content">
					<?php echo apply_filters( 'the_content', wp_kses_post( $details ) );//phpcs:ignore ?>
				</div>
			</div>
		<?php endif; ?>
	</details><!--.accordion-->
<?php endif; ?>
