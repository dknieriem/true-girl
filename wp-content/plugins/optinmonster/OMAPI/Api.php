<?php
/**
 * Api class.
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
 * Api class.
 *
 * @since 1.0.0
 */
class OMAPI_Api {

	/**
	 * Base API route.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $base = OPTINMONSTER_APP_URL;

	/**
	 * Current API route.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $route = false;

	/**
	 * Full API URL endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $url = false;

	/**
	 * Current API method.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $method = false;

	/**
	 * API Username.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $user = false;

	/**
	 * API Key.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $key = false;

	/**
	 * New API Key.
	 *
	 * @since 1.3.4
	 *
	 * @var bool|string
	 */
	public $apikey = false;

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $plugin = false;

	/**
	 * The Api Version (v1 or v2) for this request.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	public $version = 'v1';

	/**
	 * Additional data to add to request body
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $additional_data = array();

	/**
	 * The HTTP response array.
	 *
	 * @since 1.6.5
	 *
	 * @var null|array
	 */
	public $response = null;

	/**
	 * The HTTP response code.
	 *
	 * @since 1.6.5
	 *
	 * @var int
	 */
	public $response_code = 0;

	/**
	 * The parsed HTTP response body.
	 *
	 * @since 1.6.5
	 *
	 * @var mixed
	 */
	public $response_body = null;

	/**
	 * Builds the API Object
	 *
	 * @since 1.8.0
	 *
	 * @param string $version The Api Version (v1 or v2)
	 * @param string $endpoint The Api Endpoint
	 * @param string $method The Request method
	 *
	 * @return self
	 */
	public static function build( $version, $route, $method = 'POST' ) {
		$creds  = OMAPI::get_instance()->get_api_credentials();
		// Check if we have the new API and if so only use it
		if ( $creds['apikey'] ) {
			return new OMAPI_Api( $route, array( 'apikey' => $creds['apikey'] ), $method, $version );
		}

		return new OMAPI_Api( $route, array( 'user' => $creds['user'], 'key' => $creds['key'] ), $method, $version);
	}

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $route   The API route to target.
	 * @param array $creds    Array of API credentials.
	 * @param string $method  The API method.
	 * @param string $version The version number of our API
	 */
	public function __construct( $route, $creds, $method = 'POST', $version = 'v1' ) {
		// Set class properties.
		$this->route    = $route;
		$this->version  = $version;
		$this->method   = $method;
		$this->user     = ! empty( $creds['user'] ) ? $creds['user'] : '';
		$this->key      = ! empty( $creds['key'] ) ? $creds['key'] : '';
		$this->apikey   = ! empty( $creds['apikey'] ) ? $creds['apikey'] : '';
		$this->plugin   = OMAPI::get_instance()->plugin_slug;
	}

	/**
	 * Processes the API request.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed $value The response to the API call.
	 */
	public function request( $args = array() ) {
		// Build the body of the request.
		$body = array(
			'omapi-user' => $this->user,
			'omapi-key'  => $this->key
		);

		// If a plugin API request, add the data.
		if ( 'info' == $this->route || 'update' == $this->route ) {
			$body['omapi-plugin'] = $this->plugin;
		}

		// Add in additional data if needed.
		if ( ! empty( $this->additional_data ) ) {
			$body['omapi-data'] = maybe_serialize( $this->additional_data );
		}

		$body = wp_parse_args( $args, $body );
		$url  = in_array( $this->method, array( 'GET', 'DELETE' ), true )
			? add_query_arg( $body, $this->getUrl() )
			: $this->getUrl();
		$url  = esc_url_raw( $url );

		// Build the headers of the request.
		$headers = array(
			'Content-Type'          => 'application/x-www-form-urlencoded',
			'Cache-Control'         => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
			'Pragma'                => 'no-cache',
			'Expires'               => 0,
			'Origin'                => site_url(),
			'OMAPI-Referer'         => site_url(),
			'OMAPI-Sender'          => 'WordPress',
		);

		if ( $this->apikey ) {
			$headers['X-OptinMonster-ApiKey'] = $this->apikey;
		}

		// Setup data to be sent to the API.
		$data = array(
			'headers'   => $headers,
			'body'      => $body,
			'timeout'   => 3000,
			'sslverify' => false,
			'method'    => $this->method,
		);

		// Perform the query and retrieve the response.
		$this->response = wp_remote_request( $url, $data );

		// Bail out early if there are any errors.
		if ( is_wp_error( $this->response ) ) {
			return $this->response;
		}

		// Get the response code and response body.
		$this->response_code = wp_remote_retrieve_response_code( $this->response );
		$this->response_body = json_decode( wp_remote_retrieve_body( $this->response ) );

		// Get the correct success response code to check against.
		$response_code = 'DELETE' === $this->method ? 204 : 200;

		// If not a 200 status header, send back error.
		if ( $response_code != $this->response_code ) {
			$type  = ! empty( $this->response_body->type ) ? $this->response_body->type : 'api-error';
			$error = ! empty( $this->response_body->message ) ? stripslashes( $this->response_body->message ) : '';
			if ( empty( $error ) ) {
				$error = ! empty( $this->response_body->status_message ) ? stripslashes( $this->response_body->status_message ) : '';
			}
			if ( empty( $error ) ) {
				$error = ! empty( $this->response_body->error ) ? stripslashes( $this->response_body->error ) : 'unknown';
			}

			return new WP_Error( $type, sprintf( __( 'The API returned a <strong>%s</strong> response with this message: <strong>%s</strong>', 'optin-monster-api' ), $this->response_code, $error ) );
		}

		// Return the json decoded content.
		return $this->response_body;
	}

	/**
	 * The gets the URL based on our base, endpoint and version
	 *
	 * @since 1.8.0
	 *
	 * @return string The API url.
	 */
	public function getUrl() {
		return $this->base . '/' . $this->version . '/' . $this->route;
	}

	/**
	 * Sets a class property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The property to set.
	 * @param string $val The value to set for the property.
	 * @return mixed $value The response to the API call.
	 */
	public function set( $key, $val ) {
		$this->{$key} = $val;
	}

	/**
	 * Allow additional data to be passed in the request
	 *
	 * @since 1.0.0
	 *
	 * @param array $data
	 * return void
	 */
	public function set_additional_data( array $data ) {
		$this->additional_data = array_merge( $this->additional_data, $data );
	}
}
