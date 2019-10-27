<?php
/**
 * Plugin Name: OptinMonster API
 * Plugin URI:  https://optinmonster.com
 * Description: OptinMonster is the best WordPress popup plugin that helps you grow your email list and sales with email popups, exit intent popups, floating bars and more!
 * Author:      OptinMonster Team
 * Author URI:  https://optinmonster.com
 * Version:     1.7.0
 * Text Domain: optin-monster-api
 * Domain Path: languages
 *
 * OptinMonster is is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OptinMonster is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OptinMonster. If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoload the class files.
spl_autoload_register( 'OMAPI::autoload' );

// Store base file location
define( 'OMAPI_FILE', __FILE__ );

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.7.0';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'OptinMonster API';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'optinmonster';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * OMAPI_Ajax object
	 *
	 * @var OMAPI_Ajax
	 */
	public $ajax;

	/**
	 * OMAPI_Type object
	 *
	 * @var OMAPI_Type
	 */
	public $type;

	/**
	 * OMAPI_Output object
	 *
	 * @var OMAPI_Output
	 */
	public $output;

	/**
	 * OMAPI_Shortcode object
	 *
	 * @var OMAPI_Shortcode
	 */
	public $shortcode;

	/**
	 * OMAPI_Actions object (loaded only in the admin)
	 *
	 * @var OMAPI_Actions
	 */
	public $actions;

	/**
	 * OMAPI_Menu object (loaded only in the admin)
	 *
	 * @var OMAPI_Menu
	 */
	public $menu;

	/**
	 * OMAPI_Content object (loaded only in the admin)
	 *
	 * @var OMAPI_Content
	 */
	public $content;

	/**
	 * OMAPI_Save object (loaded only in the admin)
	 *
	 * @var OMAPI_Save
	 */
	public $save;

	/**
	 * OMAPI_Refresh object (loaded only in the admin)
	 *
	 * @var OMAPI_Refresh
	 */
	public $refresh;

	/**
	 * OMAPI_Validate object (loaded only in the admin)
	 *
	 * @var OMAPI_Validate
	 */
	public $validate;

	/**
	 * OMAPI_Welcome object (loaded only in the admin)
	 *
	 * @var OMAPI_Welcome
	 */
	public $welcome;

	/**
	 * OMAPI_Review object (loaded only in the admin)
	 *
	 * @var OMAPI_Review
	 */
	public $review;

	/**
	 * AM_Notification object (loaded only in the admin)
	 *
	 * @var AM_Notification
	 */
	public $notifications;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the plugin widgets.
		add_action( 'widgets_init', array( $this, 'widgets' ) );

		// Load the plugin.
		add_action( 'init', array( $this, 'init' ) );

		// Filter the WooCommerce category/tag REST API responses.
		add_filter( 'woocommerce_rest_prepare_product_cat', 'OMAPI_WooCommerce::add_category_base_to_api_response' );
		add_filter( 'woocommerce_rest_prepare_product_tag', 'OMAPI_WooCommerce::add_tag_base_to_api_response' );
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = 'optin-monster-api';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Registers the OptinMonster widgets.
	 *
	 * @since 1.0.0
	 */
	public function widgets() {

		// To do: add widgets.
		register_widget( 'OMAPI_Widget' );

	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define necessary plugin constants.
		if ( ! defined( 'OPTINMONSTER_APIJS_URL' ) ) {
			define( 'OPTINMONSTER_APIJS_URL', 'https://a.opmnstr.com/app/js/api.min.js' );
		}

		if ( ! defined( 'OPTINMONSTER_APP_URL' ) ) {
			define( 'OPTINMONSTER_APP_URL', 'https://app.optinmonster.com' );
		}

		if ( ! defined( 'OPTINMONSTER_APP_API_URL' ) ) {
			define( 'OPTINMONSTER_APP_API_URL', 'https://app.optinmonster.com/v1/' );
		}

		// Load our global option.
		$this->load_option();

		// Load global components.
		$this->load_global();

		// Load admin only components.
		if ( is_admin() ) {
			$this->load_admin();
		}

		// Run hook once OptinMonster has been fully loaded.
		do_action( 'optin_monster_api_loaded' );

	}

	/**
	 * Sets our global option if it is not found in the DB.
	 *
	 * @since 1.0.0
	 */
	public function load_option() {

		$option = get_option( 'optin_monster_api' );
		if ( ! $option || empty( $option ) ) {
			$option = OMAPI::default_options();
			update_option( 'optin_monster_api', $option );
		}

	}

	/**
	 * Loads all global related classes into scope.
	 *
	 * @since 1.0.0
	 */
	public function load_global() {

		// Register global components.
		$this->ajax      = new OMAPI_Ajax();
		$this->type      = new OMAPI_Type();
		$this->output    = new OMAPI_Output();
		$this->shortcode = new OMAPI_Shortcode();

		// Fire a hook to say that the global classes are loaded.
		do_action( 'optin_monster_api_global_loaded' );

	}

	/**
	 * Loads all admin related classes into scope.
	 *
	 * @since 1.0.0
	 */
	public function load_admin() {

		// Manually load notification api.
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-am-notification.php';

		// Register admin components.
		$this->actions       = new OMAPI_Actions();
		$this->menu          = new OMAPI_Menu();
		$this->content       = new OMAPI_Content();
		$this->save          = new OMAPI_Save();
		$this->refresh       = new OMAPI_Refresh();
		$this->validate      = new OMAPI_Validate();
		$this->welcome       = new OMAPI_Welcome();
		$this->review        = new OMAPI_Review();
		$this->pointer       = new OMAPI_Pointer();
		$this->notifications = new AM_Notification( 'om', $this->version );

		if ( $this->menu->has_trial_link() ) {
			$this->cc = new OMAPI_ConstantContact();
		}

		// Fire a hook to say that the admin classes are loaded.
		do_action( 'optin_monster_api_admin_loaded' );

	}

	/**
	 * Internal method that returns a optin based on ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id     The optin ID used to retrieve a optin.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function get_optin( $id ) {

		return get_post( $id );

	}

	/**
	 * Internal method that returns a optin based on slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The optin slug used to retrieve a optin.
	 * @return array|bool  Array of optin data or false if none found.
	 */
	public function get_optin_by_slug( $slug ) {

		return get_page_by_path( $slug, OBJECT, 'omapi' );

	}

	/**
	 * Internal method that returns all optins created on the site.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of args to modify the query for retreiving optins.
	 * @return array|bool Array of optin data or false if none found.
	 */
	public function get_optins( $args = array() ) {

		$optins = get_posts(
			wp_parse_args(
				$args,
				array(
					'no_found_rows'          => true,
					'nopaging'               => true,
					'post_type'              => 'omapi',
					'posts_per_page'         => -1,
					'update_post_term_cache' => false,
				)
			)
		);

		if ( empty( $optins ) ) {
			return false;
		}

		// Return the optin data.
		return $optins;
	}

	/**
	 * Returns the main option for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return array The main option array for the plugin.
	 */
	public function get_option( $key = '', $subkey = '', $default = false ) {

		$option = get_option( 'optin_monster_api' );
		if ( ! empty( $key ) && ! empty( $subkey ) ) {
			return isset( $option[ $key ][ $subkey ] ) ? $option[ $key ][ $subkey ] : $default;
		} else if ( ! empty( $key ) ) {
			return isset( $option[ $key ] ) ? $option[ $key ] : $default;
		} else {
			return $option;
		}

	}

	/**
	 * Returns the API credentials for OptinMonster.
	 *
	 * @since 1.0.0
	 *
	 * @return array|bool $creds The user's API creds for OptinMonster.
	 */
	public function get_api_credentials() {

		// Prepare variables.
		$option = $this->get_option();
		$key    = false;
		$user   = false;
		$apikey = false;


		// Attempt to grab the new API Key
		if ( empty( $option['api']['apikey'] ) ) {
			if ( defined( 'OPTINMONSTER_REST_API_LICENSE_KEY' ) ) {
				$apikey = OPTINMONSTER_REST_API_LICENSE_KEY;
			}
		} else {
			$apikey = $option['api']['apikey'];
		}

		// Attempt to grab the Legacy API key and API user.
		if ( empty( $option['api']['key'] ) ) {
			if ( defined( 'OPTINMONSTER_API_LICENSE_KEY' ) ) {
				$key = OPTINMONSTER_API_LICENSE_KEY;
			}
		} else {
			$key = $option['api']['key'];
		}

		if ( empty( $option['api']['user'] ) ) {
			if ( defined( 'OPTINMONSTER_API_USER' ) ) {
				$user = OPTINMONSTER_API_USER;
			}
		} else {
			$user = $option['api']['user'];
		}

		// Check if we have any of the authentication data
		if ( ! $apikey ) {
			// Do we at least have Legacy API Key and User
			if ( ! $key || ! $user ) {
				return false;
			}
		}


		// Return the API credentials.
		return apply_filters( 'optin_monster_api_creds',
			array(
				'key'  => $key,
				'user' => $user,
				'apikey' => $apikey,
			)
		);

	}

	/**
	 * Check to see if we have any optins to migrate to the SaaS
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function can_migrate() {
		if ( false == ( $old_optins = get_transient( '_om_old_optins' ) ) ) {
			$args = array(
				'post_type' => 'optin',
				'posts_per_page' => -1,
			);
			$old_optins = get_posts( $args );
			set_transient( '_om_old_optins', $old_optins, DAY_IN_SECONDS );
		}

		if ( empty( $old_optins ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Check for legacy Optin_Monster class
	 *
	 * @since 1.1.5
	 *
	 * @return bool
	 */
	public static function is_legacy_active() {
		return class_exists( 'Optin_Monster' );
	}

	/**
	 * Check if the  main WooCommerce class is active.
	 *
	 * @since 1.1.9
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Return the WooCommerce versions string.
	 *
	 * @since 1.6.5
	 *
	 * @return string
	 */
	public static function woocommerce_version() {
		return defined( 'WC_VERSION' ) ? WC_VERSION : '0.0.0';
	}

	/**
	 * Determines if the passed version string passes the operator compare
	 * against the currently installed version of WooCommerce.
	 *
	 * Defaults to checking if the current WooCommerce version is greater than
	 * the passed version.
	 *
	 * @since 1.7.0
	 *
	 * @param string $version  The version to check.
	 * @param string $operator The operator to use for comparison.
	 *
	 * @return string
	 */
	public static function woocommerce_version_compare( $version = '', $operator = '>=' ) {
		return version_compare( self::woocommerce_version(), $version, $operator );
	}

	/**
	 * Check to see if Mailpoet is active.
	 *
	 * @since 1.2.3
	 *
	 * @return bool
	 */
	public static function is_mailpoet_active() {
		return class_exists( 'WYSIJA_object' ) || class_exists( '\\MailPoet\\Config\\Initializer' );
	}

	/**
	 * Returns possible API key error flag.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if there are API key errors, false otherwise.
	 */
	public function get_api_key_errors() {

		$option = $this->get_option();
		return isset( $option['is_expired'] ) && $option['is_expired'] || isset( $option['is_disabled'] ) && $option['is_disabled'] || isset( $option['is_invalid'] ) && $option['is_invalid'];

	}

	/**
	 * Retrieves the proper default view for the OptinMonster settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return string $view The default view for the OptinMonster settings page.
	 */
	public function get_view() {

		return $this->get_api_credentials() ? 'optins' : 'api';

	}

	/**
	 * Loads the default plugin options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of default plugin options.
	 */
	public static function default_options() {

		$options = array(
			'api'         => array(),
			'optins'      => array(),
			'is_expired'  => false,
			'is_disabled' => false,
			'is_invalid'  => false,
			'welcome'     => array(
				'status'  => 'none', //none, welcomed
				'review'    => 'ask', //ask, asked, dismissed
				'version'   => '1141', //base to check against
			)
		);
		return apply_filters( 'optin_monster_api_default_options', $options );

	}

	/**
	 * PRS-0 compliant autoloader.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classname The classname to check with the autoloader.
	 */
	public static function autoload( $classname ) {

		// Return early if not the proper classname.
		if ( 'OMAPI' !== mb_substr( $classname, 0, 5 ) ) {
			return;
		}

		// Check if the file exists. If so, load the file.
		$filename = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . str_replace( '_', DIRECTORY_SEPARATOR, $classname ) . '.php';
		if ( file_exists( $filename ) ) {
			require $filename;
		}

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return OMAPI
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof OMAPI ) ) {
			self::$instance = new OMAPI();
		}

		return self::$instance;

	}

}

register_activation_hook( __FILE__, 'optin_monster_api_activation_hook' );
/**
 * Fired when the plugin is activated.
 *
 * @since 1.0.0
 *
 * @global int $wp_version      The version of WordPress for this install.
 * @global object $wpdb         The WordPress database object.
 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
 */
function optin_monster_api_activation_hook( $network_wide ) {

	global $wp_version;
	if ( version_compare( $wp_version, '3.5.1', '<' ) && ! defined( 'OPTINMONSTER_FORCE_ACTIVATION' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( sprintf( __( 'Sorry, but your version of WordPress does not meet OptinMonster\'s required version of <strong>3.5.1</strong> to run properly. The plugin has been deactivated. <a href="%s">Click here to return to the Dashboard</a>.', 'optin-monster-api' ), get_admin_url() ) );
	}

	$instance = OMAPI::get_instance();

	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		$site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site->blog_id );

			// Set default option.
			$option = get_option( 'optin_monster_api' );
			if ( ! $option || empty( $option ) ) {
				update_option( 'optin_monster_api', OMAPI::default_options() );
			}

			restore_current_blog();
		}
	} else {
		// Set default option.
		$option = get_option( 'optin_monster_api' );
		if ( ! $option || empty( $option ) ) {
			update_option( 'optin_monster_api', OMAPI::default_options() );
		}
	}

	// If we don't have api credentials, set up the redirect on plugin activation.
	if ( ! $instance->get_api_credentials() ) {
		$options = $instance->get_option();
		$options['welcome']['status'] = 'none';
		update_option( 'optin_monster_api', $options );
	}
}

register_uninstall_hook( __FILE__, 'optin_monster_api_uninstall_hook' );
/**
 * Fired when the plugin is uninstalled.
 *
 * @since 1.0.0
 *
 * @global object $wpdb The WordPress database object.
 */
function optin_monster_api_uninstall_hook() {

	$instance = OMAPI::get_instance();

	global $wpdb;
	if ( is_multisite() ) {
		$site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site->blog_id );
			delete_option( 'optin_monster_api' );
			restore_current_blog();
		}
	} else {
		delete_option( 'optin_monster_api' );
	}

}

// Load the plugin.
$optin_monster_api = OMAPI::get_instance();

// Conditionally load the template tag.
if ( ! function_exists( 'optin_monster' ) ) {
	/**
	 * Primary template tag for outputting OptinMonster optins in templates.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $id     The ID of the optin to load.
	 * @param string $type   The type of field to query.
	 * @param array  $args   Associative array of args to be passed.
	 * @param bool   $return Flag to echo or return the optin HTML.
	 */
	function optin_monster( $id, $type = 'id', $args = array(), $return = false ) {

		// If we have args, build them into a shortcode format.
		$args_string = '';
		if ( ! empty( $args ) ) {
			foreach ( (array) $args as $key => $value ) {
				$args_string .= ' ' . $key . '="' . $value . '"';
			}
		}

		// Build the shortcode.
		$shortcode = ! empty( $args_string ) ? '[optin-monster ' . $type . '="' . $id . '"' . $args_string . ']' : '[optin-monster ' . $type . '="' . $id . '"]';

		// Return or echo the shortcode output.
		if ( $return ) {
			return do_shortcode( $shortcode );
		} else {
			echo do_shortcode( $shortcode );
		}

	}
}

// Backwards compat for the v1 template tag.
if ( ! function_exists( 'optin_monster_tag' ) ) {
	/**
	 * Primary template tag for outputting OptinMonster optins in templates (v1).
	 *
	 * @since 1.0.0
	 *
	 * @param int    $string The post name of the optin to load.
	 * @param bool   $return Flag to echo or return the optin HTML.
	 */
	function optin_monster_tag( $id, $return = false ) {

		// Return the v2 template tag.
		return optin_monster( $id, 'slug', array(), $return );

	}
}
