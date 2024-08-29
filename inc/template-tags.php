<?php
/**
 * Custom template tags for this theme.
 *
 * @package cc_starter
 * @since 1.0.0
 * 
 */

 if ( ! function_exists( 'cc_starter_paging_nav' ) ) :
	/**
	 * Display navigation to next/previous set of posts when applicable.
	 */
	function cc_starter_paging_nav() {
		// Don't print empty markup if there's only one page.
		if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
			return;
		}
	
		$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );
	
		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}
	
		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';
	
		$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';
	
		// Set up paginated links.
		$links = paginate_links( array(
			'base'     => $pagenum_link,
			'format'   => $format,
			'total'    => $GLOBALS['wp_query']->max_num_pages,
			'current'  => $paged,
			'mid_size' => 2,
			'add_args' => array_map( 'urlencode', $query_args ),
			'prev_text' => __( '←', 'cc_starter' ),
			'next_text' => __( '→', 'cc_starter' ),
			'type'      => 'list',
		) );
	
		if ( $links ) :
	
		?>
		<nav class="navigation paging-navigation clearfix" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'cc_starter' ); ?></h1>
				<?php echo $links; ?>
		</nav><!-- .navigation -->
		<?php
		endif;
	}
	endif;

 if ( ! function_exists( 'cc_starter_post_nav' ) ) :
	/**
	 * Display navigation to next/previous set of posts when applicable.
	 */
	function cc_starter_post_nav() {
		$next_post = get_next_post();
		$previous_post = get_previous_post();

		if (!$next_post) {
			$next_post = get_posts(array('posts_per_page' => 1, 'order' => 'ASC', 'post_type' => get_post_type()));
			$next_post = !empty($next_post) ? $next_post[0] : null;
		}

		if (!$previous_post) {
			$previous_post = get_posts(array('posts_per_page' => 1, 'order' => 'DESC', 'post_type' => get_post_type()));
			$previous_post = !empty($previous_post) ? $previous_post[0] : null;
		}

		if ($next_post) {
			$next_post_ID = $next_post->ID;
			$next_post_title = $next_post->post_title;
			$next_post_url = get_permalink($next_post->ID);
			$next_post_thumb = get_the_post_thumbnail($next_post->ID, '90x90');
		}

		if ($previous_post) {
			$previous_post_ID = $previous_post->ID;
			$previous_post_title = $previous_post->post_title;
			$previous_post_url = get_permalink($previous_post->ID);
			$previous_post_thumb = get_the_post_thumbnail($previous_post->ID, '90x90');
		}
	
		?>
		<?php
			$nav_items_count = (!empty($next_post_title) ? 1 : 0) + (!empty($previous_post_title) ? 1 : 0);
			$nav_class = 'post-navigation__links post-navigation__links--' . $nav_items_count;

		?>
		<nav class="post-navigation" aria-label="Post navigation">
			<h2 class="post-navigation__heading">Explore More <?php echo ucfirst(get_post_type()); ?> Stories</h2>
			<div class="<?php echo esc_attr($nav_class); ?>">
				<?php if ( ! empty( $next_post_title ) ) : ?>
				<div class="post-navigation__item post-navigation__item--next">
					<a href="<?php echo esc_url($next_post_url); ?>" class="post-navigation__link" rel="next">
						<?php if ($next_post_thumb) : ?>
							<div class="post-navigation__thumbnail">
								<?php echo $next_post_thumb; ?>
							</div>
						<?php endif; ?>
						<h3 class="post-navigation__title"><?php echo esc_html($next_post_title); ?></h3>
					</a>
				</div>
				<?php endif; ?>
				<?php if ( ! empty( $previous_post_title ) ) : ?>
				<div class="post-navigation__item post-navigation__item--previous">
					<a href="<?php echo esc_url($previous_post_url); ?>" class="post-navigation__link" rel="prev">
						<?php if ($previous_post_thumb) : ?>
							<div class="post-navigation__thumbnail">
								<?php echo $previous_post_thumb; ?>
							</div>
						<?php endif; ?>
						<h3 class="post-navigation__title"><?php echo esc_html($previous_post_title); ?></h3>
					</a>
				</div>
				<?php endif; ?>
			</div>
		</nav>
	<?php
	}
endif;

/**
 * Display the post date and author information.
 *
 * @package cc_starter
 * @since 1.0.0
 */

if ( ! function_exists( 'cc_starter_posted_on' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time and author.
     */
    function cc_starter_posted_on() {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
        
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            $time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
        }

        $time_string = sprintf( $time_string,
            esc_attr( get_the_date( 'c' ) ),
            esc_html( get_the_date() ),
            esc_attr( get_the_modified_date( 'c' ) ),
            esc_html( get_the_modified_date() )
        );

        $posted_on = sprintf(
            /* translators: %s: post date. */
            _x( 'Posted on %s', 'post date', 'cc_starter' ),
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        $byline = sprintf(
            /* translators: %s: post author. */
            _x( 'by %s', 'post author', 'cc_starter' ),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>';
    }
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function cc_non_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'cc_non_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'cc_non_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so cc_non_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so cc_non_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in cc_non_categorized_blog.
 */
function cc_non_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'cc_non_categories' );
}
add_action( 'edit_category', 'cc_non_category_transient_flusher' );
add_action( 'save_post',     'cc_non_category_transient_flusher' );
