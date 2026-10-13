<?php
if ( ! defined( 'ABSPATH' ) ) { die( 'Forbidden.' ); }
// Do not expose user listings / slugs to anonymous visitors.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! current_user_can( 'list_users' ) ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) { return 'users' === $name ? false : $provider; }, 10, 2 );
