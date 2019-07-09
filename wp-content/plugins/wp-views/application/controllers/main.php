<?php

/**
 * Main Views controller.
 *
 * @since 2.5.0
 * @since m2m WPV_Ajax included
 */
class WPV_Main {

	public function initialize() {
		$this->add_hooks();
	}

	public function add_hooks() {
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ) );

		add_action( 'toolset_common_loaded', array( $this, 'initialize_classes' ) );
		
		add_action( 'after_setup_theme', array( $this, 'init_api' ), 9999 );
		
		add_action( 'init', array( $this, 'on_init' ) );
	}

	public function initialize_classes() {
		WPV_WPML_Integration::initialize();

		$admin_help = new WPV_Controller_Admin_Help();
		$admin_help->init();
		
		// @since m2m.
		if ( defined( 'DOING_AJAX' ) ) {
			WPV_Ajax::initialize();
		}
		
		// @since m2m
		$filter_manager = WPV_Filter_Manager::get_instance();
		$filter_manager->initialize();
	}

	/**
	 * Register Views classes with Toolset_Common_Autoloader.
	 *
	 * @since 2.5.0
	 */
	public function register_autoloaded_classes() {
		// It is possible to regenerate the classmap with Zend framework, for example:
		//
		// cd application
		// /srv/www/ZendFramework-2.4.9/bin/classmap_generator.php --overwrite
		$classmap = include( WPV_PATH . '/application/autoload_classmap.php' );

		do_action( 'toolset_register_classmap', $classmap );
	}
	
	/**
	 * Init the public Views filters API.
	 *
	 * @note This gets available at after_setup_theme:9999 because we need to wait for Toolset Common to fully load.
	 *
	 * @since m2m
	 */
	public function init_api() {
		WPV_Api::initialize();
	}
	
	public function on_init() {
		$wpv_shortcodes = new WPV_Shortcodes();
		$wpv_shortcodes->initialize();
		$wpv_shortcodes_gui = new WPV_Shortcodes_GUI();
		$wpv_shortcodes_gui->initialize();
	}
}
