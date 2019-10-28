<?php
/**
 * Admin Pointer class.
 *
 * @since 1.6.5
 *
 * @package OMAPI
 * @author  Erik Jonasson
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Pointer class.
 *
 * @since 1.6.5
 */
class OMAPI_Pointer {
	/**
	 * Holds the class object.
	 *
	 * @since 1.6.5
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Class Constructor
	 */
	public function __construct() {
		$this->set();

		add_action( 'admin_enqueue_scripts', array( $this, 'load_pointer' ) );
	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.6.5
	 */
	public function set() {
		self::$instance = $this;
		$this->base     = OMAPI::get_instance();
		$this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();
	}

	/**
	 * Loads our Admin Pointer, if:
	 *  1. We're on a valid WP Version
	 *  2. We're on the Dashboard Page
	 *  3. We don't have an API Key
	 *  4. The Pointer hasn't already been dismissed
	 *
	 * @since 1.6.5
	 */
	public function load_pointer() {
		// Don't run on WP < 3.3.
		if ( get_bloginfo( 'version' ) < '3.3' ) {
			return;
		}
		$screen = get_current_screen();
		// If we're not on the dashboard, or we already have an API key, don't trigger the pointer.
		if ( 'dashboard' !== $screen->id || $this->base->get_api_credentials() ) {
			return;
		}

		$content  = '<h3>' . __( 'Get More Leads, Subscribers and Sales Today!', 'optin-monster-api' ) . '</h3>';
		$content .= '<div class="om-pointer-close-link"><a class="close" href="#"></a></div>';
		$content .= '<h4>' . __( 'Grow Your Business with OptinMonster', 'optin-monster-api' ) . '</h4>';
		$content .= '<p>' . __( 'Turn your website visitors into subscribers and customers with OptinMonster, the #1 conversion optimization toolkit in the world.', 'optin-monster-api' ) . '</p>';
		$content .= '<p>' . __( 'For a limited time, get 50% off any plan AND get instant access to OptinMonster University - our exclusive training portal with over $2,000 worth of courses, courses, content and videos', 'optin-monster-api' ) . '<strong> ' . __( '100% Free!', 'optin-monster-api' ) . '</strong></p>';
		$content .= '<p><a class="button button-primary" id="omPointerButton" href="admin.php?page=optin-monster-api-welcome">' . __( 'Click Here to Learn More', 'optin-monster-api' ) . '</a>';

		$pointer = array(
			'id'      => 'om-welcome-pointer',
			'target'  => '#toplevel_page_optin-monster-api-settings',
			'options' => array(
				'content'  => $content,
				'position' => array(
					'edge'  => 'left',
					'align' => 'right',
				),
			),
		);

		// Make sure the pointer hasn't been dismissed.
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( in_array( $pointer['id'], $dismissed ) ) {
			return;
		}

		// Add pointers style to queue.
		wp_enqueue_style( 'wp-pointer' );
		// Add pointers script to queue. Add custom script.
		wp_enqueue_script( $this->base->plugin_slug . '-pointer', plugins_url( 'assets/js/pointer.js', OMAPI_FILE ), array( 'wp-pointer' ), $this->base->version, true );
		wp_enqueue_style( $this->base->plugin_slug . '-settings', plugins_url( '/assets/css/pointer.css', OMAPI_FILE ), array(), $this->base->version );

		// Add pointer options to script.
		wp_localize_script( $this->base->plugin_slug . '-pointer', 'omapiPointer', $pointer );

	}
}
