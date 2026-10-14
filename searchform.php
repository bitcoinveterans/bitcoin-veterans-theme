<?php
/**
 * Search form (spec Phase 6): input per the §3.5 form rules, submit button
 * is .btn.btn--primary. The query field keeps name="s".
 *
 * @package cc_starter
 */

?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="screen-reader-text" for="s"><?php esc_html_e( 'Search for:', 'cc_starter' ); ?></label>
    <input type="search" class="form-control search-field" placeholder="Search …" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" id="s">
    <button type="submit" class="btn btn--primary"><span class="screen-reader-text"><?php esc_html_e( 'Search', 'cc_starter' ); ?></span><i class="fa fa-search" aria-hidden="true"></i></button>
</form>
