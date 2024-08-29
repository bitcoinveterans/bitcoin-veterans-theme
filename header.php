<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">

<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/favicon-16x16.png">
<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/site.webmanifest">
<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/safari-pinned-tab.svg" color="#5bbad5">


<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://fonts.gstatic.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'cc_starter' ); ?></a>
<?php wp_body_open(); ?>
	<header class="site-header" role="banner">
        <div class="container container-996">
            <a href="<?php echo esc_url( home_url() ); ?>" class="site-header__logo" aria-label="Bitcoin Veterans Home">
				<img src="<?php bloginfo('template_url');?>/assets/img/logo.png" alt="<?php bloginfo('name');?> logo" class="">
            </a>
            <nav class="site-header__nav" role="navigation" aria-label="Main Navigation">
				<?php
				wp_nav_menu( array(
					'theme_location'    => 'primary',
					'menu_class'        => 'menu',
					'container'         => ''
					) );
				?>
            </nav>
            <button aria-label="Toggle Main Menu" aria-expanded="false" aria-controls="main-menu" class="site-header__toggle"></button>
        </div>
    </header>

