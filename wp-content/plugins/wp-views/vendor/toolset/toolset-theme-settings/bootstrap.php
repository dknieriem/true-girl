<?php

/**
 * Toolset_Theme_Settings_Bootstrap
 *
 * Bootstrap class that is taking care of loading theme settings and all dependencies
 *
 */

class Toolset_Theme_Settings_Bootstrap {

	private static $instance;

	const TOOLSET_AUTOLOADER = 'toolset_autoloader';


	private function __construct() {

		$this->register_utils();
		$this->register_inc();

		add_filter( 'toolset_is_theme_settings_available', '__return_true' );

		//add_action('init', array($this, 'run_compatibility_loader'), 98);

		/**
		 * Action when the Toolset Theme Settings is completely loaded.
		 *
		 * @param Toolset_Theme_Settings_Bootstrap instance
		 *
		 * @since 0.9.0
		 */
		do_action( 'toolset_theme_settings_loaded', $this );
	}


	/**
	 * @return Toolset_Theme_Settings_Bootstrap
	 * @since 0.9.0
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Toolset_Theme_Settings_Bootstrap();
		}
		return self::$instance;
	}


	public function register_utils() {
		// This needs to happen very very early
		require_once TOOLSET_THEME_SETTINGS_PATH . '/utils/autoloader.php';
		Toolset_Theme_Settings_Autoloader::initialize();
	}

	public function register_inc(){
		$this->register_autoloaded_classes();
		Toolset_Compatibility_Loader::get_instance();
	}

	public function run_compatibility_loader(){
	}


	/**
	 * Register classes from autoloade_classmap.php for auto load
	 *
	 * @since 0.9.0
	 */
	private function register_autoloaded_classes() {
		$autoload_classmap_file = TOOLSET_THEME_SETTINGS_PATH . '/autoload_classmap.php';

		if( ! is_file( $autoload_classmap_file ) ) {
			// abort if file does not exist
			return;
		}

		$autoload_classmap = include( $autoload_classmap_file );

		if( is_array( $autoload_classmap ) ) {
			// Register autoloaded classes.
			$autoloader = Toolset_Theme_Settings_Autoloader::get_instance();
			$autoloader->register_classmap( $autoload_classmap );
		}
	}

};