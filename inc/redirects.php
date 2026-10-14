<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Short vanity URLs that should follow the current summit. 302 on purpose: targets change yearly.
add_action( 'template_redirect', function () {
	if ( ! is_404() ) { return; }
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$map  = array( 'summit' => '/summit2026/' );
	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( $map[ $path ] ), 302, 'Bitcoin Veterans theme' );
		exit;
	}
}, 1 );
