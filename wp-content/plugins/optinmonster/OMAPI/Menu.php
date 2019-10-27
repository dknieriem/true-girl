<?php
/**
 * Menu class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu class.
 *
 * @since 1.0.0
 */
class OMAPI_Menu {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var OMAPI
	 */
	public $base;

	/**
	 * Holds the admin menu slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Holds a tabindex counter for easy navigation through form fields.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $tabindex = 429;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $isTesting
	 */
	public function __construct( $isTesting = false ) {

		if ( ! $isTesting ) {
			// Set our object.
			$this->set();

			// Load actions and filters.
			add_action( 'admin_menu', array( $this, 'menu' ) );
			// Load helper body classes
			add_filter( 'admin_body_class', array( $this, 'admin_body_classes' ) );

		}

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();
		$this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();

	}

	/**
	 * Loads the OptinMonster admin menu.
	 *
	 * @since 1.0.0
	 */
	public function menu() {

		$this->hook = add_menu_page(
			__( 'OptinMonster', 'optin-monster-api' ),
			__( 'OptinMonster', 'optin-monster-api' ),
			apply_filters( 'optin_monster_api_menu_cap', 'manage_options' ),
			'optin-monster-api-settings',
			array( $this, 'page' ),
			'none',
			579
		);

		// Load global icon font styles.
		add_action( 'admin_head', array( $this, 'icon' ) );

		// Load settings page assets.
		if ( $this->hook ) {
			add_action( 'load-' . $this->hook, array( $this, 'assets' ) );
		}

	}

	/**
	 * Loads the custom Archie icon.
	 *
	 * @since 1.0.0
	 */
	public function icon() {

		?>
		<style type="text/css">@font-face{font-family: 'archie';src:url('<?php echo plugins_url( '/assets/fonts/archie.eot?velzrt', OMAPI_FILE ); ?>');src:url('<?php echo plugins_url( '/assets/fonts/archie.eot?#iefixvelzrt', OMAPI_FILE ); ?>') format('embedded-opentype'),url('<?php echo plugins_url( '/assets/fonts/archie.woff?velzrt', OMAPI_FILE ); ?>') format('woff'),url('<?php echo plugins_url( '/assets/fonts/archie.ttf?velzrt', OMAPI_FILE ); ?>') format('truetype'),url('<?php echo plugins_url( '/assets/fonts/archie.svg?velzrt#archie', OMAPI_FILE ); ?>') format('svg');font-weight: normal;font-style: normal;}#toplevel_page_optin-monster-api-settings .dashicons-before,#toplevel_page_optin-monster-api-settings .dashicons-before:before,#toplevel_page_optin-monster-api-welcome .dashicons-before,#toplevel_page_optin-monster-api-welcome .dashicons-before:before{font-family: 'archie';speak: none;font-style: normal;font-weight: normal;font-variant: normal;text-transform: none;line-height: 1;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;}#toplevel_page_optin-monster-api-settings .dashicons-before:before,#toplevel_page_optin-monster-api-welcome .dashicons-before:before{content: "\e600";font-size: 38px;margin-top: -9px;margin-left: -8px;}</style>
		<?php

	}

	public function admin_body_classes( $classes ) {

		$classes .= ' omapi-screen ';

		if ( $this->base->get_api_key_errors() ) {
			$classes .= ' omapi-has-api-errors ';
		}

		return $classes;

	}

	/**
	 * Loads assets for the settings page.
	 *
	 * @since 1.0.0
	 */
	public function assets() {

		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer' ) );
		add_action( 'in_admin_header', array( $this, 'output_plugin_screen_banner') );
		add_action( 'admin_enqueue_scripts', array( $this, 'fix_plugin_js_conflicts'), 100 );

	}

	/**
	 * Register and enqueue settings page specific CSS.
	 *
	 * @since 1.0.0
	 */
	public function styles() {

		wp_register_style( $this->base->plugin_slug . '-select2', plugins_url( '/assets/css/select2.min.css', OMAPI_FILE ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-select2' );
		wp_register_style( $this->base->plugin_slug . '-settings', plugins_url( '/assets/css/settings.css', OMAPI_FILE ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-settings' );

		// Run a hook to load in custom styles.
		do_action( 'optin_monster_api_admin_styles', $this->view );

	}

	/**
	 * Register and enqueue settings page specific JS.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {
		global $wpdb;

		// Posts query.
		$postTypes = implode( '","', get_post_types( array( 'public' => true ) ) );
		$posts     = $wpdb->get_results( "SELECT ID AS `id`, post_title AS `text` FROM {$wpdb->prefix}posts WHERE post_type IN (\"{$postTypes}\") AND post_status IN('publish', 'future') ORDER BY post_title ASC", ARRAY_A );

		// Taxonomies query.
		$tags = $wpdb->get_results( "SELECT terms.term_id AS 'id', terms.name AS 'text' FROM {$wpdb->prefix}term_taxonomy tax  LEFT JOIN {$wpdb->prefix}terms terms ON terms.term_id = tax.term_id WHERE tax.taxonomy = 'post_tag' ORDER BY text ASC", ARRAY_A );

		wp_register_script( $this->base->plugin_slug . '-select2', plugins_url( '/assets/js/select2.min.js', OMAPI_FILE ), array( 'jquery' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-select2' );
		wp_register_script( $this->base->plugin_slug . '-settings', plugins_url( '/assets/js/settings.js', OMAPI_FILE ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', $this->base->plugin_slug . '-select2' ), $this->base->version, true );
		wp_localize_script( $this->base->plugin_slug . '-settings', 'OMAPI', array(
			'posts' => $posts,
			'tags'  => $tags
		) );
		wp_enqueue_script( $this->base->plugin_slug . '-settings' );
		wp_register_script( $this->base->plugin_slug . '-clipboard', plugins_url( '/assets/js/clipboard.min.js', OMAPI_FILE ), array( $this->base->plugin_slug . '-settings' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-clipboard' );
		wp_register_script( $this->base->plugin_slug . '-tooltip', plugins_url( '/assets/js/tooltip.min.js', OMAPI_FILE ), array( $this->base->plugin_slug . '-settings' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-tooltip' );
		wp_register_script( $this->base->plugin_slug . '-jspdf', plugins_url( '/assets/js/jspdf.min.js', OMAPI_FILE ), array( $this->base->plugin_slug . '-settings' ), $this->base->version, true );
		wp_enqueue_script( $this->base->plugin_slug . '-jspdf' );
		wp_localize_script(
			$this->base->plugin_slug . '-settings',
			'omapi',
			array(
				'ajax'        => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'omapi-query-nonce' ),
				'confirm'     => __( 'Are you sure you want to reset these settings?', 'optin-monster-api' ),
				'date_format' => 'F j, Y',
				'supportData' => $this->get_support_data(),
			)
		);

		// Run a hook to load in custom styles.
		do_action( 'optin_monster_api_admin_scripts', $this->view );

	}

	/**
	 * Deque specific scripts that cause conflicts on settings page
	 *
	 * @since 1.1.5.9
	 */
	public function fix_plugin_js_conflicts(){

		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the OptinMonster Settings screen.
		if ( isset( $screen->id ) && 'toplevel_page_optin-monster-api-settings' !== $screen->id ) {
			return;
		}

		// Dequeue scripts that might cause our settings not to work properly.
		wp_dequeue_script( 'optimizely_config' );

	}

	/**
	 * Combine Support data together to pass into localization
	 *
	 * @since 1.1.5
	 * @return array
	 */
	public function get_support_data() {
		$server_data = '';
		$optin_data = '';

		if ( isset($_GET['optin_monster_api_view']) && $_GET['optin_monster_api_view'] == 'support') {
			$optin_data = $this->get_optin_data();
			$server_data = $this->get_server_data();
		}
		$data = array(
			'server' => $server_data,
			'optins' => $optin_data
		);

		return $data;
	}

	/**
	 * Build Current Optin data array to localize
	 *
	 * @since 1.1.5
	 *
	 * @return array
	 */
	private function get_optin_data() {

		$optins = $this->base->get_optins();
		$optin_data = array();

		if ( $optins ) {
			foreach ( $optins as $optin ) {
				$optin = get_post( $optin->ID );
				$slug = $optin->post_name;
				$design_type = get_post_meta( $optin->ID, '_omapi_type', true );
				$optin_data[ $slug ] = array(
					'Campaign Type'                    => $design_type,
					'WordPress ID'                     => $optin->ID,
					'Associated IDs'                   => get_post_meta( $optin->ID, '_omapi_ids', true ),
					'Current Status'                   => get_post_meta( $optin->ID, '_omapi_enabled', true ) ? 'Live' : 'Disabled',
					'User Settings'                    => get_post_meta( $optin->ID, '_omapi_users', true ),
					'Pages to Never show on'           => get_post_meta( $optin->ID, '_omapi_never', true ),
					'Pages to Only show on'            => get_post_meta( $optin->ID, '_omapi_only', true ),
					'Categories'                       => get_post_meta( $optin->ID, '_omapi_categories', true ),
					'Taxonomies'                       => get_post_meta( $optin->ID, '_omapi_taxonomies', true ),
					'Template types to Show on'        => get_post_meta( $optin->ID, '_omapi_show', true ),
					'Shortcodes Synced and Recognized' => get_post_meta( $optin->ID, '_omapi_shortcode', true ) ? htmlspecialchars_decode( get_post_meta( $optin->ID, '_omapi_shortcode_output', true ) ) : 'None recognized',
				);
				if ( OMAPI_Utils::is_inline_type( $design_type ) ) {
					$optin_data[$slug]['Automatic Output Status'] = get_post_meta( $optin->ID, '_omapi_automatic', true ) ? 'Enabled' : 'Disabled';
				}

			}
		}
		return $optin_data;
	}

	/**
	 * Build array of server information to localize
	 *
	 * @since 1.1.5
	 *
	 * @return array
	 */
	private function get_server_data() {

		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$used_plugins   = "\n";
		$api_ping       = wp_remote_request( OPTINMONSTER_APP_URL . '/v1/ping' );
		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}
			$used_plugins .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}


		$array = array(
			'Server Info'        => esc_html( $_SERVER['SERVER_SOFTWARE'] ),
			'PHP Version'        => function_exists( 'phpversion' ) ? esc_html( phpversion() ) : 'Unable to check.',
			'Error Log Location' => function_exists( 'ini_get' ) ? ini_get( 'error_log' ) : 'Unable to locate.',
			'Default Timezone'   => date_default_timezone_get(),
			'WordPress Home URL' => get_home_url(),
			'WordPress Site URL' => get_site_url(),
			'WordPress Version'  => get_bloginfo( 'version' ),
			'Multisite'          => is_multisite() ? 'Multisite Enabled' : 'Not Multisite',
			'Language'           => get_locale(),
			'API Ping Response'  => wp_remote_retrieve_response_code( $api_ping ),
			'Active Theme'       => $theme,
			'Active Plugins'     => $used_plugins,

		);

		return $array;
	}

	/**
	 * Customizes the footer text on the OptinMonster settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text  The default admin footer text.
	 * @return string $text Amended admin footer text.
	 */
	public function footer( $text ) {

		$url  = 'https://wordpress.org/support/plugin/optinmonster/reviews?filter=5#new-post';
		$text = sprintf( __( 'Please rate <strong>OptinMonster</strong> <a href="%s" target="_blank" rel="noopener">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%s" target="_blank" rel="noopener noreferrer">WordPress.org</a> to help us spread the word. Thank you from the OptinMonster team!', 'optin-monster-api' ), $url, $url );
		return $text;

	}

	/**
	 * Outputs the OptinMonster settings page.
	 *
	 * @since 1.0.0
	 */
	public function page() {

		?>

		<div class="wrap omapi-page">
			<h2></h2>
			<div class="omapi-ui">
				<div class="omapi-tabs">
					<ul class="omapi-panels">
						<?php
							$i = 0; foreach ( $this->get_panels() as $id => $panel ) :
							$first  = 0 == $i ? ' omapi-panel-first' : '';
							$active = $id == $this->view ? ' omapi-panel-active' : '';
						?>
						<li class="omapi-panel omapi-panel-<?php echo sanitize_html_class( $id ); ?><?php echo $first . $active; ?>"><a href="<?php echo esc_url_raw( add_query_arg( 'optin_monster_api_view', $id, admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ); ?>" class="omapi-panel-link" data-panel="<?php echo $id; ?>" data-panel-title="<?php echo $panel; ?>"><?php echo $panel; ?></a></li>
						<?php $i++; endforeach; ?>
					</ul>
				</div>
				<div class="omapi-tabs-content">
					<?php
						foreach ( $this->get_panels() as $id => $panel ) :
						$active = $id == $this->view ? ' omapi-content-active' : '';
					?>
					<div class="omapi-content omapi-content-<?php echo sanitize_html_class( $id ); ?><?php echo $active; ?>">
						<?php
						do_action( 'optin_monster_api_content_before', $id, $panel, $this );
						do_action( 'optin_monster_api_content_' . $id, $panel, $this );
						do_action( 'optin_monster_api_content_after', $id, $panel, $this ); ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Retrieves the available tab panels.
	 *
	 * @since 1.0.0
	 *
	 * @return array $panels Array of tab panels.
	 */
	public function get_panels() {

		// Only load the API panel if no API credentials have been set.
		$panels              = array();
		$creds               = $this->base->get_api_credentials();
		$can_migrate         = $this->base->can_migrate();
		$is_legacy_active    = $this->base->is_legacy_active();
		$woo_version_compare = OMAPI::woocommerce_version_compare( '3.0.0' );
		$can_manage_woo      = current_user_can( 'manage_woocommerce' );

		// Set panels requiring credentials.
		if ( $creds ) {
			$panels['optins'] = __( 'Campaigns', 'optin-monster-api' );
		}

		// Set default panels.
		$panels['api']  = __( 'API Credentials', 'optin-monster-api' );

		// Set the WooCommerce panel.
		if ( $creds && $woo_version_compare && $can_manage_woo ) {
			$panels['woocommerce'] = __( 'WooCommerce', 'optin-monster-api' );
		}

		// Set the Support panel
		$panels['support'] = __( 'Support', 'optin-monster-api' );

		// Set the migration panel.
		if ( $creds && $can_migrate && $is_legacy_active ) {
			$panels['migrate'] = __( 'Migration', 'optin-monster-api' );
		}

		return apply_filters( 'optin_monster_api_panels', $panels );

	}

	/**
	 * Retrieves the setting UI for the setting specified.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id      The optin ID to target.
	 * @param string $setting The possible subkey setting for the option.
	 * @return string         HTML setting string.
	 */
	public function get_setting_ui( $id, $setting = '' ) {

		// Prepare variables.
		$ret      = '';
		$optin_id = isset( $_GET['optin_monster_api_id'] ) ? absint( $_GET['optin_monster_api_id'] ) : 0;
		$value    = 'optins' == $id ? get_post_meta( $optin_id, '_omapi_' . $setting, true ) : $this->base->get_option( $id, $setting );
		$optin = get_post( $optin_id);

		// Load the type of setting UI based on the option.
		switch ( $id ) {
			case 'api' :
				switch ( $setting ) {
					case 'user' :
						$ret = $this->get_password_field( $setting, $value, $id, __( 'Legacy API Username', 'optin-monster-api' ), __( 'The Legacy API Username found in your OptinMonster Account API area.', 'optin-monster-api' ), __( 'Enter your Legacy API Username here...', 'optin-monster-api' ) );
					break 2;

					case 'key' :
						$ret = $this->get_password_field( $setting, $value, $id, __( 'Legacy API Key', 'optin-monster-api' ), __( 'The Legacy API Key found in your OptinMonster Account API area.', 'optin-monster-api' ), __( 'Enter your Legacy API Key here...', 'optin-monster-api' ) );
					break 2;

					case 'apikey' :
						$ret = $this->get_password_field( $setting, $value, $id, __( 'API Key', 'optin-monster-api'), __( 'A single API Key found in your OptinMonster Account API area.', 'optin-monster-api'), __( 'Enter your API Key here...', 'optin-monster-api') );
					break 2;

					case 'omwpdebug' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Debugging Rules', 'optin-monster-api' ), __( 'Allow logged-out/non-admin debugging of plugin rules with the <code>omwpdebug</code> query variable?', 'optin-monster-api' ) );
					break 2;
				}
			break;

			case 'settings' :
				switch ( $setting ) {
					case 'cookies' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Clear local cookies on campaign update?', 'optin-monster-api' ), __( 'If checked, local cookies will be cleared for all campaigns after campaign settings are adjusted and saved.', 'optin-monster-api' ) );
					break 2;
				}
			break;

			case 'woocommerce' :
				switch ( $setting ) {
					case 'settings' :
						$ret = $this->get_woocommerce();
					break 2;
				}
			break;

			case 'support' :
				switch ( $setting ) {
					case 'video' :
						$ret = '<div class="omapi-half-column"><div class="omapi-video-container"><iframe width="640" height="360" src="https://www.youtube.com/embed/tUoJcp5Z9H0?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe></div></div>';
						break 2;

					case 'links' :
						$ret = $this->get_support_links( $setting, 'Helpful Links' );
						break 2;

					case 'server-report';
						$ret = $this->get_plugin_report($setting, 'Server / Plugin Report');
						break 2;
				}
				break;

			case 'toggle' :
				switch ( $setting ) {
					case 'advanced-start' :
						$ret = $this->get_toggle_start( $setting, __( 'Advanced Settings', 'optin-monster-api'), __('More specific settings available for campaign visibility.', 'optin-monster-api') );
					break 2;
					case 'advanced-end' :
						$ret = $this->get_toggle_end();
					break 2;
					case 'woocommerce-start' :
						$ret = $this->get_toggle_start( $setting, __( 'WooCommerce Settings', 'optin-monster-api'), __('More specific settings available for WooCommerce integration.', 'optin-monster-api') );
						break 2;
					case 'woocommerce-end' :
						$ret = $this->get_toggle_end();
						break 2;
				}
			break;

			case 'optins' :
				switch ( $setting ) {
					case 'enabled' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Enable campaign on site?', 'optin-monster-api' ), __( 'The campaign will not be displayed on this site unless this setting is checked.', 'optin-monster-api' ) );
					break 2;

					case 'automatic' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Display the campaign automatically after blog posts', 'optin-monster-api' ), sprintf( __( 'If no advanced settings are selected below, the campaign will display after every post. You can turn this off and add it manually to your posts by <a href="%s" target="_blank" rel="noopener">clicking here and viewing the tutorial.</a>', 'optin-monster-api' ), 'https://optinmonster.com/docs/how-to-manually-add-an-after-post-or-inline-optin/' ), array('omapi-after-post-auto-select') );
					break 2;
					case 'automatic_shortcode' :
						$full_shortcode ='[optin-monster-shortcode id="'. $optin->post_name .'"]';
						$ret = $this->get_text_field(
							$setting,
							$full_shortcode,
							$id,
							__( 'Shortcode for this campaign', 'optin-monster-api' ),
							sprintf( __( 'Use the shortcode to manually add this campaign to inline to a post or page. <a href="%s" title="Click here to learn more about how this work" target="_blank" rel="noopener">Click here to learn more about how this works.</a>', 'optin-monster-api' ), 'https://optinmonster.com/docs/how-to-manually-add-an-after-post-or-inline-optin/' ),
							false,
							array(),
							true
						);
					break 2;

					case 'users' :
						$ret = $this->get_dropdown_field( $setting, $value, $id, $this->get_user_output(), __( 'Who should see this campaign?', 'optin-monster-api' ), sprintf( __( 'Determines who should be able to view this campaign. Want to hide for newsletter subscribers? <a href="%s" target="_blank" rel="noopener">Click here to learn how.</a>', 'optin-monster-api' ), 'https://optinmonster.com/docs/how-to-hide-optinmonster-from-existing-newsletter-subscribers/' ) );
					break 2;

					case 'never' :
						$val = is_array( $value ) ? implode( ',', $value ) : $value;
						$ret = $this->get_custom_field( $setting, '<input type="hidden" value="' . $val . '" id="omapi-field-' . $setting . '" class="omapi-select" name="omapi[' . $id . '][' . $setting . ']" data-placeholder="' . esc_attr__( 'Type to search and select post(s)...', 'optin-monster-api' ) . '">', __( 'Never load campaign on:', 'optin-monster-api' ), __( 'Never loads the campaign on the selected posts and/or pages. Does not disable automatic Global output.', 'optin-monster-api' ) );
					break 2;

					case 'only' :
						$val = is_array( $value ) ? implode( ',', $value ) : $value;
						$ret = $this->get_custom_field( $setting, '<input type="hidden" value="' . $val . '" id="omapi-field-' . $setting . '" class="omapi-select" name="omapi[' . $id . '][' . $setting . ']" data-placeholder="' . esc_attr__( 'Type to search and select post(s)...', 'optin-monster-api' ) . '">', __( 'Load campaign specifically on:', 'optin-monster-api' ), __( 'Loads the campaign on the selected posts and/or pages.', 'optin-monster-api' ) );
					break 2;

					case 'categories' :
						$categories = get_categories();
						if ( $categories ) {
							ob_start();
							wp_category_checklist( 0, 0, (array) $value, false, null, true );
							$cats = ob_get_clean();
							$ret  = $this->get_custom_field( 'categories', $cats, __( 'Load campaign on post categories:', 'optin-monster-api' ) );
						}
					break;

					case 'taxonomies' :
						// Attempt to load post tags.
						$html = '';
						$tags = get_taxonomy( 'post_tag' );
						if ( $tags ) {
							$tag_terms = get_tags();
							if ( $tag_terms ) {
								$display = (array) $value;
								$display = isset( $display['post_tag'] ) ? implode( ',', $display['post_tag'] ) : '';
								$html    = $this->get_custom_field( $setting, '<input type="hidden" value="' . $display . '" id="omapi-field-' . $setting . '" class="omapi-select" name="tax_input[post_tag][]" data-placeholder="' . esc_attr__( 'Type to search and select post tag(s)...', 'optin-monster-api' ) . '">', __( 'Load campaign on post tags:', 'optin-monster-api' ), __( 'Loads the campaign on the selected post tags.', 'optin-monster-api' ) );
							}
						}

						// Possibly load taxonomies setting if they exist.
						$taxonomies                = get_taxonomies( array( 'public' => true, '_builtin' => false ) );
						$taxonomies['post_format'] = 'post_format';
						$data                      = array();

						// Allow returned taxonmies to be filtered before creating UI.
						$taxonomies = apply_filters('optin_monster_api_setting_ui_taxonomies', $taxonomies );

						if ( $taxonomies ) {
							foreach ( $taxonomies as $taxonomy ) {
								$terms = get_terms( $taxonomy );
								if ( $terms ) {
									ob_start();
									$display = (array) $value;
									$display = isset( $display[ $taxonomy ] ) ? $display[ $taxonomy ] : array();
									$tax     = get_taxonomy( $taxonomy );
									$args    = array(
										'descendants_and_self' => 0,
										'selected_cats'        => (array) $display,
										'popular_cats'         => false,
										'walker'               => null,
										'taxonomy'             => $taxonomy,
										'checked_ontop'        => true
									);
									wp_terms_checklist( 0, $args );
									$output = ob_get_clean();
									if ( ! empty( $output ) ) {
										$data[ $taxonomy ] = $this->get_custom_field( 'taxonomies', $output, __( 'Load campaign on ' . strtolower( $tax->labels->name ) . ':', 'optin-monster-api' ) );
									}
								}
							}
						}

						// If we have taxonomies, add them to the taxonomies key.
						if ( ! empty( $data ) ) {
							foreach ( $data as $setting ) {
								$html .= $setting;
							}
						}

						// Return the data.
						$ret = $html;
					break;

					case 'show' :
						$ret = $this->get_custom_field( 'show', $this->get_show_fields( $value ), __( 'Load campaign on post types and archives:', 'optin-monster-api' ) );
					break;

					case 'mailpoet' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Save lead to MailPoet?', 'optin-monster-api' ), __( 'If checked, successful campaign leads will be saved to MailPoet.', 'optin-monster-api' ) );
					break 2;

					case 'mailpoet_list' :
						$ret = $this->get_dropdown_field( $setting, $value, $id, $this->get_mailpoet_lists(), __( 'Add lead to this MailPoet list:', 'optin-monster-api' ), __( 'All successful leads for the campaign will be added to this particular MailPoet list.', 'optin-monster-api' ) );
					break 2;

					// Start WooCommerce settings.
					case 'show_on_woocommerce' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on all WooCommerce pages', 'optin-monster-api' ), __( 'The campaign will show on any page where WooCommerce templates are used.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_shop' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce shop', 'optin-monster-api' ), __( 'The campaign will show on the product archive page (shop).', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_product' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce products', 'optin-monster-api' ), __( 'The campaign will show on any single product.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_cart' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Cart', 'optin-monster-api' ), __( 'The campaign will show on the cart page.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_checkout' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Checkout', 'optin-monster-api' ), __( 'The campaign will show on the checkout page.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_account' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Customer Account', 'optin-monster-api' ), __( 'The campaign will show on the WooCommerce customer account pages.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on all WooCommerce Endpoints', 'optin-monster-api' ), __( 'The campaign will show when on any WooCommerce Endpoint.', 'optin-monster-api' ) );
						break 2;
					case 'is_wc_endpoint_order_pay' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Order Pay endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for order pay is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_order_received' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Order Received endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for order received is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_view_order' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce View Order endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for view order is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_edit_account' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Edit Account endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for edit account is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_edit_address' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Edit Address endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for edit address is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_lost_password' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Lost Password endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for lost password is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_customer_logout' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Customer Logout endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for customer logout is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_endpoint_add_payment_method' :
						$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Show on WooCommerce Add Payment Method endpoint', 'optin-monster-api' ), __( 'The campaign will show when the endpoint page for add payment method is displayed.', 'optin-monster-api' ) );
						break 2;

					case 'is_wc_product_category' :
						$taxonomy = 'product_cat';
						$terms = get_terms( $taxonomy );
						if ( $terms ) {
							ob_start();
							$display = isset( $value ) ? (array) $value : array();
							$args    = array(
								'descendants_and_self' => 0,
								'selected_cats'        => $display,
								'popular_cats'         => false,
								'walker'               => null,
								'taxonomy'             => $taxonomy,
								'checked_ontop'        => true
							);
							wp_terms_checklist( 0, $args );
							$output = ob_get_clean();
							if ( ! empty( $output ) ) {
								$ret = $this->get_custom_field( $setting, $output, __( 'Show on WooCommerce Product Categories:', 'optin-monster-api' ) );
							}
						}
						break 2;

					case 'is_wc_product_tag' :
						$taxonomy = 'product_tag';
						$terms = get_terms( $taxonomy );
						if ( $terms ) {
							ob_start();
							$display = isset( $value ) ? (array) $value : array();
							$args    = array(
								'descendants_and_self' => 0,
								'selected_cats'        => $display,
								'popular_cats'         => false,
								'walker'               => null,
								'taxonomy'             => $taxonomy,
								'checked_ontop'        => true
							);
							wp_terms_checklist( 0, $args );
							$output = ob_get_clean();
							if ( ! empty( $output ) ) {
								$ret = $this->get_custom_field( $setting, $output, __( 'Show on WooCommerce Product Tags:', 'optin-monster-api' ) );
							}
						}
						break 2;

				}
			break;
			case 'note' :
				switch ( $setting ) {
					case 'sidebar_widget_notice' :
						$ret = $this->get_optin_type_note( $setting, __('Use Widgets to set Sidebar output', 'optin-monster-api'), __('You can set this campaign to show in your sidebars using the OptinMonster widget within your sidebars.', 'optin-monster-api'), 'widgets.php', __('Go to Widgets', 'optin-monster-api') );
					break 2;
				}
			break;
		}

		// Return the setting output.
		return apply_filters( 'optin_monster_api_setting_ui', $ret, $setting, $id );

	}

	/**
	 * Returns the user output settings available for an optin.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of user dropdown values.
	 */
	public function get_user_output() {

		return apply_filters( 'optin_monster_api_user_output',
			array(
				array(
					'name'  => __( 'Show campaign to all visitors and users', 'optin-monster-api' ),
					'value' => 'all'
				),
				array(
					'name'  => __( 'Show campaign to only visitors (not logged-in)', 'optin-monster-api' ),
					'value' => 'out'
				),
				array(
					'name'  => __( 'Show campaign to only users (logged-in)', 'optin-monster-api' ),
					'value' => 'in'
				)
			)
		);

	}

	/**
	 * Returns the available MailPoet lists.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of MailPoet lists.
	 */
	public function get_mailpoet_lists() {

		// Prepare variables.
		$mailpoet  = null;
		$lists     = array();
		$ret       = array();
		$listIdKey = 'id';

		// Get lists. Check for MailPoet 3 first. Default to legacy.
		if ( class_exists( '\\MailPoet\\Config\\Initializer' ) ) {
			$lists = \MailPoet\API\API::MP('v1')->getLists();
		} else {
			$mailpoet  = WYSIJA::get( 'list', 'model' );
			$lists     = $mailpoet->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
			$listIdKey = 'list_id';
		}

		// Add default option.
		$ret[]    = array(
			'name'  => __( 'Select your MailPoet list...', 'optin-monster-api' ),
			'value' => 'none'
		);

		// Loop through the list data and add to array.
		foreach ( (array) $lists as $list ) {
			$ret[] = array(
				'name'  => $list['name'],
				'value' => $list[ $listIdKey ],
			);
		}

		/**
		 * Filters the MailPoet lists.
		 *
		 *
		 * @param array       $ret      The MailPoet lists array.
		 * @param array       $lists    The raw MailPoet lists array. Format differs by plugin verison.
		 * @param WYSIJA|null $mailpoet The MailPoet object if using legacy. Null otherwise.
		 */
		return apply_filters( 'optin_monster_api_mailpoet_lists', $ret, $lists, $mailpoet );

	}

	/**
	 * Retrieves the UI output for the single posts show setting.
	 *
	 * @since 2.0.0
	 *
	 * @param array $value  The meta index value for the show setting.
	 * @return string $html HTML representation of the data.
	 */
	public function get_show_fields( $value ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		$output  = '<label for="omapi-field-show-index" class="omapi-custom-label">';
		$output .= '<input type="checkbox" id="omapi-field-show-index" name="omapi[optins][show][]" value="index"' . checked( in_array( 'index', (array) $value ), 1, false ) . ' /> ' . __( 'Front Page and Search Pages', 'optin-monster-api' ) . '</label><br />';
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( (array) $post_types as $show ) {
			$pt_object = get_post_type_object( $show );
			$label     = $pt_object->labels->name;
			$output   .= '<label for="omapi-field-show-' . esc_html( strtolower( $label ) ) . '" class="omapi-custom-label">';
			$output   .= '<input type="checkbox" id="omapi-field-show-' . esc_html( strtolower( $label ) ) . '" name="omapi[optins][show][]" tabindex="' . $this->tabindex . '" value="' . $show . '"' . checked( in_array( $show, (array) $value ), 1, false ) . ' /> ' . esc_html( $label ) . '</label><br />';

			// Increment the global tabindex counter and iterator.
			$this->tabindex++;
		}

		return $output;

	}

	/**
	 * Retrieves the UI output for a plain text input field setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $id      The setting ID to target for name field.
	 * @param string $label   The label of the input field.
	 * @param string $desc    The description for the input field.
	 * @param string $place   Placeholder text for the field.
	 * @param array $classes  Array of classes to add to the field.
	 * @param boolean $copy   Turn on clipboard copy button and make field readonly
	 * @return string $html   HTML representation of the data.
	 */
	public function get_text_field( $setting, $value, $id, $label, $desc = false, $place = false, $classes = array(), $copy = false ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Check for copy set
		$readonly_output = $copy ? 'readonly' : '';

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-text-field omapi-field-box-' . $setting . ' omapi-clear">';
				$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
				$field .= '<input type="text" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . esc_attr( $value ) . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . $readonly_output .' />';
				if ( $copy ) {
					$field .= '<span class="omapi-copy-button button"  data-clipboard-target="#omapi-field-' . $setting . '">Copy to clipboard</span>';
				}
				if ( $desc ) {
					$field .= '<br /><label for="omapi-field-' . $setting . '"><span class="omapi-field-desc">' . $desc . '</span></label>';
				}
				$field .= '</p>';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_text_field', $field, $setting, $value, $id, $label );

	}


	/**
	 * Retrieves the UI output for a password input field setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $id      The setting ID to target for name field.
	 * @param string $label   The label of the input field.
	 * @param string $desc    The description for the input field.
	 * @param string $place   Placeholder text for the field.
	 * @param array $classes  Array of classes to add to the field.
	 * @return string $html   HTML representation of the data.
	 */
	public function get_password_field( $setting, $value, $id, $label, $desc = false, $place = false, $classes = array() ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-password-field omapi-field-box-' . $setting . ' omapi-clear">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
				$field .= '<input type="password" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . ' />';
				if ( $desc ) {
					$field .= '<br /><label for="omapi-field-' . $setting . '"><span class="omapi-field-desc">' . $desc . '</span></label>';
				}
			$field .= '</p>';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_password_field', $field, $setting, $value, $id, $label );

	}

	/**
	 * Retrieves the UI output for a hidden input field setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $id      The setting ID to target for name field.
	 * @param array $classes  Array of classes to add to the field.
	 * @return string $html   HTML representation of the data.
	 */
	public function get_hidden_field( $setting, $value, $id, $classes = array() ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-hidden-field omapi-field-box-' . $setting . ' omapi-clear omapi-hidden">';
		$field .= '<input type="hidden" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '" />';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_hidden_field', $field, $setting, $value, $id );

	}
	/**
	 * Retrieves the UI output for a plain textarea field setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $id      The setting ID to target for name field.
	 * @param string $label   The label of the input field.
	 * @param string $desc    The description for the input field.
	 * @param string $place   Placeholder text for the field.
	 * @param array $classes  Array of classes to add to the field.
	 * @return string $html   HTML representation of the data.
	 */
	public function get_textarea_field( $setting, $value, $id, $label, $desc = false, $place = false, $classes = array() ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-textarea-field omapi-field-box-' . $setting . ' omapi-clear">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
				$field .= '<textarea id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" rows="5" tabindex="' . $this->tabindex . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . '>' . $value . '</textarea>';
				if ( $desc ) {
					$field .= '<br /><label for="omapi-field-' . $setting . '"><span class="omapi-field-desc">' . $desc . '</span></label>';
				}
			$field .= '</p>';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_textarea_field', $field, $setting, $value, $id, $label );

	}

	/**
	 * Retrieves the UI output for a checkbox setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $id      The setting ID to target for name field.
	 * @param string $label   The label of the input field.
	 * @param string $desc    The description for the input field.
	 * @param array $classes  Array of classes to add to the field.
	 * @return string $html   HTML representation of the data.
	 */
	public function get_checkbox_field( $setting, $value, $id, $label, $desc = false, $classes = array() ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-checkbox-field omapi-field-box-' . $setting . ' omapi-clear">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
				$field .= '<input type="checkbox" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . checked( $value, 1, false ) . ' /> ';
				if ( $desc ) {
					$field .= '<label for="omapi-field-' . $setting . '"><span class="omapi-field-desc">' . $desc . '</span></label>';
				}
			$field .= '</p>';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_checkbox_field', $field, $setting, $value, $id, $label );

	}

	/**
	 * Retrieves the UI output for a dropdown field setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $id      The setting ID to target for name field.
	 * @param array $data     The data to be used for option fields.
	 * @param string $label   The label of the input field.
	 * @param string $desc    The description for the input field.
	 * @param array $classes  Array of classes to add to the field.
	 * @return string $html   HTML representation of the data.
	 */
	public function get_dropdown_field( $setting, $value, $id, $data, $label, $desc = false, $classes = array() ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-dropdown-field omapi-field-box-' . $setting . ' omapi-clear">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
				$field .= '<select id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '">';
				foreach ( $data as $i => $info ) {
					$field .= '<option value="' . $info['value'] . '"' . selected( $info['value'], $value, false ) . '>' . $info['name'] . '</option>';
				}
				$field .= '</select>';
				if ( $desc ) {
					$field .= '<br /><label for="omapi-field-' . $setting . '"><span class="omapi-field-desc">' . $desc . '</span></label>';
				}
			$field .= '</p>';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'omapi_dropdown_field', $field, $setting, $value, $id, $label, $data );

	}

	/**
	 * Retrieves the UI output for a field with a custom output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $setting The name of the setting to be saved to the DB.
	 * @param mixed $value    The value of the setting.
	 * @param string $label   The label of the input field.
	 * @param string $desc    The description for the input field.
	 * @return string $html   HTML representation of the data.
	 */
	public function get_custom_field( $setting, $value, $label, $desc = false ) {

		// Build the HTML.
		$field = '<div class="omapi-field-box omapi-custom-field omapi-field-box-' . $setting . ' omapi-clear">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label></p>';
			$field .= $value;
			if ( $desc ) {
				$field .= '<br /><label for="omapi-field-' . $setting . '"><span class="omapi-field-desc">' . $desc . '</span></label>';
			}
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_custom_field', $field, $setting, $value, $label );

	}

	/**
	 * Starts the toggle wrapper for a toggle section.
	 *
	 * @since 1.1.5
	 *
	 * @param $label
	 * @param $desc
	 *
	 * @return mixed|void
	 */
	public function get_toggle_start( $setting, $label, $desc ) {
		$field = '<div class="omapi-ui-toggle-controller">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label></p>';
			if ( $desc ) {
				$field .= '<span class="omapi-field-desc">' . $desc . '</span>';
			}
		$field .= '</div>';
		$field .= '<div class="omapi-ui-toggle-content">';

		return apply_filters( 'optin_monster_api_toggle_start_field', $field, $label, $desc  );
	}

	/**
	 * Closes toggle wrapper.
	 *
	 * @since 1.1.5
	 * @return string HTML end for toggle start
	 */
	public function get_toggle_end(){

		$field = '</div>';

		return apply_filters( 'optin_monster_api_toggle_end_field', $field );
	}

	/**
	 *  Helper note output with title, text, and admin linked button.
	 *
	 * @since 1.1.5
	 *
	 * @param $setting
	 * @param $title
	 * @param $text
	 * @param $admin_page
	 * @param $button
	 *
	 * @return mixed|void
	 */
	public function get_optin_type_note( $setting, $title, $text, $admin_page, $button ) {

		$field = '<div class="omapi-field-box  omapi-inline-notice omapi-field-box-' . $setting . ' omapi-clear">';
		if ($title ) {
			$field .= '<p class="omapi-notice-title">' . $title . '</p>';
		}
		if ($text) {
			$field .= '<p class="omapi-field-desc">' . $text . '</p>';
		}
		if ( $admin_page && $button ) {
			// Increment the global tabindex counter.
			$this->tabindex++;
			$field .= '<a href="' . esc_url_raw( admin_url( $admin_page ) ) . '" class="button button-small" title="' . $button . '" target="_blank">' . $button . '</a>';
		}
		$field .= '</div>';

		return apply_filters('optin_monster_api_inline_note_display', $field, $title, $text, $admin_page, $button );
	}

	/**
	 * Support Link output
	 *
	 * @param $setting
	 *
	 * @return mixed|void HTML of the list filtered as needed
	 */
	public function get_support_links( $setting, $title ) {

		$field ='';

		$field .= '<div class="omapi-support-links ' . $setting . '"><h3>' . $title . '</h3><ul>';
		$field .= '<li><a target="_blank" rel="noopener" href="' . esc_url( 'https://optinmonster.com/docs/' ) . '">'. __('Documentation','optin-monster-api') . '</a></li>';
		$field .= '<li><a target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://wordpress.org/plugins/optinmonster/changelog/' ) . '">'. __('Changelog','optin-monster-api') . '</a></li>';
		$field .= '<li><a target="_blank" rel="noopener" href="' . esc_url( OPTINMONSTER_APP_URL . '/account/support/' ) . '">'. __('Create a Support Ticket','optin-monster-api') . '</a></li>';
		$field .= '</ul></div>';

		return apply_filters( 'optin_monster_api_support_links', $field, $setting);
	}

	public function get_plugin_report( $setting, $title ) {

		$field ='';

		$field .= '<div class="omapi-support-data ' . $setting . '"><h3>' . $title . '</h3>';
		$link = OPTINMONSTER_APP_URL . '/account/support/';
		$field .= '<p>' . sprintf( wp_kses( __( 'Download the report and attach to your <a href="%s">support ticket</a> to help speed up the process.', 'optin-monster-api' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $link ) ) . '</p>';
		$field .= '<a href="' . esc_url_raw( '#' ) . '" id="js--omapi-support-pdf" class="button button-primary button-large omapi-support-data-button" title="Download a PDF Report for Support" target="_blank">Download PDF Report</a>';
		$field .= '</div>';

		return apply_filters( 'optin_monster_api_support_data', $field, $setting, $title );
	}

	/**
	 * Returns the WooCommerce tab output.
	 *
	 * @since 1.7.0
	 *
	 * @return string $output The WooCommerce panel output.
	 */
	public function get_woocommerce() {

		$keys_tab       = OMAPI::woocommerce_version_compare( '3.4.0' ) ? 'advanced' : 'api';
		$keys_admin_url = admin_url( "admin.php?page=wc-settings&tab={$keys_tab}&section=keys" );
		$output         = '';

		if ( OMAPI_WooCommerce::is_connected() ) {
			// Set some default key details.
			$defaults = array(
				'key_id'        => '',
				'description'   => 'no description found',
				'truncated_key' => 'no truncated key found',
			);

			// Get the key details.
			$key_id        = $this->base->get_option( 'woocommerce', 'key_id' );
			$details       = OMAPI_WooCommerce::get_key_details_by_id( $key_id );
			$r             = wp_parse_args( array_filter( $details ), $defaults );
			$description   = esc_html( $r['description'] );
			$truncated_key = esc_html( $r['truncated_key'] );

			// Set up the key details for output.
			$key_string = "<code>{$description} (&hellip;{$truncated_key})</code>";
			$key_url    = esc_url( add_query_arg( 'edit-key', $r['key_id'], $keys_admin_url ) );

			$output .= '<p>WooCommerce is currently connected to OptinMonster with the following key:</p>';
			$output .= '<p>' . $key_string . ' <a href="' . $key_url . '">View key</a></p>';
			$output .= '<p>You need to disconnect WooCommerce, below, to remove your keys from OptinMonster, or to change the consumer key/secret pair associated with OptinMonster.</p>';
			$output .= $this->get_hidden_field( 'disconnect', '1', 'woocommerce' );
		} else {

			$output .= '<p>In order to integrate WooCommerce with the Display Rules in the campaign builder, OptinMonster needs <a href="' . $keys_admin_url . '" target="_blank">WooCommerce REST API credentials</a>. OptinMonster only needs Read access permissions to work. Enter an existing consumer key/secret pair below, or we can auto-generate a new pair of keys for you.</p>';
			$output .= $this->get_text_field(
				'consumer_key',
				'',
				'woocommerce',
				__( 'Consumer key', 'optin-monster-api' ),
				'',
				'ck_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
			);
			$output .= $this->get_text_field(
				'consumer_secret',
				'',
				'woocommerce',
				__( 'Consumer secret', 'optin-monster-api' ),
				'',
				'cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
			);
		}

		return $output;
	}

	/**
	 * Returns svg of OM Logo
	 *
	 * @return string The OptinMonster logo SVG data.
	 */
	public function get_svg_logo() {
		return '<svg class="omapi-svg-logo" viewBox="0 0 716 112" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.41">
	<path d="M527.78 91.4c0 1.1-.55 1.74-1.73 1.92-2.54.36-16 .36-18.35-.1-1.19-.26-1.82-1.08-1.82-2.45 0-3.27.36-13.99.36-17.26 0-2.54-.27-10.9-.27-13.45 0-5.54-2.36-8.27-7.18-8.27-2.9 0-8.9 3.36-8.9 6.63v32.72c0 1.36-.64 2.09-1.91 2.27-2.64.36-15.54.27-17.9-.09-1.28-.18-1.91-.91-1.91-2l.09-28.53c0-10.09-.82-18-2.36-23.72-.19-.82-.28-2 .81-2.27 2.55-.27 5.9-.73 10.18-1.37 6.45-1.27 10-1.9 10.81-1.9 1.18 0 .91 8.18 2.1 8.18.45 0 1.72-1.37 6.53-4.18 4.82-2.82 9-4.19 12.55-4.19 9.81 0 18.62 4.82 18.62 15.27 0 11.63 0 18.81.1 31.17 0 5.27.09 9.18.09 11.81l.09-.18zm40.35-47.24c0 .36-.28.9-.91 1.63-4.91-1.27-12.36.27-12.36 6.27 0 5.27 11.9 9.91 11.9 21.9 0 4-1.72 9.64-4.72 12.09-6 5.09-14.72 9.36-22.45 9.36-1.9 0-9.63-12.9-9.63-14.63 0-.37.27-.55.64-.64 2.81-.54 5.72-1.09 8.54-1.54 3.54-1.19 5.36-3 5.36-5.64 0-1.81-1-3.81-3.09-6.18a179.39 179.39 0 0 1-5.82-6.54C525.14 44.7 542.96 29.9 559.4 29.9c1.55 0 8.64 13 8.64 14.17l.09.1zm47.16 31.26c0 1.9-.91 9-.91 10.9 0 2.36-.27 2.45-2.36 3.64a35.86 35.86 0 0 1-17.36 4.27c-11.72 0-17.54-4.46-17.54-13.45 0-4.45.64-19.54.64-24.08 0-4.82-7-.82-7-4.27 0-1.1.27-4.91.27-5.9 0-1.46-.27-6.37-.27-7.73 0-.82 1.27-1.27 3.9-1.37 1.46 0 2.28-.45 2.37-1.18.27-1.54.36-4 .27-7.27l-.27-7.45c0-4.72.46-7 1.36-7 1.73 0 8 1.46 9.45 1.64 2.1.27 5.18.73 9.36 1.18 1 .1 1.55.45 1.55 1 0 2.73-.82 12-.82 14.72 0 2 .55 3 1.73 3 2.36 0 10.45-.36 12.9-.36.91 0 1.36.27 1.36.9 0 1.82-.63 6.55-.63 8.37l.09 8.45c0 .81-.55 1.27-1.73 1.27-4.18-.1-8.45-.18-12.63-.18-.64 0-.9.9-.9 2.63l.08 8.63c0 6.37.82 9.64 7.55 9.64 3.81 0 9.54-3.09 9.54-.1v.1zm37.53-18.9c0-8-9.9-13.18-15.36-6.28-1.54 2-2.27 4.28-2.27 7 0 1.1.54 1.64 1.73 1.64 3 0 7.63-.46 13.99-1.37 1.36-.18 2-.54 2-1h-.1zm21.26 3.72c0 4.1-1.36 6.45-4 7-.45.09-4.63.54-12.54 1.36-4.81.55-11.9 1.18-21.35 1.91 1.73 8.27 15.45 9.81 23.99 4.82 1.54-1 3.09-1.91 4.63-2.91 1.82.9 7.27 11.36 7.27 13.26 0 .28-.27.73-.9 1.37-4.91 5.27-12.55 7.9-23 7.9-19.08 0-32.35-12.08-32.35-31.26 0-18.35 11.63-32.08 30.35-32.08 7.73 0 14.36 2.82 19.81 8.55a28 28 0 0 1 8.18 20.17l-.09-.09zm41.07-4.82c0 3.1-3.45 2.37-6 2.19-3.72-.28-8.17-.28-10.17 1.45-1.18 1-1.82 3.18-1.82 6.36 0 2.91.19 12.9.19 15.81 0 1.91.45 8.54.45 10.45 0 1-.64 1.55-2 1.64-3.9.36-14.08.36-18.17-.1-1.37-.17-2-.81-2-2l.09-29.07c0-9.54-.91-17.36-2.64-23.45-.18-.81-.27-1.9.82-2.09 2.27-.27 5.45-.72 9.82-1.36 6.36-1.27 9.72-1.9 9.9-1.9 1.82 0 1.1 7.63 2.45 7.63.55 0 1.1-1.1 5-3.18 3.9-2.1 7.63-3.19 11.18-3.19 2.27 0 3.36.46 3.36 1.46 0 1.18-.1 2.82-.36 5-.64 5.9-.28 9.36-.28 14.35h.18zm-348.3 35.54c0 1.36-.64 2.18-1.82 2.36-2.45.36-15.72.36-18.17-.1-1.19-.26-1.82-1.26-1.82-3 0-3.17.36-13.53.36-16.62 0-7.09-.09-12.09-.27-15.09-.27-4.81-2.55-7.17-6.82-7.17-3.81 0-8.36 3.81-8.81 7.72-.18 1.73-.27 6.63-.27 14.9 0 3.18.36 14.09.36 17.36 0 1.36-.55 2-1.73 2h-18.35c-1.1 0-1.73-.82-1.73-2.46 0-3.27.36-13.9.36-17.17 0-2.54-.27-11.08-.27-13.63 0-14.9-15.8-7.27-15.8-1.9l.08 32.89c0 2.09-.18 2.18-2.27 2.36-4.36.36-11.72.36-17.27-.1-2-.17-2-.17-2-2.26l.1-29.08c0-6.63-.46-14.09-1.46-22.36-.18-1-.36-2.08.73-2.45 2-.82 9.09-1.18 10.45-1.45 1.36-.28 7.27-2.1 9.54-2.1 1.82 0 .9 8.55 1.73 8.46 2.45-.64 8.18-8.9 20.08-8.9 7.09 0 14.36 3.36 16.26 10.26 4.28-5.82 12.64-10.27 19.82-10.27 10.17 0 18.71 5 18.71 15.63 0 3.64-.36 15.72-.36 19.36 0 4.27.36 18.72.36 22.99l.28-.18zM41.44 63.15c0-6.09-4-12.09-10.63-12.09-9.91 0-14.09 13-7.91 20.54 6.18 7.54 18.54 3.36 18.54-8.45m66.33.54c0-11.72-12.81-16.26-18.72-8.45-4.9 6.54-3 20.72 8 20.72 7.09 0 10.72-4.09 10.72-12.27m22.45.1c0 16.99-10.9 30.35-28.35 30.35-6.73 0-15.09-4.64-14.82-4.64-.54 0-.81.64-.81 1.82 0 3.63.63 15 .63 18.63 0 1.27-.63 1.9-2 1.9-3.09 0-15.54.28-17.72-.27-1.18-.27-1.81-1-1.81-2.27l.09-46.7c0-9.46-.73-17.36-2.1-23.82-.18-.81-.27-1.9.64-2.27 3.27-1.27 14.82-3 18.45-3 3.73 0 2.36 6.27 3.54 6.27-.45 0 1.37-1.09 5.18-3.27 3.91-2.18 7.36-3.27 10.54-3.27 17.9 0 28.63 12.81 28.63 30.44l-.1.1zm46.34 11.44c0 2.64-.9 8.36-.9 10.9 0 2.2-.19 2.46-2.37 3.64a35.84 35.84 0 0 1-17.36 4.28c-11.72 0-17.53-4.46-17.53-13.45 0-4.64.63-19.45.63-24.08 0-4.91-7-.73-7-4.27 0-1.1.28-4.91.28-5.91 0-1.45-.27-6.36-.27-7.73 0-.81 1.27-1.27 3.9-1.36 1.46 0 2.27-.45 2.37-1.18.27-1.54.36-4 .27-7.27l-.27-7.45c0-4.73.45-7 1.36-7 2.18 0 7.18 1.37 9.45 1.64 2.09.27 5.18.72 9.36 1.18 1 .09 1.54.45 1.54 1 0 3.09-.81 11.63-.81 14.72 0 2 .54 3 1.72 3 2.36 0 10.45-.36 12.9-.36.91 0 1.37.27 1.37.9 0 1.82-.64 6.55-.64 8.36l.1 8.45c0 .82-.55 1.28-1.73 1.28-4.36-.1-8.27-.18-12.63-.18-.64 0-.91.9-.91 2.63l.09 8.63c0 6.36.9 9.64 7.54 9.64 4.27 0 9.54-3.19 9.54-.1v.1zm23.54-10.08c0 5 .36 21.35.36 26.35 0 .82-.54 1.36-1.54 1.55-2.82.45-15.82.54-18.63 0-.91-.19-1.46-.46-1.64-.82-.27-.55.27-24.9.27-27.08 0-11.45-.45-20.63-1.36-27.44-.36-3 .37-2.82 6.73-2.1 8.17.82 9.54-.27 15.8-.27 1.73 0 1.46 1.1 1.37 2.37-.9 7.9-1.36 16.99-1.36 27.44m1.54-45.07c0 7-4 10.54-11.9 10.54-12.54 0-15.36-12.54-8.27-18.27 7.45-6 20.17-1.9 20.17 7.73m65.07 71.15c0 1.09-.55 1.73-1.73 1.9-2.54.37-16 .37-18.36-.08-1.18-.28-1.81-1.1-1.81-2.46 0-3.27.36-14 .36-17.26 0-2.55-.27-10.9-.27-13.45 0-5.54-2.37-8.27-7.18-8.27-3.1 0-8.9 3.45-8.9 6.63v32.72c0 1.36-.64 2.09-1.92 2.27-3 .36-15.08.36-17.9-.1-1.27-.17-1.9-.9-1.9-2l.09-28.52c0-10.1-.82-18-2.37-23.72-.18-.91-.18-2 .82-2.27 2.55-.28 5.9-.73 10.18-1.37 6.45-1.27 10-1.9 10.81-1.9 1.18 0 .91 8.17 2.1 8.17-.46 0 1.72-1.36 6.53-4.18 4.82-2.81 9-4.18 12.55-4.18 9.81 0 18.62 4.82 18.62 15.27 0 11.63 0 18.81.1 31.17 0 5.27.09 9.18.09 11.81l.09-.18zM61.25 63.15c0 18.45-12.18 31.08-30.72 31.08C12.73 94.23 0 81.78 0 63.88c0-18.54 11.54-31.63 30.53-31.63 18.36 0 30.72 12.54 30.72 30.9" fill="#fff" fill-rule="nonzero"/>
	<g opacity=".15" transform="translate(-37.66 -251.39)">
		<clipPath id="a">
			<path d="M385.88 344.16h139.94v15.09H385.88z"/>
		</clipPath>
		<g clip-path="url(#a)">
			<path d="M455.85 344.16c38.62 0 69.97 3.36 69.97 7.54s-31.35 7.55-69.97 7.55-69.97-3.37-69.97-7.55 31.35-7.54 69.97-7.54" fill-rule="nonzero"/>
		</g>
	</g>
	<path d="M460.26 32.8c.91-.55 1.1-3.91 1.18-4.9.28-2.28 3.1-3.28 4.91-2.1 3.18 2.27 4.82 6.63 5.27 10.36.46 3.82.1 8.9-2.63 11.9-2.91 3.28-10.27 4.28-14.63 3.82-1.18-.09-2.28-.36-3.37-.54l-2.09-18.72 2.28.45c2.27.45 6.9.91 9-.27h.08z" fill="#996" fill-rule="nonzero"/>
	<path d="M467.35 29.16a7.6 7.6 0 0 0-1.9-1.9c-.74-.55-2.1-.1-2.2.81l-.08.64c1.36-.46 2.9-.1 4.18.45m2.54 12.82c.55-2.91.28-6.27-.72-9.18a6.36 6.36 0 0 0-2.82-2.18c-1.73-.73-2.64-.46-3.45.27-.1.73-.28 1.27-.46 1.82 4.73 1.54 6.73 6.08 7.46 9.36v-.1zm-13.8-6.73a24.9 24.9 0 0 1-5.1-.54l1.64 15.08c2.18.45 4.72.45 7 .18 1.63-5.81 0-12.63-3.64-14.8l.1.08zm5.08-1c-.63.36-1.55.64-2.63.82 3.27 3.45 4.27 9.72 3.08 14.63 2.73-.55 5-1.55 6.18-2.82.28-.27.46-.63.73-.9 0-5.19-2.09-10.73-7.09-11.82-.09.1-.09.1-.18.1h-.09z" fill="#c0c0a0" fill-rule="nonzero"/>
	<path d="M460.26 44.06c-2.18.55-4.54.64-6.27.46-.73-.1-1.36-.18-2-.36l-1.09-9.36c1.73.36 3.46.54 5.1.54 2.35 1.46 3.9 4.9 4.17 8.72h.1zm6.64-13.17l.27 1.63c.18 1.1.18 2.37.09 3.64a10.65 10.65 0 0 0-4.82-3.45c.18-.55.37-1.1.46-1.82.72-.64 1.72-1 3.45-.27l.55.27m-1.37-3.55c.28.46.46.91.64 1.37-1-.27-2-.37-3.1-.1l.1-.63c.09-.9 1.45-1.36 2.18-.82l.18.1v.08zm-4.36 6.91c-.64.36-1.55.64-2.63.82 2 2.09 3.08 5.18 3.45 8.45a7.12 7.12 0 0 0 3.18-2c.73-.73-1.18-6.81-3.82-7.36-.09.1-.09.1-.18.1" fill="#fbfac4" fill-rule="nonzero"/>
	<path d="M375.03 32.8c-.92-.55-1.1-3.91-1.19-4.9-.27-2.28-3.09-3.28-4.9-2.1-3.19 2.27-4.82 6.63-5.27 10.36-.46 3.82-.1 8.9 2.63 11.9 2.9 3.28 10.27 4.28 14.63 3.82 1.18-.09 2.27-.36 3.36-.54l2.1-18.72-2.28.45c-2.27.45-6.9.91-9-.27h-.08z" fill="#996" fill-rule="nonzero"/>
	<path d="M367.94 29.16a7.61 7.61 0 0 1 1.9-1.9c.73-.55 2.1-.1 2.19.81l.09.64c-1.37-.46-2.91-.1-4.18.45m-2.55 12.82c-.54-2.91-.27-6.27.73-9.18a6.36 6.36 0 0 1 2.82-2.18c1.72-.73 2.63-.46 3.45.27.09.73.27 1.27.45 1.82-4.72 1.54-6.72 6.08-7.45 9.36v-.1zm13.82-6.73c1.63 0 3.36-.18 5.08-.54l-1.63 15.08c-2.18.45-4.73.45-7 .18-1.63-5.81 0-12.63 3.64-14.8l-.1.08zm-5.1-1c.64.36 1.55.64 2.64.82-3.27 3.45-4.27 9.72-3.09 14.63-2.72-.55-5-1.55-6.18-2.82-.27-.27-.45-.63-.72-.9 0-5.19 2.09-10.73 7.08-11.82.1.1.1.1.19.1h.09z" fill="#c0c0a0" fill-rule="nonzero"/>
	<path d="M375.03 44.06c2.18.55 4.54.64 6.27.46.72-.1 1.36-.18 2-.36l1.08-9.36a24.9 24.9 0 0 1-5.08.54c-2.37 1.46-3.91 4.9-4.19 8.72h-.08zm-6.64-13.17l-.27 1.63c-.18 1.1-.18 2.37-.09 3.64a10.64 10.64 0 0 1 4.81-3.45c-.18-.55-.36-1.1-.45-1.82-.73-.64-1.72-1-3.45-.27l-.55.27m1.36-3.55c-.27.46-.45.91-.63 1.37 1-.27 2-.37 3.09-.1l-.1-.63c-.08-.9-1.44-1.36-2.17-.82l-.19.1v.08zm4.37 6.91c.63.36 1.54.64 2.63.82-2 2.09-3.09 5.18-3.45 8.45a7.12 7.12 0 0 1-3.18-2c-.73-.73 1.18-6.81 3.81-7.36.1.1.1.1.19.1" fill="#fbfac4" fill-rule="nonzero"/>
	<path d="M385.11 29.16c-3.18-.27-6.27.37-9.36.82l2.73-2.64a38.36 38.36 0 0 1 9.54-6.45c5-2.54 10.72-4.45 16.45-5-4-1.72-8.55-2.54-12.82-3.54 16.45-3.45 37.53-4.72 52.62 8.09 8.63 7.36 13.36 21 13.36 33.35 0 46.62-79.42 46.62-79.42 0a37.4 37.4 0 0 1 4.45-17.72c-1.09.27-2.09.64-3.18 1.09l-4.82 2 3.45-3.9a28.05 28.05 0 0 1 7.18-5.91l-.18-.19z" fill="#8ed41e" fill-rule="nonzero"/>
	<path d="M420.73 14.8c7.73.91 15.27 3.46 21.54 8.82 7.63 6.45 11.72 18.45 11.72 29.26 0 19.8-16.36 30.08-33.26 30.62V14.8z" fill="#70a91b" fill-rule="nonzero"/>
	<path d="M391.47 24.9c6-4 15.81-5.28 27.63-4.64-2.1-2.46-4-4-6.82-5.36 5.36-.19 9.63 1 20.27 4.63a51.33 51.33 0 0 0-30.99-6.63 37.93 37.93 0 0 1 10.81 5.09c-10.8-1.19-23.26 4-28.26 8.26 3.27-.36 6.18.28 9.27 1.64-4.36 1.54-7.18 2.9-9.9 5.54 4.72-2.36 8.45-4.36 14.44-5.63-2.36-.82-3.63-2.27-6.45-2.9" fill="#a1e141" fill-rule="nonzero"/>
	<path d="M417.82 13.9c10.91 0 19.72 8.54 19.72 19.17 0 10.54-8.8 19.17-19.72 19.17-10.9 0-19.71-8.54-19.71-19.17 0-10.54 8.81-19.17 19.71-19.17" fill="#70a91b" fill-rule="nonzero"/>
	<path d="M386.57 50.97c.63-1.18 60.42-1.63 61.6 0 2.1 2.73.92 5.36 0 7.63h-61.6c-.55-2.45-1.46-5.08 0-7.63" fill="#70a91b" fill-rule="nonzero"/>
	<path d="M387.38 52.7c.64-1.55 58.89-2.18 60.07 0 2 3.72.82 7.36 0 10.45h-60.07c-.54-3.37-1.36-6.9 0-10.45" fill="#4b7113" fill-rule="nonzero"/>
	<path d="M435.82 13.08c-.82-15.54-21.72-17.18-27.17-5 9.63-5.27 19.44-3.36 27.17 5" fill="#70a91b" fill-rule="nonzero"/>
	<path d="M424.19.17C418.1-.73 411.47 1.9 408.74 8c3.63-2 7.36-2.9 10.9-2.9.73-1.37 2.46-4 4.64-4.92h-.1z" fill="#85c51f" fill-rule="nonzero"/>
	<path d="M417.82 11.9c9.82 0 17.72 7.9 17.72 17.72 0 9.81-7.9 17.72-17.72 17.72-9.8 0-17.71-7.9-17.71-17.72 0-9.82 7.9-17.72 17.71-17.72" fill="#d3e8ef" fill-rule="nonzero"/>
	<path d="M406.65 16.44c5.9-4.9 14.17-4.9 18.35.18 4.18 5 2.73 13.09-3.18 18.09-5.9 4.9-14.17 4.9-18.35-.19-4.18-5-2.73-13.08 3.18-18.08" fill="#fff" fill-rule="nonzero"/>
	<path d="M418.28 23.62c6.9 0 12.45 5.18 12.45 11.63 0 6.45-5.55 11.63-12.45 11.63-6.9 0-12.45-5.18-12.45-11.63 0-6.45 5.54-11.63 12.45-11.63" fill="#0d82df" fill-rule="nonzero"/>
	<path d="M418.28 23.62c3.36 0 6.45 1.27 8.63 3.27a16.47 16.47 0 0 1-5.09 7.82c-4.9 4.08-11.45 4.72-15.9 2-.09-.46-.09-.91-.09-1.46 0-6.45 5.54-11.63 12.45-11.63" fill="#0399ed" fill-rule="nonzero"/>
	<path d="M418.28 27.8c4.36 0 8 3.36 8 7.45 0 4.1-3.55 7.45-8 7.45-4.36 0-8-3.36-8-7.45s3.55-7.45 8-7.45" fill="#232323" fill-rule="nonzero"/>
	<path d="M418.28 27.8a8.3 8.3 0 0 1 6.72 3.36 14.3 14.3 0 0 1-3.18 3.45 15.33 15.33 0 0 1-10.81 3.64c-.46-.9-.73-2-.73-3.09 0-4.09 3.55-7.45 8-7.45v.09z" fill="#323232" fill-rule="nonzero"/>
	<path d="M411.28 24.26c3.27 0 5.9 2.45 5.9 5.54s-2.63 5.54-5.9 5.54c-3.27 0-5.9-2.45-5.9-5.54s2.63-5.54 5.9-5.54" fill="#fff" fill-rule="nonzero"/>
	<path d="M376.48 57.06c27.44-4.09 54.98-3.73 82.42 0 2 14.36.9 27.99 0 41.7-27.44 1.1-54.98 1.28-82.42 0-1.64-13.9-1.82-27.8 0-41.7" fill="#9caeb3" fill-rule="nonzero"/>
	<path d="M384.38 95.95c22.27.82 44.44.73 66.7 0-8-4.54-15.99-8.9-24.99-11.36-2.9 1.37-6 2.46-9.08 3.37-.46.09-.46.09-.91 0a76.34 76.34 0 0 1-7.55-2.91 97.44 97.44 0 0 0-24.26 11l.1-.1zm-5.27-34.17c-1.18 11-1 22.09.19 33.08 8-5 16.53-9 25.53-11.81-10.36-5.45-17.63-13.27-25.72-21.27M454 59.6c-24.26-2.9-48.61-3.18-72.88 0 10 11.18 21 20.36 35.63 25.17C432.27 80.32 443 70.6 453.99 59.6m2.1 35.26c.72-11 1.45-21.99.26-32.98-8.17 8.09-16.9 15.8-27.08 21.17 9.46 2.81 18.27 7 26.9 11.81h-.09z" fill="#d3e8ef" fill-rule="nonzero"/>
	<path d="M384.38 95.95c10.73.37 21.45.55 32.26.55v-8.54c-.09 0-.18 0-.45-.1a75.27 75.27 0 0 1-7.54-2.9 97.5 97.5 0 0 0-24.27 11m-5.27-34.17c-1.18 11-1 22.08.19 33.07 8-5 16.53-9 25.53-11.81-10.36-5.45-17.63-13.27-25.72-21.27m37.53-4.45c-11.9 0-23.71.73-35.53 2.28 9.91 11.17 21 20.26 35.53 25.17V57.33z" fill="#fff" fill-rule="nonzero"/>
	<path d="M401.2 63.88c-.1.36-.28.72-.46 1.09a3.26 3.26 0 0 1-1 1.45c-1.09.82-2.45.55-3.63.09-2.64-1.1-4.45-3.73-5.55-6.27-1.27-2.91-1.36-5-.63-8.18 18.35-1 37.16-1.18 54.97-.09v.1h.28c.27 3.08 0 4.35-.73 6.9-.82 2.9-2.54 6.45-5.45 7.81-1.1.55-2.36.82-3.36.1-.46-.37-.82-.91-1.1-1.46-.63-1-.8-2.45-1-3.54-.36.72-.8 1.18-1.54 1.36a26.4 26.4 0 0 1-3.72.54c-1.18.1-2.45.19-3.73.19-1.9 0-4-.19-5.81-.73-.28 1.1-.73 2.09-1.82 2.45-1.18.36-2.64.55-3.9.64-1.37.09-2.73.18-4 .18-2.1 0-4.46-.18-6.37-1a2.45 2.45 0 0 1-1.45-1.54v-.1z" fill="#996" fill-rule="nonzero"/>
	<path d="M432 51.43c.73 4.09.55 9.36-.64 9.63-1.81.45-8.81 1.18-12.26-.1-.91-1.72-.91-6.08-.82-9.62 4.72 0 9.45 0 13.72.09m-29.35.81a82.01 82.01 0 0 0-1.9 6.73c-.37 1.64-.65 3.27-.92 4.45-.36 1.64-1.18 1.91-2.73 1.27-4-1.72-6.08-8.45-5.9-12.54 2.72-.18 6.81-.36 11.45-.54 4.45-.1 9.45-.18 14.45-.18.45 5 .45 11-.64 12.26-1.9.64-9.36 1.55-13-.09-1.63-.72-1-6.81-.9-11.26l.09-.1zm30.08-.81c.54 2.18 1.18 4.45 1.81 7.36.37 1.81.55 3.54.82 4.72.37 1.82 1.1 2.1 2.55 1.37 3.54-1.82 5.54-8.55 5.45-13-2.64-.18-6.36-.36-10.63-.45" fill="#c0c0a0" fill-rule="nonzero"/>
	<path d="M418.28 53.88c4.27 0 8.54 0 12.45.09.63 2.27.63 6.36-2.1 7.36-2.9.27-7.08.36-9.53-.54-.64-1.28-.82-4.1-.82-6.91m-15.72.27c3.9-.09 8.36-.18 12.81-.18.45 3.27 1.27 9.81-2.82 10.18-3 .27-6.72.27-9.08-.73-1.36-.64-1.18-5.18-1-9.18l.09-.09zm-11.18.55c1.91-.18 4.45-.28 7.45-.37.36 0 1.1 9 1.1 9-.37 1.64-1.19 1.9-2.73 1.27-3.19-1.36-5.18-5.9-5.73-9.81l-.09-.1zm43.98 8.81c.37 1.82 1.1 2.1 2.55 1.37 2.9-1.46 4.72-6.18 5.27-10.27a97.38 97.38 0 0 0-6.27-.37c-1.46 1.1-1.73 8.64-1.55 9.27" fill="#fbfac4" fill-rule="nonzero"/>
	<path d="M410.46 54.06c.19 3.55 0 7.09-.45 7.36-.9.55-4.27 1.27-5.9-.09-.64-.45-.55-3.9-.46-7.18 2.18-.09 4.45-.09 6.9-.18l-.09.1zm15.27-.09c.1 2.64-.09 5.09-.45 5.27-.82.45-4 1-5.64-.09-.54-.36-.54-2.82-.45-5.27h6.54v.09zm-28.71.45c-.1 1-.1 2.19-.1 3.37v3.63c0 1.36-.36 1.64-1.09 1-1.45-1.09-2.72-4.54-3.27-7.72 1.27-.1 2.82-.18 4.46-.28m45.07.1c-1.19-.1-2.55-.19-4.1-.19 0 1 .1 2.1.1 3.28v3.9c0 1.46.27 1.73 1 1.1 1.27-1.19 2.45-4.64 3-8.1" fill="#fff" fill-rule="nonzero"/>
	<path d="M381.93 96.68c-1 4.54-5.54 7.54-10.09 7.36-6.27-.27-15.99-7.18-15.8-14 .08-3.81 2.9-7.27 5.8-9.44 2.83-2.19 7.55-4.46 11.19-3.46 3.63 1 6.17 6 7.36 9.27 1.09 2.82 2.18 7.27 1.45 10.27h.1z" fill="#85c51f" fill-rule="nonzero"/>
	<path d="M380.39 96.22c.09-.54.18-1.09.18-1.72-1.18-5-4-10.72-7.18-11.63-4.9-1.46-14.81 5.09-14.27 11.81 2.73 4.18 8.55 7.63 12.72 7.81 3.82.19 7.73-2.36 8.55-6.18v-.09z" fill="#70a91b" fill-rule="nonzero"/>
	<path d="M375.84 91.14c1.27-2.55-.81-7-4-6.64-3.36.46-10.63 5.46-10.63 9.36 0 2.46 3.36 4.18 6.9 3.18 2.64-.72 6.73-3.81 7.73-5.9m2.82 6.36c.64-1.55-.27-4.27-2.09-4-1.9.36-6.45 3.63-6.27 5.45.09 1.46 2.63 2.73 4.63 2a7.69 7.69 0 0 0 3.73-3.45" fill="#5d8d17" fill-rule="nonzero"/>
	<path d="M370.84 79.32c.1 1.64-3.09 3.1-4.36 1.91-1.09-2.09-.81-4.63.1-5.18 1-.54 3.45.46 4.26 3.27M364.67 82.32c.36 1.82-2.1 3.36-3.28 2.55-1.54-1.55-2.09-4.27-1.36-4.91.73-.64 3.36.36 4.64 2.36M360.03 86.87c.55 1.18-.64 2.81-2 2.72-1.45-1.09-2.81-3.63-2.27-4.27.55-.72 3 0 4.27 1.55M383.3 69.96c.72-1.36-1.28-2.27-2.91-2.9-1.64-.55-2.46-.55-3.1.81-.72 1.37-1.08 3.46.64 4 1.64.55 4.73-.54 5.46-1.9h-.1z" fill="#2c440c" fill-rule="nonzero"/>
	<path d="M384.75 64.88c.18 1.54-2 2.36-3.82 2.72-1.72.37-2.45.18-2.63-1.36-.19-1.55.27-4 2.09-4.36 1.72-.37 4.18 1.45 4.36 3" fill="#2c440c" fill-rule="nonzero"/>
	<path d="M385.11 59.79c.19 1.45-2 2.18-3.81 2.54-1.73.36-2.46.18-2.64-1.36-.18-1.45.27-3.73 2-4.09 1.73-.36 4.18 1.36 4.36 2.82l.1.09zM370.84 56.42c.28 1.19 2.55.91 4.28.55 1.72-.45 2.36-.82 2-2-.28-1.18-1.37-2.81-3.1-2.36-1.72.45-3.45 2.63-3.18 3.9v-.09z" fill="#2c440c" fill-rule="nonzero"/>
	<path d="M369.3 70.87l.54 1.55-1.18-.91c-.54-.46-1-1.1-1.36-1.82.09.73.18 1.18.36 1.82-1.72-2.82-3.27-5.9-2.27-10 .91-3.72 4.64-5.45 7.27-6.18a13.9 13.9 0 0 1 5.09-.54c1 .09 2.73.36 3.45 1.27.73.91 1.1 2.82 1.27 4.09.28 2 .19 4.09-.08 6.09a13.1 13.1 0 0 1-1.73 5 5.98 5.98 0 0 1-2.64 2.36 6.42 6.42 0 0 1-3.9.36c-1.82-.36-3.46-1.45-4.91-3l.1-.09z" fill="#85c51f" fill-rule="nonzero"/>
	<path d="M380.3 57.33c1.09 1.28 1.63 6.64.36 10.54a7.22 7.22 0 0 1-6.45-1.63c.09.55.27.9.45 1.18-1.09-.45-2-1.81-2.63-3.36.08 1.27.27 1.82.45 2.55-1.36-.46-2.45-2-3.27-3.64-.73-1.45-.64-3.54.63-4.45 3.37-2.46 9.1-2.64 10.36-1.1l.1-.09z" fill="#a1e141" fill-rule="nonzero"/>
	<path d="M453.45 96.68c1 4.54 5.54 7.54 10.08 7.36 6.27-.27 16-7.18 15.81-14-.09-3.81-2.9-7.27-5.81-9.44-2.82-2.19-7.55-4.46-11.18-3.46s-6.18 6-7.36 9.27c-1.09 2.82-2.18 7.27-1.45 10.27h-.1z" fill="#85c51f" fill-rule="nonzero"/>
	<path d="M454.9 96.22c-.09-.54-.18-1.09-.18-1.72 1.18-5 4-10.72 7.18-11.63 4.9-1.46 14.8 5.09 14.26 11.81-2.72 4.18-8.54 7.63-12.72 7.81-3.82.19-7.72-2.36-8.54-6.18v-.09z" fill="#70a91b" fill-rule="nonzero"/>
	<path d="M459.53 91.14c-1.27-2.55.82-7 4-6.64 3.37.46 10.63 5.46 10.63 9.36 0 2.46-3.36 4.18-6.9 3.18-2.64-.72-6.73-3.81-7.73-5.9m-2.81 6.36c-.64-1.55.27-4.27 2.09-4 1.9.36 6.45 3.63 6.27 5.45-.1 1.46-2.64 2.73-4.64 2a7.68 7.68 0 0 1-3.72-3.45" fill="#5d8d17" fill-rule="nonzero"/>
	<path d="M464.44 79.32c-.09 1.64 3.1 3.1 4.36 1.91 1.1-2.09.82-4.63-.09-5.18-1-.54-3.45.46-4.27 3.27M470.62 82.32c-.36 1.82 2.1 3.36 3.27 2.55 1.55-1.55 2.1-4.27 1.37-4.91-.73-.64-3.37.36-4.64 2.36M475.26 86.87c-.55 1.18.63 2.81 2 2.72 1.45-1.09 2.81-3.63 2.27-4.27-.55-.72-3 0-4.27 1.55M452 69.96c-.74-1.36 1.26-2.27 2.9-2.9 1.64-.55 2.45-.55 3.09.81.73 1.37 1.1 3.46-.64 4-1.63.55-4.72-.54-5.45-1.9h.1z" fill="#2c440c" fill-rule="nonzero"/>
	<path d="M450.63 64.88c-.18 1.54 2 2.36 3.82 2.72 1.72.37 2.45.18 2.63-1.36.18-1.55-.27-4-2.09-4.36-1.73-.37-4.18 1.45-4.36 3" fill="#2c440c" fill-rule="nonzero"/>
	<path d="M450.18 59.79c-.19 1.45 2 2.18 3.81 2.54 1.73.36 2.45.18 2.63-1.36.19-1.45-.27-3.73-2-4.09-1.72-.36-4.17 1.36-4.36 2.82l-.08.09zM464.53 56.42c-.27 1.19-2.54.91-4.27.55-1.72-.45-2.36-.82-2-2 .28-1.18 1.36-2.81 3.09-2.36 1.73.45 3.45 2.63 3.18 3.9v-.09z" fill="#2c440c" fill-rule="nonzero"/>
	<path d="M465.99 70.87l-.55 1.55 1.18-.91c.55-.46 1-1.1 1.37-1.82a10.5 10.5 0 0 1-.37 1.82c1.73-2.82 3.28-5.9 2.28-10-.91-3.72-4.64-5.45-7.28-6.18a13.91 13.91 0 0 0-5.08-.54c-1 .09-2.73.36-3.46 1.27-.72.91-1.09 2.82-1.27 4.09-.27 2-.18 4.09.1 6.09.27 1.81.8 3.54 1.72 5a5.97 5.97 0 0 0 2.63 2.36c1.28.54 2.55.63 3.91.36 1.82-.36 3.45-1.45 4.91-3l-.1-.09z" fill="#85c51f" fill-rule="nonzero"/>
	<path d="M455 57.33c-1.1 1.28-1.65 6.64-.37 10.54a7.22 7.22 0 0 0 6.45-1.63c-.1.55-.27.9-.46 1.18 1.1-.45 2-1.81 2.64-3.36-.09 1.27-.27 1.82-.45 2.55 1.36-.46 2.45-2 3.27-3.64.72-1.45.63-3.54-.64-4.45-3.36-2.46-9.09-2.64-10.36-1.1l-.09-.09z" fill="#a1e141" fill-rule="nonzero"/>
</svg>';
	}

	/**
	 * Return html of header banner
	 *
	 * @return string
	 */
	public function get_plugin_screen_banner() {

		$screen = get_current_screen();

		$html = '';

		$html .= '<div class="omapi-static-banner">';
			$html .= '<div class="inner-container">';
			$html .= '<div class="logo-wrapper">' . $this->get_svg_logo() . '<span class="omapi-logo-version">' . sprintf( __( 'v%s', 'optin-monster-api' ), $this->base->version ) . '</span></div>';
			$html .= '<div class="static-menu"><ul>';
			$html .= '<li><a target="_blank" rel="noopener" href="' . esc_url_raw( 'https://optinmonster.com/docs/' ) . '">' . __('Need Help?', 'optin-monster-api') . '</a></li>';
			$html .= '<li><a href="' . esc_url_raw( 'https://optinmonster.com/contact-us/' ) . '" target="_blank" rel="noopener">' .  __('Send Us Feedback', 'optin-monster-api') . '</a></li>';
			if( $screen->id === 'toplevel_page_optin-monster-api-settings' ) {
				$html .= '<li class="omapi-menu-button"><a id="omapi-create-new-optin-button" href="' . OPTINMONSTER_APP_URL . '/campaigns/new/" class="button button-secondary omapi-new-optin" title="' . __( 'Create New Campaign', 'optin-monster-api' ) . '" target="_blank" rel="noopener">' . __( 'Create New Campaign', 'optin-monster-api' ) . '</a></li>';
			}
			$html .= '</ul></div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;

	}

	/**
	 * Echo out plugin header banner
	 *
	 * @since 1.1.5.2
	 */
	public function output_plugin_screen_banner() {
		echo $this->get_plugin_screen_banner();
	}

	/**
	 * Called whenever a signup link is displayed, this function will
	 * check if there's an affiliate ID specified.
	 *
	 * There are three ways to specify an ID, ordered by highest to lowest priority
	 * - add_filter( 'optinmonster_sas_id', function() { return 1234; } );
	 * - define( 'OPTINMONSTER_SAS_ID', 1234 );
	 * - get_option( 'optinmonster_sas_id' ); (with the option being in the wp_options
	 * table) If an ID is present, returns the affiliate link with the affiliate ID. If no ID is
	 * present, just returns the OptinMonster WP landing page link instead.
	 */
	public function get_sas_link() {

		global $omSasId;
		$omSasId = '';

		// Check if sas ID is a constant
		if ( defined( 'OPTINMONSTER_SAS_ID' ) ) {
			$omSasId = OPTINMONSTER_SAS_ID;
		}

		// Now run any filters that may be on the sas ID
		$omSasId = apply_filters( 'optinmonster_sas_id', $omSasId );

		/**
		 * If we still don't have a sas ID by this point
		 * check the DB for an option
		 */
		if ( empty( $omSasId ) ) {
			$sasId = get_option( 'optinmonster_sas_id', $omSasId );
		}

		// Return the sas link if we have a sas ID
		if ( ! empty( $omSasId ) ) {
			return 'https://www.shareasale.com/r.cfm?u='
				   . urlencode( trim( $omSasId ) )
				   . '&b=601672&m=49337&afftrack=&urllink=optinmonster.com';
		}

		// Return the regular WP landing page by default
		return 'https://optinmonster.com/wp/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard';

	}

	/**
	 * Called whenever a signup link is displayed, this function will
	 * check if there's a trial ID specified.
	 *
	 * There are three ways to specify an ID, ordered by highest to lowest priority
	 * - add_filter( 'optinmonster_trial_id', function() { return 1234; } );
	 * - define( 'OPTINMONSTER_TRIAL_ID', 1234 );
	 * - get_option( 'optinmonster_trial_id' ); (with the option being in the wp_options
	 * table) If an ID is present, returns the trial link with the affiliate ID. If no ID is
	 * present, just returns the OptinMonster WP landing page URL.
	 */
	public function get_trial_link() {

		global $omTrialId;
		$omTrialId = '';

		// Check if trial ID is a constant
		if ( defined( 'OPTINMONSTER_TRIAL_ID' ) ) {
			$omTrialId = OPTINMONSTER_TRIAL_ID;
		}

		// Now run any filters that may be on the trial ID
		$omTrialId = apply_filters( 'optinmonster_trial_id', $omTrialId );

		/**
		 * If we still don't have a trial ID by this point
		 * check the DB for an option
		 */
		if ( empty( $omTrialId ) ) {
			$omTrialId = get_option( 'optinmonster_trial_id', $omTrialId );
		}

		// Return the trial link if we have a trial ID
		if ( ! empty( $omTrialId ) ) {
			return 'https://www.shareasale.com/r.cfm?u='
				   . urlencode( trim( $omTrialId ) )
				   . '&b=601672&m=49337&afftrack=&urllink=optinmonster.com%2Ffree-trial%2F%3Fid%3D' . urlencode( trim( $omTrialId ) );
		}

		// Return the regular WP landing page by default
		return 'https://optinmonster.com/wp/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard';

	}

	public function get_action_link() {
		global $omTrialId, $omSasId;
		$trial = $this->get_trial_link();
		$sas   = $this->get_sas_link();

		if ( ! empty( $omTrialId ) ) {
			return $trial;
		} else if ( ! empty( $omSasId ) ) {
			return $sas;
		} else {
			return 'https://optinmonster.com/wp/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard';
		}
	}

	public function has_trial_link() {

		$link = $this->get_trial_link();
		return strpos( $link, 'optinmonster.com/wp' ) === false;

	}

	public function get_dashboard_link() {

		return $this->has_trial_link() ? esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-welcome' ) ) : esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-settings' ) );

	}

}
