<?php
/**
 * Refresh class.
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
 * Refresh class.
 *
 * @since 1.0.0
 */
class OMAPI_Refresh {

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
	 * @var object
	 */
	public $base;

	/**
	 * Arguments for the API requests.
	 *
	 * @since 1.6.5
	 *
	 * @var array
	 */
	protected $api_args = array( 'limit' => 100 );

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();
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
	 * Maybe refresh optins if the action has been requested.
	 *
	 * @since 1.0.0
	 */
	public function maybe_refresh() {

		// If we are missing our save action, return early.
		if ( empty( $_POST['omapi_refresh'] ) ) {
			return;
		}

		// Verify the nonce field.
		check_admin_referer( 'omapi_nonce_' . $this->view, 'omapi_nonce_' . $this->view );

		// Refresh the optins.
		$this->refresh();

		// Provide action to refresh optins.
		do_action( 'optin_monster_api_refresh_optins', $this->view );
	}

	/**
	 * Refresh the optins.
	 *
	 * @since 1.0.0
	 */
	public function refresh() {
		$api = OMAPI_Api::build( 'v1', 'optins', 'GET' );

		// Set additional flags.
		$additional_data = array(
			'wp' => $GLOBALS['wp_version'],
		);

		if ( OMAPI::is_woocommerce_active() && version_compare( OMAPI::woocommerce_version(), '3.0.0', '>=' ) ) {
			$additional_data['woocommerce'] = OMAPI::woocommerce_version();
		}

		$api->set_additional_data( $additional_data );

		$results = array();
		$body    = $api->request( $this->api_args, false );

		// Loop through paginated requests until we have fetched all the campaigns.
		while ( ! is_wp_error( $body ) || empty( $body ) ) {
			$limit       = absint( wp_remote_retrieve_header( $api->response, 'limit' ) );
			$page        = absint( wp_remote_retrieve_header( $api->response, 'page' ) );
			$total       = absint( wp_remote_retrieve_header( $api->response, 'total' ) );
			$total_pages = ceil( $total / $limit );
			$results     = array_merge( $results, (array) $body );

			// If we've reached the end, prevent any further requests.
			if ( $page >= $total_pages || $limit === 0 ) {
				break;
			}

			$args         = $this->api_args;
			$args['page'] = $page + 1;

			// Request the next page.
			$body = $api->request( $args, false );
		}

		if ( is_wp_error( $body ) ) {
			// If no optins available, make sure they get deleted.
			if ( in_array( $body->get_error_code(), array( 'optins', 'no-campaigns-error' ), true ) ) {
				$this->base->save->store_optins( array() );
			}

			// Set an error message.
			$this->error = $body->get_error_message();
			add_action( 'optin_monster_api_messages_' . $this->view, array( $this, 'error' ) );
			return;
		}

		// Store the optin data.
		$this->base->save->store_optins( $results );

		// Update our sites as well
		$sites = $this->base->sites->fetch();

		// Update the option to remove stale error messages.
		$option = $this->base->get_option();
		$option['is_invalid']  = false;
		$option['is_expired']  = false;
		$option['is_disabled'] = false;
		$option['siteIds']     = $sites;

		update_option( 'optin_monster_api', $option );

		// Set a message.
		add_action( 'optin_monster_api_messages_' . $this->view, array( $this, 'message' ) );
	}

	/**
	 * Output an error message.
	 *
	 * @since 1.0.0
	 */
	public function error() {

		?>
		<div class="updated error"><p><?php echo $this->error; ?></p></div>
		<?php
	}

	/**
	 * Output a refresh message.
	 *
	 * @since 1.0.0
	 */
	public function message() {

		?>
		<div class="updated"><p><?php _e( 'Your campaigns have been refreshed successfully.', 'optin-monster-api' ); ?></p></div>
		<?php
	}
}
