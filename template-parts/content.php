<?php
/**
 * @package cc_2024
 */
?>
<div id="post-<?php the_ID(); ?>" <?php post_class('flex-card'); ?> role="article">
    <header class="entry-header">

        <div class="post-meta">Posted In: <?php the_category( ', ' ); ?></div>
        
        <h2 class="post-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title=""><?php the_title(); ?></a></h2>

    </header><!-- .entry-header -->
    <!-- image container -->
    <div class="flex-card-image">
        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
        <?php 
                                
        if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())) {

            if ($class=='blog-big') {
                the_post_thumbnail('default-post-top');
            } else {
                the_post_thumbnail('default-post');
            }
        
        
        
        } else { 

            if ($class=='blog-big') { ?>
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/default-post-top.jpg" >

            <?php } else { ?>

                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/default-post.jpg" >

             <?php }
                    
         
        
        } ?>
    </a>
    </div>
    <!-- content container -->
    <div class="flex-card-content">
       <?php wpe_excerpt('wpe_excerptlength_small', 'wpe_excerptmore'); ?>
       <div class="learn-more"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'cc_non' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">Read More</a></div>
    </div>
</div><!-- #post-## -->