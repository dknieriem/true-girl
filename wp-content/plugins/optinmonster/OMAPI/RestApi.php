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
class OMAPI_RestApi {

	/**
	 * The Base OMAPI Object
	 *
	 *  @since 1.8.0
	 *
	 * @var OMAPI
	 */
	protected $base;

	/**
	 * The REST API Namespace
	 *
	 *  @since 1.8.0
	 *
	 * @var string The namespace
	 */
	protected $namespace = 'omapp/v1';

	public function __construct( ) {
		$this->base     = OMAPI::get_instance();
		$this->register_rest_routes();
	}

	/**
	 * Registers our Rest Routes for this App
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			$this->namespace,
			'/optins/refresh',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'fetch_campaigns' )
			)
		);
	}

	/**
	 * Refresh our campaigns
	 *
	 * @since 1.8.0
	 *
	 * @param WP_REST_Request The REST Request
	 * @return WP_REST_Response The API Response
	 */
	public function fetch_campaigns( $request ) {
		$header = $request->get_header( 'X-OptinMonster-ApiKey' );

		// Use this API Key to make a request
		if ( $this->validate_api_key( $header ) ) {

			$this->base->refresh->refresh();

			return new WP_REST_Response(
				array( 'message' => 'OK'),
				200
			);
		}

		return new WP_REST_Response(
			array( 'message' => 'Could not verify this API Key.'),
			401
		);
	}

	/**
	 * Validate this API Key
	 * We validate an API Key by fetching the Sites this key can fetch
	 * And then confirming that this key has access to at least one of these sites
	 *
	 * @since 1.8.0
	 *
	 * @param string $api_key
	 * @return bool True if the Key can be validated
	 */
	public function validate_api_key( $api_key ) {
		$option   = $this->base->get_option();
		$site_ids = ! empty( $option['siteIds'] ) ? $option['siteIds'] : array();

		if ( empty( $site_ids ) ) {
			return false;
		}

		$api_key_sites = $this->base->sites->fetch( $api_key );

		if ( empty( $api_key_sites ) ) {
			return false;
		}

		foreach ( $site_ids as $site_id ) {
			if ( in_array( $site_id, $api_key_sites ) ) {
				return true;
			}
		}

		return false;
	}
}
