<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin so that it
 * is ready for translation.
 *
 * @since        1.5.5
 * @version      1.0.0
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
class Shortcodes_Ultimate_Addon_i18n {

	/**
	 * The path of the main plugin file.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $plugin_file    The path of the main plugin file.
	 */
	private $plugin_file;

	/**
	 * The plugin text domain.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $textdomain    The plugin text domain.
	 */
	private $textdomain;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.5.5
	 * @param   string   $plugin_file   The path of the main plugin file.
	 * @param   string   $textdomain    The plugin text domain.
	 */
	public function __construct( $plugin_file, $textdomain ) {

		$this->plugin_file = $plugin_file;
		$this->textdomain  = $textdomain;

	}


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since   1.5.5
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->textdomain,
			false,
			path_join( dirname( plugin_basename( $this->plugin_file ) ), 'languages' )
		);

	}

}
