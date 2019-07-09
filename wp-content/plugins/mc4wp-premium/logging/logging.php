<?php

defined('ABSPATH') or exit;

function _mc4wp_premium_bootstrap_logging() {
	/*
	* Do not run if logging is disabled.
	* This allows people to disable all logging by adding the following to their wp-config.php or functions.php file.
	* 
	* 	define('MC4WP_LOGGING', false);
	*
	*/
	if( defined( 'MC4WP_LOGGING' ) && ! MC4WP_LOGGING ) {
		return false;
	}

	include_once dirname(__FILE__) . '/includes/functions.php';

	if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
		$plugin = new MC4WP_Plugin(__FILE__, MC4WP_PREMIUM_VERSION);
		$logging_admin = new MC4WP_Logging_Admin($plugin);
		$logging_admin->add_hooks();
	}

	$logger = new MC4WP_Logger();
	$logger->add_hooks();
}

add_action( 'after_setup_theme', '_mc4wp_premium_bootstrap_logging' );


