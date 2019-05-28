<?php
/**
 * Plugin Name: Thrive Headline Optimizer
 * Plugin URI: https://thrivethemes.com
 * Description: Generate reports to find out how well your site is performing
 * Author URI: https://thrivethemes.com
 * Version: 1.1.6
 * Author: <a href="https://thrivethemes.com">Thrive Themes</a>
 * Text Domain: thrive-headline
 * Domain Path: /languages/
 */

define( 'THO_PLUGIN_FILE_PATH', __FILE__ );

define( 'THO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once dirname( __FILE__ ) . '/constants.php';

require_once THO_PATH . 'start.php';

if ( is_admin() ) {
	require_once THO_PATH . 'admin/start.php';
}