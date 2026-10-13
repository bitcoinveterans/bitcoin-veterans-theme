<?php
/**
 * Font Awesome Kit Setup
 *
 * @package cc_starter
 * @since 1.0.0
 */

if (! function_exists('fa_custom_setup_kit') ) {
	function fa_custom_setup_kit($kit_url = '') {
	  foreach ( [ 'wp_enqueue_scripts', 'admin_enqueue_scripts', 'login_enqueue_scripts' ] as $action ) {
		add_action(
		  $action,
		  function () use ( $kit_url ) {
			wp_enqueue_script( 'font-awesome-kit', $kit_url, [], null );
		  }
		);
	  }
	}
  }

  fa_custom_setup_kit('https://kit.fontawesome.com/023ca203ba.js');