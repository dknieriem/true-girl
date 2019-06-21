<?php

use \OTGS\Toolset\Maps\Model\Shortcode\Distance\ConditionalDisplay;
use \OTGS\Toolset\Maps\Model\Shortcode\Distance\Value;
use \OTGS\Toolset\Maps\Controller\Compatibility;

/**
* Toolset Maps - Common methods
*
* @package ToolsetMaps
*
* @since 1.0
*/
class Toolset_Addon_Maps_Common {

	const option_name					= 'wpv_addon_maps_options';
	const address_coordinates_option	= 'toolset_maps_address_coordinates';

	const API_GOOGLE = 'google';
	const API_AZURE  = 'azure';
	const API_AZURE_URL = 'https://atlas.microsoft.com/search/address/json';

	static $used_map_ids		= array();
	static $used_marker_ids		= array();

	static $coordinates_set		= null;
	static $coordinates_save	= false;

	static $stored_options		= array();

	static $maps_api_url_js			= '//maps.googleapis.com/maps/api/js';
	static $maps_api_url_geocode	= 'https://maps.googleapis.com/maps/api/geocode/json';

	protected $default_options = array(
		'marker_images'		    => array(),
		'map_counter'		    => 0,
		'marker_counter'	    => 0,
		'api_key'			    => '',
		'server_side_api_key'   => '',
		'azure_api_key'         => '',
		'map_style_files'       => array(),
		'default_map_style'     => 'Standard',
		'api_used'              => self::API_GOOGLE,
	);

	public static $map_defaults = array(
		'map_width'					=> '100%',
		'map_height'				=> '250px',
		'general_zoom'				=> 5,
		'general_center_lat'		=> 0,
		'general_center_lon'		=> 0,
		'fitbounds'					=> 'on',
		'single_zoom'				=> 14,
		'single_center'				=> 'on',
		'map_type'					=> 'roadmap',
		'show_layer_interests'		=> 'false',
		'marker_icon'				=> '',
		'marker_icon_hover'			=> '',
		'draggable'					=> 'on',
		'scrollwheel'				=> 'on',
		'double_click_zoom'			=> 'on',
		'map_type_control'			=> 'on',
		'full_screen_control'		=> 'off',
		'zoom_control'				=> 'on',
		'street_view_control'		=> 'on',
		'background_color'			=> '',
		'cluster'					=> 'off',
		'cluster_grid_size'			=> 60,
		'cluster_max_zoom'			=> '',
		'cluster_click_zoom'		=> 'on',
		'cluster_min_size'			=> 2,
		'spiderfy'                  => 'off',
		'style_json'                => '',
        // Street View attributes
        'street_view'               => 'off',
        'marker_id'                 => '',
        'location'                  => '',
        'address'                   => '',
		'heading'                   => '',
		'pitch'                     => '',
	);

	public static $icons_added = array();

	function __construct() {

		

		self::$stored_options = get_option( self::option_name, array() );

		add_action( 'init',				array( $this, 'init' ), 5 );
		add_action( 'admin_init',		array( $this, 'admin_init' ) );

		add_filter( 'toolset_filter_toolset_maps_get_options',		array( $this, 'get_options' ) );
		add_action( 'toolset_filter_toolset_maps_update_options',	array( $this, 'update_options' ) );

		add_filter( 'toolset_filter_toolset_maps_get_api_key',		        array( $this, 'get_api_key' ) );
		add_filter( 'toolset_filter_toolset_maps_get_server_side_api_key',  array( $this, 'get_server_side_api_key' ) );
		add_filter( 'toolset_filter_toolset_maps_get_azure_api_key',        array( $this, 'get_azure_api_key' ) );

		/**
		* toolset_is_maps_available
		*
		* Filter to check whether Toolset Maps is installed
		*
		* @since 1.2
		*/

		add_filter( 'toolset_is_maps_available', '__return_true' );

		add_filter( 'toolset_maps_get_api_used',    array( $this, 'get_api_used' ) );
	}

	function get_options( $options = array() ) {
		$stored_options = self::$stored_options;
		self::$stored_options = wp_parse_args( $stored_options, $this->default_options );

		return self::$stored_options;
	}

	function set_options() {
		update_option( self::option_name, self::$stored_options );
	}

	function update_options( $options = array() ) {
		$options = wp_parse_args( $options,	$this->default_options );
		self::$stored_options = $options;
		$this->set_options();
	}

	function get_api_key( $api_key = '' ) {
		$saved_options = $this->get_options();
		return $saved_options['api_key'];
	}

	/**
     * Server-side API key may or may not be set - if it isn't return first API key to be used instead
     * @since 1.5
	 * @return string
	 */
	public function get_server_side_api_key() {
        $saved_options = $this->get_options();

        if ( empty( $saved_options['server_side_api_key'] ) ) {
            return $saved_options['api_key'];
        }
        return $saved_options['server_side_api_key'];
	}

	function init() {

		$this->register_assets();
		add_action( 'wp_enqueue_scripts',		array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'toolset_enqueue_scripts',	array( $this, 'toolset_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts',	array( $this, 'admin_enqueue_scripts' ) );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links'), 10, 4 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

		add_action( 'wp_footer', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		add_action( 'admin_footer', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		// When saving meta values, the footer actions are not fired
		add_action( 'added_post_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		add_action( 'updated_post_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		//add_action( 'deleted_post_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		add_action( 'added_term_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		add_action( 'updated_term_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		//add_action( 'deleted_term_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		add_action( 'added_user_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		add_action( 'updated_user_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );
		//add_action( 'deleted_user_meta', array( $this, 'maybe_save_stored_coordinates_in_footer' ), 99 );

        // In a Layouts post content cell, Map shortcodes may get rendered twice. Reset used Ids in such case
        add_action( 'ddl-before_layout_render', array( $this, 'reset_used_ids' ), 10, 2);

		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'elementor_preview_enqueue_scripts' ) );

		// Generally available shortcodes, autoloaded when used
		add_shortcode(
			'toolset-maps-distance-conditional-display',
			array( $this, 'render_shortcode_toolset_maps_location_conditional_display' )
		);
		add_shortcode(
			'toolset-maps-distance-value',
			array( $this, 'render_shortcode_toolset_maps_distance_value' )
		);

		// Init Gutenberg block(s)
		$editor_blocks = new Compatibility\MapsEditorBlocks();
		$editor_blocks->initialize();
	}

	function admin_init() {
	    $this->register_admin_assets();

		// Admin notices
		add_action( 'admin_notices',											array( $this, 'display_admin_notices' ) );

		// Register the Map section
		add_filter( 'toolset_filter_toolset_register_settings_section',			array( $this, 'register_settings_maps_section' ), 60 );

		// Register the Google Maps API key section
		add_filter( 'toolset_filter_toolset_register_settings_maps_section',	array( $this, 'toolset_maps_api_key_options' ) );
		add_action( 'wp_ajax_wpv_addon_maps_update_api_key',					array( $this, 'toolset_maps_update_api_key' ) );

		// Add .json extension to allowed mime types in WP
		add_filter( 'upload_mimes',                                             array( $this, 'add_json_mime_type' ), 1, 1 );

		// AJAX callback to check G API
		add_action( 'wp_ajax_wpv_addon_maps_check_g_api',                       array( $this, 'check_g_api' ) );

		// Register API used section
		add_filter(
			'toolset_filter_toolset_register_settings_maps_section',
			array( $this, 'register_api_select_settings_section' ),
			5
		);

		// AJAX callback to update API used
		add_action( 'wp_ajax_toolset_maps_update_api_used', array( $this, 'update_api_used' ) );

		// Register Azure API key section
		add_filter(
			'toolset_filter_toolset_register_settings_maps_section',
			array ($this, 'register_azure_api_key_settings_section'),
			11
		);

		// AJAX callback to save Azure API key
		add_action( 'wp_ajax_toolset_maps_update_azure_api_key', array( $this, 'update_azure_api_key' ) );
	}

	/**
	 * Dispatches this shortcode to its autoloaded handler class
	 * @since 1.6
	 * @param array|string $atts
	 * @param string $content
	 * @param string $shortcode_tag
	 * @return string
	 */
	public function render_shortcode_toolset_maps_location_conditional_display( $atts, $content, $shortcode_tag ) {
		$renderer = new ConditionalDisplay( $atts, $content, $shortcode_tag );
		return $renderer->render();
	}

	/**
	 * @since 1.6
	 *
	 * @param array|string $atts
	 * @param string $content
	 * @param string $shortcode_tag
	 *
	 * @return string
	 */
	public function render_shortcode_toolset_maps_distance_value( $atts, $content, $shortcode_tag ) {
		$renderer = new Value( $atts, $content, $shortcode_tag );
		return $renderer->render();
	}

	/**
	 * Register settings section to change used API
	 *
	 * @since 1.5
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function register_api_select_settings_section( array $sections ) {
		$sections['maps-api-used'] = array(
			'slug'		=> 'maps-api-used',
			'title'		=> __( 'API to use', 'toolset-maps' ),
			'content'	=> $this->render_api_select_settings_section()
		);
		return $sections;
	}

	/**
	 * Render settings section to change used API
	 *
	 * @since 1.5
	 *
	 * @return string
	 */
	protected function render_api_select_settings_section() {
		$explanation = __(
			'Select the API to be used for maps rendering, geolocation and other functionalities of this plugin.',
			'toolset-maps'
		);
		$google = __( 'Google Maps API', 'toolset-maps' );
		$azure = __( 'Microsoft Azure Maps API', 'toolset-maps' );
		$api_google = self::API_GOOGLE;
		$api_azure = self::API_AZURE;
		$api_google_checked = checked( $this->get_api_used(), self::API_GOOGLE, false );
		$api_azure_checked = checked( $this->get_api_used(), self::API_AZURE, false );

		return <<<HTML
			<p>$explanation</p>
			<ul class="js-toolset-maps-api-used-form">
				<li>
					<label><input type="radio" name="toolset-maps-api-used" value="$api_google" $api_google_checked/>$google</label>
				</li>
				<li>
					<label><input type="radio" name="toolset-maps-api-used" value="$api_azure" $api_azure_checked/>$azure</label>
				</li>
			</ul>
HTML;
	}

	/**
	 * Return the API to be used. Compare the string returned to API_* constants to make decisions.
	 *
	 * @since 1.5
	 *
	 * @return string
	 */
	public function get_api_used( $api_used = '' ) {
		$options = $this->get_options();

		return $options['api_used'];
	}

	/**
	 * Returns Azure API key.
	 *
	 * @since 1.5
	 *
	 * @return string
	 */
	public function get_azure_api_key( $azure_api_key = '' ) {
		$options = $this->get_options();

		return $options['azure_api_key'];
	}

	/**
	 * Register settings section to add Azure API key
	 *
	 * @since 1.5
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function register_azure_api_key_settings_section( array $sections ) {
		$sections['maps-azure-key'] = array(
			'slug'		=> 'maps-azure-key',
			'title'		=> __( 'Microsoft Azure Maps API key', 'toolset-maps' ),
			'content'	=> $this->render_azure_api_key_settings_section()
		);
		return $sections;
	}

	/**
	 * Render settings section to add Azure API key
	 *
	 * @since 1.5
	 *
	 * @return string
	 */
	protected function render_azure_api_key_settings_section() {
		$description = __( "Set your Azure Maps API key.", 'toolset-maps' );
		$placeholder = __( 'Azure Maps API key', 'toolset-maps' );
		$help_text = sprintf(
			__(
				'An Azure Maps API key is <strong>required</strong> to use this API. You will need to create an Azure account and a <a href="%1$s" target="_blank">Maps API key</a>.',
				'toolset-maps'
			),
			'https://docs.microsoft.com/en-us/azure/azure-maps/quick-demo-map-app#create-an-account-and-get-your-key'
		);

		return <<<HTML
			<p>$description</p>
			<div class="js-wpv-map-plugin-form">
				<p>
					<input id="js-wpv-map-azure-api-key" type="text" name="wpv-map-azure-api-key"
                        class="regular-text js-wpv-map-api-key" value="{$this->get_azure_api_key()}"
                        autocomplete="off" size="44" placeholder="$placeholder"
                    />
				</p>
				<p>$help_text</p>
			</div>
HTML;
	}

	/**
	 * Ajax action to check G API. If not successful, it will forward the error string from get_coordinates method.
	 * @since 1.4.2
	 */
	public function check_g_api() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'toolset-maps' )
			) );
		}

		if (
			! isset( $_POST['wpnonce'] )
            || ! wp_verify_nonce( $_POST['wpnonce'], 'toolset_views_addon_maps_global' )
		) {
			wp_send_json_error( array(
				'type'    => 'nonce',
				'message' => __(
                    'Your security credentials have expired. Please reload the page to get new ones.',
                    'toolset-maps'
                )
			) );
		}

		$result = self::get_coordinates( 'Madrid, Spain', true );

		if ( is_array( $result ) ) {
			wp_send_json_success( __('Google API seems to be configured properly.', 'toolset-maps') );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
     * If rendered in a Layouts post content cell, reset used_*_ids, as they may have been already populated.
     * @since 1.4
	 * @param WPDD_layout $layout
	 * @param WPDD_layout_render $target
	 */
	public function reset_used_ids( $layout, $target ) {
        self::$used_map_ids = array();
        self::$used_marker_ids = array();
	}

	/**
     * So we can upload map style JSON files
     * @since 1.4
	 * @param array $mime_types
	 * @return array
	 */
	function add_json_mime_type( array $mime_types ) {
		$mime_types['json'] = 'application/json';

		return $mime_types;
	}

	/**
	 * Warns capable user if an API key is missing, offers information and link to enter the key.
	 */
	function display_admin_notices() {
	    if (
	    	!$this->is_user_capable_of_activating_plugins_and_on_plugin_page()
		    || $this->is_any_api_key_entered()
	    ) {
	        return;
        }

		$analytics_strings = array(
			'utm_source'	=> 'toolsetmapsplugin',
			'utm_campaign'	=> 'toolsetmaps',
			'utm_medium'	=> 'views-integration-settings-for-api-key',
			'utm_term'		=> 'our documentation'
		);
		$toolset_maps_settings_link = Toolset_Addon_Maps_Common::get_settings_link();
		?>
        <div class="message notice notice-warning">
            <p>
                <i class="icon-toolset-map-logo ont-color-orange ont-icon-24"></i>
				<?php echo sprintf(
					__( '<strong>You need an API key</strong> to use Toolset Maps. Find more information in %1$sour documentation%2$s and visit the %3$sToolset Maps settings page%4$s to choose an API and enter the key.', 'toolset-maps' ),
					'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'api-key' ), TOOLSET_ADDON_MAPS_DOC_LINK ) . '" target="_blank">',
					'</a>',
					'<a href="' . $toolset_maps_settings_link . '">',
					'</a>'
				); ?>
            </p>
        </div>
		<?php
	}

	/**
     * @since 1.4.1
	 * @return bool
	 */
	protected function is_user_capable_of_activating_plugins_and_on_plugin_page() {
		global $pagenow;
		return ( current_user_can( 'activate_plugins' ) && $pagenow == 'plugins.php' );
	}

	/**
	 * @since 1.5.3
	 * @return bool
	 */
	protected function is_any_api_key_entered() {
		return ( $this->get_api_key() || $this->get_azure_api_key() );
	}

	function register_settings_maps_section( $sections ) {
		if ( isset( $sections['maps'] ) ) {
			return $sections;
		}
		$sections['maps'] = array(
			'slug'	=> 'maps',
			'title'	=> __( 'Maps', 'toolset-maps' )
		);
		return $sections;
	}

	function toolset_maps_api_key_options( $sections ) {
		$saved_options = $this->get_options();
		ob_start();
		$this->render_api_key_options( $saved_options );
		$section_content = ob_get_clean();

		$sections['maps-api-key'] = array(
			'slug'		=> 'maps-api-key',
			'title'		=> __( 'Google Map API key', 'toolset-maps' ),
			'content'	=> $section_content
		);
		return $sections;
	}

	function render_api_key_options( $saved_options ) {
		?>
		<p>
			<?php _e( "Set your Google Maps API key.", 'toolset-maps' ); ?>
		</p>
		<div class="js-wpv-map-plugin-form">
			<p>
				<input id="js-wpv-map-api-key" type="text" name="wpv-map-api-key"
                   class="regular-text js-wpv-map-api-key" value="<?php echo esc_attr( $saved_options['api_key'] ); ?>"
                   autocomplete="off" size="40"
                   placeholder="<?php echo esc_attr( __( 'Google Maps API key', 'toolset-maps' ) ); ?>"
                />
			</p>
			<p>
				<?php
				echo sprintf(
					__( 'An API key is <strong>required</strong> to use Toolset Maps. You will need to create a <a href="%1$s" target="_blank">project in the Developers console</a>, then create an API key and enable it for some specific API services.', 'toolset-maps' ),
					'https://console.developers.google.com'
				);
				?>
			</p>
            <p>
				<?php echo __( 'For added protection of your API keys, you may want to setup a 2nd key for server-side requests:', 'toolset-maps' ) ?>
            </p>
            <p>
                <input id="js-wpv-map-server_side_api_key" type="text" name="wpv-map-server_side_api_key"
                       class="regular-text js-wpv-map-server_side_api_key"
                       value="<?php echo esc_attr( $saved_options['server_side_api_key'] ); ?>" autocomplete="off"
                       size="40" placeholder="<?php echo esc_attr( __( 'Optional 2nd key', 'toolset-maps' ) ); ?>"
                />
            </p>
			<p>
				<?php
				$analytics_strings = array(
					'utm_source'	=> 'toolsetmapsplugin',
					'utm_campaign'	=> 'toolsetmaps',
					'utm_medium'	=> 'views-integration-settings-for-api-key',
					'utm_term'		=> 'our documentation'
				);
				echo sprintf(
					__( 'You can find more information in %1$sour documentation%2$s.', 'toolset-maps' ),
					'<a href="' . Toolset_Addon_Maps_Common::get_documentation_promotional_link( array( 'query' => $analytics_strings, 'anchor' => 'api-key' ), TOOLSET_ADDON_MAPS_DOC_LINK ) . '" target="_blank">',
					'</a>'
				);
				?>
			</p>
		</div>
        <div>
            <button id="toolset-maps-check-api-button" type="button" class="button button-secondary" title="<?php _e('Check Google Maps API working.', 'toolset-maps') ?>"><?php _e('Check API', 'toolset-maps') ?></button>
            <p id="toolset-maps-api-check-result"></p>
        </div>
		<?php
	}

	/**
	 * @since 1.5 - saves 2nd key too
	 */
	function toolset_maps_update_api_key() {

		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'toolset-maps' )
			);
			wp_send_json_error( $data );
		}

		if (
		! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'toolset_views_addon_maps_global' )
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __(
					'Your security credentials have expired. Please reload the page to get new ones.',
					'toolset-maps'
                )
			);
			wp_send_json_error( $data );
		}

		$saved_options = $this->get_options();
		$saved_options['api_key'] = isset( $_POST['api_key'] )
			? sanitize_text_field( $_POST['api_key'] )
			: '';
		$saved_options['server_side_api_key'] = isset( $_POST['server_side_api_key'] )
			? sanitize_text_field( $_POST['server_side_api_key'] )
			: '';

		self::$stored_options = $saved_options;
		$this->set_options();
		wp_send_json_success();
	}

	/**
	 * Updates the API used setting
	 * @since 1.5
	 */
	public function update_api_used() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'toolset-maps' )
			);
			wp_send_json_error( $data );
		}

		if (
			! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'toolset_views_addon_maps_global' )
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __(
					'Your security credentials have expired. Please reload the page to get new ones.',
					'toolset-maps'
				)
			);
			wp_send_json_error( $data );
		}

		$saved_options = $this->get_options();
		$saved_options['api_used'] = isset( $_POST['api_used'] )
			? sanitize_text_field( $_POST['api_used'] )
			: self::API_GOOGLE;

		self::$stored_options = $saved_options;
		$this->set_options();
		wp_send_json_success();
	}

	/**
	 * Updates the Azure API key
	 * @since 1.5
	 */
	public function update_azure_api_key() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'toolset-maps' )
			);
			wp_send_json_error( $data );
		}

		if (
			! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'toolset_views_addon_maps_global' )
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __(
					'Your security credentials have expired. Please reload the page to get new ones.',
					'toolset-maps'
				)
			);
			wp_send_json_error( $data );
		}

		$saved_options = $this->get_options();
		$saved_options['azure_api_key'] = isset( $_POST['azure_api_key'] )
			? sanitize_text_field( $_POST['azure_api_key'] )
			: '';

		self::$stored_options = $saved_options;
		$this->set_options();
		wp_send_json_success();
	}

	function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		$this_plugin = basename( TOOLSET_ADDON_MAPS_PATH ) . '/toolset-maps-loader.php';
		if ( $plugin_file == $this_plugin ) {
			$toolset_maps_settings_link = Toolset_Addon_Maps_Common::get_settings_link();
			$actions['settings'] = '<a href="' . $toolset_maps_settings_link . '">' . __( 'Settings', 'toolset-maps' ) . '</a>';
		}
		return $actions;
	}

	function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		$this_plugin = basename( TOOLSET_ADDON_MAPS_PATH ) . '/toolset-maps-loader.php';
		if ( $plugin_file == $this_plugin ) {
			$promo_args = array(
				'query'	=> array(
					'utm_source'	=> 'mapsplugin',
					'utm_campaign'	=> 'maps',
					'utm_medium'	=> 'release-notes-plugin-row',
					'utm_term'		=> 'Toolset Maps '.TOOLSET_ADDON_MAPS_VERSION.' release notes'
				)
			);
			$plugin_link = self::get_documentation_promotional_link(
                $promo_args,
                'https://toolset.com/version/maps-' . str_replace( '.', '-', TOOLSET_ADDON_MAPS_VERSION ) . '/'
            );
			$plugin_meta[] = sprintf(
                '<a href="%1$s" target="_blank">%2$s</a>',
                $plugin_link,
                __( 'Toolset Maps '.TOOLSET_ADDON_MAPS_VERSION.' release notes', 'toolset-maps' )
            );
		}
		return $plugin_meta;
	}

	function register_assets() {

		

		if ( is_admin() ) {
			$assets_url = TOOLSET_ADDON_MAPS_URL;
		} else {
			$assets_url = TOOLSET_ADDON_MAPS_FRONTEND_URL;
		}

		if ( self::API_GOOGLE === $this->get_api_used() ) {
			$this->register_google_specific_assets( $assets_url );
		} else {
			$this->register_azure_specific_assets( $assets_url );
		}

		wp_localize_script(
			'views-addon-maps-script',
			'views_addon_maps_i10n',
			array(
				'marker_default_url'		=> TOOLSET_ADDON_MAPS_FRONTEND_URL . '/resources/images/spotlight-poi.png',
				'cluster_default_imagePath'	=> TOOLSET_ADDON_MAPS_FRONTEND_URL . '/resources/images/clusterer/m',
				'marker_not_found_warning'  => __(
					'Toolset Maps: Street View from marker requested for map: %s but marker not found.',
					'toolset-maps'
				),
				'api_used'                  => $this->get_api_used(),
				'azure_api_key'             => $this->get_azure_api_key(),
			)
		);

		// Map preview module
		wp_register_script(
			'views-addon-maps-preview-script',
			TOOLSET_ADDON_MAPS_URL . '/resources/js/wpv_addon_maps_preview.js',
			array( 'views-addon-maps-script' ),
			TOOLSET_ADDON_MAPS_VERSION,
			true
		);

		// Toolset -> Settings JS code
        $settings_script_dependencies = array( 'jquery', 'underscore', 'quicktags', 'icl_media-manager-js' );
        if ( self::get_saved_option( 'api_key') ) {
            $settings_script_dependencies[] = 'views-addon-maps-preview-script';
        }
		wp_register_script( 'views-addon-maps-settings-script', TOOLSET_ADDON_MAPS_URL . '/resources/js/wpv_addon_maps_settings.js', $settings_script_dependencies, TOOLSET_ADDON_MAPS_VERSION, true );
		$wpv_addon_maps_settings_localization = array(
			'nonce'					=> wp_create_nonce( 'toolset_views_addon_maps_settings' ),
			'global_nonce'			=> wp_create_nonce( 'toolset_views_addon_maps_global' ),
			'setting_saved'			=> __( 'Settings saved', 'toolset-maps' ),
            'duplicate_style_warning' => __('Cannot upload: JSON style with that name already exists!', 'toolset-maps'),
			'api_used'              => $this->get_api_used()
		);
		wp_localize_script( 'views-addon-maps-settings-script', 'wpv_addon_maps_settings_local', $wpv_addon_maps_settings_localization );

        // CSS fixes
        wp_register_style(
            'toolset-maps-fixes',
            $assets_url . '/resources/css/toolset_maps_fixes.css',
            array(),
            TOOLSET_ADDON_MAPS_VERSION
        );
	}

	protected function register_google_specific_assets( $assets_url ) {
		$maps_api_js_url = self::$maps_api_url_js;
		$maps_api_js_url = apply_filters( 'toolset_filter_toolset_maps_api_js_url', $maps_api_js_url );

		$args = array(
			'sensor'	=> false,
			'libraries'	=> 'places',
			'v'         => '3'
		);

		$maps_api_key = $this->get_api_key();
		if ( ! empty( $maps_api_key ) ) {
			$args['key'] = esc_attr( $maps_api_key );
		}

		$maps_api_js_url = add_query_arg( $args, $maps_api_js_url );

		/**
		 * Google Maps script
		 * @version 3.x (uses v= parameter instead of ver= that WP would provide for version)
		 */
		wp_register_script( 'google-maps', $maps_api_js_url, array(), null, true );

		/**
		 * jQuery geocomplete
		 * @version 1.7.0
		 * @url http://ubilabs.github.io/geocomplete/
		 */
		wp_register_script(
			'jquery-geocomplete',
			$assets_url . '/resources/js/jquery.geocomplete.min.js',
			array( 'jquery', 'google-maps' ),
			'1.7.0',
			true
		);

		/**
		 * Frontend rendering script
		 */
		wp_register_script( 'views-addon-maps-script', $assets_url . '/resources/js/wpv_addon_maps.js', array( 'jquery', 'google-maps', 'underscore' ), TOOLSET_ADDON_MAPS_VERSION, true );

		/**
		 * Marker clusterer
		 * @version 1.0
		 * @url https://github.com/googlemaps/v3-utility-library/tree/master/markerclusterer
		 */
		wp_register_script( 'marker-clusterer-script', $assets_url . '/resources/js/markerclusterer.js', array( 'jquery', 'google-maps', 'underscore', 'views-addon-maps-script' ), '1.0', true );
		wp_localize_script(
			'marker-clusterer-script',
			'views_addon_maps_clusterer_i10n',
			array(
				'cluster_default_imagePath'	=> TOOLSET_ADDON_MAPS_FRONTEND_URL . '/resources/images/clusterer/m'
			)
		);

		/**
		 * Overlapping marker spiderfier
		 * @version 1.0
		 * @url https://github.com/jawj/OverlappingMarkerSpiderfier
		 */
		wp_register_script(
			'overlapping-marker-spiderfier',
			$assets_url . '/resources/js/oms.min.js',
			array('google-maps'),
			'1.0',
			true
		);

		/**
		 * Editor assets for Types in backend and Forms in frontend
		 */
		wp_register_script(
			'toolset-google-map-editor-script',
			$assets_url . '/resources/js/wpv_addon_maps_editor.js',
			array( 'jquery-geocomplete' ),
			TOOLSET_ADDON_MAPS_VERSION,
			true
		);
		wp_localize_script(
			'toolset-google-map-editor-script',
			'toolset_google_address_i10n',
			array(
				'showhidecoords'	=> __( 'Show/Hide coordinates', 'toolset-maps' ),
				'latitude'			=> __( 'Latitude', 'toolset-maps' ),
				'longitude'			=> __( 'Longitude', 'toolset-maps' ),
				'usethisaddress'	=> __( 'Use this address', 'toolset-maps' ),
				'closestaddress'	=> __( 'Closest address: ', 'toolset-maps' ),
				'autocompleteoff'	=> __(
					'We could not connect to the Google Maps autocomplete service, but you can add an address manually.',
					'toolset-maps'
				),
				'usemylocation'     => __( 'Use my location', 'toolset-maps' )
			)
		);
	}

	protected function register_azure_specific_assets( $assets_url ) {
		// Register Azure assets
		$azure_css_source = add_query_arg( 'api-version', '1.1', 'https://atlas.microsoft.com/sdk/css/atlas.min.css' );
		wp_register_style( 'azure-maps-css', $azure_css_source, array(), null );

		$azure_js_source = add_query_arg( 'api-version', '1.1', 'https://atlas.microsoft.com/sdk/js/atlas.min.js' );
		wp_register_script( 'azure-maps-js', $azure_js_source, array(), null );

		// Address autocomplete script
		wp_register_script(
			'toolset-maps-address-autocomplete',
			$assets_url . '/resources/js/toolset_maps_address_autocomplete.js',
			array( 'jquery', 'jquery-ui-autocomplete', 'underscore', 'azure-maps-js' ),
			TOOLSET_ADDON_MAPS_VERSION
		);
		wp_localize_script(
			'toolset-maps-address-autocomplete',
			'toolset_maps_address_autocomplete_i10n',
			array(
				'azure_api_key'     => $this->get_azure_api_key(),
				'showhidecoords'	=> __( 'Show/Hide coordinates', 'toolset-maps' ),
				'latitude'			=> __( 'Latitude', 'toolset-maps' ),
				'longitude'			=> __( 'Longitude', 'toolset-maps' ),
				'usethisaddress'	=> __( 'Use this address', 'toolset-maps' ),
				'closestaddress'	=> __( 'Closest address: ', 'toolset-maps' ),
				'usemylocation'     => __( 'Use my location', 'toolset-maps' )
			)
		);

		// Frontend rendering script
		wp_register_script(
			'views-addon-maps-script',
			$assets_url . '/resources/js/wpv_addon_maps.js',
			array( 'jquery', 'azure-maps-js', 'underscore' ),
			TOOLSET_ADDON_MAPS_VERSION,
			true
		);
	}

	/**
	 * Register assets that will be used only on admin side
     * @since 1.5
	 */
	public function register_admin_assets() {
	    wp_register_style(
            'toolset-maps-plugins-page',
            TOOLSET_ADDON_MAPS_URL . '/resources/css/toolset_maps_plugins_page.css',
            array(),
            TOOLSET_ADDON_MAPS_VERSION
        );
    }

	function wp_enqueue_scripts() {
		wp_enqueue_style( 'toolset-maps-fixes' );

		self::maybe_enqueue_azure_css();
	}

	function toolset_enqueue_scripts( $page ) {
		if ( $page == 'toolset-settings' ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_media();
			wp_enqueue_script( 'views-addon-maps-settings-script' );
		}

		wp_enqueue_style( 'toolset-maps-fixes' );
	}

	function admin_enqueue_scripts( $page ) {
		if ( 'plugins.php' === $page ) {
		    wp_enqueue_style( 'toolset-maps-plugins-page' );
        }

        self::maybe_enqueue_azure_css();
	}

	/**
	 * Helper method to potentially add azure CSS if its JS is added JIT, and later than enqueue hooks fire.
	 * @since 1.5.4
	 */
	public static function maybe_enqueue_azure_css() {
		if (
			apply_filters( 'toolset_maps_get_api_used', '' ) === Toolset_Addon_Maps_Common::API_AZURE
			&& wp_script_is( 'azure-maps-js' )
			&& ! wp_style_is( 'azure-maps-css' )
		) {
			wp_enqueue_style( 'azure-maps-css' );
		}
	}

	/**
	 * Elementor editor preview fix. Brute force loading all JS there, because we have no way of knowing if
	 * they're going to be needed or not. (This get key is an iframe, and then sections are loaded by ajax, and by
	 * the time map is rendered, it's too late to add JS.)
	 * @since 1.5
	 */
	public function elementor_preview_enqueue_scripts() {
		if ( toolset_getget( 'elementor-preview' ) ) {
			if ( ! wp_script_is( 'views-addon-maps-script' ) ) {
				wp_enqueue_script( 'views-addon-maps-script' );
			}
			if ( ! wp_script_is( 'marker-clusterer-script' ) ) {
				wp_enqueue_script( 'marker-clusterer-script' );
			}
			if ( ! wp_script_is( 'overlapping-marker-spiderfier' ) ) {
				wp_enqueue_script( 'overlapping-marker-spiderfier' );
			}
		}
	}

	static function get_stored_coordinates() {
		$coordinates_set = self::$coordinates_set;
		if ( $coordinates_set === null ) {
			$coordinates_set = get_option( self::address_coordinates_option, array() );
			self::$coordinates_set = $coordinates_set;
		}
		return $coordinates_set;
	}

	/**
     * @since 1.4.1
     *
	 * @param string $address
	 *
	 * @return string
	 */
	protected static function get_coordinates_not_valid( $address ) {
		return sprintf(
			__( 'The pair of coordinates passed for address %1$s is not valid.', 'toolset-maps' ),
			$address
		);
	}

	/**
	 * Given an address, ping the Maps API and get the latitude and longitude coordinates
	 *
	 * @since 1.0
     * @since 1.4.2 Parameter to skip cache (used for API checking)
     *
	 * @param string $address
	 * @param bool $skip_cache
	 *
	 * @return array|mixed|object|string
	 */
	static function get_coordinates( $address, $skip_cache=false ) {

		

		$address_hash = md5( $address );
		$coordinates_set = self::get_stored_coordinates();

		if (
            ! isset( $coordinates_set[ $address_hash ] )
            || $skip_cache
        ) {
			if (
				strpos( $address, '{' ) === 0
				&& strpos( $address, '}' ) === intval( strlen( $address ) - 1 )
			) {
				$address_trimmed = str_replace( array( '{', '}' ), '', $address );
				$address_components = explode( ',', $address_trimmed );
				$address_components = array_map( 'trim', $address_components );
				if ( count( $address_components ) == 2 ) {
					$address_lat = $address_components[0];
					$address_lon = $address_components[1];
					if (
						self::is_valid_latitude( $address_lat )
						&& self::is_valid_longitude( $address_lon )
					) {
						$coordinates_set[ $address_hash ]['lat'] 			= $address_lat;
						$coordinates_set[ $address_hash ]['lon'] 			= $address_lon;
						$coordinates_set[ $address_hash ]['address']		= $address;
						$coordinates_set[ $address_hash ]['address_passed']	= $address;

						$data = $coordinates_set[ $address_hash ];

						self::$coordinates_save = true;
						self::$coordinates_set = $coordinates_set;
					} else {
					    return self::get_coordinates_not_valid( $address );
					}
				} else {
					return self::get_coordinates_not_valid( $address );
				}
			} else {
				$api_used = self::get_saved_option( 'api_used' );

				if ( self::API_GOOGLE === $api_used ) {
					$data = self::get_coordinates_google( $address, $address_hash, $coordinates_set );
				} elseif ( self::API_AZURE === $api_used ) {
					$data = self::get_coordinates_azure( $address, $address_hash, $coordinates_set );
				} else {
					return sprintf(
						__( 'Unknown maps API configured!', 'toolset-maps' ),
						$address
					);
				}
			}

		} else {

			$data = $coordinates_set[ $address_hash ];

		}

		return $data;
	}

	/**
	 * Resolve address to coordinates using Azure API
	 *
	 * @since 1.5
	 *
	 * @param string $address
	 * @param string $address_hash
	 * @param array $coordinates_set
	 *
	 * @return mixed|string|void
	 */
	protected static function get_coordinates_azure( $address, $address_hash, array $coordinates_set ) {
		$args = array(
			'subscription-key' => apply_filters( 'toolset_filter_toolset_maps_get_azure_api_key', '' ),
			'api-version' => '1.0',
			'query' => urlencode( $address )
		);
		$url = add_query_arg( $args, self::API_AZURE_URL );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return __( 'wp_remote_get could not communicate with the Azure Maps API.', 'toolset-maps' );
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $response_body ) ) {
			return sprintf(
				__(
					'wp_remote_retrieve_body could not get data from the Azure Maps API response. URL was %s',
					'toolset-maps'
				),
				$url
			);
		}

		if ( $response['response']['code'] == 200 ) {

			$decoded_body = json_decode( $response_body );

			// If an address unknown to API is given, handle gracefully and don't save to cache.
			if ( $decoded_body->summary->numResults === 0 ) {
				return __( 'Given address is unknown to Azure API', 'toolset-maps' );
			}

			$result = $decoded_body->results[0];

			$coordinates_set[ $address_hash ]['lat'] 			= $result->position->lat;
			$coordinates_set[ $address_hash ]['lon'] 			= $result->position->lon;
			$coordinates_set[ $address_hash ]['address']		= $result->address->freeformAddress;
			$coordinates_set[ $address_hash ]['address_passed']	= $address;

			$data = $coordinates_set[ $address_hash ];

			self::$coordinates_save = true;
			self::$coordinates_set = $coordinates_set;

		} else {
			return sprintf(
				__( '%1$s - Unable to contact Azure API service. URL was %2$s', 'toolset-maps' ),
				$response['response']['code'],
				$url
			);
		}

		return $data;
	}

	/**
	 * Resolve address to coordinates using Google API
	 *
	 * @since 1.5
	 *
	 * @param string $address
	 * @param string $address_hash
	 * @param array $coordinates_set
	 *
	 * @return array|mixed|object|string|void
	 */
	protected static function get_coordinates_google( $address, $address_hash, $coordinates_set ) {
		$args = array( 'address' => urlencode( $address ), 'sensor' => 'false' );

		$maps_api_key = apply_filters( 'toolset_filter_toolset_maps_get_server_side_api_key', '' );
		if ( ! empty( $maps_api_key ) ) {
			$args['key'] = esc_attr( $maps_api_key );
		}

		$maps_api_url_geocode = self::$maps_api_url_geocode;
		$maps_api_url_geocode = apply_filters( 'toolset_filter_toolset_maps_api_geocode_url', $maps_api_url_geocode );

		$url        = add_query_arg( $args, $maps_api_url_geocode );
		$response 	= wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return __( 'wp_remote_get could not communicate with the Google Maps API.', 'toolset-maps' );
		}

		$data = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $data ) ) {
			return sprintf(
				__( 'wp_remote_retrieve_body could not get data from the Google Maps API response. URL was %s', 'toolset-maps' ),
				$url
			);
		}

		if ( $response['response']['code'] == 200 ) {

			$data = json_decode( $data );

			if ( $data->status === 'OK' ) {

				$coordinates = $data->results[0]->geometry->location;

				$coordinates_set[ $address_hash ]['lat'] 			= $coordinates->lat;
				$coordinates_set[ $address_hash ]['lon'] 			= $coordinates->lng;
				$coordinates_set[ $address_hash ]['address']		= (string) $data->results[0]->formatted_address;
				$coordinates_set[ $address_hash ]['address_passed']	= $address;

				$data = $coordinates_set[ $address_hash ];

				self::$coordinates_save = true;
				self::$coordinates_set = $coordinates_set;

			} elseif ( $data->status === 'ZERO_RESULTS' ) {
				return sprintf(
					__( 'ZERO_RESULTS - No location found for the entered address. URL was %s', 'toolset-maps' ),
					$url
				);
			} elseif( $data->status === 'INVALID_REQUEST' ) {
				return sprintf(
					__( 'INVALID_REQUEST - Invalid request. Did you enter an address? URL was %s', 'toolset-maps' ),
					$url
				);
			} elseif ( $data->status === 'REQUEST_DENIED' ) {
				return sprintf(
					__( 'REQUEST_DENIED - %s', 'toolset-maps' ),
					$data->error_message
				);
			} else {
				return sprintf(
					__( '%1$s - Something went wrong while retrieving your map, please ensure you have entered the short code correctly. URL was %2$s', 'toolset-maps' ),
					$data->status,
					$url
				);
			}

		} else {
			return sprintf(
				__( '%1$s - Unable to contact Google API service. URL was %2$s', 'toolset-maps' ),
				$response['response']['code'],
				$url
			);
		}

		return $data;
	}

	static function is_valid_latitude( $latitude ) {
		if ( preg_match( "/^-?(0|[1-8]?[1-9]|[1-9]0)(\.{1}\d{1,20})?$/", $latitude ) ) {
			return true;
		} else {
			return false;
		}
	}

	static function is_valid_longitude( $longitude ) {
		if ( preg_match( "/^-?([0-9]|[1-9][0-9]|[1][0-7][0-9]|180)(\.{1}\d{1,20})?$/", $longitude ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Renders map <div> which will be picked up by JS to create an actual map on frontend
	 *
	 * @since 1.0
     *
	 * @param string $map_id
	 * @param array $map_data
	 * @param string $content
	 *
	 * @return string
	 */
	static function render_map( $map_id, array $map_data, $content = '' ) {
		$map_data = wp_parse_args( $map_data, self::$map_defaults );
		$lat = 0;
		$long = 0;

		if ( preg_match( '/^[+-]?((\d+(\.\d*)?)|(\.\d+))$/', $map_data['map_width'] ) ) {
			$map_data['map_width'] .= 'px';
		}
		if ( preg_match( '/^[+-]?((\d+(\.\d*)?)|(\.\d+))$/', $map_data['map_height'] ) ) {
			$map_data['map_height'] .= 'px';
		}

		$current_used_map_ids = self::$used_map_ids;
		$current_used_map_ids[] = $map_id;
		self::$used_map_ids = $current_used_map_ids;

		if ( $map_data['address'] ) {
		    $latLong = self::get_coordinates( $map_data['address'] );
			if ( is_array( $latLong ) ) {
				$lat = $latLong['lat'];
				$long = $latLong['lon'];
			}
        }

		$return = '<div';
		$return .= ' id="js-wpv-addon-maps-render-' . $map_id . '"';
		$return .= ' style="width:' . $map_data['map_width'] . '; height:' . $map_data['map_height'] . ';"';
		$return .= ' class="wpv-addon-maps-render js-wpv-addon-maps-render js-wpv-addon-maps-render-' . $map_id . '"';
		$return .= ' data-map="' . esc_attr( $map_id ) . '"';
		$return .= ' data-generalzoom="' . esc_attr( $map_data['general_zoom'] ) . '"';
		$return .= ' data-generalcenterlat="' . esc_attr( $map_data['general_center_lat'] ) . '"';
		$return .= ' data-generalcenterlon="' . esc_attr( $map_data['general_center_lon'] ) . '"';
		$return .= ' data-fitbounds="' . esc_attr( $map_data['fitbounds'] ) . '"';
		$return .= ' data-singlezoom="' . esc_attr( $map_data['single_zoom'] ) . '"';
		$return .= ' data-singlecenter="' . esc_attr( $map_data['single_center'] ) . '"';
		$return .= ' data-maptype="' . esc_attr( $map_data['map_type'] ) . '"';
		$return .= ' data-showlayerinterests="' . esc_attr( $map_data['show_layer_interests'] ) . '"';
		$return .= ' data-markericon="' . esc_attr( $map_data['marker_icon'] ) . '"';
		$return .= ' data-markericonhover="' . esc_attr( $map_data['marker_icon_hover'] ) . '"';
		$return .= ' data-draggable="' . esc_attr( $map_data['draggable'] ) . '"';
		$return .= ' data-scrollwheel="' . esc_attr( $map_data['scrollwheel'] ) . '"';
		$return .= ' data-doubleclickzoom="' . esc_attr( $map_data['double_click_zoom'] ) . '"';
		$return .= ' data-maptypecontrol="' . esc_attr( $map_data['map_type_control'] ) . '"';
		$return .= ' data-fullscreencontrol="' . esc_attr( $map_data['full_screen_control'] ) . '"';
		$return .= ' data-zoomcontrol="' . esc_attr( $map_data['zoom_control'] ) . '"';
		$return .= ' data-streetviewcontrol="' . esc_attr( $map_data['street_view_control'] ) . '"';
		$return .= ' data-backgroundcolor="' . esc_attr( $map_data['background_color'] ) . '"';
		$return .= ' data-cluster="' . esc_attr( $map_data['cluster'] ) . '"';
		$return .= ' data-clustergridsize="' . esc_attr( $map_data['cluster_grid_size'] ) . '"';
		$return .= ' data-clustermaxzoom="' . esc_attr( $map_data['cluster_max_zoom'] ) . '"';
		$return .= ' data-clusterclickzoom="' . esc_attr( $map_data['cluster_click_zoom'] ) . '"';
		$return .= ' data-clusterminsize="' . esc_attr( $map_data['cluster_min_size'] ) . '"';
		$return .= ' data-stylejson="' . self::get_given_or_default_style_json( $map_data['style_json'] ) . '"';
		$return .= ' data-spiderfy="' . esc_attr( $map_data['spiderfy'] ) . '"';
		$return .= ' data-streetview="' . esc_attr( $map_data['street_view'] ) . '"';
		$return .= ' data-markerid="' . esc_attr( $map_data['marker_id'] ) . '"';
		$return .= ' data-location="' . esc_attr( $map_data['location'] ) . '"';
		$return .= ' data-lat="' . esc_attr( $lat ) . '"';
		$return .= ' data-long="' . esc_attr( $long ) . '"';
		$return .= ' data-heading="' . esc_attr( $map_data['heading'] ) . '"';
		$return .= ' data-pitch="' . esc_attr( $map_data['pitch'] ) . '"';
		$return .= '>';
		$return .= $content;
		$return .= '</div>';

		$return .= self::maybe_add_hidden_icon_image_html_for_azure(
			$map_data['marker_icon'],
			$map_data['marker_icon_hover']
		);

		return $return;
	}

	/**
	 * @param string $given_map_style
	 * @return string
	 */
	protected static function get_given_or_default_style_json( $given_map_style ) {
	    if ( $given_map_style ) return esc_attr( $given_map_style );

		$style_json_name = self::get_saved_option( 'default_map_style' );
		$style_options = array_flip( self::get_style_options() );

		return esc_attr( $style_options[$style_json_name] );
    }

	/**
	 * Usually we need just one option from the saved options array
	 * @param string $key
	 * @return mixed
	 */
	protected static function get_saved_option( $key ) {
		$saved_options = apply_filters( 'toolset_filter_toolset_maps_get_options', array() );

		if ( isset( $saved_options[$key] ) ) {
			return $saved_options[ $key ];
		} else {
			return null;
		}
	}

	/**
	 * Makes an option array for styles select
	 * @return array
	 */
	protected static function get_style_options() {
		return array_merge(
			array( '' => 'Standard' ),
			self::get_preloaded_json_styles(),
			array_flip( self::get_uploaded_json_styles() )
		);
	}

	/**
	 * Get uploaded JSON files as key - value. Turn saved relative URLs into full ones for this site.
	 * @return array
	 */
	protected static function get_uploaded_json_styles() {
		return array_map(
			'self::make_relative_upload_link_full',
			self::get_saved_option( 'map_style_files' )
		);
	}

	/**
	 * Massage preloaded JSON file names into format for options
	 * @return array
	 */
	protected static function get_preloaded_json_styles() {
		$styles_options = array();

		foreach ( self::get_preloaded_json_style_names() as $style_name ) {
			$file_location = TOOLSET_ADDON_MAPS_URL . "/resources/json/$style_name.json";
			$styles_options[$file_location] = $style_name;
		}

		return $styles_options;
	}

	/**
	 * Style names = style filenames (without extension)
	 * @return array
	 */
	protected static function get_preloaded_json_style_names() {
		return array_map(
			function ( $path ) {
				return pathinfo( $path, PATHINFO_FILENAME );
			},
			self::scan_preloaded_json_style_files()
		);
	}

	/**
	 * Scans for available JSON style files in the resources/json folder, so we can just add and remove files without
	 * changing the code.
	 * @return array
	 */
	protected static function scan_preloaded_json_style_files() {
		return array_diff(
			scandir(TOOLSET_ADDON_MAPS_PATH . '/resources/json'),
			array( '.', '..' )
		);
	}

	/**
	 * @param string $link URL relative to upload base URL given
	 * @return string Full URL returned
	 */
	protected static function make_relative_upload_link_full( $link ) {
		return self::get_upload_baseurl() . $link;
	}

	/**
	 * @return string Base URL for WP upload
	 */
	protected static function get_upload_baseurl() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'];
	}

	/**
	 * @param string $map_id
	 * @param array $marker_data
	 * @param string $content
	 *
	 * @return string
     *
     * @since 1.0
	 */
	static function render_marker( $map_id, array $marker_data, $content = '' ) {
		$defaults = array(
			'id'			=> '',
			'title'			=> '',
			'lat'			=> '',
			'lon'			=> '',
			'icon'			=> '',
			'icon_hover'	=> '',
            'street_view'   => 'no'
		);
		if (
			empty( $marker_data['id'] )
			|| empty( $marker_data['lat'] )
			|| empty( $marker_data['lon'] )
		) {
			return '';
		}
		$marker_data = wp_parse_args( $marker_data, $defaults );

		$current_used_marker_ids = self::$used_marker_ids;
		if ( ! isset( $current_used_marker_ids[ $map_id ] ) ) {
			$current_used_marker_ids[ $map_id ] = array();
		}
		$current_used_marker_ids[ $map_id ][] = $marker_data['id'] ;
		self::$used_marker_ids = $current_used_marker_ids;

		$icon = $marker_data['icon'];
		$icon_hover = $marker_data['icon_hover'];

		$return = '<div style="display:none"';
		$return .= ' class="wpv-addon-maps-marker js-wpv-addon-maps-marker js-wpv-addon-maps-marker-' . esc_attr( $marker_data['id'] ) . ' js-wpv-addon-maps-markerfor-' . esc_attr( $map_id ) . '"';
		$return .= ' data-marker="' . esc_attr( $marker_data['id'] ) . '"';
		$return .= ' data-markertitle="' . esc_attr( $marker_data['title'] ) . '"';
		$return .= ' data-markerfor="' . esc_attr( $map_id ) . '"';
		$return .= ' data-markerlat="' . esc_attr( $marker_data['lat'] ) . '"';
		$return .= ' data-markerlon="' . esc_attr( $marker_data['lon'] ) . '"';
		$return .= ' data-markericon="' . esc_attr( $icon ) . '"';
		$return .= ' data-markericonhover="' . esc_attr( $icon_hover ) . '"';
		$return .= ' data-streetview="' . esc_attr( $marker_data['street_view'] ) . '"';
		$return .= '>' . $content . '</div>';

		$return .= self::maybe_add_hidden_icon_image_html_for_azure( $icon, $icon_hover );

		return $return;
	}

	/**
	 * Given icon path, return the HTML load the icon in a hidden image.
	 *
	 * If the path is empty or the icon has already been added previously, returns empty string.
	 *
	 * @since 1.5.3
	 *
	 * @param string $icon
	 *
	 * @return string
	 */
	static protected function render_hidden_icon_image( $icon ) {
		if ( !empty( $icon ) && !in_array( $icon, self::$icons_added ) ) {
			return "<div style='display: none;'>"
			       . "<img src='$icon' width='24' height='28' id='$icon' />"
			       ."</div>";
			self::$icons_added[] = $icon;
		} else {
			return '';
		}
	}

	/**
	 * For Azure API, we need to send icon image to be loaded by browser
	 *
	 * @since 1.5.3
	 *
	 * @param string $icon
	 * @param string $icon_hover
	 *
	 * @return string
	 */
	static protected function maybe_add_hidden_icon_image_html_for_azure( $icon, $icon_hover ) {
		$html = '';

		if ( self::API_AZURE === apply_filters( 'toolset_maps_get_api_used', '' ) ) {
			$html .= self::render_hidden_icon_image( $icon );
			$html .= self::render_hidden_icon_image( $icon_hover );
		}
		return $html;
	}

	function maybe_save_stored_coordinates_in_footer() {
		$coordinates_save = self::$coordinates_save;
		if ( $coordinates_save ) {
			$coordinates_set = self::$coordinates_set;
			if (
				$coordinates_set !== null
				&& is_array( $coordinates_set )
			) {
				update_option( self::address_coordinates_option, $coordinates_set, false );
			}
		}
	}

	static function save_stored_coordinates( $coordinates_set ) {
		self::$coordinates_set = $coordinates_set;
		update_option( self::address_coordinates_option, $coordinates_set, false );
	}


	/**
	* get_documentation_promotional_link
	*
	* @param $args	array
	* 		@param query	array
	* 		@param anchor	string
	* @param $url	string
	*
	* @return string
	*
	* @note utm_source=toolsetmapsplugin&utm_campaign=toolsetmaps&utm_medium=foo&utm_term=bar
	*
	* @since 1.0
	*/

	static function get_documentation_promotional_link( $args = array(), $url = TOOLSET_ADDON_MAPS_DOC_LINK ) {
		if ( isset( $args['query'] ) ) {
			$url = esc_url( add_query_arg( $args['query'], $url ) );
		}
		if ( isset( $args['anchor'] ) ) {
			$url .= '#' . esc_attr( $args['anchor'] );
		}
		return $url;
	}

	static function get_settings_link() {
		$toolset_maps_settings_link = admin_url( 'admin.php?page=toolset-settings&tab=maps' );
		$toolset_maps_settings_link = apply_filters( 'toolset_filter_toolset_maps_settings_link', $toolset_maps_settings_link );
		return $toolset_maps_settings_link;
	}

	/**
	* Pluck a certain field out of each object in a list, if it exists.
	*
	* This has the same functionality and prototype of
	* array_column() (PHP 5.5) but also supports objects.
	* This is a post of the native wp_list_pluck
	* but avoids errors when the $field key is not found on the $list entry
	*
	* @since 1.1
	*
	* @param array      $list      List of objects or arrays
	* @param int|string $field     Field from the object to place instead of the entire object
	* @param int|string $index_key Optional. Field from the object to use as keys for the new array.
	*                              Default null.
	* @return array Array of found values. If `$index_key` is set, an array of found values with keys
	*               corresponding to `$index_key`. If `$index_key` is null, array keys from the original
	*               `$list` will be preserved in the results.
	*/
	static function pseudo_list_pluck( $list, $field, $index_key = null ) {
		if ( ! $index_key ) {
			/*
			 * This is simple. Could at some point wrap array_column()
			 * if we knew we had an array of arrays.
			 */
			foreach ( $list as $key => $value ) {
				if ( is_object( $value ) ) {
					if ( property_exists( $value, $field ) ) {
						$list[ $key ] = $value->$field;
					} else {
						unset( $list[ $key ] );
					}
				} else {
					if ( isset( $value[ $field ] ) ) {
						$list[ $key ] = $value[ $field ];
					} else {
						unset( $list[ $key ] );
					}
				}
			}
			return $list;
		}

		/*
		 * When index_key is not set for a particular item, push the value
		 * to the end of the stack. This is how array_column() behaves.
		 */
		$newlist = array();
		foreach ( $list as $value ) {
			if ( is_object( $value ) ) {
				if ( property_exists( $value, $field ) ) {
					if ( isset( $value->$index_key ) ) {
						$newlist[ $value->$index_key ] = $value->$field;
					} else {
						$newlist[] = $value->$field;
					}
				}
			} else {
				if ( isset( $value[ $field ] ) ) {
					if ( isset( $value[ $index_key ] ) ) {
						$newlist[ $value[ $index_key ] ] = $value[ $field ];
					} else {
						$newlist[] = $value[ $field ];
					}
				}
			}
		}

		return $newlist;
	}

}

$Toolset_Addon_Maps_Common = new Toolset_Addon_Maps_Common();
