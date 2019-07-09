<?php
/*
Plugin Name: MailChimp for WordPress - Premium
Plugin URI: https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp-pro&utm_campaign=plugins-page
Description: Premium functionality to MailChimp for WordPress.
Version: 4.5.2
Author: ibericode
Author URI: https://ibericode.com/
License: GPL v3
Text Domain: mailchimp-for-wp

MailChimp for WordPress alias MC4WP
Copyright (C) 2012-2018, Danny van Kooten, danny@ibericode.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


// Prevent direct file access
defined( 'ABSPATH' ) or exit;

// Define some useful constants
define( 'MC4WP_PREMIUM_VERSION', '4.5.2' );
define( 'MC4WP_PREMIUM_PLUGIN_FILE', __FILE__ );
/**
 * Loads the various premium add-on plugins
 *
 * @access private
 * @ignore
 */
function _mc4wp_premium_load() {

	// load autoloader
    $dir = dirname( __FILE__ );
	require_once $dir . '/vendor/autoload_52.php';

	// make sure core plugin is installed and at version 3.0.8
	if( ! defined( 'MC4WP_VERSION' ) || version_compare( MC4WP_VERSION, '3.0.8', '<' ) ) {

		// if not, show a notice
		$required_plugins = array(
			'mailchimp-for-wp' => array(
				'url' => 'https://wordpress.org/plugins/mailchimp-for-wp/',
				'name' => 'MailChimp for WordPress core',
				'version' => '3.0.8'
			)
		);
		$notice = new MC4WP_Required_Plugins_Notice( 'MailChimp for WordPress - Premium', $required_plugins );
		$notice->add_hooks();
		return;
	}

	// PHP 5.2 compatible plugins
	$plugins = array(
		'ajax-forms',
		'custom-color-theme',
		'email-notifications',
		'styles-builder',
		'multiple-forms',
		'lucy',
		'logging',
	);


	// PHP 5.3+ plugins
	if( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
		$plugins[] = 'ecommerce-loader';
		$plugins[] = 'licensing';
		$plugins[] = 'append-form-to-post';
	}


    /**
     * Filters which add-on plugins should be loaded
     *
     * Takes an array of plugin slugs, defaults to all plugins.
     *
     * @param array $plugins
     */
    $plugins = (array) apply_filters( 'mc4wp_premium_enabled_plugins', $plugins );

    // include each plugin
	foreach( $plugins as $plugin ) {
        require_once $dir . "/$plugin/$plugin.php";
	}

}

add_action( 'plugins_loaded', '_mc4wp_premium_load', 30 );

/**
 * @access private
 * @ignore
 * */
function _mc4wp_premium_activate() {
    // run logging installer
    require_once dirname( __FILE__ ) . '/logging/includes/class-installer.php';
    MC4WP_Logging_Installer::run();
}

register_activation_hook( __FILE__, '_mc4wp_premium_activate' );
