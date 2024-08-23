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
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'coplan_2023' ); ?></a>
<?php wp_body_open(); ?>
	<header class="site-header" role="banner">
        <div class="container container-996">
            <a href="<?php echo esc_url( home_url() ); ?>" class="site-header__logo" aria-label="Bitcoin Veterans Home">
				<img src="<?php bloginfo('template_url');?>/assets/img/logo.png" alt="<?php bloginfo('name');?> logo" class="">
            </a>
            <nav class="site-header__nav" role="navigation" aria-label="Main Navigation">
                <button aria-label="Toggle Main Menu" aria-expanded="false" aria-controls="main-menu" class="site-header__toggle"></button>
				<?php
				// wp_nav_menu( array(
				// 	'theme_location'    => 'primary',
				// 	'menu_class'        => 'menumain-nav__menu',
				// 	'container'         => ''
				// 	) );
				?>
                <ul id="main-menu" class="menu">
                    <li class="menu-item current-menu-item menu-item-has-children">
                        <a href="#" aria-haspopup="true" aria-expanded="false">About</a>
                        <ul class="sub-menu">
                            <li id="menu-item-66" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-66"><a href="#">Founders</a></li>
                            <li id="menu-item-46" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item menu-item-46"><a href="#">About the Mission</a></li>
                            <li id="menu-item-44" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-44 menu-item-has-children">
                                <a href="#">Join the Mission</a>
                                <ul class="sub-menu">
                                    <li id="menu-item-66" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-66"><a href="#">Founders</a></li>
                                    <li id="menu-item-46" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item menu-item-46"><a href="#">About the Mission</a></li>
                                    <li id="menu-item-44" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-44"><a href="#">Join the Mission</a></li>
                                    <li id="menu-item-45" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-45"><a href="#">Support the Mission</a></li>
                                    <li id="menu-item-43" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-43"><a href="#">BV Authors</a></li>
                                    <li id="menu-item-43" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-43"><a href="#">Our Sponsors</a></li>
                                </ul>
                            </li>
                            <li id="menu-item-45" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-45"><a href="#">Support the Mission</a></li>
                            <li id="menu-item-43" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-43"><a href="#">BV Authors</a></li>
                            <li id="menu-item-43" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-43"><a href="#">Our Sponsors</a></li>
                        </ul>
                    </li>
                    <li id="menu-item-41" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-41"><a href="#">Resources</a>
                        <ul class="sub-menu">
                            <li id="menu-item-66" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-66"><a href="#">Podcasts</a></li>
                            <li id="menu-item-46" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-46"><a href="#">BV Blog</a></li>
                            <li id="menu-item-46" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-46"><a href="#">BV Authors</a></li>
                            <li id="menu-item-44" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-44"><a href="#">Join MeetUps</a></li>
                            <li id="menu-item-45" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-45"><a href="#">FAQs</a></li>
                            <li id="menu-item-43" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-43"><a href="#">BV Gear</a></li>
                        </ul>
                    </li>
                    <li id="menu-item-47" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-47"><a href="#">Events</a></li>
                    <li id="menu-item-48" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-48"><a href="#">Podcast</a></li>
                    <li id="menu-item-50" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-50"><a href="#">FAQs</a></li>
                    <li id="menu-item-51" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-51"><a href="#">Contact</a></li>
                </ul>	
            </nav>
        </div>
    </header>

