<?php
/*
Plugin Name: Toolset Views
Plugin URI: https://toolset.com/?utm_source=viewsplugin&utm_campaign=views&utm_medium=plugins-list-full-version&utm_term=Visit plugin site
Description: When you need to create lists of items, Views is the solution. Views will query the content from the database, iterate through it and let you display it with flair. You can also enable pagination, search, filtering and sorting by site visitors.
Author: OnTheGoSystems
Author URI: https://toolset.com
Version: 2.6
*/



// ----------------------------------
// Plugin initialization
// ----------------------------------

/**
* Set WPV_VERSION
*/

if ( defined( 'WPV_VERSION' ) ) {
	return;
}

define( 'WPV_VERSION', '2.6' );

/**
* Set constants
*/

define( 'WPV_PATH',				dirname( __FILE__ ) );
define( 'WPV_PATH_EMBEDDED',	dirname( __FILE__ ) . '/embedded' );
define( 'WPV_FOLDER',			basename( WPV_PATH ) );

$wpv_templates = WPV_PATH . '/application/views';
define( 'WPV_TEMPLATES', $wpv_templates );

if ( 
	( 
		defined( 'FORCE_SSL_ADMIN' ) 
		&& FORCE_SSL_ADMIN 
	) || is_ssl() 
) {
	define( 'WPV_URL',			rtrim( str_replace( 'http://', 'https://', plugins_url() ), '/' ) . '/' . WPV_FOLDER );
} else {
	define( 'WPV_URL',			plugins_url() . '/' . WPV_FOLDER );
}

define( 'WPV_URL_EMBEDDED',		WPV_URL . '/embedded' );
if ( is_ssl() ) {
	define( 'WPV_URL_EMBEDDED_FRONTEND',	WPV_URL_EMBEDDED );
} else {
	define( 'WPV_URL_EMBEDDED_FRONTEND',	str_replace( 'https://', 'http://', WPV_URL_EMBEDDED ) );
}

/**
* Require OnTheGo Resources and Toolset Common
*/

define( 'WPV_PATH_EMBEDDED_TOOLSET', WPV_PATH . '/vendor/toolset' );
define( 'WPV_URL_EMBEDDED_TOOLSET',	WPV_URL . '/vendor/toolset' );

define('WPV_TOOLSET_THEME_SETTINGS_ABSPATH', WPV_PATH_EMBEDDED_TOOLSET . '/toolset-theme-settings');
define('WPV_TOOLSET_THEME_SETTINGS_URL', WPV_URL_EMBEDDED_TOOLSET . '/toolset-theme-settings');

require WPV_PATH_EMBEDDED_TOOLSET . '/onthego-resources/loader.php';
onthego_initialize( WPV_PATH_EMBEDDED_TOOLSET . '/onthego-resources/', WPV_URL_EMBEDDED_TOOLSET . '/onthego-resources/' );
require WPV_PATH_EMBEDDED_TOOLSET . '/toolset-common/loader.php';
toolset_common_initialize( WPV_PATH_EMBEDDED_TOOLSET . '/toolset-common/', WPV_URL_EMBEDDED_TOOLSET . '/toolset-common/' );
require_once WPV_TOOLSET_THEME_SETTINGS_ABSPATH . '/loader.php';
toolset_theme_settings_initialize(WPV_TOOLSET_THEME_SETTINGS_ABSPATH, WPV_TOOLSET_THEME_SETTINGS_URL );

/**
* Initialize the Views Settings
* @global $WPV_settings WPV_Settings Views settings manager.
* @deprecated Use $s = WPV_Settings::get_instance() instead.
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-settings.class.php';
require WPV_PATH . '/inc/wpv-settings-screen.class.php';
global $WPV_settings;
$WPV_settings = WPV_Settings::get_instance();

// ----------------------------------
// Require files
// ----------------------------------

/**
* Helper classes
*/

require_once WPV_PATH . '/inc/classes/wpv-exception-with-message.class.php';

/**
* WPV_View and other Toolset object wrappers
*/

require_once WPV_PATH_EMBEDDED . '/inc/classes/wpv-post-object-wrapper.class.php';
require_once WPV_PATH_EMBEDDED . '/inc/classes/wpv-view-base.class.php';
require_once WPV_PATH_EMBEDDED . '/inc/classes/wpv-view-embedded.class.php';
require_once WPV_PATH_EMBEDDED . '/inc/classes/wpv-wordpress-archive-embedded.class.php';
require_once WPV_PATH_EMBEDDED . '/inc/classes/wpv-content-template-embedded.class.php';

require_once WPV_PATH . '/inc/classes/wpv-view.class.php';
require_once WPV_PATH . '/inc/classes/wpv-wordpress-archive.class.php';
require_once WPV_PATH . '/inc/classes/wpv-content-template.class.php';

/**
* Cache
*/

require_once WPV_PATH_EMBEDDED . '/inc/classes/wpv-cache.class.php';

/**
* Module Manager integration
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-module-manager.php';

/**
* Constants
* @todo merge this and load just one
*/

require WPV_PATH_EMBEDDED . '/inc/constants-embedded.php';
require WPV_PATH . '/inc/constants.php';

/**
* Working files
* @todo review
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-admin-messages.php';
require WPV_PATH_EMBEDDED . '/inc/functions-core-embedded.php';
require WPV_PATH . '/inc/functions-core.php';

/**
* AJAX management
* @todo most of this should be decoupled to the right files
*/

require WPV_PATH . '/inc/wpv-deprecated.php';
require WPV_PATH . '/inc/wpv-admin-ajax.php';
require WPV_PATH . '/inc/wpv-admin-ajax-layout-wizard.php';

/**
* Debug tool
*/

if ( ! function_exists( 'wpv_debuger' ) ) { 
	require_once WPV_PATH_EMBEDDED . '/inc/wpv-query-debug.class.php';
}

/**
* Shortcodes
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-shortcodes.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-shortcodes-in-shortcodes.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-shortcodes-gui.php';
if ( ! function_exists( 'wpv_shortcode_generator_initialize' ) ) {
	add_action( 'after_setup_theme', 'wpv_shortcode_generator_initialize', 999 );
	function wpv_shortcode_generator_initialize() {
		$toolset_common_bootstrap = Toolset_Common_Bootstrap::getInstance();
		$toolset_common_sections = array( 'toolset_shortcode_generator' );
		$toolset_common_bootstrap->load_sections( $toolset_common_sections );
		require WPV_PATH_EMBEDDED . '/inc/classes/wpv-shortcode-generator.php';
		$wpv_shortcode_generator = new WPV_Shortcode_Generator();
		$wpv_shortcode_generator->initialize();
	}
}

/**
* Conditional
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-conditional.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-condition.php';

/**
* Working files
* @todo review
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-formatting-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-meta-html-embedded.php';
require WPV_PATH . '/inc/wpv-admin-changes.php';// Review contents, there might be DEPRECATED things
require WPV_PATH_EMBEDDED . '/inc/wpv-layout-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-pagination-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-archive-loop.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-user-functions.php';

/**
* Query modifiers
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-filter-order-by-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-types-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-post-types-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-limit-embedded.php';

/**
 * Backend edit sections and query filters
 *
 * Only load the sections and filter files when editing a View or WordPress Archive, or when doing AJAX
 *
 * @since unknown* @since unknown
 * @since 2.4.0 WIP Added the post type filter
 */

if ( 
	( 
		isset( $_GET['page'] ) 
		&& in_array( $_GET['page'], array( 'views-editor', 'view-archives-editor' ) ) 
	) || (
		defined( 'DOING_AJAX' ) 
		&& DOING_AJAX
	)
) {
	// Edit sections
	require_once WPV_PATH . '/inc/sections/wpv-screen-options.php';
	require_once WPV_PATH . '/inc/sections/wpv-section-title.php';
	
	require_once WPV_PATH . '/inc/sections/wpv-section-limit-offset.php';
	
	require_once WPV_PATH . '/inc/sections/wpv-section-layout-extra.php';
	require_once WPV_PATH . '/inc/sections/wpv-section-layout-extra-js.php';
	require_once WPV_PATH . '/inc/sections/wpv-section-content.php';
	// Query filters
	require_once( WPV_PATH . '/inc/filters/wpv-filter-author.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-category.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-date.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-id.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-meta-field.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-parent.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-post-type.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-search.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-status.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-sticky.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-taxonomy-term.php' );
	require_once( WPV_PATH . '/inc/filters/wpv-filter-users.php' );
	
	//require_once( WPV_PATH . '/inc/filters/editor-addon-parametric.class.php' );
}

/**
 * Frontend query filters.
 *
 * @since unknown
 * @since 2.4.0 WIP Added the post type filter embedded side
 */

require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-author-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-category-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-date-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-id-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-meta-field-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-parent-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-post-type-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-search-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-status-embedded.php' );
require_once( WPV_PATH_EMBEDDED . '/inc/filters/wpv-filter-sticky-embedded.php' );

/**
* WPML integration
*/
require WPV_PATH_EMBEDDED . '/inc/WPML/wpv_wpml_core.php';

/**
 * CRED integration
 *
 * @since 2.4.0
 */
require_once WPV_PATH_EMBEDDED . '/inc/third-party/wpv-compatibility-cred.class.php';
WPV_Compatibility_CRED::initialize();


/**
 * WooCommerce integration
 */

require WPV_PATH_EMBEDDED . '/inc/third-party/wpv-compatibility-woocommerce.class.php';


// Other third-party compatibility fixes
require_once WPV_PATH_EMBEDDED . '/inc/third-party/wpv-compatibility-generic.class.php';
WPV_Compatibility_Generic::initialize();


/**
* Main plugin classes
*/

require WPV_PATH_EMBEDDED . '/inc/wpv.class.php';
require WPV_PATH . '/inc/wpv-plugin.class.php';
global $WP_Views;
$WP_Views = new WP_Views_plugin;

require WPV_PATH_EMBEDDED . '/inc/views-templates/functions-templates.php';
require WPV_PATH . '/inc/views-templates/wpv-template-plugin.class.php';
global $WPV_templates;
$WPV_templates = new WPV_template_plugin();

/**
* Query controllers
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-filter-query.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-taxonomy-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-users-embedded.php';

/**
* Frameworks integration
*/

require_once WPV_PATH_EMBEDDED . '/inc/third-party/wpv-framework-api.php';

/**
* Widgets
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-widgets.php';

/**
* Listing pages
* @todo review whether we can load this on demand
*/

// Including files for listing pages
require_once( WPV_PATH . '/inc/wpv-listing-common.php');
//Including files for Views listings and editing
require_once( WPV_PATH . '/inc/redesign/wpv-views-listing-page.php');
require_once( WPV_PATH . '/inc/wpv-add-edit.php');
//Including file for Content Templates listing and editing
require_once( WPV_PATH . '/inc/redesign/wpv-content-templates-listing-page.php');
require_once( WPV_PATH . '/inc/ct-editor/ct-editor.php');
//Including file for WordPress Archives listing and editing
require_once( WPV_PATH . '/inc/redesign/wpv-archive-listing-page.php');
require_once( WPV_PATH . '/inc/wpv-archive-add-edit.php');

/**
* Export / import
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-import-export-embedded.php';
require WPV_PATH . '/inc/wpv-import-export.php';

/**
* Working files
* @todo review
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-summary-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-readonly-embedded.php';
require WPV_PATH . '/inc/wpv-admin-update-help.php';
require WPV_PATH . '/inc/wpv-admin-notices.php';

/**
* Public Views API functions
*/

require WPV_PATH_EMBEDDED . '/inc/wpv-api.php';

/**
* Load all dependencies that needs toolset common loader
* to be completely loaded before being required
*/
if ( ! function_exists( 'wpv_toolset_common_dependent_setup' ) ) {
	add_action('after_setup_theme', 'wpv_toolset_common_dependent_setup', 11 );
	function wpv_toolset_common_dependent_setup(){
		require_once WPV_PATH_EMBEDDED . '/inc/wpv-views-help-videos.class.php';
		require_once WPV_PATH_EMBEDDED . '/inc/wpv-views-scripts.class.php';
	}
}

//add_filter( 'plugin_action_links', 'wpv_views_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'wpv_views_plugin_plugin_row_meta', 10, 4 );

/**
* toolset_is_views_available
*
* Filter to check whether Views is installed
*
* @since 1.9
*/

add_filter( 'toolset_is_views_available', '__return_true' );

/**
* toolset_views_version
*
* Return the current Views version installed
*
* @since 2.1
*/

add_filter( 'toolset_views_version_installed', 'wpv_return_installed_version' );

// ----------------------------------
// Inline documentation plugin support
// ----------------------------------

if( did_action( 'inline_doc_help_viewquery' ) == 0){
	do_action('inline_doc_help_viewquery', 'admin_screen_view_query_init');
}
if( did_action( 'inline_doc_help_viewfilter' )== 0){
	do_action('inline_doc_help_viewfilter', 'admin_screen_view_filter_init');
}
if( did_action( 'inline_doc_help_viewpagination' )== 0){
	do_action('inline_doc_help_viewpagination', 'admin_screen_view_pagination_init');
}	
if( did_action( 'inline_doc_help_viewlayout' )== 0){
	do_action('inline_doc_help_viewlayout', 'admin_screen_view_layout_init');
}	
if( did_action( 'inline_doc_help_viewlayoutmetahtml' )== 0){
	do_action('inline_doc_help_viewlayoutmetahtml', 'admin_screen_view_layoutmetahtml_init');
}	
if( did_action( 'inline_doc_help_viewtemplate' )== 0){
	do_action('inline_doc_help_viewtemplate', 'admin_screen_view_template_init');
}

/*
 * Bootstrap Views
 */
require_once WPV_PATH . '/application/bootstrap.php';
