<?php

/**
 * Views Settings Embedded
 * 
 * It implements both ArrayAccess and dynamic properties. ArrayAccess is deprecated.
 *
 * @link https://git.onthegosystems.com/toolset/views/wikis/best-practices#accessing-views-settings
 *
 * @since 1.8
 *
 * @property-read string $debug_mode Combines wpv_debug_mode and wpv_debug_mode_type.
 * 		Possible values are 'off', 'full' and 'compact'.
 * @property array $shown_hidden_custom_fields Values from wpv_show_hidden_fields setting in the form of an array.
 *
 * @property int	$views_template_loop_blog
 * @property int	$wpv_bootstrap_version
 * @property int	$wpv_codemirror_autoresize
 * @property int	$wpv_enable_pagination_manage_history
 * @property int	$wpv_enable_parametric_search_manage_history
 * @property array	$wpv_custom_conditional_functions
 * @property array	$wpv_custom_inner_shortcodes
 * @property int	$wpv_debug_mode
 * @property string $wpv_debug_mode_type
 * @property int	$wpv_map_plugin
 * @property mixed	$wpv_saved_auto_detected_framework
 * @property int	$wpv_show_edit_view_link
 * @property mixed	$wpv_show_hidden_fields
 * @property array	$wpv_post_types_for_archive_loop
 */
class WPV_Settings implements ArrayAccess {


    /**
     * WP Option Name for Views settings.
     */
    const OPTION_NAME = 'wpv_options';


    /* ************************************************************************* *\
        SETTING NAMES
    \* ************************************************************************* */


    // Note: @since tags here define since when the option exist, not since when the constant is defined.

    /**
     * Setting with this prefix + post type slug ($post_type->name) holds an ID of Content Template that should be
     * used for single posts of this type, or 0 if no CT is assigned. It doesn't have to be set for all post types.
     *
     * @since unknown
     */
    const SINGLE_POST_TYPES_CT_ASSIGNMENT_PREFIX = 'views_template_for_';


    /**
     * Setting with this prefix + post type slug ($post_type->name) holds an ID of Content Template that should be
     * used for post archive for this post type, or 0 if no CT is assigned. It doesn't have to be set for all post types.
     *
     * @since unknown
     */
    const CPT_ARCHIVES_CT_ASSIGNMENT_PREFIX = 'views_template_archive_for_';


    /**
     * Setting with this prefix + taxonomy slug ($taxonomy->name) holds an ID of Content Template that should be
     * used for archives of this taxonomy, or 0 if no CT is assigned. It doesn't have to be set for all taxonomies.
     *
     * @since unknown
     */
    const TAXONOMY_ARCHIVES_CT_ASSIGNMENT_PREFIX = 'views_template_loop_';


    /**
     * Bootstrap version that is expected to be used in a theme.
     *
     * Allowed values are:
     * - '2': Bootstrap 2.0
     * - '3': Bootstrap 3.0
     * - '-1': Site is not using Bootstrap (@since 1.9)
     * - '1' or missing value (or perhaps anything else, too): Bootstrap version not set
     *
     * @since unknown
     */
    const BOOTSTRAP_VERSION = 'wpv_bootstrap_version';


	/**
	 * Array of functions and class methods that are allowed inside the Views [wpv-conditional] shortcode.
	 */
	const CUSTOM_CONDITIONAL_FUNCTIONS = 'wpv_custom_conditional_functions';


	/**
	 * Array of custom and third-party shortcodes that can be used as Views shortcode arguments.
	 *
	 * Names of shortcodes don't include the surrounding [ ] braces.
	 */
	const CUSTOM_INNER_SHORTCODES = 'wpv_custom_inner_shortcodes';


	/**
	 * Indicates whether Views debug mode is turned on. Numeric value, 0 or 1.
	 */
	const IS_DEBUG_MODE = 'wpv_debug_mode';


	/**
	 * Determines the type of debug mode. Can be 'full' or 'compact'.
	 *
	 * The name is unfortunate, perhaps in future those two settings can be merged.
	 */
	const DEBUG_MODE_TYPE = 'wpv_debug_mode_type';


	/**
	 * Indicates whether Edit links for Views, WPAs and CTs should be displayed on the frontend.
	 *
	 * Numeric value, 0 or 1.
	 */
	const SHOW_FRONTEND_EDIT_LINKS = 'wpv_show_edit_view_link';


	/**
	 * Array of fields (meta keys) that should be displayed on Views GUI,
	 * even though they would be hidden by default. List of meta keys separated by ','.
	 */
	const SHOWN_HIDDEN_CUSTOM_FIELDS = 'wpv_show_hidden_fields';


	/**
	 * Indicates whether the codemirror editors in Views should be automatically resized as their content grows.
	 *
	 * Numeric value, 0 or 1.
	 */
	const CODEMIRROR_AUTORESIZE = 'wpv_codemirror_autoresize';
	
	/**
	 * Indicates whether the manual AJAX pagination browser history management should be enabled.
	 *
	 * Numeric value, 0 or 1.
	 */
	const ENABLE_PAGINATION_MANAGE_HISTORY = 'wpv_enable_pagination_manage_history';
	
	/**
	 * Indicates whether the manual AJAX pagination browser history management should be enabled.
	 *
	 * Numeric value, 0 or 1.
	 */
	const ENABLE_PARAMETRIC_SEARCH_MANAGE_HISTORY = 'wpv_enable_parametric_search_manage_history';


	/**
	 * ID of a Content Template used in the native posts archive, that is, the blog.
	 *
	 * There should not be any conflict since "blog" is a reserved word in WordPress and people should not be able to
	 * create post types or taxonomies with that slug.
	 *
	 * If no CT is set, the value is 0.
	 */
	const BLOG_LOOP_CONTENT_TEMPLATE = 'views_template_loop_blog';


	/**
	 * Determine whether the built-in map addon within Views is enabled.
	 *
	 * Numeric value, 0 or 1.
	 */
	const IS_LEGACY_MAP_ADDON_ENABLED = 'wpv_map_plugin';


	/**
	 * When dealing with the Views Integration, we offer to auto-register some frameworks if we detect that they are
	 * installed on the site. Here we store which one of those has been enabled, since there is no actual code
	 * registering it and we must do it ourselves.
	 *
	 * Should be(!) a string.
	 */
	const SAVED_AUTODETECTED_FRAMEWORK = 'wpv_saved_auto_detected_framework';
	
	/**
	 * Array of assignments for post types in each archive loop.
	 */
	const POST_TYPES_FOR_ARCHIVE_LOOP = 'wpv_post_types_for_archive_loop';



	/* ************************************************************************* *\
        SINGLETON
    \* ************************************************************************* */


	/**
	 * @var WPV_Settings Instance of WPV_Settings.
	 */
	private static $instance = null;


	/**
	 * @return WPV_Settings The instance of WPV_Settings.
	 */
	public static function get_instance() {
		if( null == WPV_Settings::$instance ) {
			WPV_Settings::$instance = new WPV_Settings();
		}
		return WPV_Settings::$instance;
	}
	
	public static function clear_instance() {
		if ( WPV_Settings::$instance ) {
			WPV_Settings::$instance = null;
		}
	}


	/* ************************************************************************* *\
        DEFAULTS
    \* ************************************************************************* */


	/**
	 * @var array Default setting values.
	 * @todo reformat and turn strings into documented constants
	 */
	protected static $defaults = array(
		WPV_Settings::BLOG_LOOP_CONTENT_TEMPLATE				=> 0,
		WPV_Settings::BOOTSTRAP_VERSION							=> 1,
		WPV_Settings::CUSTOM_CONDITIONAL_FUNCTIONS				=> array(),
		WPV_Settings::CUSTOM_INNER_SHORTCODES					=> array(),
		WPV_Settings::IS_DEBUG_MODE								=> '',
		WPV_Settings::DEBUG_MODE_TYPE							=> 'compact',
		WPV_Settings::IS_LEGACY_MAP_ADDON_ENABLED				=> 0,
		WPV_Settings::SHOW_FRONTEND_EDIT_LINKS					=> 1,
		WPV_Settings::SHOWN_HIDDEN_CUSTOM_FIELDS				=> '',
		WPV_Settings::SAVED_AUTODETECTED_FRAMEWORK				=> '',
		WPV_Settings::CODEMIRROR_AUTORESIZE						=> '',
		WPV_Settings::ENABLE_PAGINATION_MANAGE_HISTORY			=> true,
		WPV_Settings::ENABLE_PARAMETRIC_SEARCH_MANAGE_HISTORY	=> true
	);


	/**
	 * @return array Associative array of default values for settings.
	 */
	public function get_defaults() {
		return WPV_Settings::$defaults;
	}


	/**
	 * WPV_Settings constructor.
	 *
	 * @todo make this private
	 */
	protected function __construct() {
		$this->load_settings();
	}


	/* ************************************************************************* *\
        OPTION LOADING AND SAVING
    \* ************************************************************************* */


	private $settings = null;


	/**
	 * Load settings from the database.
	 */
	private function load_settings() {
		$this->settings = get_option( self::OPTION_NAME );
		if( !is_array( $this->settings ) ) {
			$this->settings = array(); // Defaults will be used in this case.
		}
	}


	/**
	 * Persists settings in the database
	 *
	 * @todo Consider some optimalization - only update options that have changed.
	 */
	public function save() {
		update_option( self::OPTION_NAME, $this->settings );
	}



	/* ************************************************************************* *\
        ArrayAccess IMPLEMENTATION
    \* ************************************************************************* */


	/**
	 * isset() for ArrayAccess interface.
	 *
	 * @param mixed $offset setting name
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->settings[ $offset ] );
	}


	/**
	 * Getter for ArrayAccess interface.
	 *
	 * @param mixed $offset setting name
	 * @return mixed setting value
	 */
	public function offsetGet( $offset ) {
		if ( $offset ) {
			return $this->get( $offset );
		} else {
			return null;
		}
	}


	/**
	 * Setter for ArrayAccess interface.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {
		$this->set( $offset, $value );
	}


	/**
	 * unset() for ArrayAccess interface.
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {
		if ( isset( $this->settings[ $offset ] ) ) {
			unset( $this->settings[ $offset ] );
		}
	}


	/* ************************************************************************* *\
        MAGIC PROPERTIES
    \* ************************************************************************* */


	/**
	 * PHP dynamic setter.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * PHP dynamic setter.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}


	/**
	 * PHP dynamic fields unset() method support
	 * @param string $key
	 */
	public function __unset( $key ) {
		if ( $this->offsetExists( $key ) ) {
			$this->offsetUnset( $key );
		}
	}

	/**
	 * PHP dynamic support for isset($this->name)
	 * @param string $key
	 * @return boolean
	 */
	public function __isset( $key ) {
		return $this->offsetExists( $key );
	}


	/* ************************************************************************* *\
        GENERIC GET/SET METHODS
    \* ************************************************************************* */


	/**
	 * Obtain a value for a setting (or all settings).
	 *
	 * @param string $key name of the setting to retrieve
	 * @return mixed value of the key or an array with all key-value pairs
	 */
	public function get( $key = null ) {
		if( $key ) {
			// Retrieve one setting
			$method_name = '_get_' . $key;
			if( method_exists( $this, $method_name ) ) {

				// Use custom getter if it exists
				return $this->$method_name();

			} else {
				return $this->get_raw_value( $key );
			}
		} else {
			// Retrieve all settings
			return wp_parse_args( $this->settings, WPV_Settings::$defaults );
		}
	}


	/**
	 * Get "raw" value from settings or default settings, without taking custom getters into account.
	 *
	 * @param string $key Setting name
	 * @return null|mixed Setting value or null if it's not defined anywhere.
	 */
	private function get_raw_value( $key ) {

		if( isset( $this->settings[ $key ] ) ) {
			// Return user-set value, if available
			return $this->settings[ $key ];
		} elseif( isset( WPV_Settings::$defaults[ $key ] ) ) {
			// Use default value, if available
			return WPV_Settings::$defaults[ $key ];
		} else {
			// There isn't any key like that
			return null;
		}
	}


	/**
	 * Set Setting(s).
	 *
	 * Usage:
	 *  One key-value pair
	 *  set('key', 'value');
	 *
	 *  Multiple key-value pairs
	 *  set( array('key1' => 'value1', 'key2' => 'value2' );
	 *
	 * @param mixed $param1 name of the setting or an array with name-value pairs of the settings (bulk set)
	 * @param mixed $param2 value of the setting
	 */
	public function set( $param1, $param2 = null ) {
		if( is_array( $param1 ) ) {
			foreach( $param1 as $key => $value ) {
				$this->settings[ $key ] = $value;
			}
		} else if( is_object( $param1 ) && is_a( $param1, 'WPV_Settings' ) ) {
			// DO NOTHING.
			// It's assigned already.
		} else if( is_string( $param1 ) || is_integer( $param1 ) ) {

			$key = $param1;
			$value = $param2;

			// Use custom setter if it exists.
			$method_name = '_set_' . $key;
			if( method_exists( $this, $method_name ) )  {
				$this->$method_name( $value );
			} else {
				// Fall back to array access mode
				$this->settings[ $key ] = $value;
			}

		}
	}


	/**
	 * Find out whether we have any knowledge about setting of given name.
	 *
	 * Looks for it's value, default value or for custom getter.
	 *
	 * @param string $key Setting name.
	 * @return bool True if setting seems to exist.
	 */
	public function has_setting( $key ) {
		return (
			isset( $this->settings[ $key ] )
			|| isset( WPV_Settings::$defaults[ $key ] )
			|| method_exists( $this, '_get_' . $key )
		);
	}


	/* ************************************************************************* *\
        CUSTOM GETTERS AND SETTERS
    \* ************************************************************************* */


	/**
	 * Safe wpv_custom_conditional_functions getter, allways returns an array.
	 * @return array
	 */
	protected function _get_wpv_custom_conditional_functions() {
		$value = $this->get_raw_value( WPV_Settings::CUSTOM_CONDITIONAL_FUNCTIONS );
		if( !is_array( $value ) ) {
			return array();
		}
		return $value;
	}


	/**
	 * Safe wpv_custom_conditional_functions setter.
	 *
	 * Consider adding more safety checks so only syntactically valid function/class names can be used.
	 *
	 * @param array $value
	 */
	protected function _set_wpv_custom_conditional_functions( $value ) {
		if( is_array( $value ) ) {
			$this->settings[ WPV_Settings::CUSTOM_CONDITIONAL_FUNCTIONS ] = $value;
		}
	}


	protected function _get_wpv_custom_inner_shortcodes() {
		$value = $this->get_raw_value( WPV_Settings::CUSTOM_INNER_SHORTCODES );
		if( !is_array( $value ) ) {
			return array();
		}
		return $value;
	}


	protected function _set_wpv_custom_inner_shortcodes( $value ) {
		if( is_array( $value ) ) {
			$this->settings[ WPV_Settings::CUSTOM_INNER_SHORTCODES ] = $value;
		}
	}


	protected function _get_wpv_debug_mode() {
		$value = (int) $this->get_raw_value( WPV_Settings::IS_DEBUG_MODE );
		return ( in_array( $value, array( 0, 1 ) ) ? $value : (int) WPV_Settings::$defaults[ WPV_Settings::IS_DEBUG_MODE ] );
	}


	protected function _set_wpv_debug_mode( $value ) {
		$value = (int) $value;
		if( in_array( $value, array( 0, 1 ) ) ) {
			$this->settings[ WPV_Settings::IS_DEBUG_MODE ] = $value;
		}
	}


	protected function _is_valid_wpv_debug_mode_type( $value ) {
		return in_array( $value, array( 'full', 'compact' ) );
	}


	protected function _get_wpv_debug_mode_type() {
		$value = $this->get_raw_value( WPV_Settings::DEBUG_MODE_TYPE );
		if( !$this->_is_valid_wpv_debug_mode_type( $value ) ) {
			return WPV_Settings::$defaults[ WPV_Settings::DEBUG_MODE_TYPE ];
		}
		return $value;
	}


	protected function _set_wpv_debug_mode_type( $value ) {
		if( $this->_is_valid_wpv_debug_mode_type( $value ) ) {
			$this->settings[ WPV_Settings::DEBUG_MODE_TYPE ] = $value;
		}
	}


	protected function _get_wpv_show_edit_view_link() {
		$value = (int) $this->get_raw_value( WPV_Settings::SHOW_FRONTEND_EDIT_LINKS );
		return ( in_array( $value, array( 0, 1 ) ) ? $value : (int) WPV_Settings::$defaults[ WPV_Settings::SHOW_FRONTEND_EDIT_LINKS ] );
	}


	protected function _set_wpv_show_edit_view_link( $value ) {
		$value = (int) $value;
		if( in_array( $value, array( 0, 1 ) ) ) {
			$this->settings[ WPV_Settings::SHOW_FRONTEND_EDIT_LINKS ] = $value;
		}
	}


	protected function _get_wpv_codemirror_autoresize() {
		$value = (int) $this->get_raw_value( WPV_Settings::CODEMIRROR_AUTORESIZE );
		return ( in_array( $value, array( 0, 1 ) ) ? $value : (int) WPV_Settings::$defaults[ WPV_Settings::CODEMIRROR_AUTORESIZE ] );
	}


	protected function _set_wpv_codemirror_autoresize( $value ) {
		$value = (int) $value;
		if( in_array( $value, array( 0, 1 ) ) ) {
			$this->settings[ WPV_Settings::CODEMIRROR_AUTORESIZE ] = $value;
		}
	}
	
	protected function _get_wpv_enable_pagination_manage_history() {
		$value = (int) $this->get_raw_value( WPV_Settings::ENABLE_PAGINATION_MANAGE_HISTORY );
		return ( in_array( $value, array( 0, 1 ) ) ? $value : (int) WPV_Settings::$defaults[ WPV_Settings::ENABLE_PAGINATION_MANAGE_HISTORY ] );
	}


	protected function _set_wpv_enable_pagination_manage_history( $value ) {
		$value = (int) $value;
		if( in_array( $value, array( 0, 1 ) ) ) {
			$this->settings[ WPV_Settings::ENABLE_PAGINATION_MANAGE_HISTORY ] = $value;
		}
	}
	
	protected function _get_wpv_enable_parametric_search_manage_history() {
		$value = (int) $this->get_raw_value( WPV_Settings::ENABLE_PARAMETRIC_SEARCH_MANAGE_HISTORY );
		return ( in_array( $value, array( 0, 1 ) ) ? $value : (int) WPV_Settings::$defaults[ WPV_Settings::ENABLE_PARAMETRIC_SEARCH_MANAGE_HISTORY ] );
	}


	protected function _set_wpv_enable_parametric_search_manage_history( $value ) {
		$value = (int) $value;
		if( in_array( $value, array( 0, 1 ) ) ) {
			$this->settings[ WPV_Settings::ENABLE_PARAMETRIC_SEARCH_MANAGE_HISTORY ] = $value;
		}
	}


	protected function _get_views_template_loop_blog() {
		return (int) $this->get_raw_value( WPV_Settings::BLOG_LOOP_CONTENT_TEMPLATE );
	}


	protected function _set_views_template_loop_blog( $value ) {
		if( is_numeric( $value ) ) {
			$this->settings[ WPV_Settings::BLOG_LOOP_CONTENT_TEMPLATE ] = (int) $value;
		}
	}


	protected function _get_wpv_map_plugin() {
		$value = (int) $this->get_raw_value( WPV_Settings::IS_LEGACY_MAP_ADDON_ENABLED );
		return ( in_array( $value, array( 0, 1 ) ) ? $value : (int) WPV_Settings::$defaults[ WPV_Settings::IS_LEGACY_MAP_ADDON_ENABLED ] );
	}


	protected function _set_wpv_map_plugin( $value ) {
		$value = (int) $value;
		if( in_array( $value, array( 0, 1 ) ) ) {
			$this->settings[ WPV_Settings::IS_LEGACY_MAP_ADDON_ENABLED ] = $value;
		}
	}
	
	protected function _get_wpv_post_types_for_archive_loop() {
		$value = $this->get_raw_value( WPV_Settings::POST_TYPES_FOR_ARCHIVE_LOOP );
		if( !is_array( $value ) ) {
			return array();
		}
		return $value;
	}


	protected function _set_wpv_post_types_for_archive_loop( $value ) {
		if( is_array( $value ) ) {
			$this->settings[ WPV_Settings::POST_TYPES_FOR_ARCHIVE_LOOP ] = $value;
		}
	}


	/* ************************************************************************* *\
        HIGHER LEVEL CUSTOM GETTERS AND SETTERS
    \* ************************************************************************* */


	protected function _get_shown_hidden_custom_fields() {
		$value = $this->wpv_show_hidden_fields;
		if( !is_string( $value ) ) {
			$value = '';
		}
		return explode( ',', $value );
	}


	protected function _set_shown_hidden_custom_fields( $value ) {
		if( is_array( $value ) ) {
			$this->wpv_show_hidden_fields = implode( ',', $value );
		}
	}


	protected function _get_debug_mode() {
		if( 0 == $this->wpv_debug_mode ) {
			return 'off';
		} else {
			return $this->wpv_debug_mode_type;
		}
	}


	/* ************************************************************************* *\
        SETTING-SPECIFIC FUNCTIONALITY
    \* ************************************************************************* */


    /**
     * Get Content Template ID assigned to a post type as a single post template.
     *
     * @param string $post_type Post type slug.
     * @return int Content Template ID or zero if none is assigned.
     * @since 1.9
     */
    function get_ct_assigned_to_single_post_type( $post_type ) {
        $setting_name = WPV_Settings::SINGLE_POST_TYPES_CT_ASSIGNMENT_PREFIX . $post_type;
        return (int) $this->get( $setting_name );
    }


    /**
     * Get Content Template ID assigned to a post type as a post archive template.
     *
     * @param string $post_type Post type slug.
     * @return int Content Template ID or zero if none is assigned.
     * @since 1.9
     */
    function get_ct_assigned_to_cpt_archive( $post_type ) {
        $setting_name = WPV_Settings::CPT_ARCHIVES_CT_ASSIGNMENT_PREFIX . $post_type;
        return (int) $this->get( $setting_name );
    }


    /**
     * Get Content Template ID assigned to a taxonomy as an archive template.
     *
     * @param string $taxonomy_slug Taxonomy slug.
     * @return int Content Template ID or zero if none is assigned.
     * @since 1.9
     */
    function get_ct_assigned_to_taxonomy_archive( $taxonomy_slug ) {
        $setting_name = WPV_Settings::TAXONOMY_ARCHIVES_CT_ASSIGNMENT_PREFIX . $taxonomy_slug;
        return (int) $this->get( $setting_name );
    }


	/**
	 * Get an array of post types with Content Template IDs assigned as templates for single posts.
	 *
	 * @return array Elements with post type slugs as keys and CT IDs as values. When no assignment exists, the key
	 *      will not be present in the array.
	 * @since unknown
	 */
	function get_view_template_settings() {
		$post_types = get_post_types();

		$template_settings = array();

		foreach ( $post_types as $type ) {
			$assigned_ct_id = $this->get_ct_assigned_to_single_post_type( $type );
			if ( 0 != $assigned_ct_id ) {
				$template_settings[ $type ] = $assigned_ct_id;
			}
		}

		return $template_settings;
	}


	/**
	 * Determine if the settings hold an association between Content Template and post type, post archive or taxonomy
	 * archive.
	 *
	 * @param string $setting_name Name of the setting.
	 * @return bool
	 * @since 1.12
	 */
	private function is_setting_an_abstract_ct_association( $setting_name ) {
		return ( strpos( $setting_name, 'views_template_' ) === 0 );
	}


	/**
	 * Replace "abstract" associations of Content Template to post type, post archives or taxonomy archives.
	 *
	 * This method replaces occurences of a Content Template ID by another ID in Views' settings starting
	 * with 'views_template_'. It doesn't touch CT associations for individual posts.
	 *
	 * @param int $original_ct_id Content Template ID that should be replaced.
	 * @param int $new_ct_id Different ID of an existing Content Template (which must exist! we don't check it here)
	 *     or zero to remove the association.
	 * @param bool $autosave If true, settings will be saved to database (if any change has been performed).
	 *
	 * @since 1.12
	 */
	public function replace_abstract_ct_associations( $original_ct_id, $new_ct_id, $autosave = true ) {

		$saving_needed = false;

		foreach ( $this->settings as $setting_name => $setting_value ) {
			if( $this->is_setting_an_abstract_ct_association( $setting_name ) && $setting_value == $original_ct_id ) {
				$this->settings[ $setting_name ] = (int) $new_ct_id;
				$saving_needed = true;
			}
		}

		if( $autosave && $saving_needed ) {
			$this->save();
		}
	}


	/**
	 * Removes View's settings (not Views Plugin Settings) from removed posts.
	 *
	 * @since unknown
	 */
	public function refresh_view_settings_data() {
		// TODO this clearing function deletes all View options but the ones starting with wpv
		// and runs every single time a WPA is updated
		// it loops through every View setting too: it's too expensive
		// We need a better way of clearing the Views settings for loops about WPA and CT when the related objects have been deleted
		// MAYBE it would be better to check on render time, and if now available then delete the record, and remove all this clearing function altogether

		global $wpdb;

		$settings = WPV_Settings::get_instance();
		$s = $settings->get();

		foreach ( $s as $k => $v ) {
			if ( substr( $k, 0, 3 ) != "wpv" ) {
				$post_exists = $wpdb->get_row(
						$wpdb->prepare(
								"SELECT * FROM {$wpdb->posts}
						WHERE ID = %d
						AND post_type IN ('view','view-template')
						LIMIT 1",
								$v
						),
						'ARRAY_A'
				);
				if ( ! $post_exists ) {
					unset( $settings[ $k ] );
				}
			}
		}
	}


}
