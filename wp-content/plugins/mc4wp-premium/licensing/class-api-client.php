<?php

namespace MC4WP\Licensing;

use Exception;

class Client {
	
	/** @var string */
	protected $base_url;

	/** @var string */
	protected $license_key;

	/**
	 * @param string $base_url
	 * @param string $license_key
	 * @throws Exception
	 */
	public function __construct( $base_url, $license_key = '' ) {
		$this->base_url = $base_url;
		$this->license_key = $license_key;
	}

	public function request( $method, $path, $data = array() ) {
		$url = $this->base_url . $path;
		$method = strtoupper( $method );
		$args = array(
			'method' => $method,
			'headers' => array(
				'Accepts' => 'application/json',
			),
		);

		// add license key if we have
		if( ! empty( $this->license_key ) ) {
			$args['headers']['Authorization'] = 'Bearer ' . urlencode( $this->license_key );
		}

		// add request data
		if( ! empty( $data ) ) {
			if( in_array( $method, array( 'GET', 'DELETE' ) ) ) {
				$url = add_query_arg( $data, $url );
			} else {
				$args['headers']['Content-Type'] = 'application/json';
				$args['body'] = json_encode( $data );
			}
		}

		$response = wp_remote_request( $url, $args );

		// check for errors
		if( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_code() . ': ' . $response->get_error_message() );
		}
		
		$body = wp_remote_retrieve_body( $response );
		if( empty( $body ) ) {
			return '';
		}

		$data = json_decode( $body );
		if( $data === null ) {
			throw new Exception( 'Error parsing API response: ' . $body );
		}

		// parse HTTP response
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		if( $response_code >= 400 ) {
			throw new ApiException( $response_message, $response_code, $data );
		}
	
		return $data;
	}
}
