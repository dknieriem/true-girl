<?php

namespace OTGS\Toolset\Maps;

/**
 * Class Bootstrap
 * @package OTGS\Toolset\Maps
 * @since 1.5.3
 */
class Bootstrap {
	protected $soft_dependencies = array();

	public function __construct( array $do_available ) {
		$this->soft_dependencies = $do_available;
	}

	public function init() {
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ), 10 );
		add_action( 'toolset_common_loaded', array( $this, 'initialize_classes' ), 20 );
	}

	/**
	 * Register autoload classmap to Toolset Common autoloader
	 */
	public function register_autoloaded_classes() {
		$classmap = include( TOOLSET_ADDON_MAPS_PATH . '/application/autoload_classmap.php' );

		do_action( 'toolset_register_classmap', $classmap );
	}

	/**
	 * Initialize classes based on soft_dependencies.
	 */
	public function initialize_classes() {
		// TODO: new classes should be instantiated here, if their dependencies are met. E.g.:
		if ( in_array( 'views', $this->soft_dependencies ) ) {

		}
	}
}