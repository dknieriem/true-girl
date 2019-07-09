<?php

abstract class Toolset_Theme_Integration_Settings_Abstract_Controller{

	const TOOLSET_DEFAULT = 'toolset_use_theme_setting';
	const TOOLSET_SWITCH_DEFAULT = 'toolset_use_theme_setting';
	const TOOLSET_SWITCH_CUSTOM = 'toolset_use_custom';
	const TOOLSET_USE_THEME_SETTING = 'toolset_use_theme_setting';
	const TOOLSET_THEME_NO_DEFAULT = 'toolset_no_default';

	protected $object_id = null;
	protected $is_views_active = false;
	protected $is_layouts_active = false;
	protected $collections = null;

	protected $active_theme = null;
	protected $theme_name = null;
	protected $theme_slug = null;
	protected $theme_parent_name = null;
	protected $theme_parent_slug = null;
	protected $theme_domain = null;
	/**
	 * @var bool
	 * tracks if there was any real change in layouts object to avoid useless calls
	 */
	protected $is_layout_settings_saved = false;
	protected $helper = null;
	protected $current_object_type = null;

	protected $allowed_targets = array( "global" => "global", "customizer" => "customizer", "local" => "local", "control_filters" => "control_filters", "toolset_custom" => "toolset_custom" );

	/**
	 * Toolset_Theme_Integration_Settings_Abstract_Controller constructor.
	 *
	 * @param Toolset_Theme_Integration_Settings_Helper|null $helper
	 * @param null $arg_one
	 */
	public function __construct( Toolset_Theme_Integration_Settings_Helper $helper = null, $arg_one = null ){
		$this->helper = $helper;
		$this->object_id = $this->helper->get_object_id();
		$this->current_object_type = $this->helper->get_current_object_type();
		$this->is_views_active = $this->helper->is_views_active();
		$this->is_layouts_active = $this->helper->is_layouts_active();

		$this->set_up_active_theme();

		$this->init();
	}

	public function init(){
		$this->collections = Toolset_Theme_Integration_Settings_Collections_Factory::getInstance();
		$this->admin_init();
		add_action( 'toolset_theme_settings_force_settings_refresh', array(
			$this, 'force_settings_refresh' ) );
	}

	public function admin_init(){
		// if config file doesn't exist stop executing
		if ( ! $this->collections->get_collections() || count( $this->collections->get_collections() ) === 0 ){
			return;
		}
		$this->add_hooks();

	}

	public function add_hooks(){

		add_action( 'wp_ajax_toolset_theme_integration_get_section_display_type', array(
			$this,
			'ajax_get_layout_assignment_type'
		) );

		add_action( 'wp_ajax_toolset_theme_integration_save_wpa_settings', array(
			$this,
			'views_wpa_save_theme_settings'
		) );

		add_action( 'wp_ajax_toolset_theme_integration_save_ct_settings', array(
			$this,
			'views_ct_save_theme_settings'
		) );

		// save user selected settings in current layout object (JSON)
		add_filter( 'ddl_layout_settings_save', array( $this, 'layout_settings_save_callback' ), 10, 3 );

		// update post metas when layout object has already been saved to DB
		add_action( 'ddl_action_layout_has_been_saved', array(
			$this,
			'layout_has_been_saved_callback'
		), 99, 3 );


		// update posmetas for $post resources assigned to layout when assignments have been removed
		add_filter( 'remove_layout_assignment_to_post_object', array( $this, 'handle_resource_assignment_removed' ), 99, 5 );

		// update posmetas for $post resources assigned to layout when assignments have been removed
		add_filter( 'wpv_filter_remove_ct_assignment_from_post_object', array( $this, 'handle_resource_assignment_removed' ), 99, 3 );

		// update postmeta for $post resource assigned to layout or CT when the post is created or edited.
		add_action('save_post', array($this, 'handle_resource_assignment_change'), 99);
	}

	protected function set_up_active_theme(){
		$theme_data = $this->helper->get_current_theme_data();

		$this->active_theme = wp_get_theme();
		$this->theme_domain = $this->get_theme_domain();

		$this->theme_name = $theme_data['theme_name'];
		$this->theme_slug = $theme_data['theme_slug'];
		$this->theme_parent_name = $theme_data['theme_parent_name'];
		$this->theme_parent_slug = $theme_data['theme_parent_slug'];
	}

	public function get_theme_domain(){

		if( !$this->active_theme ) return '';

		$active_theme_textdomain = $this->active_theme->get('TextDomain');

		if ( empty( $active_theme_textdomain ) ) {
			return '';
		}

		return $active_theme_textdomain;

	}

	/**
	 * @since 2.5
	 *
	 * @param $json
	 * @param $post
	 * @param $raw
	 * hooks to layouts to save_current_value the custom theme settings to the Layout object
	 *
	 * @return mixed
	 */
	protected function layout_save_theme_settings( $post_data, $json ) {

		parse_str( $post_data, $theme_settings );

		if ( is_string( $theme_settings ) ) {
			$theme_settings = array( $theme_settings );
		}

		if ( is_array( $theme_settings ) && count( $theme_settings ) ) {
			// save only if there is a difference, otherwise skip
			if (
				! isset( $json[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ] )
				|| $json[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ] !== $theme_settings
			) {
				$json[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ] = $theme_settings;
				$this->is_layout_settings_saved = true;
			}
		}

		return $json;
	}

	/**
	 * @param $toolset_theme_settings
	 * @param null $object_ids
	 * @param bool $force_delete
	 *
	 * @return int
	 */
	protected function update_local_models_values( $toolset_theme_settings, $object_ids = null, $force_delete = false ) {

		if ( empty( $object_ids ) ) {
			return 0;
		}

		try {
			$allowed_targets = $this->allowed_targets;
			$local_collection = $this->collections->get_collection_by_type( $allowed_targets['local'] );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			return -1;
		}

		if ( is_null( $local_collection ) ) {
			return 0;
		}

		$updated = 0;

		foreach( $toolset_theme_settings as $name => $current_value ) {
			$model = $local_collection->where( 'name', $name );
			if (
				isset( $model[0] )
				&& $model[0] instanceof Toolset_Theme_Integration_Settings_Model_local
				&& ! $this->skip_update_local_model_values( $model[0], $toolset_theme_settings )
			) {

				$ret = $this->update_model_values( $model[0], $current_value, $object_ids, $force_delete );
				if ( $ret ) {
					$updated++;
				}
			}
		}

		return $updated;
	}

	/**
	 * @param $model
	 * @param $toolset_theme_settings
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function skip_update_local_model_values( $model, $toolset_theme_settings ) {

		if ( $model instanceof Toolset_Theme_Integration_Settings_Model_Interface === false ) {
			throw new Exception( sprintf( '%s type is not supported by %s method, only implementations of %s interface are allowed!', gettype( $model ),__METHOD__, 'Toolset_Theme_Integration_Settings_Model_Interface' ) );
		}

		if (
			isset( $toolset_theme_settings[ 'toolset_switch_' . $model->name ] )
			&& $toolset_theme_settings[ 'toolset_switch_' . $model->name ] == $model->get_default_switch_value()
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param $model
	 * @param $current_value
	 * @param $object_ids
	 * @param bool $force_delete
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function update_model_values( $model, $current_value, $object_ids, $force_delete = false ){

		if ( $model instanceof Toolset_Theme_Integration_Settings_Model_Interface === false ){
			throw new Exception( sprintf( '%s type is not supported by %s method, only implementations of %s interface are allowed!', gettype( $model ),__METHOD__, 'Toolset_Theme_Integration_Settings_Model_Interface' ) );
		}

		// to delete a $meta set model value to NULL and model will do the rest
		if ( $force_delete ){
			$current_value = NULL;
		}

		$model->set_current_value( $current_value );

		if ( is_array( $object_ids ) ) {
			return $model->update_saved_value_for_all_resources( $object_ids );
		} elseif ( is_int( $object_ids ) ) {
			return $model->save_current_value( $object_ids );
		}

		return false;
	}

	/**
	 * @param $update
	 * @param $layout_id
	 * @param $json
	 * Callback to "ddl_action_layout_has_been_saved" action hook, it runs after layout data has been successfully saved to DB
	 */
	public function layout_has_been_saved_callback( $update, $layout_id, $json ) {
		if (
			isset( $json[ TOOLSET_THEME_SETTINGS_DATA_KEY ] )
			&& isset( $json[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ] )
			&& $this->is_layout_settings_saved
		) {
			$layout_post_ids = apply_filters( 'ddl-get_layout_posts_ids', $layout_id );
			$this->update_local_models_values( $json[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ], $layout_post_ids );
			$this->is_layout_settings_saved = false;
		}
	}

	/**
	 * @param $json
	 * @param $post
	 * @param $raw
	 *
	 * @return mixed
	 * Callback to "ddl_layout_settings_save" filter hook, allows to override $json (layout object) at the time it is saved to DB
	 */
	public function layout_settings_save_callback( $json, $post, $raw ) {

		if ( isset( $_POST['theme_settings'] ) ) {
			$json = $this->layout_save_theme_settings( $_POST['theme_settings'], $json );
		}

		return $json;
	}

	/**
	 * @param $post_id
	 * @param bool $force_delete tells update_local_models_values method to force delete meta records from DB, defaults to false
	 *
	 * @return null
	 * Updates single post meta with single options values when the theme settings or related resources are saved.
	 */
	public function update_post_theme_settings_meta( $post_id, $force_delete = false, $resource = 'none' ) {
		if ( ! $this->helper->has_theme_settings() ) {
			return null;
		}

		$toolset_theme_settings = $this->helper->get_current_settings();

		if ( ! $toolset_theme_settings ) {
			return null;
		}

		// Views set the value to ZERO rather than using delete_post_meta, so when $ct_id is ZERO let's remove the theme settings metas
		if ( is_numeric( $resource ) && (int) $resource === 0 && $force_delete === false ) {
			$force_delete = true;
		}

		$this->update_local_models_values( $toolset_theme_settings, (int) $post_id, $force_delete );

		return $post_id;
	}

	/**
	 * @param $post_id
	 * @param string $resource
	 *
	 * @return mixed
	 * updates post meta when $resource assignment changes.
	 */
	public function handle_resource_assignment_change( $post_id, $resource = 'none' ) {
		$this->helper->load_current_settings_object( $post_id );
		$this->update_post_theme_settings_meta( $post_id, false, $resource );
	}


	/**
	 * @param $ret
	 * @param $post_id
	 * @param string $resource
	 *
	 * @return mixed
	 * updates post meta when $resource assignment has been removed.
	 */
	public function handle_resource_assignment_removed( $ret, $post_id, $resource = 'none' ) {
		$this->update_post_theme_settings_meta( $post_id, true );
		return $ret;
	}

	/**
	 * @since 2.5
	 * AJAX callback for toolset_theme_integration_save_ct_settings action, responsible for saving theme settings into Views CT
	 */
	public function views_ct_save_theme_settings() {
		$uid = get_current_user_id();

		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		} else {
			$ct_id = (int) $_POST['id'];
		}

		if (
			! isset( $_POST['wpnonce'] )
			|| ! wp_verify_nonce( $_POST['wpnonce'], "wpv_ct_{$ct_id}_update_properties_by_{$uid}" )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if ( empty( $_POST['theme_settings'] ) ) {
			$data = array(
				'type'    => 'data_missing',
				'message' => __( 'Wrong or missing data.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		parse_str( $_POST['theme_settings'], $theme_settings );

		$theme_settings_array = get_post_meta( $ct_id, '_views_template_theme_settings', true );
		$theme_settings_array = empty( $theme_settings_array ) ? array() : $theme_settings_array;
		$theme_settings_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ] = isset( $theme_settings_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ] )
			? $theme_settings_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ]
			: array();
		$theme_settings_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ] = $theme_settings;

		update_post_meta( $ct_id, '_views_template_theme_settings', $theme_settings_array );

		$data = array(
			'id'      => $ct_id,
			'message' => __( 'Theme settings saved', 'wpv-views' ),
		);

		$this->update_post_meta_after_content_template_update( $ct_id, $theme_settings );

		wp_send_json_success( $data );
	}

	/**
	 * @since 2.5
	 *
	 * @param $ct_id
	 * updates the post meta for posts associated with the updated CT.
	 */
	public function update_post_meta_after_content_template_update( $ct_id, $theme_settings ) {

		$ct_post_ids = apply_filters( 'wpv_get_posts_by_content_template', array(), $ct_id );

		if ( ! count( $ct_post_ids ) ) {
			return;
		}

		$where_used = array_map( array( $this, 'filter_int_ids' ), $ct_post_ids );

		$this->update_local_models_values( $theme_settings, $where_used );
	}

	public function filter_int_ids( $item ) {
		return (int) $item;
	}

	/**
	 * @since 2.5
	 * AJAX callback for toolset_theme_integration_save_wpa_settings action, responsible for saving theme settings into Views WPA
	 */
	public function views_wpa_save_theme_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['wpnonce'] )
			|| ! wp_verify_nonce( $_POST['wpnonce'], 'wpv_nonce_editor_nonce' )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if ( empty( $_POST['theme_settings'] ) ) {
			$data = array(
				'type'    => 'data_missing',
				'message' => __( 'Wrong or missing data.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		$view_id = intval( $_POST['id'] );
		parse_str( $_POST['theme_settings'], $theme_settings );

		$view_array = get_post_meta( $view_id, '_wpv_settings', true );
		$view_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ] = isset( $view_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ] )
			? $view_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ]
			: array();
		$view_array[ TOOLSET_THEME_SETTINGS_DATA_KEY ][ $this->theme_slug ] = $theme_settings;
		update_post_meta( $view_id, '_wpv_settings', $view_array );

		do_action( 'wpv_action_wpv_save_item', $view_id );

		$data = array(
			'id'      => $view_id,
			'message' => __( 'Theme settings saved', 'wpv-views' ),
		);

		wp_send_json_success( $data );
	}

	/**
	 * @since 2.5
	 *
	 * @param $layout_assignment string
	 *
	 * @return string
	 * returns the help tip message for layouts based on assignment
	 */
	public function get_layouts_tip_message( $layout_assignment ) {
		if ( $layout_assignment == 'archive' ) {
			return __( 'This section lets you control the settings of the theme for the archive that uses this layout.', 'wp-views' );
		} elseif ( $layout_assignment == 'posts' ) {
			return __( 'This section lets you control the settings of the theme for all the pages that use this layout.', 'wp-views' );
		} else {
			return __( 'This section lets you control the settings of the theme for content that uses this layout.', 'wp-views' );
		}
	}

	/**
	 * @since 2.5
	 * Fetches the layout assignment for a given layout
	 */
	public function ajax_get_layout_assignment_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'toolset_theme_display_type' ) ) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		} else {
			$layout_id = (int) $_POST['id'];
		}

		$assignment_type = $this->get_layout_assignment_type( $layout_id );
		$data            = array(
			'display_type'    => $assignment_type,
			'tooltip_message' => $this->get_layouts_tip_message( $assignment_type )
		);

		wp_send_json_success( $data );
	}

	/**
	 * @since 2.5
	 * Returns the section types to be displayed based on the layout assignment
	 */
	public function get_layout_assignment_type( $layout_id ) {
		$archives     = apply_filters( 'ddl-get_layout_loops', $layout_id );
		$single       = apply_filters( 'ddl-layout_at_least_one_single_assignment', $layout_id );
		$assigned_to_post_types  = apply_filters('ddl-get_layout_post_types_object', $layout_id , true);
		$section_type = null;

		if ( $this->helper->has_theme_setting_by_key( 'toolset_layout_to_cred_form' ) ) {
			$section_type = $this->helper->get_current_settings_by_key( 'toolset_layout_to_cred_form' );
		}

		if ( is_array( $archives ) && count( $archives ) > 0 ) {
			$section_type = 'archive';
		}

		if (
			$single === true
			|| (
				is_array( $assigned_to_post_types )
				&& count( $assigned_to_post_types ) > 0
			)
		) {
			$section_type = ( $section_type == 'archive' ) ? 'shared' : 'posts';
		}

		return $section_type;
	}

	/**
	 * @param null $post_id
	 * @param null $object_id
	 *
	 * @return null/array
	 */
	protected function get_current_settings_object( $post_id = null, $object_id = null ) {
		if ( ! $this->helper->has_theme_settings() ) {
			$this->helper->load_current_settings_object( $post_id, $object_id );
		}
		return $this->helper->get_current_settings();
	}

	/**
	 * @param null $post_id
	 * @param null $object_id
	 */
	public function force_settings_refresh( $post_id = null, $object_id = null ){
		$this->helper->load_current_settings_object( $post_id, $object_id );
		$did_reload = $this->reload_models_values();
		if( $did_reload ){
			do_action( 'toolset_theme_settings_did_settings_refresh' );
		}
	}

	/**
	 * @return bool
	 */
	protected function reload_models_values(){
		$at_least_one_reloaded = false;
		$settings = $this->helper->get_current_settings();

		if( empty( $settings ) ){
			return $at_least_one_reloaded;
		}

		foreach( $settings as $name => $value ){
			$models = $this->collections->where( 'name', $name );
			foreach( $models as $model ){
				if( $model instanceof Toolset_Theme_Integration_Settings_Model_Interface &&
				    ( $this->allowed_targets['global'] === $model->type ||
				      $this->allowed_targets['customizer']  === $model->type ||
				      $this->allowed_targets['local']  === $model->type ) &&
				    $value !== $model->get_default_value()
				){
					$model->set_current_value( $value );
					$at_least_one_reloaded = true;
				}
			}
		}

		return $at_least_one_reloaded;
	}

	/**
	 * @since 2.5
	 *
	 * @param $key - the setting name
	 * @param $default - default, which will be returned if no value is found in the settings object
	 *
	 * @return mixed
	 * returns the value to be used or rendered, it decides whether to load saved or default
	 */
	public function get_theme_setting( $key, $default = self::TOOLSET_THEME_NO_DEFAULT ) {

		$this->get_current_settings_object();

		if ( $this->helper->has_theme_setting_by_key( $key ) ) {
			$loaded_value = $this->helper->get_current_settings_by_key( $key );
			if ( $loaded_value === self::TOOLSET_USE_THEME_SETTING ) {
				return $default;
			}

			return $loaded_value;
		}

		if ( $default !== self::TOOLSET_THEME_NO_DEFAULT ) {
			return $default;
		}

		return $this->get_setting_default( $key );
	}

	/**
	 * @since 2.5
	 *
	 * @param $key - the setting name
	 *
	 * @return mixed
	 * returns the default value for a specific theme defined in the JSON file
	 */
	public function get_setting_default( $key ) {
		$models = $this->collections->where( 'name', $key );

		if ( isset( $models[0] ) && $models[0] instanceof Toolset_Theme_Integration_Settings_Model_Interface ) {
			return $models[0]->get_default_value();
		}

		return null;
	}


}