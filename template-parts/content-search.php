<?php
/**
 * The template part for displaying results in search pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package _s
 */

?>
<div id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
    <header class="entry-header">

        <div class="thumb">
            <?php if ( has_post_thumbnail() ) { ?>
                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php the_post_thumbnail( 'medium' ); ?>
            </a>
            <?php  } else {  ?>
                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class=""><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/default-search.jpg" class="wp-post-image"></a>
            <?php } ?>
        </div><!-- thumb -->

    </header><!-- .entry-header -->

    <div class="entry-right">
        <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="entry-content">
            <?php wpe_excerpt('wpe_excerptlength_big', 'wpe_excerptmore'); ?>
            <div class="learn-more"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'cc_non' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">read more <span><i class="fa fa-arrow-right"></i></span></a></div>
        </div><!-- .entry-content -->
    </div>

</div><!-- post-<?php the_ID(); ?> -->


