<?php
/**
 * Rest API Class, where we register/execute any REST API Routes
 *
 * @since 1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest Api class.
 *
 * @since 1.8.0
 */
class OMAPI_Sites {

	/**
	 * The Base OMAPI Object
	 *
	 *  @since 1.8.0
	 *
	 * @var OMAPI
	 */
	protected $base;

	public function __construct( ) {
		$this->base = OMAPI::get_instance();
	}

	/**
	 * Refresh the site data.
	 *
	 * @since 1.8.0
	 *
	 * @param mixed $api_key If we want to use a custom API Key, pass it in
	 *
	 * @return array|null $sites An array of sites if the request is successful
	 */
	public function fetch( $api_key = null ) {
		$api = OMAPI_Api::build( 'v2', 'sites/origin', 'GET' );

		if ( $api_key ) {
			$api->set( 'apikey', $api_key );
		}

		$body  = $api->request();
		$sites = array();

		if ( ! is_wp_error( $body ) && ! empty( $body->data ) ) {
			foreach ( $body->data as $site ) {
				$sites[] = (int) $site->numericId;
			}
		}

		return $sites;
	}
}
