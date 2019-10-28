<?php
/**
 * Ajax class.
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
 * Ajax class.
 *
 * @since 1.0.0
 */
class OMAPI_Ajax {

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
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		// Load non-WordPress style ajax requests.
		if ( isset( $_REQUEST['optin-monster-ajax-route'] ) && $_REQUEST['optin-monster-ajax-route'] ) {
			if ( isset( $_REQUEST['action'] ) ) {
				add_action( 'init', array( $this, 'ajax' ), 999 );
			}
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
		$this->view     = 'ajax';
	}

	/**
	 * Callback to process external ajax requests.
	 *
	 * @since 1.0.0
	 */
	public function ajax() {

		switch ( $_REQUEST['action'] ) {
			case 'mailpoet':
				$this->mailpoet();
				break;
			default:
				break;
		}
	}

	/**
	 * Opts the user into MailPoet.
	 *
	 * @since 1.0.0
	 */
	public function mailpoet() {

		// Run a security check first.
		check_ajax_referer( 'omapi', 'nonce' );

		// Prepare variables.
		$optin = $this->base->get_optin_by_slug( stripslashes( $_REQUEST['optin'] ) );
		$list  = get_post_meta( $optin->ID, '_omapi_mailpoet_list', true );
		$email = ! empty( $_REQUEST['email'] ) ? stripslashes( $_REQUEST['email'] ) : false;
		$name  = ! empty( $_REQUEST['name'] ) ? stripslashes( $_REQUEST['name'] ) : false;
		$user  = array();

		// Possibly split name into first and last.
		if ( $name ) {
			$names = explode( ' ', $name );
			if ( isset( $names[0] ) ) {
				$user['firstname'] = $names[0];
			}

			if ( isset( $names[1] ) ) {
				$user['lastname'] = $names[1];
			}
		}

		// Save the email address.
		$user['email'] = $email;

		// Store the data.
		$data = array(
			'user'      => $user,
			'user_list' => array( 'list_ids' => array( $list ) ),
		);
		$data = apply_filters( 'optin_monster_pre_optin_mailpoet', $data, $_REQUEST, $list, null );

		// Save the subscriber. Check for MailPoet 3 first. Default to legacy.
		if ( class_exists( '\\MailPoet\\Config\\Initializer' ) ) {
			// Customize the lead data for MailPoet 3.
			if ( isset( $user['firstname'] ) ) {
				$user['first_name'] = $user['firstname'];
				unset( $user['firstname'] );
			}

			if ( isset( $user['lastname'] ) ) {
				$user['last_name'] = $user['lastname'];
				unset( $user['lastname'] );
			}

			if ( \MailPoet\Models\Subscriber::findOne( $user['email'] ) ) {
				try {
					\MailPoet\API\API::MP( 'v1' )->subscribeToList( $user['email'], $list );
				} catch ( Exception $e ) {
					// Do nothing.
				}
			} else {
				try {
					\MailPoet\API\API::MP( 'v1' )->addSubscriber( $user, array( $list ) );
				} catch ( Exception $e ) {
					// Do nothing.
				}
			}
		} else {
			$userHelper = WYSIJA::get( 'user', 'helper' );
			$userHelper->addSubscriber( $data );
		}

		// Send back a response.
		wp_send_json_success();
	}
}
