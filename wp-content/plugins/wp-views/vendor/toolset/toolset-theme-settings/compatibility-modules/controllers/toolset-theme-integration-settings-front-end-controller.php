<?php

class Toolset_Theme_Integration_Settings_Front_End_Controller extends Toolset_Theme_Integration_Settings_Abstract_Controller {

	protected $allowed_filter_callbacks = array(
		'__return_false',
		'__return_true',
		'__return_zero',
		'__return_empty_array',
		'__return_null',
		'__return_empty_string'
	);
	/**
	 * @var array
	 * store the callbacks we want to execute another later to try again to hook into them
	 */
	protected $global_options_hooks = array();
	/**
	 * @var array
	 * store global and customizer options as key value pairs for later check/use
	 */
	protected $global_options = array();

	public function __construct( Toolset_Theme_Integration_Settings_Helper $helper = null, $arg_one = null ){
		parent::__construct( $helper, $arg_one );
		$this->add_pre_options_filters();
	}

	public function init(){
		parent::init();
	}

	public function add_hooks(){
		add_filter( 'toolset_theme_integration_get_setting', array( $this, 'filter_get_setting' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'register_theme_filters' ) );
		add_action( 'wp', array( $this, 'execute_global_option_hooks' ), 10 );
		add_action( 'toolset_theme_settings_did_settings_refresh', array($this, 'reload_global_and_customizer_hooks') );
		add_filter( "get_post_metadata", array( $this, 'get_settings_for_layout_or_ct_passed_from_url'), 10, 4) ;
	}

	public function get_settings_for_layout_or_ct_passed_from_url( $value, $object_id, $meta_key, $single){

		if( apply_filters('force_get_settings_for_layout_or_ct_passed_from_url', false ) ){
			return $this->override_local_post_meta_value( $value, $object_id, $meta_key, $single );
		}

		if( ! isset( $_GET['layout_id'] ) && ! isset( $_GET['content-template-id'] ) )  {
			return $value;
		}

		return $this->override_local_post_meta_value( $value, $object_id, $meta_key, $single );

	}

	protected function override_local_post_meta_value( $value, $object_id, $meta_key, $single ){
		global $post;

		if( ! $post || ( $post->ID !== $object_id ) ) {
			return $value;
		}

		$allowed_targets = $this->allowed_targets;
		$local_collection = $this->collections->get_collection_by_type( $allowed_targets['local'] );

		if ( is_null( $local_collection ) ) {
			return $value;
		}

		$model = $local_collection->where( 'name', $meta_key );

		if( isset($model[0]) ){
			$model = $model[0];
		} else {
			return $value;
		}

		if( $model->get_current_value() && $model->get_current_value() !== $model->get_default_value() ){
			return $model->get_current_value();
		}


		return $value;
	}

	/**
	 * @since 2.5
	 *
	 * @param $value - null value to be populated with fetched settings value
	 * @@param $setting_key - name of the setting to be retrieved
	 *
	 * @return mixed
	 * returns the value for a specific setting, and used in a filter
	 */
	public function filter_get_setting( $value, $setting_key ) {
		if ( $setting_key ) {
			$value = $this->get_theme_setting( $setting_key );
		}

		return $value;
	}

	/**
	 * Adds filters programmatically from the JSON file based on the user choice
	 */
	public function register_theme_filters() {

		$allowed_targets = $this->allowed_targets;
		$control_filters = $this->collections->get_collection_by_type( $allowed_targets['control_filters'] );

		if ( empty( $control_filters ) ) {
			return;
		}

		foreach ( $control_filters->getIterator() as $model ) {
			if ( $model instanceof Toolset_Theme_Integration_Settings_Model_control_filters && in_array( $model->filter_method, $this->allowed_filter_callbacks ) ) {
				if ( $model->get_current_value() && $model->get_default_value() !== $model->get_current_value() ) {
					add_filter( $model->name, $model->filter_method );
				}
			}
		}
	}

	/**
	 * @since 2.5
	 * filters the global options present in the config file.
	 */
	public function add_filter_global_option_objects() {
		$global_models = $this->get_global_collection_items();

		if ( null === $global_models ) {
			return;
		}

		foreach ( $global_models as $model ) {
			if( !is_null( $model->get_current_value() ) && $model->get_current_value() !== $model->get_default_value() ){
				$this->global_options[$model->name] = $model->get_current_value();
				add_filter( "pre_option_{$model->name}", array( $this, "pre_option_save" ), 99, 2 );
				add_filter( "option_{$model->name}", array( $this, "pre_option_save" ), 99, 2 );
				add_filter( "default_option_{$model->name}", array( $this, "pre_option_save" ), 99, 2 );
			}
		}
	}

	public function add_filter_customizer_option_objects() {
		$customizer_models = $this->get_customizer_collection_items();

		if ( null === $customizer_models ) {
			return;
		}

		foreach ( $customizer_models as $model ) {
			if( ! is_null( $model->get_current_value() ) && $model->get_current_value() !== $model->get_default_value() ){
				$this->global_options[$model->name] = $model->get_current_value();
				add_filter( "theme_mod_{$model->name}", array(
					$this,
					"pre_customizer_option_value"
				), 99 );
			}
		}
	}

	public function add_filter_global_option_key(){
		$global_key = $this->get_global_key();

		if( !$global_key ) return;

		add_filter( "pre_option_{$global_key}", array(
			$this,
			"pre_global_option_save"
		), 99, 2 );
		add_filter( "option_{$global_key}", array(
			$this,
			"pre_global_option_save"
		), 99, 2 );
		add_filter( "default_option_{$global_key}", array(
			$this,
			"pre_global_option_save"
		), 99, 2 );
	}


	protected function get_global_collection_items() {
		$allowed_targets = $this->allowed_targets;
		$global = $this->get_collection_by_type( $allowed_targets['global'] );
		return $global instanceof Toolset_Theme_Integration_Settings_Model_Collection ? $global->getIterator() : null;
	}

	protected function get_customizer_collection_items() {
		$allowed_targets = $this->allowed_targets;
		$customiser = $this->get_collection_by_type( $allowed_targets['customizer'] );
		return $customiser instanceof Toolset_Theme_Integration_Settings_Model_Collection ? $customiser->getIterator() : null;
	}

	protected function get_global_key() {
		$allowed_targets = $this->allowed_targets;
		$global = $this->get_collection_by_type( $allowed_targets['global'] );
		if( empty( $global ) ) return null;
		$model = $global->getItem(0);
		return $model->global_key;
	}

	protected function get_collection_by_type( $type ){
		return $this->collections->get_collection_by_type( $type );
	}

	protected function add_pre_options_filters(){
		$this->add_filter_customizer_option_objects();
		$this->add_filter_global_option_objects();
		$this->add_filter_global_option_key();
	}

	public function reload_global_and_customizer_hooks(){
		$this->add_filter_customizer_option_objects();
		$this->add_filter_global_option_objects();
		$this->add_filter_global_option_key();
	}
	
	/**
	 * @since 2.5
	 *
	 * @param $value
	 * @param $option_key
	 *
	 */
	public function pre_option_save( $value, $option_key ) {

		$this->collect_postsponed_callbacks( $option_key );

		$globals = $this->get_global_collection_items();
		
		$model = $globals->where( 'name', $option_key );
		
		if( isset( $model[0] ) && $model[0] instanceof Toolset_Theme_Integration_Settings_Model_global ){

			$model = $model[0];

			if ( ! is_null( $model->get_current_value() ) &&
			     $model->get_current_value() !== $model->get_default_value() &&
			     $model->get_current_value() !== $value
			) {
				$value = $model->get_current_value();
				if ( 'boolean' === $model->get_expected_value_type() ){
					$value = (bool) $value;
				}
			}
		}

		return $value;
	}

	/**
	 * @since 2.5
	 *
	 * @param $theme_setting
	 * @param $option_key
	 *
	 */
	public function pre_global_option_save( $theme_setting, $option_key ) {

		$this->collect_postsponed_callbacks( $option_key );

		$globals = $this->get_global_collection_items();

		if ( is_array( $theme_setting ) && $globals && $this->helper->has_theme_settings() ) {

			foreach ( $globals as $option ) {
				$option_name = $option->name;

				if ( ! is_null( $option->get_current_value() ) &&
				     $option->get_current_value() !== $option->get_default_value()
				) {
					$theme_setting[ $option_name ] = $option->get_current_value();
				}
			}
		}
		elseif ( !is_array( $theme_setting ) && array_key_exists( $option_key, $this->global_options ) ) {
			$theme_setting = $this->global_options[ $option_key ];
		}

		return $theme_setting;
	}
	/**
	 * @since 2.5
	 *
	 * @param $default - the filter value
	 *
	 * @return mixed
	 * Hooks to Customizer theme_mod_{option_name} to override the Customizer saved settings.
	 */
	public function pre_customizer_option_value( $default ) {
		$mod_name = str_replace( 'theme_mod_', '', current_filter() );

		$allowed_targets = $this->allowed_targets;
		$collection = $this->get_collection_by_type( $allowed_targets['customizer'] );

		if( $collection instanceof Toolset_Theme_Integration_Settings_Model_Collection === false ){
			return $default;
		}

		$model = $collection->where( 'name', $mod_name );

		if ( !isset( $model[0] ) || is_null( $model[0]->get_current_value() ) || $model[0]->get_current_value() === $model[0]->get_default_value() ) {
			return $default;
		}

		if ( 
			array_key_exists( $mod_name, $this->global_options ) 
			&& ! is_null( $model[0]->get_current_value() ) 
			&& $model[0]->get_current_value() !== $model[0]->get_default_value() 
		) {
			return $model[0]->get_current_value();
		}

		return $default;
	}

	/**
	 * @since 2.5
	 *
	 * @param $option_key
	 * This solves the issue with options that are being loaded way before Layouts or Views are initialised so we cannot apply our settings
	 * on those, so to solves this we store the information of the function that called the get_option function with a specific option_name
	 * and recall this function again on wp action, which at the time our plugins are ready and override the settings successfully.
	 *
	 * This is not the most proper fix in the world, but why we cannot provide an alternative fix is we don't have our code base initialised that early
	 * so this seems like the only possible fix now.
	 *
	 */
	protected function collect_postsponed_callbacks( $option_key ){
		//Check is WP ran other times after our call on 0, if not, trace the call and add an action to refresh the options again
		if ( did_action( 'wp' ) === 1 ) {
			$call_stack    = debug_backtrace();
			$is_next_call  = false;
			$caller_object = null;
			foreach ( $call_stack as $call ) {
				if ( $is_next_call ) {
					$caller_object = $call;
					break;
				}
				if ( $call['function'] == 'get_option' && count( $call['args'] ) > 0 && $call['args'][0] == $option_key ) {
					$is_next_call = true;
				}
			}
			if ( ! empty( $caller_object ) ) {
				$this->global_options_hooks[] = $caller_object;
			}
		}
	}

	/**
	 * @since 2.5
	 * Runs the stored global option injection hooks on WP action.
	 */
	public function execute_global_option_hooks() {
		try {
			foreach ( $this->global_options_hooks as $caller_object ) {
				if ( isset( $caller_object['type'] ) ) {
					switch ( $caller_object['type'] ) {
						case "->":
							call_user_func_array( array(
								$caller_object['object'],
								$caller_object['function']
							), array_values( $caller_object['args'] ) );
							break;
						case "::":
							call_user_func_array( array(
								$caller_object['class'],
								$caller_object['function']
							), array_values( $caller_object['args'] ) );
							break;
					}
				} else {
					call_user_func_array( $caller_object['function'], array_values( $caller_object['args'] ) );
				}
			}
		} catch ( Exception $e ) {
			error_log( 'Toolset failed to overwrite theme options ' . $e->getMessage() );
		}

	}
}