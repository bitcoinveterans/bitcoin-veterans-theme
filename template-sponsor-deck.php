<?php
/* Template Name: Sponsor Deck */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$cc_deck = cc_starter_deck_data( get_queried_object_id() );
?><!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0c0b09"><meta name="color-scheme" content="dark">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_template_directory_uri() ); ?>/favicon-32x32.png">
<script>document.documentElement.className=document.documentElement.className.replace('no-js','js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'bv-deck-page' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#s2">Skip to sponsorship tiers</a>
<main id="content"><div id="root"><?php cc_starter_deck_render_static( $cc_deck ); ?></div></main>
<?php wp_footer(); ?>
<script>setTimeout(function(){if(!document.querySelector('#root .deck')){document.documentElement.className=document.documentElement.className.replace(/\bjs\b/,'no-js');}},4000);</script>
</body></html>
