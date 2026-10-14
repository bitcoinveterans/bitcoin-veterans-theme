<?php
/**
 * Prev/next navigation for single posts: two secondary buttons.
 *
 * @package cc_starter
 * @since 1.0.0
 */

$prev_post = get_previous_post();
$next_post = get_next_post();

if ( ! $prev_post && ! $next_post ) {
    return;
}
?>
<nav class="post-prev-next" aria-label="<?php esc_attr_e( 'Post navigation', 'cc_starter' ); ?>">
    <span class="post-prev-next__cell post-prev-next__cell--prev">
        <?php if ( $prev_post ) : ?>
            <a class="btn btn--secondary" href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" rel="prev">
                <span class="post-prev-next__arrow" aria-hidden="true">&larr;</span>
                <span class="btn-text"><?php esc_html_e( 'Previous Post', 'cc_starter' ); ?></span>
            </a>
        <?php endif; ?>
    </span>
    <span class="post-prev-next__cell post-prev-next__cell--next">
        <?php if ( $next_post ) : ?>
            <a class="btn btn--secondary btn--icon-right" href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" rel="next">
                <span class="post-prev-next__arrow" aria-hidden="true">&rarr;</span>
                <span class="btn-text"><?php esc_html_e( 'Next Post', 'cc_starter' ); ?></span>
            </a>
        <?php endif; ?>
    </span>
</nav>
