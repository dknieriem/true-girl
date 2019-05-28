<?php
/**
 * A class to manage various stuff in the WordPress admin area.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A class to manage various stuff in the WordPress admin area.
 */
class Avada_Admin {

	/**
	 * Holds the current theme version.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_version;

	/**
	 * Holds the WP_Theme object of Avada.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var WP_Theme object
	 */
	private $theme_object;

	/**
	 * Holds the URL to the Avada live demo site.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_url = 'https://avada.theme-fusion.com/';

	/**
	 * Holds the URL to ThemeFusion company site.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_fusion_url = 'https://theme-fusion.com/';

	/**
	 * Normalized path to includes folder.
	 *
	 * @since 5.1.0
	 *
	 * @access private
	 * @var string
	 */
	private $includes_path = '';

	/**
	 * Construct the admin object.
	 *
	 * @since 3.9.0
	 */
	public function __construct() {

		$this->includes_path = wp_normalize_path( dirname( __FILE__ ) );

		$this->set_theme_version();
		$this->set_theme_object();

		$this->register_product_envato_hosted();

		add_action( 'wp_before_admin_bar_render', array( $this, 'add_wp_toolbar_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_init', array( $this, 'init_permalink_settings' ) );
		add_action( 'admin_init', array( $this, 'save_permalink_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'edit_admin_menus' ), 999 );
		add_action( 'admin_head', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'after_switch_theme', array( $this, 'activation_redirect' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		add_action( 'try_gutenberg_panel', array( $this, 'try_gutenberg_panel_addition' ), 5 );

		// User agent for news feed widget.
		add_action( 'wp_feed_options', array( $this, 'feed_user_agent' ), 10, 2 );

		add_filter( 'tgmpa_notice_action_links', array( $this, 'edit_tgmpa_notice_action_links' ) );
		$prefix = ( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN ) ? 'network_admin_' : '';
		add_filter( "tgmpa_{$prefix}plugin_action_links", array( $this, 'edit_tgmpa_action_links' ), 10, 4 );

		// Get demos data on theme activation.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}
		add_action( 'after_switch_theme', array( 'Avada_Importer_Data', 'get_data' ), 5 );

		// Change auto update notes for LayerSlider.
		add_action( 'layerslider_ready', array( $this, 'layerslider_overrides' ) );

		// Facebook instant articles rule set definition.
		add_filter( 'instant_articles_transformer_rules_loaded', array( $this, 'add_instant_article_rules' ) );

		// Load jQuery in the demos and plugins page.
		if ( isset( $_GET['page'] ) && ( 'avada-demos' === $_GET['page'] || 'avada-plugins' === $_GET['page'] ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'add_jquery' ) );

			if ( 'avada-plugins' === $_GET['page'] ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'add_jquery_ui_styles' ) );
			}
		}

		add_action( 'wp_ajax_fusion_activate_plugin', array( $this, 'ajax_activate_plugin' ) );
		// By default TGMPA doesn't load in AJAX calls.
		// Filter is applied inside a method which is hooked to 'init'.
		add_filter( 'tgmpa_load', array( $this, 'enable_tgmpa' ), 10 );

		add_action( 'wp_ajax_fusion_install_plugin', array( $this, 'ajax_install_plugin' ) );

		// Add taxonomy meta boxes.
		if ( function_exists( 'update_term_meta' ) ) {
			add_action( 'wp_loaded', array( $this, 'avada_taxonomy_meta' ) );
		}
	}

	/**
	 * Adds jQuery.
	 *
	 * @since 5.0.0
	 * @access public
	 * @return void
	 */
	public function add_jquery() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
	}

	/**
	 * Adds jQuery.
	 *
	 * @since 5.4.1
	 * @access public
	 * @return void
	 */
	public function add_jquery_ui_styles() {
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}

	/**
	 * Adds the news dashboard widget.
	 *
	 * @since 3.9.0
	 * @access public
	 * @return void
	 */
	public function add_dashboard_widget() {
		// Create the widget.
		wp_add_dashboard_widget( 'themefusion_news', apply_filters( 'avada_dashboard_widget_title', esc_attr__( 'ThemeFusion News', 'Avada' ) ), array( $this, 'display_news_dashboard_widget' ) );

		// Make sure our widget is on top off all others.
		global $wp_meta_boxes;

		// Get the regular dashboard widgets array.
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

		$avada_widget_backup = array();
		if ( isset( $normal_dashboard['themefusion_news'] ) ) {
			// Backup and delete our new dashboard widget from the end of the array.
			$avada_widget_backup = array(
				'themefusion_news' => $normal_dashboard['themefusion_news'],
			);
			unset( $normal_dashboard['themefusion_news'] );
		}

		// Merge the two arrays together so our widget is at the beginning.
		$sorted_dashboard = array_merge( $avada_widget_backup, $normal_dashboard );

		// Save the sorted array back into the original metaboxes.
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}

	/**
	 * Adds info to the try Gutenberg panel in dashboard.
	 *
	 * @since 5.6.2
	 * @access public
	 * @return void
	 */
	public function try_gutenberg_panel_addition() {
		?>
		<div class="fusion-gutenberg-info try-gutenberg-panel-content" style="margin-bottom: 13px;">
			<h2>Avada, Fusion Builder, and Gutenberg</h2>
			<p class="about-description">Important information regarding publishing content and editing posts.</p>
			<hr>
			<div class="fusion-gutenberg-info-column-container try-gutenberg-panel-column-container">
				<div class="try-gutenberg-panel-column try-gutenberg-panel-image-column">
					<img src="https://theme-fusion.com/wp-content/uploads/2018/07/avada_gutenberg-800x500.jpg" style="border:none;">
				</div>
				<div class="try-gutenberg-panel-column" style="grid-template-rows: auto;">
					<div>
						<h3>Important test information</h3>

						<p>New to WordPress (4.9.8) is the callout to try the new Gutenberg editor as a plugin. This plugin is under development and intended for testing and feedback purposes. Gutenberg will be merged into the WordPress Core for version 5.0, which currently has no ETA. The whole Gutenberg project is still in a pre-beta stage, which needs to be remembered when testing.</p>
						<p>If you want to try out Gutenberg, feel free to install the plugin and once you are done with testing, you can simply deactivate it and carry on working as per usual. View the install option below.</p>
					</div>
				</div>
				<div class="try-gutenberg-panel-column" style="grid-template-rows: auto;">
					<div>
						<h3>Compatibility information</h3>

						<p>Avada and Gutenberg is a work in progress and in time compatibility will be ensured by our team, as the development of the new editor progresses. Until then, if you do decide to try the new Gutenberg editor, understand that you should not edit any existing Fusion Builder generated page/post content with Gutenberg and vice versa, as they are not interchangeable.</p>
					</div>
				</div>
			</div>
			<?php if ( ! is_plugin_active( 'gutenberg/gutenberg.php' ) ) : ?>
				<a href="#" class="fusion-toggle-gutenberg-info" style="margin-top:-20px;">View info from WP</a>
			<?php endif; ?>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					jQuery( '.try-gutenberg-panel-content' ).not( '.fusion-gutenberg-info' ).hide();

					jQuery( '.fusion-toggle-gutenberg-info' ).click( function( e ) {
						e.preventDefault();

						jQuery( '.try-gutenberg-panel-content' ).not( '.fusion-gutenberg-info' ).toggleClass( 'fusion-wp-gutenberg-open' );
						jQuery( '.try-gutenberg-panel-content' ).not( '.fusion-gutenberg-info' ).slideToggle();

						if ( jQuery( '.try-gutenberg-panel-content' ).not( '.fusion-gutenberg-info' ).hasClass( 'fusion-wp-gutenberg-open' ) ) {
							jQuery( this ).html( 'Close info from WP' );
							jQuery( '.fusion-gutenberg-info' ).css( 'margin-bottom', '50px' );
							jQuery( '.fusion-gutenberg-info' ).stop( true, true ).animate( {
								'margin-bottom': '50px'
							}, { queue: false, duration: '200' } );
						} else {
							jQuery( this ).html( 'View info from WP' );
							jQuery( '.fusion-gutenberg-info' ).stop( true, true ).animate( {
								'margin-bottom': '13px'
							}, { queue: false, duration: '200' } );
						}
					});
				});
			</script>
		</div>
		<?php
	}

	/**
	 * Renders the news dashboard widget.
	 *
	 * @since 3.9.0
	 * @access public
	 * @return void
	 */
	public function display_news_dashboard_widget() {

		// Create two feeds, the first being just a leading article with data and summary, the second being a normal news feed.
		$feeds = array(
			'first' => array(
				'link'         => 'http://theme-fusion.com/blog/',
				'url'          => 'http://theme-fusion.com/feed/',
				'title'        => esc_attr__( 'ThemeFusion News', 'Avada' ),
				'items'        => 1,
				'show_summary' => 1,
				'show_author'  => 0,
				'show_date'    => 1,
			),
			'news'  => array(
				'link'         => 'http://theme-fusion.com/blog/',
				'url'          => 'http://theme-fusion.com/feed/',
				'title'        => esc_attr__( 'ThemeFusion News', 'Avada' ),
				'items'        => 4,
				'show_summary' => 0,
				'show_author'  => 0,
				'show_date'    => 0,
			),
		);

		wp_dashboard_primary_output( 'themefusion_news', $feeds );
	}

	/**
	 * Changes the user agent for the Avada news widget.
	 *
	 * @since 5.2.1
	 * @access public
	 * @param  object $feed  SimplePie feed object, passed by reference.
	 * @param  mixed  $url   URL of feed to retrieve. If an array of URLs, the feeds are merged.
	 * @return void
	 */
	public function feed_user_agent( $feed, $url ) {

		if ( 'http://theme-fusion.com/feed/' === $url ) {
			$feed->set_useragent( 'Avada RSS Feed' );
		}
	}

	/**
	 * Create the admin toolbar menu items.
	 *
	 * @since 3.8.0
	 * @access public
	 * @return void
	 */
	public function add_wp_toolbar_menu() {

		global $wp_admin_bar, $avada_patcher;

		if ( current_user_can( 'switch_themes' ) ) {

			$registration_complete = false;
			$token                 = Avada()->registration->get_token();
			if ( '' !== $token ) {
				$registration_complete = true;
			}
			$patches              = $avada_patcher->get_patcher_checker()->get_cache();
			$avada_updates_styles = 'display:inline-block;background-color:#d54e21;color:#fff;font-size:9px;line-height:17px;font-weight:600;border-radius:10px;padding:0 6px;';

			// Done for white label plugin.
			$avada_parent_menu_name  = __( 'Avada', 'Avada' );
			$avada_parent_menu_title = '<span class="ab-icon"></span><span class="ab-label">' . esc_html( $avada_parent_menu_name ) . '</span>';
			if ( isset( $patches['avada'] ) && 1 <= $patches['avada'] ) {
				$patches_label           = '<span style="' . $avada_updates_styles . '">' . $patches['avada'] . '</span>';
				$avada_parent_menu_title = '<span class="ab-icon"></span><span class="ab-label">' . esc_html( $avada_parent_menu_name ) . ' ' . $patches_label . '</span>';
			}

			if ( ! is_admin() ) {
				$this->add_wp_toolbar_menu_item(
					$avada_parent_menu_title,
					false,
					admin_url( 'admin.php?page=avada' ),
					array(
						'class' => 'avada-menu',
					),
					'avada'
				);
			}

			if ( ! $registration_complete ) {
				$this->add_wp_toolbar_menu_item( esc_attr__( 'Product Registration', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-registration' ) );
			}

			$this->add_wp_toolbar_menu_item( esc_attr__( 'Support', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-support' ) );
			$this->add_wp_toolbar_menu_item( esc_attr__( 'Demos', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-demos' ) );
			$this->add_wp_toolbar_menu_item( esc_attr__( 'Plugins', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-plugins' ) );
			$this->add_wp_toolbar_menu_item( esc_attr__( 'System Status', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-system-status' ) );
			$this->add_wp_toolbar_menu_item( esc_attr__( 'Theme Options', 'Avada' ), 'avada', admin_url( 'themes.php?page=avada_options' ) );
			if ( isset( $patches['avada'] ) && 1 <= $patches['avada'] ) {
				$patches_label = '<span style="' . $avada_updates_styles . '">' . $patches['avada'] . '</span>';
				/* translators: The patches numeric counter. */
				$this->add_wp_toolbar_menu_item( sprintf( esc_attr__( 'Fusion Patcher %s', 'Avada' ), $patches_label ), 'avada', admin_url( 'admin.php?page=avada-fusion-patcher' ) );
			}
		} // End if().

	}

	/**
	 * Add the top-level menu item to the adminbar.
	 *
	 * @since 3.8.0
	 * @access public
	 * @param  string       $title       The title.
	 * @param  string|false $parent      The parent node.
	 * @param  string       $href        Link URL.
	 * @param  array        $custom_meta An array of custom meta to apply.
	 * @param  string       $custom_id   A custom ID.
	 */
	public function add_wp_toolbar_menu_item( $title, $parent = false, $href = '', $custom_meta = array(), $custom_id = '' ) {

		global $wp_admin_bar;

		if ( current_user_can( 'switch_themes' ) ) {
			if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
				return;
			}

			// Set custom ID.
			if ( $custom_id ) {
				$id = $custom_id;
			} else { // Generate ID based on $title.
				$id = strtolower( str_replace( ' ', '-', $title ) );
			}

			// Links from the current host will open in the current window.
			$meta = strpos( $href, site_url() ) !== false ? array() : array(
				'target' => '_blank',
			); // External links open in new tab/window.
			$meta = array_merge( $meta, $custom_meta );

			$wp_admin_bar->add_node(
				array(
					'parent' => $parent,
					'id'     => $id,
					'title'  => $title,
					'href'   => $href,
					'meta'   => $meta,
				)
			);
		}

	}

	/**
	 * Modify the menu.
	 *
	 * @since 3.8.0
	 * @access public
	 * @return void
	 */
	public function edit_admin_menus() {
		global $submenu;

		// Change Avada to Welcome.
		if ( current_user_can( 'switch_themes' ) ) {
			$submenu['avada'][0][0] = esc_attr__( 'Welcome', 'Avada' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		if ( isset( $submenu['themes.php'] ) && ! empty( $submenu['themes.php'] ) ) {
			foreach ( $submenu['themes.php'] as $key => $value ) {
				// Remove "Header" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=header_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
				// Remove "Background" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=background_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
			}

			// Reorder items in the array.
			$submenu['themes.php'] = array_values( $submenu['themes.php'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
		// Move patcher to be the last item in the Avada menu.
		if ( isset( $submenu['avada'] ) && ! empty( $submenu['avada'] ) ) {
			foreach ( $submenu['avada'] as $key => $value ) {
				if ( isset( $value[2] ) && 'avada-fusion-patcher' === $value[2] ) {
					$submenu['avada'][] = $value; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					unset( $submenu['avada'][ $key ] );
					$submenu['avada'] = array_values( $submenu['avada'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				}
			}
		}

		// Remove TGMPA menu from Appearance.
		remove_submenu_page( 'themes.php', 'install-required-plugins' );

	}

	/**
	 * Redirect to admin page on theme activation.
	 *
	 * @since 3.8.0
	 * @access public
	 * @return void
	 */
	public function activation_redirect() {
		if ( current_user_can( 'switch_themes' ) ) {
			// Do not redirect if a migration is needed for Avada 5.0.0.
			if ( true === Fusion_Builder_Migrate::needs_migration() ) {
				return;
			}
			header( 'Location:' . admin_url() . 'admin.php?page=avada' );
		}
	}

	/**
	 * Actions to run on initial theme activation.
	 *
	 * @since 3.8.0
	 * @access public
	 * @return void
	 */
	public function admin_init() {

		if ( current_user_can( 'switch_themes' ) ) {

			if ( isset( $_GET['avada-deactivate'] ) && 'deactivate-plugin' === $_GET['avada-deactivate'] ) {
				check_admin_referer( 'avada-deactivate', 'avada-deactivate-nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] == $_GET['plugin'] ) {
						deactivate_plugins( $plugin['file_path'] );
					}
				}
			}
			if ( isset( $_GET['avada-activate'] ) && 'activate-plugin' === $_GET['avada-activate'] ) {
				check_admin_referer( 'avada-activate', 'avada-activate-nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] == $_GET['plugin'] ) {
						activate_plugin( $plugin['file_path'] );

						wp_safe_redirect( admin_url( 'admin.php?page=avada-plugins' ) );
						exit;
					}
				}
			}
		}
	}

	/**
	 * AJAX callback method. Used to activate plugin.
	 *
	 * @since 5.2
	 * @access public
	 * @return void
	 */
	public function ajax_activate_plugin() {

		if ( current_user_can( 'switch_themes' ) ) {

			if ( isset( $_GET['avada_activate'] ) && 'activate-plugin' === $_GET['avada_activate'] ) {

				check_admin_referer( 'avada-activate', 'avada_activate_nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						$result   = activate_plugin( $plugin['file_path'] );
						$response = array();

						// Make sure woo setup won't run after this.
						if ( 'woocommerce' == $_GET['plugin'] ) {
							delete_transient( '_wc_activation_redirect' );
						}

						// Make sure bbpress welcome screen won't run after this.
						if ( 'bbpress' == $_GET['plugin'] ) {
							delete_transient( '_bbp_activation_redirect' );
						}

						// Make sure events calendar welcome screen won't run after this.
						if ( 'the-events-calendar' == $_GET['plugin'] ) {
							delete_transient( '_tribe_events_activation_redirect' );
						}

						if ( ! is_wp_error( $result ) ) {
							$response['message'] = 'plugin activated';
							$response['error']   = false;
						} else {
							$response['message'] = $result->get_error_message();
							$response['error']   = true;
						}

						echo json_encode( $response );
						die();
					}
				}
			}
		}
	}

	/**
	 * AJAX callback method.
	 * Used to install and activate plugin.
	 */
	public function ajax_install_plugin() {

		if ( current_user_can( 'switch_themes' ) ) {

			if ( isset( $_GET['avada_activate'] ) && 'activate-plugin' === $_GET['avada_activate'] ) {

				check_admin_referer( 'avada-activate', 'avada_activate_nonce' );

				global $tgmpa;

				// Unfortunately 'output buffering' doesn't work here as eventually 'wp_ob_end_flush_all' function is called.
				$tgmpa->install_plugins_page();

				die();
			}
		}

	}

	/**
	 * Needed in order to enable TGMP in AJAX call.
	 *
	 * @param bool $load Whether TGMP should be inited or not.
	 *
	 * @return bool
	 */
	public function enable_tgmpa( $load ) {
		return true;
	}

	/**
	 * Adds the admin menu.
	 *
	 * @access  public
	 * @return void
	 */
	public function admin_menu() {

		if ( current_user_can( 'switch_themes' ) ) {

			$plugins_callback = array( $this, 'plugins_tab' );
			if ( isset( $_GET['tgmpa-install'] ) || isset( $_GET['tgmpa-update'] ) ) {
				require_once $this->includes_path . '/class-avada-tgm-plugin-activation.php';
				remove_action( 'admin_notices', array( $GLOBALS['tgmpa'], 'notices' ) );
				$plugins_callback = array( $GLOBALS['tgmpa'], 'install_plugins_page' );
			}

			// Work around for theme check.
			$avada_menu_page_creation_method    = 'add_menu_page';
			$avada_submenu_page_creation_method = 'add_submenu_page';

			$welcome_screen = $avada_menu_page_creation_method( 'Avada', 'Avada', 'switch_themes', 'avada', array( $this, 'welcome_screen' ), 'dashicons-fusiona-logo', '2.111111' );

			if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) {
				$registration = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'Registration', 'Avada' ), esc_attr__( 'Registration', 'Avada' ), 'manage_options', 'avada-registration', array( $this, 'registration_tab' ) );
				$support      = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'Support', 'Avada' ), esc_attr__( 'Support', 'Avada' ), 'manage_options', 'avada-support', array( $this, 'support_tab' ) );
			}

			$faqs          = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'FAQ', 'Avada' ), esc_attr__( 'FAQ', 'Avada' ), 'switch_themes', 'avada-faq', array( $this, 'faq_tab' ) );
			$demos         = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'Demos', 'Avada' ), esc_attr__( 'Demos', 'Avada' ), 'manage_options', 'avada-demos', array( $this, 'demos_tab' ) );
			$plugins       = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'Plugins', 'Avada' ), esc_attr__( 'Plugins', 'Avada' ), 'install_plugins', 'avada-plugins', $plugins_callback );
			$status        = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'System Status', 'Avada' ), esc_attr__( 'System Status', 'Avada' ), 'switch_themes', 'avada-system-status', array( $this, 'system_status_tab' ) );
			$theme_options = $avada_submenu_page_creation_method( 'avada', esc_attr__( 'Theme Options', 'Avada' ), esc_attr__( 'Theme Options', 'Avada' ), 'switch_themes', 'themes.php?page=avada_options' );

			if ( ! class_exists( 'FusionReduxFrameworkPlugin' ) ) {
				$theme_options_global = $avada_submenu_page_creation_method( 'themes.php', esc_attr__( 'Theme Options', 'Avada' ), esc_attr__( 'Theme Options', 'Avada' ), 'switch_themes', 'themes.php?page=avada_options' );
			}

			add_action( 'admin_print_scripts-' . $welcome_screen, array( $this, 'welcome_screen_scripts' ) );
			if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) {
				add_action( 'admin_print_scripts-' . $registration, array( $this, 'registration_screen_scripts' ) );
				add_action( 'admin_print_scripts-' . $support, array( $this, 'support_screen_scripts' ) );
			}

			add_action( 'admin_print_scripts-' . $faqs, array( $this, 'faq_screen_scripts' ) );
			add_action( 'admin_print_scripts-' . $demos, array( $this, 'demos_screen_scripts' ) );
			add_action( 'admin_print_scripts-' . $plugins, array( $this, 'plugins_screen_scripts' ) );
			add_action( 'admin_print_scripts-' . $status, array( $this, 'status_screen_scripts' ) );
			add_action( 'admin_print_scripts', array( $this, 'theme_options_screen_scripts' ) );
		} // End if().
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function welcome_screen() {
		require_once $this->includes_path . '/admin-screens/welcome.php';
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function registration_tab() {
		require_once $this->includes_path . '/admin-screens/registration.php';
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function support_tab() {
		require_once $this->includes_path . '/admin-screens/support.php';
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function faq_tab() {
		require_once $this->includes_path . '/admin-screens/faq.php';
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function demos_tab() {
		require_once $this->includes_path . '/admin-screens/demos.php';
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function plugins_tab() {
		require_once $this->includes_path . '/admin-screens/plugins.php';
	}

	/**
	 * Include file.
	 *
	 * @access  public
	 * @return void
	 */
	public function system_status_tab() {
		require_once $this->includes_path . '/admin-screens/system-status.php';
	}

	/**
	 * Renders the admin screens header with title, logo and tabs.
	 *
	 * @since 5.0.0
	 *
	 * @access  public
	 * @param string $screen The current screen.
	 * @return void
	 */
	public function get_admin_screens_header( $screen = 'welcome' ) {
		?>
		<h1><?php echo esc_html( apply_filters( 'avada_admin_welcome_title', __( 'Welcome to Avada!', 'Avada' ) ) ); ?></h1>

		<?php if ( 'demos' === $screen ) : ?>
			<div class="updated error importer-notice importer-notice-1" style="display: none;">
				<p><strong><?php esc_attr_e( "We're sorry but the demo data could not be imported. It is most likely due to low PHP configurations on your server. There are two possible solutions.", 'Avada' ); ?></strong></p>

				<p><strong><?php esc_attr_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_attr_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_attr_e( 'Alternate Method', 'Avada' ); ?></a></p>
				<?php /* translators: %1$s: RED. %2$s: "Reset WordPress Plugin" link. */ ?>
				<p><strong><?php esc_attr_e( 'Solution 2:', 'Avada' ); ?></strong> <?php printf( __( 'Fix the PHP configurations in the System Status that are reported in %1$s, then use the %2$s, then reimport.', 'Avada' ), '<strong style="color: red;">' . esc_attr__( 'RED', 'Avada' ) . '</strong>', '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '">' . esc_attr__( 'Reset WordPress Plugin', 'Avada' ) . '</a>' ); // WPCS: XSS ok. ?><a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=avada-system-status' ) ); ?>" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_attr_e( 'System Status', 'Avada' ); ?></a></p>
			</div>

			<div class="updated importer-notice importer-notice-2" style="display: none;">
				<?php /* translators: "Regenerate Thumbnails" plugin link. */ ?>
				<p><?php printf( esc_html__( 'Demo data successfully imported. Install and run %s plugin once if you would like images generated to the specific theme sizes. This is not needed if you upload your own images because WP does it automatically.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=regenerate-thumbnails&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . ' class="thickbox" title="' . esc_attr__( 'Regenerate Thumbnails', 'Avada' ) . '">' . esc_attr__( 'Regenerate Thumbnails', 'Avada' ) . '</a>' ); ?></p>
				<?php /* translators: "Permalinks" link. */ ?>
				<p><?php printf( esc_attr__( 'Please visit the %s page and change your permalinks structure to "Post Name" so that content links work properly.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'options-permalink.php' ) ) . '">' . esc_attr__( 'Permalinks', 'Avada' ) . '</a>' ); ?></p>
			</div>

			<div class="updated error importer-notice importer-notice-3" style="display: none;">
				<p><strong><?php esc_attr_e( "We're sorry but the demo data could not be imported. It is most likely due to low PHP configurations on your server. There are two possible solutions.", 'Avada' ); ?></strong></p>

				<p><strong><?php esc_attr_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_attr_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_attr_e( 'Alternate Method', 'Avada' ); ?></a></p>
				<?php /* translators: %1$s: RED. %2$s: "Reset WordPress Plugin" link. */ ?>
				<p><strong><?php esc_attr_e( 'Solution 2:', 'Avada' ); ?></strong> <?php printf( __( 'Fix the PHP configurations in the System Status that are reported in %1$s, then use the %2$s, then reimport.', 'Avada' ), '<strong style="color: red;">' . esc_attr__( 'RED', 'Avada' ) . '</strong>', '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '">' . esc_attr__( 'Reset WordPress Plugin', 'Avada' ) . '</a>' ); // WPCS: XSS ok. ?><a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=avada-system-status' ) ); ?>" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_attr_e( 'System Status', 'Avada' ); ?></a></p>
			</div>

			<div class="updated error importer-notice importer-notice-4" style="display: none;">
				<p><strong><?php esc_attr_e( "We're sorry but the demo data could not be imported. We were unable to find import file.", 'Avada' ); ?></strong></p>

				<p><strong><?php esc_attr_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_attr_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_attr_e( 'Alternate Method', 'Avada' ); ?></a></p>
				<p><strong><?php esc_attr_e( 'Solution 2:', 'Avada' ); ?></strong> <?php esc_attr_e( 'Make sure WordPress directory permissions are correct and uploads directory is writable.', 'Avada' ); ?><a href="https://codex.wordpress.org/Changing_File_Permissions" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_attr_e( 'Learn More', 'Avada' ); ?></a></p>
			</div>
		<?php endif; ?>
		<div class="about-text">
			<?php if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) : ?>
				<?php /* translators: %1$s: URL. %2$s: _blank. %3$s: URL. */ ?>
				<?php $welcome_text = sprintf( __( 'Avada is now installed and ready to use! Get ready to build something beautiful. Please <a href="%1$s" target="%2$s">register your purchase</a> to get automatic theme updates, import Avada demos and install premium plugins. Check out the <a href="%3$s">Support tab</a> to learn how to receive product support. We hope you enjoy it!', 'Avada' ), esc_url( admin_url( 'admin.php?page=avada-registration' ) ), '_blank', esc_url( admin_url( 'admin.php?page=avada-support' ) ) ); // WPCS: XSS ok. ?>
			<?php else : ?>
				<?php /* translators: URL. */ ?>
				<?php $welcome_text = sprintf( __( 'Avada is now installed and ready to use! Get ready to build something beautiful. Through your registration on the Envato hosted platform, you can now get automatic theme updates, import Avada demos and install premium plugins. Check out the <a href="%s" target="_blank">Envato Hosted Support Policy</a> to learn how to receive support through the Envato hosted support team. We hope you enjoy it!', 'Avada' ), esc_url( 'https://envatohosted.zendesk.com/hc/en-us/articles/115001666945-Envato-Hosted-Support-Policy' ) ); // WPCS: XSS ok. ?>
			<?php endif; ?>

			<?php echo apply_filters( 'avada_admin_welcome_text', $welcome_text ); // WPCS: XSS ok. ?>
		</div>
		<div class="avada-logo"><span class="avada-version"><?php esc_attr_e( 'Version', 'Avada' ); ?> <?php echo esc_attr( $this->theme_version ); ?></span></div>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url_raw( ( 'welcome' === $screen ) ? '#' : admin_url( 'admin.php?page=avada' ) ); ?>" class="<?php echo ( 'welcome' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'Welcome', 'Avada' ); ?></a>
			<?php if ( ! defined( 'ENVATO_HOSTED_SITE' ) ) : ?>
				<a href="<?php echo esc_url_raw( ( 'registration' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-registration' ) ); ?>" class="<?php echo ( 'registration' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'Registration', 'Avada' ); ?></a>
				<a href="<?php echo esc_url_raw( ( 'support' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-support' ) ); ?>" class="<?php echo ( 'support' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'Support', 'Avada' ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url_raw( ( 'faqs' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-faq' ) ); ?>" class="<?php echo ( 'faqs' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'FAQ', 'Avada' ); ?></a>
			<a href="<?php echo esc_url_raw( ( 'demos' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-demos' ) ); ?>" class="<?php echo ( 'demos' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'Demos', 'Avada' ); ?></a>
			<a href="<?php echo esc_url_raw( ( 'plugins' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-plugins' ) ); ?>" class="<?php echo ( 'plugins' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'Plugins', 'Avada' ); ?></a>
			<a href="<?php echo esc_url_raw( ( 'system-status' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-system-status' ) ); ?>" class="<?php echo ( 'system-status' === $screen ) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e( 'System Status', 'Avada' ); ?></a>
		</h2>
		<?php
	}

	/**
	 * Add styles to admin.
	 *
	 * @access  public
	 * @return void
	 */
	public function admin_styles() {
		?>
		<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
			<style type="text/css">
				@media screen and (max-width: 782px) {
					#wp-toolbar > ul > .avada-menu {
						display: block;
					}

					#wpadminbar .avada-menu > .ab-item .ab-icon {
						padding-top: 6px !important;
						height: 40px !important;
						font-size: 30px !important;
					}
				}
				#wpadminbar .avada-menu > .ab-item .ab-icon:before,
				.dashicons-fusiona-logo:before{
					content: "\e62d";
					font-family: 'icomoon';
					speak: none;
					font-style: normal;
					font-weight: normal;
					font-variant: normal;
					text-transform: none;
					line-height: 1;

					/* Better Font Rendering. */
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
				}

				.avada-install-plugins .theme .update-message { display: block !important; cursor: default; }
				.cp-rebranding-warning { display: none; }
			</style>

			<?php
		endif;
	}

	/**
	 * Enqueues scripts.
	 *
	 * @since 5.0.3
	 * @access  public
	 * @return void
	 */
	public function admin_scripts() {
		if ( current_user_can( 'switch_themes' ) ) {
			global $pagenow;

			$version = Avada::get_theme_version();

			// Add script to check for fusion option slider changes.
			if ( 'post-new.php' == $pagenow || 'edit.php' == $pagenow || 'post.php' == $pagenow ) {
				wp_enqueue_script( 'slider_preview', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-builder-slider-preview.js', array(), $version, true );
				wp_enqueue_style( 'fusion-font-icomoon', FUSION_LIBRARY_URL . '/assets/fonts/icomoon-admin/icomoon.css', false, $version, 'all' );
			}

			if ( 'nav-menus.php' == $pagenow || 'widgets.php' == $pagenow ) {
				wp_enqueue_style(
					'select2-css',
					Avada::$template_dir_url . '/assets/admin/css/select2.css',
					array(),
					'4.0.3',
					'all'
				);
				wp_enqueue_script(
					'selectwoo-js',
					Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
					array( 'jquery' ),
					'1.0.2'
				);

				// Range field assets.
				wp_enqueue_style(
					'avadaredux-nouislider-css',
					FUSION_LIBRARY_URL . '/inc/redux/framework/FusionReduxCore/inc/fields/slider/vendor/nouislider/fusionredux.jquery.nouislider.css',
					array(),
					'5.0.0',
					'all'
				);
				wp_enqueue_script(
					'avadaredux-nouislider-js',
					Avada::$template_dir_url . '/assets/admin/js/jquery.nouislider.min.js',
					array( 'jquery' ),
					'5.0.0',
					true
				);
				wp_enqueue_script(
					'wnumb-js',
					Avada::$template_dir_url . '/assets/admin/js/wNumb.js',
					array( 'jquery' ),
					'1.0.2',
					true
				);
				wp_enqueue_script( 'jquery-color' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'fusion-font-icomoon', FUSION_LIBRARY_URL . '/assets/fonts/icomoon-admin/icomoon.css', false, $version, 'all' );
				// ColorPicker Alpha Channel.
				wp_enqueue_script( 'wp-color-picker-alpha', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/wp-color-picker-alpha.js', array( 'wp-color-picker', 'jquery-color' ), $version );

				wp_enqueue_style( 'fontawesome', Fusion_Font_Awesome::get_backend_css_url(), array(), $version );

				if ( '1' === Avada()->settings->get( 'fontawesome_v4_compatibility' ) ) {
					wp_enqueue_script( 'fontawesome-shim-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/fa-v4-shims.js', array(), $version );
					wp_enqueue_style( 'fontawesome-shims', Fusion_Font_Awesome::get_backend_shims_css_url(), array(), $version );
				}

				if ( '1' === Avada()->settings->get( 'status_fontawesome_pro' ) ) {
					wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-pro.js', array(), $version );
				} else {
					wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-free.js', array(), $version );
				}
				wp_enqueue_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', array(), $version );

				wp_enqueue_script( 'fusion-menu-options', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-menu-options.js', array( 'selectwoo-js' ), $version, true );

				wp_localize_script(
					'fusion-menu-options',
					'fusionMenuConfig',
					array(
						'fontawesomeicons'   => fusion_get_icons_array(),
						'fontawesomesubsets' => Avada()->settings->get( 'status_fontawesome' ),
					)
				);
			}
		} // End if().

		// @codingStandardsIgnoreLine
		//wp_enqueue_script( 'beta-test', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-beta-testing.js', array(), $version, true );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function welcome_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function registration_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function support_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function faq_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function demos_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
		wp_enqueue_script( 'avada_zeroclipboard', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/zeroclipboard.js', array(), $ver );
		wp_enqueue_script( 'tiptip_jquery', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/tiptip.jquery.min.js', array(), $ver );
		wp_enqueue_script( 'avada_admin_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-admin.js', array( 'tiptip_jquery', 'avada_zeroclipboard', 'underscore' ), $ver, true );
		wp_localize_script( 'avada_admin_js', 'avadaAdminL10nStrings', $this->get_admin_script_l10n_strings() );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function plugins_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
		wp_enqueue_script( 'avada_zeroclipboard', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/zeroclipboard.js', array(), $ver );
		wp_enqueue_script( 'tiptip_jquery', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/tiptip.jquery.min.js', array(), $ver );
		wp_enqueue_script( 'avada_admin_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-admin.js', array( 'tiptip_jquery', 'avada_zeroclipboard' ), $ver, true );
		wp_localize_script( 'avada_admin_js', 'avadaAdminL10nStrings', $this->get_admin_script_l10n_strings() );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function theme_options_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_script( 'avada_theme_options_menu_mod', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-theme-options-menu-mod.js', array( 'jquery' ), $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function status_screen_scripts() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', array(), $ver );
		wp_enqueue_script( 'avada_zeroclipboard', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/zeroclipboard.js', array(), $ver );
		wp_enqueue_script( 'tiptip_jquery', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/tiptip.jquery.min.js', array(), $ver );
		wp_enqueue_script( 'avada_admin_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-admin.js', array( 'tiptip_jquery', 'avada_zeroclipboard' ), $ver, true );
		wp_localize_script( 'avada_admin_js', 'avadaAdminL10nStrings', $this->get_admin_script_l10n_strings() );
	}

	/**
	 * Get the plugin link.
	 *
	 * @access  public
	 * @param array $item The plugin in question.
	 * @return  array
	 */
	public function plugin_link( $item ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$installed_plugins = get_plugins();

		$item['sanitized_plugin'] = $item['name'];

		$actions = array();

		// We have a repo plugin.
		if ( ! $item['version'] ) {
			$item['version'] = Avada_TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
		}

		$disable_class         = '';
		$data_version          = '';
		$fusion_builder_action = '';

		if ( 'fusion-builder' === $item['slug'] && false !== get_option( 'avada_previous_version' ) ) {
			$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );

			if ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
				$disable_class         = ' disabled fusion-builder';
				$data_version          = ' data-version="' . $fusion_core_version . '"';
				$fusion_builder_action = array(
					'install' => '<div class="fusion-builder-plugin-install-nag">' . esc_html__( 'Please update Fusion Core to latest version.', 'Avada' ) . '</div>',
				);
			}
		} elseif ( 'fusion-core' !== $item['slug'] && 'fusion-builder' !== $item['slug'] && $item['premium'] && ! Avada()->registration->is_registered() ) {
			$disable_class = ' disabled avada-no-token';
		}

		// We need to display the 'Install' hover link.
		if ( ! isset( $installed_plugins[ $item['file_path'] ] ) ) {
			if ( ! $disable_class ) {
				$url = esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'page'          => rawurlencode( Avada_TGM_Plugin_Activation::$instance->menu ),
								'plugin'        => rawurlencode( $item['slug'] ),
								'plugin_name'   => rawurlencode( $item['sanitized_plugin'] ),
								'tgmpa-install' => 'install-plugin',
								'return_url'    => 'fusion_plugins',
							),
							Avada_TGM_Plugin_Activation::$instance->get_tgmpa_url()
						),
						'tgmpa-install',
						'tgmpa-nonce'
					)
				);
			} else {
				$url = '#';
			}
			if ( $fusion_builder_action ) {
				$actions = $fusion_builder_action;
			} else {
				$actions = array(
					/* translators: Plugin name. */
					'install' => '<a href="' . $url . '" class="button button-primary' . $disable_class . '"' . $data_version . ' title="' . sprintf( esc_attr__( 'Install %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Install', 'Avada' ) . '</a>',
				);
			}
		} elseif ( is_plugin_inactive( $item['file_path'] ) ) {
			// We need to display the 'Activate' hover link.
			$url = esc_url(
				add_query_arg(
					array(
						'plugin'               => rawurlencode( $item['slug'] ),
						'plugin_name'          => rawurlencode( $item['sanitized_plugin'] ),
						'avada-activate'       => 'activate-plugin',
						'avada-activate-nonce' => wp_create_nonce( 'avada-activate' ),
					),
					admin_url( 'admin.php?page=avada-plugins' )
				)
			);

			$actions = array(
				/* translators: Plugin Name. */
				'activate' => '<a href="' . $url . '" class="button button-primary"' . $data_version . ' title="' . sprintf( esc_attr__( 'Activate %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Activate', 'Avada' ) . '</a>',
			);
		} elseif ( version_compare( $installed_plugins[ $item['file_path'] ]['Version'], $item['version'], '<' ) ) {
			$disable_class = '';
			// We need to display the 'Update' hover link.
			$url = wp_nonce_url(
				add_query_arg(
					array(
						'page'         => rawurlencode( Avada_TGM_Plugin_Activation::$instance->menu ),
						'plugin'       => rawurlencode( $item['slug'] ),
						'tgmpa-update' => 'update-plugin',
						'version'      => rawurlencode( $item['version'] ),
						'return_url'   => 'fusion_plugins',
					),
					Avada_TGM_Plugin_Activation::$instance->get_tgmpa_url()
				),
				'tgmpa-update',
				'tgmpa-nonce'
			);
			if ( 'fusion-core' !== $item['slug'] && 'fusion-builder' !== $item['slug'] && $item['premium'] && ! Avada()->registration->is_registered() ) {
				$disable_class = ' disabled avada-no-token';
			}
			$actions = array(
				/* translators: Plugin Name. */
				'update' => '<a href="' . $url . '" class="button button-primary' . $disable_class . '" title="' . sprintf( esc_attr__( 'Update %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Update', 'Avada' ) . '</a>',
			);
		} elseif ( is_plugin_active( $item['file_path'] ) ) {
			$url     = esc_url(
				add_query_arg(
					array(
						'plugin'                 => rawurlencode( $item['slug'] ),
						'plugin_name'            => rawurlencode( $item['sanitized_plugin'] ),
						'avada-deactivate'       => 'deactivate-plugin',
						'avada-deactivate-nonce' => wp_create_nonce( 'avada-deactivate' ),
					),
					admin_url( 'admin.php?page=avada-plugins' )
				)
			);
			$actions = array(
				/* translators: Plugin name. */
				'deactivate' => '<a href="' . $url . '" class="button button-primary" title="' . sprintf( esc_attr__( 'Deactivate %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Deactivate', 'Avada' ) . '</a>',
			);
		} // End if().

		return $actions;
	}

	/**
	 * Removes install link for Fusion Builder, if Fusion Core was not updated to 3.0
	 *
	 * @since 5.0.0
	 * @param array  $action_links The action link(s) for a required plugin.
	 * @param string $item_slug The slug of a required plugin.
	 * @param array  $item Data belonging to a required plugin.
	 * @param string $view_context Specifying the kind of action (install, activate, update).
	 * @return array The action link(s) for a required plugin.
	 */
	public function edit_tgmpa_action_links( $action_links, $item_slug, $item, $view_context ) {
		if ( 'fusion-builder' === $item_slug && 'install' === $view_context ) {
			$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );

			if ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
				$action_links['install'] = '<span class="avada-not-installable" style="color:#555555;">' . esc_attr__( 'Fusion Builder will be installable, once Fusion Core plugin is updated.', 'Avada' ) . '<span class="screen-reader-text">' . esc_attr__( 'Fusion Builder', 'Avada' ) . '</span></span>';
			}
		}

		return $action_links;
	}

	/**
	 * Removes install link for Fusion Builder, if Fusion Core was not updated to 3.0
	 *
	 * @since 5.0.0
	 * @param array $action_links The action link(s) for a required plugin.
	 * @return array The action link(s) for a required plugin.
	 */
	public function edit_tgmpa_notice_action_links( $action_links ) {
		$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );
		$current_screen      = get_current_screen();

		if ( 'avada_page_avada-plugins' == $current_screen->id ) {
			$link_template = '<a id="manage-plugins" class="button-primary" style="margin-top:1em;" href="#avada-install-plugins">' . esc_attr__( 'Manage Plugins Below', 'Avada' ) . '</a>';
			$action_links  = array(
				'install' => $link_template,
			);
		} elseif ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
			$link_template = '<a id="manage-plugins" class="button-primary" style="margin-top:1em;" href="' . esc_url( self_admin_url( 'admin.php?page=avada-plugins' ) ) . '#avada-install-plugins">' . esc_attr__( 'Go Manage Plugins', 'Avada' ) . '</a>';
			$action_links  = array(
				'install' => $link_template,
			);
		}

		return $action_links;
	}

	/**
	 * Initialize the permalink settings.
	 *
	 * @since 3.9.2
	 */
	public function init_permalink_settings() {
		add_settings_field(
			'avada_portfolio_category_slug',                        // ID.
			esc_attr__( 'Avada portfolio category base', 'Avada' ), // Setting title.
			array( $this, 'permalink_slug_input' ),                 // Display callback.
			'permalink',                                            // Settings page.
			'optional',                                             // Settings section.
			array(
				'taxonomy' => 'portfolio_category',
			)             // Args.
		);

		add_settings_field(
			'avada_portfolio_skills_slug',
			esc_attr__( 'Avada portfolio skill base', 'Avada' ),
			array( $this, 'permalink_slug_input' ),
			'permalink',
			'optional',
			array(
				'taxonomy' => 'portfolio_skills',
			)
		);

		add_settings_field(
			'avada_portfolio_tag_slug',
			esc_attr__( 'Avada portfolio tag base', 'Avada' ),
			array( $this, 'permalink_slug_input' ),
			'permalink',
			'optional',
			array(
				'taxonomy' => 'portfolio_tags',
			)
		);
	}

	/**
	 * Show a slug input box.
	 *
	 * @since 3.9.2
	 * @access  public
	 * @param  array $args The argument.
	 */
	public function permalink_slug_input( $args ) {
		$permalinks     = get_option( 'avada_permalinks' );
		$permalink_base = $args['taxonomy'] . '_base';
		$input_name     = 'avada_' . $args['taxonomy'] . '_slug';
		$placeholder    = $args['taxonomy'];
		?>
		<input name="<?php echo esc_attr( $input_name ); ?>" type="text" class="regular-text code" value="<?php echo ( isset( $permalinks[ $permalink_base ] ) ) ? esc_attr( $permalinks[ $permalink_base ] ) : ''; ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
		<?php
	}

	/**
	 * Save the permalink settings.
	 *
	 * @since 3.9.2
	 */
	public function save_permalink_settings() {

		if ( ! is_admin() ) {
			return;
		}

		if ( fusion_doing_ajax() ) {
			return;
		}
		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) { // WPCS: CSRF ok.
			// Cat and tag bases.
			$portfolio_category_slug = ( isset( $_POST['avada_portfolio_category_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_category_slug'] ) ) : ''; // WPCS: CSRF ok.
			$portfolio_skills_slug   = ( isset( $_POST['avada_portfolio_skills_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_skills_slug'] ) ) : ''; // WPCS: CSRF ok.
			$portfolio_tags_slug     = ( isset( $_POST['avada_portfolio_tags_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_tags_slug'] ) ) : ''; // WPCS: CSRF ok.

			$permalinks = get_option( 'avada_permalinks' );

			if ( ! $permalinks ) {
				$permalinks = array();
			}

			$permalinks['portfolio_category_base'] = untrailingslashit( $portfolio_category_slug );
			$permalinks['portfolio_skills_base']   = untrailingslashit( $portfolio_skills_slug );
			$permalinks['portfolio_tags_base']     = untrailingslashit( $portfolio_tags_slug );

			update_option( 'avada_permalinks', $permalinks );
		}
	}

	/**
	 * Check for Envato hosted and register product.
	 *
	 * @since 5.3
	 *
	 * @access public
	 * @return void
	 */
	public function register_product_envato_hosted() {
		if ( defined( 'ENVATO_HOSTED_SITE' ) && ENVATO_HOSTED_SITE && defined( 'SUBSCRIPTION_CODE' ) && ! Avada()->registration->is_registered() ) {

			$license_status = Avada()->remote_install->validate_envato_hosted_subscription_code();

			$registration_args = Avada()->registration->get_args();
			$product_id        = sanitize_key( $registration_args['name'] );

			$registration_array = array(
				$product_id => $license_status,
				'scopes'    => array(),
			);
			update_option( 'fusion_registered', $registration_array );

			$registration_array = array(
				$product_id => array(
					'token' => SUBSCRIPTION_CODE,
				),
			);

			update_option( 'fusion_registration', $registration_array );
		}
	}

	/**
	 * Sets the theme version.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_theme_version() {
		$theme_version = Avada()->get_normalized_theme_version();

		$this->theme_version = $theme_version;
	}

	/**
	 * Sets the WP_Object for the theme.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_theme_object() {
		$theme_object = wp_get_theme();
		if ( $theme_object->parent_theme ) {
			$template_dir = basename( Avada::$template_dir_path );
			$theme_object = wp_get_theme( $template_dir );
		}

		$this->theme_object = $theme_object;
	}

	/**
	 * Override some LayerSlider data.
	 *
	 * @since 5.0.5
	 * @access public
	 * @return void
	 */
	public function layerslider_overrides() {

		// Disable auto-updates.
		$GLOBALS['lsAutoUpdateBox'] = false;
	}

	/**
	 * Add custom rules to Facebook instant articles plugin.
	 *
	 * @since 5.1
	 * @access public
	 * @param object $transformers The transformers object from the Facebook Instant Articles plugin.
	 * @return object
	 */
	public function add_instant_article_rules( $transformers ) {
		$selectors_pass   = array( 'fusion-fullwidth', 'fusion-builder-row', 'fusion-layout-column', 'fusion-column-wrapper', 'fusion-title', 'fusion-imageframe', 'imageframe-align-center', 'fusion-checklist', 'fusion-li-item', 'fusion-li-item-content' );
		$selectors_ignore = array( 'fusion-column-inner-bg-image', 'fusion-clearfix', 'title-sep-container', 'fusion-sep-clear', 'fusion-separator' );

		$avada_rules = '{ "rules" : [';
		foreach ( $selectors_pass as $selector ) {
			$avada_rules .= '{ "class": "PassThroughRule", "selector" : "div.' . $selector . '" },';
		}

		foreach ( $selectors_ignore as $selector ) {
			$avada_rules .= '{ "class": "IgnoreRule", "selector" : "div.' . $selector . '" },';
		}

		$avada_rules = trim( $avada_rules, ',' ) . ']}';

		$transformers->loadRules( $avada_rules );

		return $transformers;
	}

	/**
	 * Returns an array of strings that will be used by avada-admin.js for translations.
	 *
	 * @access private
	 * @since 5.2
	 * @return array
	 */
	private function get_admin_script_l10n_strings() {
		return array(
			'content'               => esc_attr__( 'Content', 'Avada' ),
			'modify'                => esc_attr__( 'Modify', 'Avada' ),
			'full_import'           => esc_attr__( 'Full Import', 'Avada' ),
			'partial_import'        => esc_attr__( 'Partial Import', 'Avada' ),
			'import'                => esc_attr__( 'Import', 'Avada' ),
			'download'              => esc_attr__( 'Download', 'Avada' ),
			'classic'               => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br />REQUIREMENTS:<br /><br />• Memory Limit of 256 MB and max execution time (php time limit) of 300 seconds.<br /><br />• Slider Revolution and LayerSlider must be activated for sliders to import.<br /><br />• Fusion Core must be activated for Fusion Slider, portfolio and FAQs to be imported.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'caffe'                 => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Fusion Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'church'                => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Fusion Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• The Events Calendar Plugin must be activated for all event data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'modern_shop'           => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Fusion Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• WooCommerce must be activated for all shop data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'classic_shop'          => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Slider Revolution must be activated for sliders to import.<br /><br />• Fusion Core must be activated for Fusion Slider, portfolio and FAQs to be imported.<br /><br />• WooCommerce must be activated for all shop data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'landing_product'       => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Slider Revolution must be activated for sliders to import.<br /><br />• Fusion Core must be activated for Fusion Slider, portfolio and FAQs to be imported.<br /><br />• WooCommerce must be activated for all shop data to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'forum'                 => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Fusion Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• bbPress must be activated for all forum data to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'technology'            => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 256 MB and max execution time (php time limit) of 300 seconds.<br /><br />• Fusion Core and LayerSlider must be activated for sliders to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'creative'              => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Slider Revolution must be activated for sliders to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Core must be activated for Fusion Slider, portfolio and FAQs to be imported. <br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			'default'               => __( 'Importing demo content will give you sliders, pages, posts, theme options, widgets, sidebars and other settings. This will replicate the live demo. <strong>Clicking this option will replace your current theme options and widgets.</strong> It can also take a minute to complete.<br /><br /> REQUIREMENTS:<br /><br />• Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.<br /><br />• Fusion Core must be activated for sliders, portfolios and FAQs to import.<br /><br />• Contact Form 7 plugin must be activated for the form to import.<br /><br />• Fusion Builder must be activated for page content to display as intended.', 'Avada' ),
			/* translators: The current step label. */
			'currently_processing'  => esc_attr__( 'Currently Processing: %s', 'Avada' ),
			/* translators: The current step label. */
			'currently_removing'    => esc_attr__( 'Currently Removing: %s', 'Avada' ),
			'file_does_not_exist'   => esc_attr__( 'The file does not exist', 'Avada' ),
			/* translators: URL. */
			'error_timeout'         => wp_kses_post( sprintf( __( 'Demo server couldn\'t be reached. Please check for wp_remote_get on the <a href="%s" target="_blank">System Status</a> page.', 'Avada' ), admin_url( 'admin.php?page=avada-system-status' ) ) ),
			/* translators: URL. */
			'error_php_limits'      => wp_kses_post( sprintf( __( 'Demo import failed. Please check for PHP limits in red on the <a href="%s" target="_blank">System Status</a> page. Change those to the recommended value and try again.', 'Avada' ), admin_url( 'admin.php?page=avada-system-status' ) ) ),
			'remove_demo'           => esc_attr__( 'Removing demo content will remove ALL previously imported demo content from this demo and restore your site to the previous state it was in before this demo content was imported.', 'Avada' ),
			'update_fc'             => __( 'Fusion Builder Plugin can only be installed and activated if Fusion Core plugin is at version 3.0 or higher. Please update Fusion Core first.', 'Avada' ),
			/* translators: URL. */
			'register_first'        => sprintf( __( 'This plugin can only be installed or updated, after you have successfully completed the Avada product registration on the <a href="%s" target="_blank">Product Registration</a> tab.', 'Avada' ), admin_url( 'admin.php?page=avada-registration' ) ),
			'plugin_install_failed' => __( 'Plugin install failed. Please try Again.', 'Avada' ),
			'plugin_active'         => __( 'Active', 'Avada' ),
		);
	}

	/**
	 * Add meta boxes to taxonomies
	 *
	 * @access public
	 * @since 3.1.1
	 * @return void
	 */
	public function avada_taxonomy_meta() {
		global $fusion_settings, $pagenow;

		if ( ! ( 'term.php' === $pagenow || 'edit-tags.php' === $pagenow || ( fusion_doing_ajax() && ! empty( $_REQUEST['action'] ) && 'add-tag' === $_REQUEST['action'] ) ) ) {
			return;
		}

		// Get array of available sliders.
		$sliders_array = avada_get_available_sliders_array();

		// Include Tax meta class.
		include_once Avada::$template_dir_path . '/includes/class-avada-taxonomy-meta.php';

		// Where to add meta fields.
		$args = array(
			'screens' => apply_filters( 'fusion_tax_meta_allowed_screens', array( 'category', 'portfolio_category', 'faq_category', 'product_cat', 'tribe_events_cat', 'post_tag', 'portfolio_tags', 'product_tag', 'topic-tag', 'portfolio_skills' ) ),
		);

		// Init taxonomy meta boxes.
		$avada_meta = new Avada_Taxonomy_Meta( $args );

		// Add fields.
		$avada_meta->header(
			'fusion_tax_heading',
			array(
				'value' => __( 'Fusion Taxonomy Options', 'Avada' ),
				'class' => 'avada-tax-heading avada-tax-heading-edit',
			)
		);

		$avada_meta->select(
			'slider_type',
			array(
				'no'      => __( 'No Slider', 'Avada' ),
				'layer'   => __( 'LayerSlider', 'Avada' ),
				'flex'    => __( 'Fusion Slider', 'Avada' ),
				'rev'     => __( 'Slider Revolution', 'Avada' ),
				'elastic' => __( 'Elastic Slider', 'Avada' ),
			),
			array(
				'name'    => __( 'Slider Type ', 'Avada' ),
				'default' => 'no',
				'class'   => 'avada-sliders-selection',
				'desc'    => __( 'Select the type of slider that displays.', 'Avada' ),
			)
		);

		$avada_meta->select(
			'fusion_tax_slider',
			$sliders_array['layer_sliders'],
			array(
				'name'       => __( 'Select LayerSlider ', 'Avada' ),
				'default'    => 0,
				'class'      => 'avada-sliders-group avada-layer-slider',
				'desc'       => __( 'Select the unique name of the slider.', 'Avada' ),
				'dependency' => array(
					array(
						'field'      => 'slider_type',
						'value'      => 'layer',
						'comparison' => '==',
					),
				),
			)
		);

		$avada_meta->select(
			'fusion_tax_wooslider',
			$sliders_array['fusion_sliders'],
			array(
				'name'       => __( 'Select Fusion Slider ', 'Avada' ),
				'default'    => 0,
				'class'      => 'avada-sliders-group avada-flex-slider',
				'desc'       => __( 'Select the unique name of the slider.', 'Avada' ),
				'dependency' => array(
					array(
						'field'      => 'slider_type',
						'value'      => 'flex',
						'comparison' => '==',
					),
				),
			)
		);

		$avada_meta->select(
			'fusion_tax_revslider',
			$sliders_array['rev_sliders'],
			array(
				'name'       => __( 'Select Slider Revolution Slider', 'Avada' ),
				'default'    => 0,
				'class'      => 'avada-sliders-group avada-rev-slider',
				'desc'       => __( 'Select the unique name of the slider.', 'Avada' ),
				'dependency' => array(
					array(
						'field'      => 'slider_type',
						'value'      => 'rev',
						'comparison' => '==',
					),
				),
			)
		);

		$avada_meta->select(
			'fusion_tax_elasticslider',
			$sliders_array['elastic_sliders'],
			array(
				'name'       => __( 'Select Elastic Slider', 'Avada' ),
				'default'    => 0,
				'class'      => 'avada-sliders-group avada-elastic-slider',
				'desc'       => __( 'Select the unique name of the slider.', 'Avada' ),
				'dependency' => array(
					array(
						'field'      => 'slider_type',
						'value'      => 'elastic',
						'comparison' => '==',
					),
				),
			)
		);

		$avada_meta->buttonset(
			'slider_position',
			array(
				'default' => __( 'Default', 'Avada' ),
				'below'   => __( 'Below', 'Avada' ),
				'above'   => __( 'Above', 'Avada' ),
			),
			array(
				'name'       => __( 'Slider Position', 'Avada' ),
				'default'    => 'default',
				'class'      => 'avada-sliders-group avada-slider-buttonset',
				/* translators: The "Fusion Theme Options" link. */
				'desc'       => sprintf( esc_attr__( 'Select if the slider shows below or above the header. Only works for top header position. %s', 'Avada' ), Avada()->settings->get_default_description( 'slider_position', '', 'select' ) ),
				'dependency' => array(
					array(
						'field'      => 'slider_type',
						'value'      => 'no',
						'comparison' => '!=',
					),
				),
			)
		);

		$avada_meta->text(
			'main_padding_top',
			array(
				'name' => __( 'Page Content Top Padding', 'Avada' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc' => sprintf( esc_attr__( 'In pixels ex: 20px. %s', 'Avada' ), Avada()->settings->get_default_description( 'main_padding', 'top' ) ),
			)
		);

		$avada_meta->text(
			'main_padding_bottom',
			array(
				'name' => __( 'Page Content Bottom Padding', 'Avada' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc' => sprintf( esc_attr__( 'In pixels ex: 20px. %s', 'Avada' ), Avada()->settings->get_default_description( 'main_padding', 'bottom' ) ),
			)
		);

		$avada_meta->colorpicker(
			'header_bg_color',
			array(
				'name'    => __( 'Header Background Color', 'Avada' ),
				'default' => Avada()->settings->get( 'archive_header_bg_color' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc'    => sprintf( esc_attr__( 'Controls the background color for the header. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'archive_header_bg_color' ) ),
			)
		);

		$avada_meta->colorpicker(
			'mobile_header_bg_color',
			array(
				'name'    => __( 'Mobile Header Background Color', 'Avada' ),
				'default' => Avada()->settings->get( 'mobile_archive_header_bg_color' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc'    => sprintf( esc_attr__( 'Controls the background color for the header on mobile devices. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'mobile_archive_header_bg_color' ) ),
			)
		);

		$avada_meta->image(
			'page_title_bg',
			array(
				'name' => __( 'Page Title Bar Background', 'Avada' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc' => sprintf( esc_attr__( 'Select an image to use for the page title bar background. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg', 'url' ) ),
			)
		);

		$avada_meta->image(
			'page_title_bg_retina',
			array(
				'name'       => __( 'Page Title Bar Background Retina', 'Avada' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc'       => sprintf( esc_attr__( 'Select an image to use for retina devices. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_retina', 'url' ) ),
				'dependency' => array(
					array(
						'field'      => 'page_title_bg',
						'value'      => '',
						'comparison' => '!=',
					),
				),
			)
		);

		$avada_meta->text(
			'page_title_height',
			array(
				'name' => __( 'Page Title Bar Height', 'Avada' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc' => sprintf( esc_attr__( 'Controls the height of the page title bar on desktop. Enter value including any valid CSS unit besides %% which does not work for page title bar, ex: 87px. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_height' ) ),
			)
		);

		$avada_meta->text(
			'page_title_mobile_height',
			array(
				'name' => __( 'Page Title Bar Mobile Height', 'Avada' ),
				/* translators: The "Fusion Theme Options" link. */
				'desc' => sprintf( esc_attr__( 'Controls the height of the page title bar on mobile. Enter value including any valid CSS unit besides %% which does not work for page title bar, ex: 70px. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_mobile_height' ) ),
			)
		);

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
