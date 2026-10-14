<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">

<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/favicon-16x16.png">
<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/site.webmanifest">
<link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/safari-pinned-tab.svg" color="#f7931a">


<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#0c0b09">
<meta name="color-scheme" content="dark">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'cc_starter' ); ?></a>
<?php wp_body_open(); ?>
	<header class="site-header" role="banner">
        <div class="container container-996">
            <a href="<?php echo esc_url( home_url() ); ?>" class="site-header__logo grit" aria-label="Bitcoin Veterans Home">
				<img src="<?php bloginfo('template_url');?>/assets/img/logo.png" alt="<?php bloginfo('name');?> logo" class="">
            </a>
            <nav id="site-navigation" class="site-header__nav" role="navigation" aria-label="Main Navigation">
				<?php
				wp_nav_menu( array(
					'theme_location'    => 'primary',
					'menu_class'        => 'menu',
					'container'         => ''
					) );
				?>
            </nav>
            <button class="site-header__toggle" aria-controls="site-navigation" aria-expanded="false"><span class="screen-reader-text">Menu</span><svg class="site-header__toggle-icon" aria-hidden="true" focusable="false" width="28" height="28" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="square" fill="none"/></svg></button>
        </div>
    </header>

