<?php
/*
  Plugin Name: Shortcodes Ultimate: Shortcode Creator
  Plugin URI: https://getshortcodes.com/add-ons/shortcode-creator/
  Version: 1.5.11
  Author: Vladimir Anokhin
  Author URI: https://vanokhin.com/
  Description: Provides UI for creating custom shortcodes
  Text Domain: shortcodes-ultimate-maker
  Domain Path: /languages
  License: license.txt
 */

/**
 * Begins execution of the plugin.
 *
 * @since 1.5.5
 */
function run_shortcodes_ultimate_maker() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shortcodes-ultimate-maker.php';

	$plugin = new Shortcodes_Ultimate_Maker( __FILE__, '1.5.11' );

	do_action( 'su/maker/ready', $plugin );

}

add_action( 'plugins_loaded', 'run_shortcodes_ultimate_maker' );

/**
 * Run plugin updater.
 *
 * @since  1.5.11
 */
function update_shortcodes_ultimate_maker() {

	require_once plugin_dir_path( __FILE__ ) . 'admin/plugin-update-check.php';

	$updater = new PluginUpdateChecker_2_0 (
		'https://kernl.us/api/v1/updates/5991a5d26c27485243c652ce/',
		__FILE__,
		'shortcodes-ultimate-maker',
		24
	);

	add_action(
		'puc_manual_check_link-shortcodes-ultimate-maker',
		'__return_empty_string'
	);

}

update_shortcodes_ultimate_maker();

/**
 * The code that runs during plugin activation.
 *
 * @since  1.5.5
 */
function activate_shortcodes_ultimate_maker() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shortcodes-ultimate-maker-activator.php';

	Shortcodes_Ultimate_Maker_Activator::activate();

}

register_activation_hook( __FILE__, 'activate_shortcodes_ultimate_maker' );

/**
 * The code that runs during plugin deactivation.
 *
 * @since  1.5.5
 */
function deactivate_shortcodes_ultimate_maker() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-shortcodes-ultimate-maker-deactivator.php';

	Shortcodes_Ultimate_Maker_Deactivator::deactivate();

}

register_deactivation_hook( __FILE__, 'deactivate_shortcodes_ultimate_maker' );

/**
 * Make plugin meta available for translation via POEdit.
 */
if ( false ) {
	__( 'Vladimir Anokhin', 'shortcodes-ultimate-maker' );
	__( 'Shortcodes Ultimate: Shortcode Creator', 'shortcodes-ultimate-maker' );
	__( 'Provides UI for creating custom shortcodes', 'shortcodes-ultimate-maker' );
}
