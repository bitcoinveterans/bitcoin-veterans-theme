<?php
/**
 * Register Widget Areas
 *
 * @package cc_starter
 * @since 1.0.0
 */

// Initialize theme widgets
function cc_starter_widgets_init() {

	// register_sidebar(array(
	// 	'name' => 'Sidebar Default',
	// 	'id' => 'sidebar-1',
	// 	'before_widget' => '<div id="%1$s" class="widget-container %2$s">',
	// 	'after_widget' => '</div>',
	// 	'before_title' => '<div class="widget-title">',
	// 	'after_title' => '</div>',
	// ) );

	// register_sidebar(
	// 	array(
	// 		'name'          => 'Footer Area 1',
	// 		'id'            => 'footer-area-1',
	// 		'before_widget' => '<div id="%1$s" class="widget-container %2$s">',
	// 		'after_widget'  => '</div>',
	// 		'before_title'  => '<div class="widget-title">',
	// 		'after_title'   => '</div>',
	// 	)
	// );
}
add_action( 'widgets_init', 'cc_starter_widgets_init' );

// Unregister default widgets.
function cc_starter_unregister_default_widgets() {
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Links' );
	unregister_widget( 'WP_Widget_Meta' );
	unregister_widget( 'WP_Widget_Recent_Comments' );
	unregister_widget( 'WP_Widget_RSS' );
	unregister_widget( 'WP_Widget_Tag_Cloud' );
}
add_action( 'widgets_init', 'cc_starter_unregister_default_widgets', 11 );
