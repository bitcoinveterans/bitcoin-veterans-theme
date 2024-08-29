<?php get_header(); ?>
    <section class="banner-inner" style="background-image: url(<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/header-default.jpg);">
        <div class="module">

            <div class="page-title-wrap">
                <h1 class="page-title">blog roll</h1>
                <div class="page-sub-title">
                    <?php if (is_category()):  ?>
                        Category: <?php echo single_cat_title( '', false ); ?>
                    <?php elseif (is_tag()): ?>
                        Tag: <?php single_tag_title() ?>

                    <?php elseif (is_archive()): ?>
                        Archive: <?php the_time('F Y') ?>

                    <?php else: ?>
                    <?php endif;?>
                </div>
            </div>

        </div><!--module-->
    </section><!-- banner -->

    <section class="group page-cols">

        <div class="module">

            <div class="col-1 cf">

                <div class="blog-wrap">
                    <?php if ( have_posts() ) : while (have_posts()) : the_post(); ?>

                        <div class="blog-small cf">

                            <div class="thumb">
    							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'default-post' ); ?></a>
    						</div><!-- thumb -->

                            <div class="post-content">
    							<header class="post-header">
    								<h2 class="post-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title=""><?php the_title(); ?></a></h2>
    								<div class="meta"><time class="post-date" datetime="<?php echo get_the_date( 'Y-m-d' ); ?>"><?php echo get_the_date( 'm/d/Y' ); ?></time></div>
    							</header>

                                <div class="entry-content">
                                    <?php wpe_excerpt('wpe_excerptlength_small', 'wpe_excerptmore'); ?>
                                </div>

                                <div class="learn-more"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'cc_2024' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">continue reading <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a></div>
                            </div>

                        </div><!-- blog-small -->

                    <?php endwhile; ?>

                    <?php endif; ?>
                    </div><!-- blog-wrap -->
                    <div class="paginate">
                        <?php
                			if(function_exists('wp_pagenavi')) {
                				//wp_paginate();
                				wp_pagenavi();
                			}
                			else {
                				cc_2024_paging_nav();
                			}
                		?>
                    </div>
                </div><!-- col-1 -->

                <?php get_sidebar('blog'); ?>

            </div><!-- module -->

        </section><!-- group -->
    	<?php get_footer(); ?>
