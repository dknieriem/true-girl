<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 8/30/2016
 * Time: 3:53 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Thrive_Quiz_Builder_Admin class.
 */
class Thrive_Quiz_Builder_Admin {

	/**
	 * Constructor for Thrive_Quiz_Builder_Admin
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'includes' ) );

		add_action( 'admin_init', 'tqb_admin_init' );

		/**
		 * Add Thrive Quiz Builder To Dashboard
		 */
		add_filter( 'tve_dash_installed_products', array( $this, 'add_to_dashboard_list' ) );
		add_filter( 'tve_dash_admin_product_menu', array( $this, 'add_to_dashboard_menu' ) );

		/**
		 * Add admin scripts and styles
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'tcb_ajax_load', array( $this, 'tqb_tcb_ajax_load' ) ); //applied from TCB plugin
		}

		add_filter( 'tie_load_admin_scripts', array( $this, 'allow_tie_scripts' ) );

		/**
		 * Add Dashboard Features. Ex: API Connections, Font Manager
		 */
		add_filter( 'tve_dash_features', array( $this, 'dash_add_features' ) );
	}

	/**
	 * Includes required files
	 */
	public function includes() {
		require_once 'tqb-admin-functions.php';
	}

	/**
	 * Push Thrive Quiz Builder to Thrive Dashboard installed products list
	 *
	 * @param array $items all the thrive products.
	 *
	 * @return array
	 */
	public function add_to_dashboard_list( $items ) {
		require_once 'classes/class-tqb-product.php';
		$items[] = new TQB_Product();

		return $items;
	}

	/**
	 * Push the Thrive Quiz Builder to Thrive Dashboard menu
	 *
	 * @param array $menus items already in Thrive Dashboard.
	 *
	 * @return array
	 */
	public function add_to_dashboard_menu( $menus = array() ) {

		$menus['tqb'] = array(
			'parent_slug' => 'tve_dash_section',
			'page_title'  => __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ),
			'menu_title'  => __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ),
			'capability'  => 'manage_options',
			'menu_slug'   => 'tqb_admin_dashboard',
			'function'    => array( $this, 'dashboard' ),
		);

		return $menus;
	}

	/**
	 * Enqueue all required scripts and styles
	 *
	 * @param string $hook page hook.
	 */
	public function enqueue_scripts( $hook ) {

		$accepted_hooks = apply_filters( 'tqb_accepted_admin_pages', array(
			'thrive-dashboard_page_tqb_admin_dashboard'
		) );

		if ( ! in_array( $hook, $accepted_hooks, true ) ) {
			return;
		}

		/* first, the license check */
		if ( ! tqb()->license_activated() ) {
			return;
		}

		/* second, the minimum required TCB version */
		if ( ! tqb()->check_tcb_version() ) {
			return;
		}

		/**
		 * Enqueue dash scripts
		 */
		tve_dash_enqueue();

		/**
		 * Specific admin styles
		 */
		tqb_enqueue_style( 'tqb-admin-style', tqb()->plugin_url( 'assets/css/admin/tqb-styles.css' ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'backbone' );
		wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ) );
		wp_enqueue_script( 'tqb-highcharts', tqb()->plugin_url( 'assets/js/admin/libs/highcharts.js' ), array(
			'jquery',
		) );
		wp_enqueue_script( 'tqb-highcharts-more', tqb()->plugin_url( 'assets/js/admin/libs/highcharts-more.js' ), array(
			'jquery',
			'tqb-highcharts',
		) );
		wp_enqueue_script( 'tqb-highcharts3d', tqb()->plugin_url( 'assets/js/admin/libs/highcharts-3d.js' ), array(
			'jquery',
			'tqb-highcharts',
		) );

		tqb_enqueue_script( 'tqb-admin-js', tqb()->plugin_url( 'assets/js/dist/tqb-admin.min.js' ), array(
			'jquery',
			'backbone',
			'tqb-highcharts',
			'tqb-highcharts-more',
		), false, true );

		/**
		 * Enqueue Wystia script for popover videos
		 */

		wp_localize_script( 'tqb-admin-js', 'ThriveQuizB', tqb_get_localization() );

		/**
		 * Output the main templates for backbone views used in dashboard.
		 */
		add_action( 'admin_print_footer_scripts', array( $this, 'render_backbone_templates' ) );
	}

	/**
	 * Output Thrive Quiz Builder dashboard - the main plugin admin page
	 */
	public function dashboard() {

		if ( ! tqb()->license_activated() ) {
			return include tqb()->plugin_path( '/includes/admin/views/license-inactive.phtml' );
		}

		if ( ! tqb()->check_tcb_version() ) {
			return include tqb()->plugin_path( 'includes/admin/views/tcb_version_incompatible.phtml' );
		}

		include tqb()->plugin_path( '/includes/admin/views/dashboard.phtml' );
	}

	/**
	 * Render backbone templates
	 */
	public function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( tqb()->plugin_path( 'includes/admin/views/templates' ), 'templates' );
		tve_dash_output_backbone_templates( $templates );
	}

	public function allow_tie_scripts( $screens ) {

		$screens[] = 'thrive-dashboard_page_tqb_admin_dashboard';

		return $screens;
	}

	/**
	 * Allow features to be loaded from dashboard.
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public function dash_add_features( $features ) {

		$features['font_manager']     = true;
		$features['icon_manager']     = true;
		$features['api_connections']  = true;
		$features['general_settings'] = true;

		return $features;
	}

	/**
	 * Hook applied from TCB
	 * Used for loading a file through ajax call
	 * Used for displaying lightbox for choosing a template
	 *
	 * @param $file string
	 */
	public function tqb_tcb_ajax_load( $file ) {
		switch ( $file ) {
			case 'tqb_templates':
				include tqb()->plugin_path( 'tcb-bridge/editor-lightbox/variation-templates.php' );
				exit();
				break;
			case 'tqb_compute_result_page_states':
				include tqb()->plugin_path( 'tcb-bridge/editor-lightbox/result-intervals.php' );
				exit();
				break;
			case 'tqb_import_state_content':
				include tqb()->plugin_path( 'tcb-bridge/editor-lightbox/import-content.php' );
				exit();
				break;
			case 'tqb_social_share_badge_template':
				include tqb()->plugin_path( 'tcb-bridge/editor-lightbox/social-share-badge-template.php' );
				exit();
				break;
			case 'tqb_quiz_shortcode':
				include tqb()->plugin_path( 'tcb-bridge/editor-lightbox/quiz-shortcode-chooser.php' );
				exit();
				break;
		}
	}
}

return new Thrive_Quiz_Builder_Admin();
