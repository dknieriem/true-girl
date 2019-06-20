<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization and hooks.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
final class Shortcodes_Ultimate_Maker {

	/**
	 * The path to the main plugin file.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_file   The path to the main plugin file.
	 */
	private $plugin_file;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_version   The current version of the plugin.
	 */
	private $plugin_version;

	/**
	 * The path to the plugin folder.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_path   The path to the plugin folder.
	 */
	private $plugin_path;

	/**
	 * The URL of the plugin folder.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $plugin_url    The URL of the plugin folder.
	 */
	private $plugin_url;

	/**
	 * The text domain for i18n.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $textdomain    The text domain for i18n.
	 */
	private $textdomain;

	/**
	 * The ID of the add-on.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $addon_id   The ID of the add-on.
	 */
	private $addon_id;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since   1.5.5
	 * @param string  $plugin_file    The path to the main plugin file.
	 * @param string  $plugin_version The current version of the plugin.
	 */
	public function __construct( $plugin_file, $plugin_version ) {

		$this->plugin_file    = $plugin_file;
		$this->plugin_version = $plugin_version;
		$this->plugin_path    = plugin_dir_path( $plugin_file );
		$this->plugin_url     = plugin_dir_url( $plugin_file );
		$this->textdomain     = 'shortcodes-ultimate-maker';
		$this->addon_id       = 'shortcode-creator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_common_hooks();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for the plugin.
	 *
	 * @since    1.5.5
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		if ( ! class_exists( 'Shortcodes_Ultimate_Addon_i18n' ) ) {
			require_once $this->plugin_path . 'includes/class-shortcodes-ultimate-addon-i18n.php';
		}

		/**
		 * Classes responsible for displaying admin notices.
		 */
		if ( ! class_exists( 'Shortcodes_Ultimate_Addon_Notice' ) ) {
			require_once $this->plugin_path . 'admin/class-shortcodes-ultimate-addon-notice.php';
		}

		if ( ! class_exists( 'Shortcodes_Ultimate_Addon_License_Notice' ) ) {
			require_once $this->plugin_path . 'admin/class-shortcodes-ultimate-addon-license-notice.php';
		}

		if ( ! class_exists( 'Shortcodes_Ultimate_Addon_Core_Notice' ) ) {
			require_once $this->plugin_path . 'admin/class-shortcodes-ultimate-addon-core-notice.php';
		}

		/**
		 * Include custom post type class.
		 */
		require_once $this->plugin_path . 'includes/class-shortcodes-ultimate-maker-post-type.php';

		/**
		 * Include shortcode editor class.
		 */
		require_once $this->plugin_path . 'admin/class-shortcodes-ultimate-maker-editor.php';

		/**
		 * Include shortcode registration/handling class.
		 */
		require_once $this->plugin_path . 'includes/class-shortcodes-ultimate-maker-shortcodes.php';

		/**
		 * Include the class responsible for demo shortcodes.
		 */
		require_once $this->plugin_path . 'includes/class-shortcodes-ultimate-maker-demos.php';

		/**
		 * Include the class responsible for plugin upgrade procedures.
		 */
		require_once $this->plugin_path . 'includes/class-shortcodes-ultimate-maker-upgrade.php';

		/**
		 * Include the class responsible for plugin settings.
		 */
		require_once $this->plugin_path . 'admin/class-shortcodes-ultimate-maker-settings.php';

		/**
		 * Include custom do_shortcode functions.
		 */
		require_once $this->plugin_path . 'includes/do-shortcode.php';

	}

	/**
	 * Define the locale for the plugin for internationalization.
	 *
	 * @since    1.5.5
	 * @access   private
	 */
	private function set_locale() {

		$i18n = new Shortcodes_Ultimate_Addon_i18n( $this->plugin_file, $this->textdomain );

		$i18n->load_plugin_textdomain();

	}

	/**
	 * Register all of the hooks related to both admin/public areas of the site.
	 *
	 * @since    1.5.5
	 * @access   private
	 */
	private function define_common_hooks() {

		/**
		 * Register custom shortcodes.
		 */
		$shortcodes = new Shortcodes_Ultimate_Maker_Shortcodes( $this->plugin_file );

		add_action( 'su/data/shortcodes', array( $shortcodes, 'register_custom_shortcodes' ) );

	}

	/**
	 * Register all of the hooks related to the admin area functionality of the
	 * plugin.
	 *
	 * @since    1.5.5
	 * @access   private
	 */
	private function define_admin_hooks() {

		/**
		 * Run upgrade procedures.
		 */
		$upgrade = new Shortcodes_Ultimate_Maker_Upgrade( $this->plugin_file, $this->plugin_version );

		add_action( 'admin_init', array( $upgrade, 'upgrade' ) );

		/**
		 * Register new shortcodes group.
		 */
		add_filter( 'su/data/groups', array( $this, 'register_group' ) );

		/**
		 * Add help tab at add-on's screens.
		 */
		add_action( 'current_screen', array( $this, 'add_help_tab' ) );

		/**
		 * The 'Activate license key' notice.
		 */
		$license_notice = new Shortcodes_Ultimate_Addon_License_Notice( $this->addon_id, $this->plugin_path . 'admin/partials/notices/license.php' );

		add_action( 'admin_notices',                array( $license_notice, 'display_notice' ) );
		add_action( 'admin_post_su_dismiss_notice', array( $license_notice, 'dismiss_notice' ) );

		/**
		 * The 'Install Core' notice.
		 */
		$core_notice = new Shortcodes_Ultimate_Addon_Core_Notice( $this->addon_id, $this->plugin_path . 'admin/partials/notices/core.php' );

		add_action( 'admin_notices',                array( $core_notice, 'display_notice' ) );
		add_action( 'admin_post_su_dismiss_notice', array( $core_notice, 'dismiss_notice' ) );

		/**
		 * Register editor meta box.
		 */
		$editor = new Shortcodes_Ultimate_Maker_Editor( $this->plugin_file, $this->plugin_version );

		add_action( 'add_meta_boxes',               array( $editor, 'add_meta_box' )   );
		add_action( 'admin_enqueue_scripts',        array( $editor, 'enqueue_assets' ) );
		add_action( 'save_post_shortcodesultimate', array( $editor, 'save_post' )      );

		/**
		 * Register custom post type.
		 */
		$cpt = new Shortcodes_Ultimate_Maker_Post_Type();

		add_action( 'init',                                          array( $cpt, 'register_post_type' )         );
		add_filter( 'post_updated_messages',                         array( $cpt, 'custom_updated_messages' )    );
		add_filter( 'manage_shortcodesultimate_posts_columns',       array( $cpt, 'add_posts_columns' ), -10     );
		add_action( 'manage_shortcodesultimate_posts_custom_column', array( $cpt, 'posts_custom_column' ), 10, 2 );
		add_filter( 'post_row_actions',                              array( $cpt, 'post_row_actions' ), 10, 2    );

		/**
		 * Import demo shortcodes.
		 */
		$demos = new Shortcodes_Ultimate_Maker_Demos( $this->plugin_version );

		add_action( 'load-edit.php', array( $demos, 'create' ) );

		/**
		 * Add plugin settings.
		 */
		$settings = new Shortcodes_Ultimate_Maker_Settings( $this->plugin_file );

		add_action( 'admin_init',     array( $settings, 'register_settings' ) );
		add_action( 'current_screen', array( $settings, 'add_help_tab' )      );

		/**
		 * Validate license before updating.
		 */
		add_filter(
			'puc_pre_inject_update-shortcodes-ultimate-maker',
			array( $this, 'validate_license_before_updating' )
		);

	}

	/**
	 * Register new shortcodes group.
	 *
	 * @since  1.5.5
	 * @param mixed   $groups Groups collection.
	 * @return mixed          Modified groups collection.
	 */
	public function register_group( $groups ) {

		$groups[ $this->addon_id ] = _x( 'Custom', 'Custom shortcodes group name', 'shortcodes-ultimate-maker' );

		return $groups;

	}

	/**
	 * Add help tab and set help sidebar at addon's pages.
	 *
	 * @since  1.5.5
	 * @param WP_Screen $screen WP_Screen instance.
	 */
	public function add_help_tab( $screen ) {

		if ( ! $this->is_addon_screen() ) {
			return;
		}

		$screen->add_help_tab( array(
				'id'      => 'shortcodes-ultimate-maker-common',
				'title'   => __( 'Shortcode Creator', 'shortcodes-ultimate-maker' ),
				'content' => $this->get_template( 'admin/partials/help/general' ),
			) );

		// $screen->set_help_sidebar( $this->get_template( 'admin/partials/help/sidebar' ) );

	}

	/**
	 * Conditional tag to check that current screen is created by the add-on.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return boolean True if current screen is created by the add-on, False if not.
	 */
	private function is_addon_screen() {

		$screen = get_current_screen();

		if ( empty( $screen->base ) || empty( $screen->post_type ) ) {
			return false;
		}

		if ( ! in_array( $screen->base, array( 'edit', 'post' ) ) ) {
			return false;
		}

		if ( $screen->post_type !== 'shortcodesultimate' ) {
			return false;
		}

		return true;

	}

	/**
	 * Utility function to get specified template by it's name.
	 *
	 * @since 1.5.5
	 * @access private
	 * @param string  $name Template name (without extension).
	 * @param mixed   $data Template data to be passed to the template.
	 * @return string       Template content.
	 */
	private function get_template( $name, $data = null ) {

		// Sanitize name
		$name = preg_replace( '/[^A-Za-z0-9\/_-]/', '', $name );

		// Trim slashes
		$name = trim( $name, '/' );

		// The full template path
		$template = $this->plugin_path . '/' . $name . '.php';

		// Look for a specified file
		if ( file_exists( $template ) ) {

			ob_start();
			include $template;
			$output = ob_get_contents();
			ob_end_clean();

		}

		return ( isset( $output ) ) ? $output : '';

	}

	/**
	 * Validate license key before plugin update.
	 *
	 * @since 1.5.11
	 */
	public function validate_license_before_updating( $update ) {

		if (
			! get_option( "su_option_{$this->addon_id}_license" )
			&& isset( $update->download_url )
		) {
			$update->download_url = '';
		}

		return $update;

	}

}
