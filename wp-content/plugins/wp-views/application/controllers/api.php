<?php

/**
 * Public Views hook API.
 *
 * This should be the only point where other plugins (incl. Toolset) interact with Views directly.
 * Always use as a singleton in production code.
 *
 * Note: WPV_Api is initialized on after_setup_theme with priority 10.
 *
 * When implementing filter hooks, please follow these rules:
 *
 * 1.  All filter names are automatically prefixed with 'wpv_'. Only lowercase characters and underscores
 *     can be used.
 * 2.  Filter names (without a prefix) should be defined in self::$callbacks.
 * 3.  For each filter, there should be a dedicated class implementing the WPV_Api_Handler_Interface. Name of the class
 *     must be WPV_Api_Handler_{$capitalized_filter_name}. So for example, for a hook to
 *     'wpv_get_available_views' you need to create a class 'WPV_Api_Handler_Get_Available_Views'.
 *
 * @note This gets available at after_setup_theme:9999 because we need to wait for Toolset Common to fully load.
 *
 * @since m2m
 */
final class WPV_Api {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public static function initialize() {
		$instance = self::get_instance();

		$instance->register_callbacks();
	}


	/** Prefix for the callback method name */
	const CALLBACK_PREFIX = 'callback_';

	/** Prefix for the handler class name */
	const HANDLER_CLASS_PREFIX = 'WPV_Api_Handler_';

	const DELIMITER = '_';


	private $callbacks_registered = false;


	/**
	 * @return array Filter names (without prefix) as keys, filter parameters as values:
	 *     - int $args: Number of arguments of the filter
	 *     - callable $callback: A callable to override the default mechanism.
	 * @since m2m
	 */
	private function get_callbacks_to_register() {
		return array(

			/**
			 * wpv_get_available_views
			 *
			 * Return a list of published Views, as stored in the right cached transient.
			 *
			 * Generates the transient in case it is not set.
			 *
			 * @return array
			 * @since m2m
			 */
			'get_available_views' => array( 'args' => 1 ),
			
			/**
			 * wpv_get_available_content_templates
			 *
			 * Return a list of published Content Templates, as stored in the right cached transient.
			 *
			 * Generates the transient in case it is not set.
			 *
			 * @return array
			 * @since m2m
			 */
			'get_available_content_templates' => array( 'args' => 1 ),
		);
	}


	private function register_callbacks() {

		if ( $this->callbacks_registered ) {
			return;
		}
		
		// Legacy Views API
		// TODO port to this new structure
		$wpv_api_legacy = new WPV_API_Legacy();
		$wpv_api_legacy->initialize();

		foreach( $this->get_callbacks_to_register() as $callback_name => $args ) {

			$argument_count = toolset_getarr( $args, 'args', 1 );

			$callback = toolset_getarr( $args, 'callback', null );
			if ( ! is_callable( $callback ) ) {
				$callback = array( $this, self::CALLBACK_PREFIX . $callback_name );
			}

			add_filter( 'wpv_' . $callback_name, $callback, 10, $argument_count );
		}

		$this->callbacks_registered = true;

	}


	/**
	 * Handle a call to undefined method on this class, hopefully an action/filter call.
	 *
	 * @param string $name Method name.
	 * @param array $parameters Method parameters.
	 * @since 2.1
	 * @return mixed
	 */
	public function __call( $name, $parameters ) {
		
		$default_return_value = toolset_getarr( $parameters, 0, null );
		
		// Check for the callback prefix in the method name
		$name_parts = explode( self::DELIMITER, $name );
		if( 0 !== strcmp( $name_parts[0] . self::DELIMITER, self::CALLBACK_PREFIX ) ) {
			// Not a callback, resign.
			return $default_return_value;
		}

		// Deduct the handler class name from the callback name
		unset( $name_parts[0] );
		$class_name = implode( self::DELIMITER, $name_parts );
		$class_name = strtolower( $class_name );
		$class_name = mb_convert_case( $class_name, MB_CASE_TITLE );
		$class_name = self::HANDLER_CLASS_PREFIX . $class_name;

		// Obtain an instance of the handler class.
		try {
			/** @var Types_Api_Handler_Interface $handler */
			$handler = new $class_name();
		} catch( Exception $e ) {
			// The handler class could not have been instantiated, resign.
			return $default_return_value;
		}

		// Success
		return $handler->process_call( $parameters );
	}
	
}
