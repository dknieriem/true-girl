<?php
/*
Plugin Name: Toolset Maps
Plugin URI: https://toolset.com/documentation/user-guides/display-on-google-maps/
Description: Toolset Maps will extend Types, Views and Forms with advanced geolocalization features
Version: 1.7.1
Text Domain: toolset-maps
Domain Path: /languages
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com
*/



add_action( 'plugins_loaded', 'toolset_addon_map_load_or_deactivate', 25 );

static $toolset_addon_maps_late_load_list = array();

/**
* toolset_addon_map_load_or_deactivate
*
* Check dependencies, load required files and activate if posible
*
* @since 1.0
* @since 1.1 Raised the priority number of this action so it actually happens after Toolset Views Embedded is loaded.
* @since 1.5 Depends on newer versions of Views, Forms and Types (m2m), and on Toolset Common.
*/
function toolset_addon_map_load_or_deactivate() {
	$requirements = array(
		'cred'		=> array(
			'class_exists'		=> 'CRED_Main',
			'version_constant'	=> 'CRED_FE_VERSION',
			'version_minimum'	=> '2.0',
			'require_once'		=> '/includes/toolset-maps-cred.class.php',
		),
		'views'		=> array(
			'class_exists'		=> 'WP_Views',
			'version_constant'	=> 'WPV_VERSION',
			'version_minimum'	=> '2.6',
			'require_once'		=> array(
				'/includes/toolset-maps-location.php',
				'/includes/toolset-maps-location-factory.php',
				'/includes/toolset-maps-views.class.php',
				'/includes/toolset-maps-views-distance.php',
				'/includes/toolset-views-maps-distance-filter.php',
				'/includes/toolset-maps-views-distance-order.php',
				'/includes/toolset-maps-geolocation-shortcode.php'
			),
		),
		'types'		=> array(
			'class_exists'	    => 'Types_Main',
			'version_constant'	=> 'TYPES_VERSION',
			'version_minimum'	=> '3.0',
			'require_once'		=> '/includes/google_address.php'
		),
		'common'    => array(
			'function_exists'   => 'toolset_common_boostrap',
			'version_constant'  => 'TOOLSET_COMMON_VERSION',
			'version_minimum'   => '2.6.0',
			'library'           => true
		)
	);
	$do_load = false;
	$do_available = array();

	foreach ( $requirements as $req_slug => $req_data ) {
		if (
			(
				(
					isset( $req_data['class_exists'] )
					&& class_exists( $req_data['class_exists'] )
				) || (
					isset( $req_data['function_exists'] )
					&& function_exists( $req_data['function_exists'] )
				)
			)
			&& isset( $req_data['version_constant'] )
			&& defined( $req_data['version_constant'] )
			&& version_compare( constant( $req_data['version_constant'] ), $req_data['version_minimum'], '>=' )
		) {
			// If it's not a library, it's a plugin - activate Maps and load appropriate stuff
			if ( ! array_key_exists( 'library', $req_data ) ) {
				$do_load        = true;
				$do_available[] = $req_slug;
			}
		} else {
			if ( array_key_exists( 'library', $req_data ) ) {
				$do_load = false;
				break; // If a library is not present, there is no point in checking anything else
			}
		}
	}

	if ( $do_load ) {
		define( 'TOOLSET_ADDON_MAPS_VERSION', '1.7.1' );
		define( 'TOOLSET_ADDON_MAPS_PATH', dirname( __FILE__ ) );
		define( 'TOOLSET_ADDON_MAPS_TEMPLATE_PATH', TOOLSET_ADDON_MAPS_PATH . '/application/views/' );
		define( 'TOOLSET_ADDON_MAPS_FOLDER', basename( TOOLSET_ADDON_MAPS_PATH ) );
		define( 'TOOLSET_ADDON_MAPS_FIELD_TYPE', 'google_address' );
		define( 'TOOLSET_ADDON_MAPS_MESSAGE_SPACE_CHAR', '&nbsp;' );
		define( 'TOOLSET_ADDON_MAPS_DOC_LINK', 'https://toolset.com/documentation/user-guides/display-on-google-maps/' );
		define( 'TOOLSET_ADDON_MAPS_URL', rtrim( str_replace( array( 'https://', 'http://'), '//', plugins_url() ), '/' ) . '/' . TOOLSET_ADDON_MAPS_FOLDER );
		define( 'TOOLSET_ADDON_MAPS_FRONTEND_URL', TOOLSET_ADDON_MAPS_URL );
		define( 'TOOLSET_ADDON_MAPS_URL_JS', TOOLSET_ADDON_MAPS_URL.'/resources/js/' );

		// Bootstrap plugin
		require_once TOOLSET_ADDON_MAPS_PATH . '/application/Bootstrap.php';
		$bootstrap = new \OTGS\Toolset\Maps\Bootstrap( $do_available );
		$bootstrap->init();

		// Load legacy classes
		require_once TOOLSET_ADDON_MAPS_PATH.'/includes/toolset-common-functions.php';
		foreach ( $do_available as $do_slug ) {
			if ( isset( $requirements[ $do_slug ]['require_once'] ) ) {
				$require_once_files = $requirements[ $do_slug ]['require_once'];
				if(is_array($require_once_files)) {
					foreach($require_once_files as $source_file) {
						require_once TOOLSET_ADDON_MAPS_PATH . $source_file;
					}
				} else {
					require_once TOOLSET_ADDON_MAPS_PATH . $require_once_files;
				}

			}
		}
		load_plugin_textdomain(
			'toolset-maps',
			null,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	} else {
		add_action( 'admin_init', 'toolset_addon_map_deactivate' );
		add_action( 'admin_notices', 'toolset_addon_map_deactivate_notice' );
	}
}

/**
* toolset_addon_map_deactivate
*
* Deactivate this plugin
*
* @since 1.0
*/

function toolset_addon_map_deactivate() {
	$plugin = plugin_basename( __FILE__ );
	deactivate_plugins( $plugin );
	if ( ! is_network_admin() ) {
		update_option( 'recently_activated', array( $plugin => time() ) + (array) get_option( 'recently_activated' ) );
	} else {
		update_site_option( 'recently_activated', array( $plugin => time() ) + (array) get_site_option( 'recently_activated' ) );
	}
}

/**
* toolset_addon_map_deactivate_notice
*
* Deactivate notice for this plugin
*
* @since 1.0
*/
function toolset_addon_map_deactivate_notice() {
	?>
	<div class="error is-dismissable">
		<p>
		<?php
		_e(
			'Toolset Maps was <strong>deactivated</strong>. You need at least Views 2.6 or Types 3.0 or Forms 2.0.',
			'toolset-maps'
		);
		?>
		</p>
	</div>
	<?php
}
